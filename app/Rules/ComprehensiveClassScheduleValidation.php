<?php

namespace App\Rules;

use App\Models\ClassSchedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ComprehensiveClassScheduleValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    protected int $teacherId;
    protected int $studentId;
    protected string $timeStart;
    protected string $date;
    protected int $subject;
    protected ?int $ignoringId;

    public function __construct(int $teacherId, int $studentId, string $timeStart, string $date, int $subject, ?int $ignoringId = null)
    {
        $this->teacherId  = $teacherId;
        $this->studentId  = $studentId;
        $this->timeStart  = $timeStart;
        $this->date       = $date;
        $this->subject    = $subject;
        $this->ignoringId = $ignoringId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $timeStart  = Carbon::parse($this->timeStart)->format('H:i:s');
        $timeEnd    = Carbon::parse($value)->format('H:i:s');

        // dd($timeStart, $timeEnd);

        // 1. Ambil Data Esensial
        $teacher = Teacher::find($this->teacherId);
        $student = Student::with(['activePackage.package'])->whereId($this->studentId)->first();
        $subject = Subject::whereId($this->subject)->first();

        if (!$teacher || !$student) {
            $fail("Guru atau murid tidak ditemukan.");
            return;
        }

        // Tipe paket siswa: 'quota', 'monthly', 'group' (Dianggap sebagai Private: quota/monthly, Group: group)
        $packageType = $student->activePackage->package->type ?? null;

        // Ambil tipe mengajar guru sebagai array
        $teacherTypes = (array) $teacher->teaching_type;

        // --- VALIDASI AWAL: Kesesuaian Tipe Guru vs Tipe Paket (Disesuaikan untuk Array) ---

        // Cek Tipe Paket Private (quota/monthly)
        if (in_array($packageType, ['quota','monthly'])) {
            // Jika paketnya Private, guru HARUS memiliki tipe 'private'
            if (!in_array('private', $teacherTypes)) {
                $fail("Paket Private (Quota/Monthly) harus diajarkan oleh guru yang memiliki tipe 'private'.");
                return;
            }
        }

        // Cek Tipe Paket Group (group)
        else if ($packageType === 'group') {
            // Jika paketnya Group, guru HARUS memiliki tipe 'group'
            if (!in_array('group', $teacherTypes)) {
                $fail("Paket Group harus diajarkan oleh guru yang memiliki tipe 'group'.");
                return;
            }
        }
        // Catatan: Jika packageType null/tidak dikenal, validasi ini akan dilewati.

        // --- QUERY OVERLAPPING JADWAL (Guru ATAU Siswa) ---

        // Query tidak berubah, karena logika overlap waktu tetap sama.
        $existingSchedules = ClassSchedule::where('date', $this->date)
            ->where(function ($q) {
                // Bentrok pada guru ATAU siswa
                $q->where('teacher_id', $this->teacherId)
                  ->orWhere('student_id', $this->studentId);
            })
            ->where(function ($q) use ($timeEnd, $timeStart) {
                // Logika Overlapping Waktu: (New Start < Existing End) AND (New End > Existing Start)
                $q->where('time_start', '<', $timeEnd)

                  ->where('time_end', '>', $timeStart);
            })
            ->when($this->ignoringId, function ($query, $id) {
                return $query->where('id', '!=', $id);
            })
            ->get();


        // --- ANALISIS JADWAL YANG OVERLAPPING ---

        foreach ($existingSchedules as $schedule) {

            $existingPackageType = $schedule->student->activePackage->package->type ?? null;
            $isOverlapOnTeacher = ($schedule->teacher_id == $this->teacherId);
            $isOverlapOnStudent = ($schedule->student_id == $this->studentId);

            // dd($existingPackageType, $isOverlapOnStudent, $isOverlapOnTeacher, $packageType);
            // 1. Cek Konflik Sisi SISWA
            if ($isOverlapOnStudent) {
                 $fail("Konflik jadwal: Siswa sudah memiliki kelas lain pada waktu ini.");
                 return;
            }

            // 2. Cek Konflik Sisi GURU
            if ($isOverlapOnTeacher) {

                // KASUS A: PRIVATE CLASS (VR 1: Tidak boleh Overlap)
                // Jika jadwal baru/lama adalah Private (quota/monthly), maka GAGAL.
                // Ini memastikan guru yang bisa mengajar Private (walau dia juga bisa Group) tidak memiliki jadwal bentrok.
                if (in_array($existingPackageType, ['quota','monthly']) || in_array($packageType, ['quota','monthly'])) {
                    $fail("Konflik jadwal: Slot waktu bertabrakan dengan jadwal Private guru (Private tidak boleh overlapping).");
                    return;
                }

                // KASUS B: GROUP CLASS (VR 3: Harus sama persis jika ingin bergabung)
                // Pengecekan ini hanya tercapai jika keduanya BUKAN Private, artinya keduanya Group.
                if ($existingPackageType === 'group' && $packageType === 'group') {
                    // dd($existingPackageType, $packageType);


                    // Cek apakah waktu dan subjek SAMA PERSIS
                    $isTimeMatch    = ($schedule->time_start === $timeStart && $schedule->time_end === $timeEnd);
                    $isSubjectMatch = ($schedule->subject->name === $subject->name); // Asumsi $this->subject sudah berisi string subjek

                    // Student Group hanya bisa bergabung jika waktu kelas SAMA PERSIS.
                    // dd($schedule->time_start , $timeStart , $schedule->time_end , $timeEnd);
                    // dd($isTimeMatch && $isSubjectMatch, $isTimeMatch , $isSubjectMatch, $schedule->time_start , $timeStart, $schedule->time_end , $timeEnd, $schedule->subject->name  , $subject->name );
                    // dd(!($schedule->time_start === $timeStart && $schedule->time_end === $timeEnd));
                    // Jika WAKTU SAMA dan SUBJEK SAMA:
                    if ($isTimeMatch && $isSubjectMatch) {
                        // Kondisi BERGABUNG. Izinkan dan lanjutkan ke jadwal lain (jika ada).
                        continue;
                    }

                    // Jika tidak lolos kondisi BERGABUNG, itu berarti ada konflik.
                    // Konflik ini bisa terjadi karena:
                    // 1. Waktu berbeda (walau overlapping), ATAU
                    // 2. Subjek berbeda (walau waktu sama)

                    // Karena ini adalah Group Class dan terjadi overlapping, dan siswa TIDAK bergabung ke kelas yang sama persis,
                    // maka pendaftaran harus DITOLAK.
                    $fail("Konflik Group Class: Guru sedang mengajar jadwal Group lain yang berbeda subjek atau waktu. Siswa Group harus bergabung ke jadwal yang SAMA PERSIS (Waktu & Subjek) yang sudah ada.");
                    return;
                }
            }
        }

        // Jika lolos semua cek
    }
}

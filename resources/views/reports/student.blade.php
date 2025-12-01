<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Perkembangan Belajar</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin-bottom: 5px;
            text-transform: uppercase;
            font-size: 18px;
            letter-spacing: 1px;
            color: #2c3e50;
        }

        .info-box {
            background: #f7f7f7;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }

        .info-box p {
            margin: 4px 0;
        }

        hr {
            border: none;
            border-top: 1px solid #aaa;
            margin: 15px 0;
        }

        h3 {
            color: #34495e;
            margin-top: 20px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }

        .alert {
            padding: 10px;
            background: #ffe9c7;
            border: 1px solid #d4981a;
            margin-top: 15px;
            text-align: center;
            font-weight: bold;
            border-radius: 4px;
            color: #8b6e23;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #e5e7eb;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            font-size: 11px;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px 6px;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Kelas untuk lebar kolom yang spesifik */
        .col-date {
            width: 100px;
        }
        .col-time {
            width: 80px;
        }
        .col-class {
            width: 100px;
        }
        .col-teacher {
            width: 120px;
        }
        .col-notes {
            /* Menetapkan lebar spesifik untuk catatan */
            width: 150px;
        }
        /* col-feedback dihapus agar kolom ini mengambil sisa ruang */
    </style>
</head>
<body>

<div class="header">
    <h2>Report Perkembangan Belajar</h2>
</div>

<div class="info-box">
    <p><strong>Nama Siswa:</strong> {{ $student->user->name }}</p>
    <p><strong>Total Paket:</strong> {{ $totalPackage }} sesi</p>
    <p><strong>Dipakai:</strong> {{ $usedQuota }} sesi</p>
    <p><strong>Sisa Kuota:</strong> {{ $remainingQuota }} sesi</p>
</div>

<hr>

<h3>Riwayat Kelas</h3>

@if ($classHistory->isEmpty())
    <div class="alert">
        Belum ada riwayat kelas yang tersedia untuk siswa ini.
    </div>
@else
    <table>
        <thead>
            <tr>
                <th class="col-date">Tanggal</th>
                <th class="col-time">Waktu Belajar</th>
                <th class="col-class">Kelas</th>
                <th class="col-teacher">Pengajar</th>
                <th class="col-notes">Catatan</th> <th>Feedback Guru</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($classHistory as $class)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($class->classSchedule->date)->format('d M Y') }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($class->classSchedule->time_start)->format('H:i') }}
                        -
                        {{ \Carbon\Carbon::parse($class->classSchedule->time_end)->format('H:i') }}
                    </td>
                    <td>{{ $class->classSchedule->subject->name ?? '-' }}</td>
                    <td>{{ $class->classSchedule->teacher->user->name ?? '-' }}</td>
                    <td>{{ $class->notes ?? '-' }}</td>
                    <td>{{ $class->teacher_feedback ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

</body>
</html>

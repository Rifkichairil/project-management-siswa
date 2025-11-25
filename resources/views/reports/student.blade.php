<!doctype html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; }
        h2 { margin-bottom: 0; }
    </style>
</head>
<body>

<h2>📘 Report Perkembangan Belajar</h2>
<p><strong>Nama Siswa:</strong> {{ $student->name }}</p>
<p><strong>Total Paket:</strong> {{ $totalPackage }} sesi</p>
<p><strong>Dipakai:</strong> {{ $usedQuota }} sesi</p>
<p><strong>Sisa Kuota:</strong> {{ $remainingQuota }} sesi</p>

<hr>

<h3>📍 Riwayat Kelas</h3>

<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Topik</th>
            <th>Progress</th>
            <th>Catatan</th>
            <th>Feedback Guru</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($classHistory as $class)
            <tr>
                <td>{{ $class->date }}</td>
                <td>{{ $class->classReport->topic ?? '-' }}</td>
                <td>{{ $class->classReport->progress ?? '-' }}</td>
                <td>{{ $class->classReport->notes ?? '-' }}</td>
                <td>{{ $class->classReport->teacher_feedback ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>

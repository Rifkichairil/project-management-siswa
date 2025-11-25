<!doctype html>
<html>
<head>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
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
        }

        .info-box {
            background: #f7f7f7;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .info-box p {
            margin: 4px 0;
        }

        hr {
            border: none;
            border-top: 1px solid #aaa;
            margin: 15px 0;
        }

        .alert {
            padding: 10px;
            background: #ffe9c7;
            border: 1px solid #d4981a;
            margin-top: 15px;
            text-align: center;
            font-weight: bold;
            border-radius: 4px;
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
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
        }
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
            <th style="width: 90px;">Tanggal</th>
            <th style="width: 100px;">Topik</th>
            <th style="width: 80px;">Progress</th>
            <th>Catatan</th>
            <th style="width: 120px;">Feedback Guru</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($classHistory as $class)
            <tr>
                <td>{{ \Carbon\Carbon::parse($class->date)->format('d M Y') }}</td>
                <td>{{ $class->classReport->topic ?? '-' }}</td>
                <td>{{ $class->classReport->progress ?? '-' }}</td>
                <td>{{ $class->classReport->notes ?? '-' }}</td>
                <td>{{ $class->classReport->teacher_feedback ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Informasi Tamu</title>
    <link href="{{ asset('css/tabler.min.css') }}" rel="stylesheet">
    <style>
        .center-message {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 50vh;
            font-size: 1.5rem;
            color: #6c757d;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
        }
        .table th, .table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
        }
        .table tbody + tbody {
            border-top: 2px solid #dee2e6;
        }
        .table-sm th, .table-sm td {
            padding: 0.3rem;
        }
        .table-bordered {
            border: 1px solid #dee2e6;
        }
        .table-bordered th, .table-bordered td {
            border: 1px solid #dee2e6;
        }
        .table-bordered thead th, .table-bordered thead td {
            border-bottom-width: 2px;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }
        .table-hover tbody tr:hover {
            color: #212529;
            background-color: rgba(0, 0, 0, 0.075);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center my-4">Informasi Tamu</h2>

        @if($guest)
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover text-center">
                    <thead>
                        <tr>
                            <th>NIK</th>
                            <th>Kartu Akses</th>
                            <th>Lokasi Kunjungan</th>
                            <th>Terakhir Keluar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $guest->nik }}</td>
                            <td>{{ $guest->access_card ?? 'Belum ada' }}</td>
                            <td>{{ $guest->visit_location ?? 'Belum ada' }}</td>
                            <td>{{ $guest->jam_keluar ? date('Y-m-d', strtotime($guest->jam_keluar)) : 'Belum pernah' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <div class="center-message">
                Data tersebut tidak ada di dalam sistem. Silahkan mengisi guestbook.
            </div>
        @endif

        <div class="text-center mt-3">
            <a href="{{ url('/dashboard') }}" class="btn btn-primary">Kembali</a>
        </div>
    </div>
</body>
</html>

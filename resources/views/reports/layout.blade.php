<!DOCTYPE html>
<html lang="fr">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $title ?? 'Rapport Brillio' }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 10px;
        }

        .header h1 {
            color: #4f46e5;
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 14px;
        }

        .info-section {
            margin-bottom: 20px;
        }

        .info-grid {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-grid td {
            padding: 5px 0;
        }

        .label {
            font-weight: bold;
            color: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background-color: #f9fafb;
            color: #374151;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 600;
        }

        .status-completed {
            background-color: #def7ec;
            color: #03543f;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-cancelled {
            background-color: #fde8e8;
            color: #9b1c1c;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $organization->name }}</h1>
        <p>{{ $title ?? 'Rapport d\'activité' }}</p>
        <p>Généré le {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @yield('content')

    <div class="footer">
        Document généré par la plateforme Brillio - &copy; {{ date('Y') }}
    </div>
</body>

</html>
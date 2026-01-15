<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Messages de Contact</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        h1 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
            font-size: 11px;
        }

        .new {
            background-color: #fef3c7;
        }

        .replied {
            background-color: #d1fae5;
        }

        .message {
            font-size: 10px;
            max-width: 300px;
        }
    </style>
</head>

<body>
    <h1>Messages de Contact</h1>
    <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    <p>Total: {{ $messages->count() }} messages</p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Sujet</th>
                <th>Statut</th>
                <th>Message</th>
            </tr>
        </thead>
        <tbody>
            @foreach($messages as $message)
                <tr class="{{ $message->status }}">
                    <td>{{ $message->created_at->format('d/m/Y') }}</td>
                    <td>{{ $message->name }}</td>
                    <td>{{ $message->email }}</td>
                    <td>{{ Str::limit($message->subject, 30) }}</td>
                    <td>{{ ucfirst($message->status) }}</td>
                    <td class="message">{{ Str::limit($message->message, 100) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Brillio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center bg-white p-8 rounded-2xl shadow-xl border border-gray-100">
        <div class="mb-6 flex justify-center">
            <div class="h-20 w-20 bg-indigo-50 rounded-full flex items-center justify-center text-indigo-600">
                @yield('icon')
            </div>
        </div>

        <h1 class="text-4xl font-bold text-gray-900 mb-2">@yield('code')</h1>
        <h2 class="text-xl font-semibold text-gray-700 mb-4">@yield('message')</h2>

        <p class="text-gray-500 mb-8">
            @yield('description')
        </p>

        <a href="{{ url('/') }}"
            class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150 ease-in-out w-full">
            Retour à l'accueil
        </a>
    </div>
</body>

</html>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <link rel="icon" href="/img/logo.png">
    @vite(['resources/css/spa.css', 'resources/js/spa/main.jsx'])
</head>
<body class="bg-slate-50">
    <div id="root"></div>
</body>
</html>

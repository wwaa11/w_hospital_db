<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset("js/jquery.min.js") }}"></script>
    <script src="{{ asset("js/tableToExcel.js") }}"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(["resources/css/app.css", "resources/js/app.js"])
    <title>WEB Query</title>
</head>

<body>
    <div class="p-3">
        @yield("content")
    </div>
</body>
@yield("scripts")

</html>

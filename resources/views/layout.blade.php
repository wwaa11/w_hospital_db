<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/tableToExcel.js') }}"></script>
    @vite('resources/css/app.css')
    <title>WEB Query</title>
</head>

<body>
    <div class="p-3">
        @yield('content')
    </div>
</body>
@yield('scripts')

</html>

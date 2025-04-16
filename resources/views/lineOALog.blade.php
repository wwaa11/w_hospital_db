@extends('layout')
@section('content')
    <div class="w-2/3 m-auto flex p-3">
        <div class="flex-grow text-center gap-6">
            <input class="border rounded p-3" id="start" type="date" value="{{ $dateQuery['start'] }}">
            <input class="border rounded p-3" id="end" type="date" value="{{ $dateQuery['end'] }}">
            <button class="p-3 text-center w-32 border border-blue-600 rounded" onclick="query()">Query</button>
        </div>
        <div class="flex-grow text-center">
            <button class="w-2/6 border-4 border-green-400 text-green-400 rounded-s p-3 m-auto"
                onclick="exportFN()">Export</button>
        </div>
    </div>
    <table class="w-2/3 m-auto border-collapse border" id="tabel">
        <tr>
            <td colspan="3" class="border-collapse border text-center p-3">Query DateTime : {{ $date }}</td>
            <td colspan="3" class="border-collapse border text-center p-3">Form {{ $dateQuery['start'] }} to
                {{ $dateQuery['end'] }}</td>
        </tr>
        <tr>
            <td class="p-3 border-collapse border text-center">Date</td>
            <td class="p-3 border-collapse border text-center">HN</td>
            <td class="p-3 border-collapse border text-center">Name</td>
            <td class="p-3 border-collapse border text-center">Ref</td>
            <td class="p-3 border-collapse border text-center">Phone</td>
            <td class="p-3 border-collapse border text-center">LineID</td>
            <td class="p-3 border-collapse border text-center">Email</td>
        </tr>
        @foreach ($output as $index => $hn)
            <tr>
                <td class="border-collapse border text-center">{{ $hn['Date'] }}</td>
                <td class="border-collapse border text-center">{{ $hn['HN'] }}</td>
                <td class="border-collapse border text-center">{{ $hn['Name'] }}</td>
                <td class="border-collapse border text-center">{{ $hn['Ref'] }}</td>
                <td class="border-collapse border text-center">{{ $hn['Phone'] }}</td>
                <td class="border-collapse border text-center">{{ $hn['LineID'] }}</td>
                <td class="border-collapse border text-center">{{ $hn['email'] }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="5"></td>
            <td class="text-center">Total : {{ count($output) }}</td>
        </tr>
    </table>
    </div>
@endsection
@section('scripts')
    <script>
        function exportFN() {
            TableToExcel.convert(document.getElementById("tabel"));
        }

        function query() {
            var start = $('#start').val();
            var end = $('#end').val();
            window.location.href = '{{ env('APP_URL') }}/line?start=' + start + '&end=' + end
        }
    </script>
@endsection

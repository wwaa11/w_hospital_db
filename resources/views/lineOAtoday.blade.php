@extends('layout')
@section('content')
    <div class="flex p-3">
        <button class="w-1/6 border-4 border-green-400 text-green-400 rounded-s p-3 m-auto"
            onclick="exportFN()">Export</button>
    </div>
    <table class="w-2/3 m-auto border-collapse border" id="tabel">
        <tr>
            <td colspan="5" class="border-collapse border text-center p-3">{{ $dateForm }} - {{ $dateTO }}</td>
        </tr>
        <tr>
            <td class="p-3 border-collapse border text-center">Date</td>
            <td class="p-3 border-collapse border text-center">HN</td>
            <td class="p-3 border-collapse border text-center">RightCode</td>
            <td class="p-3 border-collapse border text-center">LineID</td>
            <td class="p-3 border-collapse border text-center">valid ID</td>
        </tr>
        @foreach ($output as $index => $hn)
            <tr>
                <td class="border-collapse border text-center">{{ $hn['Date'] }}</td>
                <td class="border-collapse border">{{ $hn['HN'] }}</td>
                <td class="border-collapse border">{{ $hn['Right'] }}</td>
                <td class="border-collapse border">{{ $hn['Line'] }}</td>
                <td class="border-collapse border text-center">{{ $hn['Err'] }}</td>
            </tr>
        @endforeach
    </table>
    </div>
@endsection
@section('scripts')
    <script>
        function exportFN() {
            TableToExcel.convert(document.getElementById("tabel"));
        }
    </script>
@endsection

@extends('layout')
@section('content')
    <div class="flex p-3">
        <button class="border-4 border-green-400 text-green-400 rounded-s p-3 m-auto" onclick="exportFN()">Export</button>
    </div>
    <table class="w-2/3 m-auto border-collapse border" id="tabel">
        <tr>
            <td class="p-3 border-collapse border text-center">HN</td>
            <td class="p-3 border-collapse border text-center">RefNoType</td>
            <td class="p-3 border-collapse border text-center">IDCardType</td>
            <td class="p-3 border-collapse border text-center">RefNo</td>
            <td class="p-3 border-collapse border text-center">RefIssueBy</td>
            <td class="p-3 border-collapse border text-center">LineID</td>
        </tr>
        @foreach ($output as $index => $hn)
            <tr>
                <td class="border-collapse border text-center">{{ $hn->HN }}</td>
                <td class="border-collapse border text-center">{{ $hn->RefNoType }}</td>
                <td class="border-collapse border text-center">{{ $hn->IDCardType }}</td>
                <td class="border-collapse border text-center">{{ $hn->RefNo }}</td>
                <td class="border-collapse border text-center">{{ $hn->RefIssueBy }}</td>
                <td class="border-collapse border text-center">{{ $hn->LineID }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="4"></td>
            <td colspan="2" class="text-center">( {{ date('d M Y') }} ) Total : {{ count($output) }}</td>
        </tr>
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

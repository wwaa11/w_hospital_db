@extends('layout')
@section('content')
    <div class="w-2/3 m-auto flex py-3 border-t-2 border-x-2">
        <div class="flex-grow gap-6">
            <div class="ms-3">Total : {{ count($data) }}</div>
        </div>
        <div class="flex-grow text-end me-3">
            <button class="w-36 border-2 border-[#78c0b0] text-[#78c0b0] rounded-s p-3 m-auto"
                onclick="exportFN()">Export</button>
        </div>
    </div>
    <table class="w-2/3 m-auto border-collapse border" id="tabel">
        <tr>
            <td class="p-3 border-collapse border text-center">Visitdate</td>
            <td class="p-3 border-collapse border text-center">VN</td>
            <td class="p-3 border-collapse border text-center">HN</td>
            <td class="p-3 border-collapse border text-center">Name</td>
            <td class="p-3 border-collapse border text-center">Clinic</td>
            <td class="p-3 border-collapse border text-center">RefToHospital</td>
            <td class="p-3 border-collapse border text-center">CloseVisitCode</td>
        </tr>
        @foreach ($data as $index => $hn)
            <tr>
                <td class="border-collapse border p-2">{{ $hn->Visitdate }}</td>
                <td class="border-collapse border p-2">{{ $hn->VN }}</td>
                <td class="border-collapse border p-2">{{ $hn->HN }}</td>
                <td class="border-collapse border p-2">{{ $hn->fullname }}</td>
                <td class="border-collapse border p-2">{{ $hn->Clinic }}</td>
                <td class="border-collapse border p-2">{{ $hn->RefToHospital }}</td>
                <td class="border-collapse border p-2">{{ $hn->CloseVisitCode }}</td>
            </tr>
        @endforeach
    </table>
@endsection
@section('scripts')
    <script>
        function exportFN() {
            TableToExcel.convert(document.getElementById("tabel"));
        }

        function query() {
            var start = $('#start').val();
            var end = $('#end').val();
            window.location.href = '{{ env('APP_URL') }}/a7z94Xray?start=' + start + '&end=' + end
        }
    </script>
@endsection

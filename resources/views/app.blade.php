@extends('layout')
@section('content')
    <div class="w-2/3 m-auto flex py-3 border-t-2 border-x-2">
        <div class="flex-grow text-end me-3">
            <button class="w-36 border-2 border-[#78c0b0] text-[#78c0b0] rounded-s p-3 m-auto"
                onclick="exportFN()">Export</button>
        </div>
    </div>
    <table class="w-2/3 m-auto border-collapse border" id="tabel">
        <tr>
            <td colspan="3" class="border-collapse border text-center p-3">Query DateTime : {{ date('d-m-Y H:i:s') }}
            </td>
        </tr>
        <tr>
            <td class="p-3 border-collapse border text-center">AppointmentNo</td>
            <td class="p-3 border-collapse border text-center">HN</td>
            <td class="p-3 border-collapse border text-center">AppointDateTime</td>
        </tr>
        @foreach ($apps as $index => $item)
            <tr>
                <td class="p-3 border-collapse border text-center">{{ $item->AppointmentNo }}</td>
                <td class="p-3 border-collapse border text-center">{{ $item->HN }}</td>
                <td class="p-3 border-collapse border text-center">{{ $item->date }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="5"></td>
            <td class="text-center p-3">Total : {{ count($apps) }}</td>
        </tr>
    </table>
@endsection
@section('scripts')
    <script>
        function exportFN() {
            TableToExcel.convert(document.getElementById("tabel"));
        }
    </script>
@endsection

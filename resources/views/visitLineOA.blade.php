@extends('layout')
@section('content')
    <div class="flex p-3">
        <button class="border-4 border-green-400 text-green-400 rounded-s p-3 m-auto" onclick="exportFN()">Export</button>
    </div>
    <table class="w-2/3 m-auto border-collapse border" id="tabel">
        <tr>
            <td class="p-3 border-collapse border text-center">Date</td>
            <td class="p-3 border-collapse border text-center">ทั้งหมด</td>
            <td class="p-3 border-collapse border text-center">ไม่มี LineID</td>
            <td class="p-3 border-collapse border text-center">มี LineID</td>
        </tr>
        @foreach ($outputData as $index => $date)
            <tr>
                <td class="border-collapse border text-center">{{ $index }}</td>
                <td class="border-collapse border text-center">{{ $date['total'] }}</td>
                <td class="border-collapse border text-center">{{ $date['onLine'] }} ( {{ $date['onLine%'] }}%
                    )
                </td>
                <td class="border-collapse border text-center">{{ $date['inLine'] }} ( {{ $date['inLine%'] }}%
                    )
                </td>
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

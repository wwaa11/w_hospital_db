@extends("layouts.app")
@section('content')
    <div class="w-2/3 m-auto flex py-3 border-t-2 border-x-2">
        <div class="flex-grow text-end me-3">
            <button class="w-36 border-2 border-[#78c0b0] text-[#78c0b0] rounded-s p-3 m-auto"
                onclick="exportFN()">Export</button>
        </div>
    </div>
    <table class="w-2/3 m-auto border-collapse border" id="tabel">
        @foreach ($data as $type => $items)
            <tr>
                <td colspan="4" class="border-collapse border text-center p-3">{{ $type }}</td>
            </tr>
            @foreach ($items as $item)
            <tr>
                <td class="p-3 border-collapse border text-center">{{ $item->AppointmentNo }}</td>
                <td class="p-3 border-collapse border text-center">{{ $item->AppointDateTime }}</td>
                <td class="p-3 border-collapse border text-center">{{ $item->HN }}</td>
                <td class="p-3 border-collapse border ">{{ $item->FullName }}</td>
            </tr>
            @endforeach
        @endforeach
    </table>
@endsection
@section('scripts')
    <script>
        function exportFN() {
            TableToExcel.convert(document.getElementById("tabel"));
        }
    </script>
@endsection

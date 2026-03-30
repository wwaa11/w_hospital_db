@extends('layouts.app')
@section('content')
    <div class="w-2/3 m-auto flex py-3 border-t-2 border-x-2">
        <div class="flex-grow text-end me-3">
            <button class="w-36 border-2 border-[#78c0b0] text-[#78c0b0] rounded-s p-3 m-auto"
                onclick="exportFN()">Export</button>
        </div>
    </div>
    <table class="w-2/3 m-auto border-collapse border" id="tabel">
        <tr>
            <td class="p-3 border-collapse border text-center">HN</td>
            <td class="p-3 border-collapse border text-center">PDPA_4</td>
            <td class="p-3 border-collapse border text-center">Status</td>
        </tr>
        @foreach ($datas as $index => $item)
            <tr>
                <td class="p-3 border-collapse border text-center">{{ $item->HN }}</td>
                <td class="p-3 border-collapse border text-center">{{ $item->PDPA_4 }}</td>
                <td class="p-3 border-collapse border text-center">
                    @switch($item->PDPA_4)
                        @case(1)
                        @case(3)
                            อนุญาต
                        @break

                        @case(2)
                            ไม่อนุญาต
                        @break

                        @default
                            ไม่ระบุ
                    @endswitch
                </td>
            </tr>
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

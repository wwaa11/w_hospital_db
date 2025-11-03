@extends("layout")
@section("content")
    <div class="m-auto flex w-2/3 border-x-2 border-t-2 py-3">
        <div class="me-3 flex-grow text-end">
            <button class="m-auto w-36 rounded-s border-2 border-[#78c0b0] p-3 text-[#78c0b0]" onclick="exportFN()">Export</button>
        </div>
    </div>
    <table class="m-auto w-2/3 border-collapse border" id="tabel">
        <tr>
            <td class="border-collapse border p-3 text-center" colspan="7">Query DateTime : {{ date("d-m-Y H:i:s") }}
            </td>
        </tr>
        <tr>
            <td class="border-collapse border p-3 text-center">HN</td>
            <td class="border-collapse border p-3 text-center">LAB-EntryDateTime</td>
            <td class="border-collapse border p-3 text-center">LAB-RequestNo</td>
            <td class="border-collapse border p-3 text-center">LAB-FacilityRmsNo</td>
            <td class="border-collapse border p-3 text-center">Xray-EntryDateTime</td>
            <td class="border-collapse border p-3 text-center">Xray-RequestNo</td>
            <td class="border-collapse border p-3 text-center">Xray-FacilityRmsNo</td>
        </tr>
        @foreach ($datas as $index => $item)
            <tr>
                <td class="border-collapse border p-3 text-center">{{ $item["HN"] }}</td>
                <td class="border-collapse border p-3 text-center">{{ $item["labEntryDateTime"] }}</td>
                <td class="border-collapse border p-3 text-center">{{ $item["labRequestNo"] }}</td>
                <td class="border-collapse border p-3 text-center">{{ $item["labFacilityRmsNo"] }}</td>
                <td class="border-collapse border p-3 text-center">{{ $item["xrayEntryDateTime"] }}</td>
                <td class="border-collapse border p-3 text-center">{{ $item["xrayRequestNo"] }}</td>
                <td class="border-collapse border p-3 text-center">{{ $item["xrayFacilityRmsNo"] }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="5"></td>
            <td class="p-3 text-center">Total : {{ count($datas) }}</td>
        </tr>
    </table>
@endsection
@section("scripts")
    <script>
        function exportFN() {
            TableToExcel.convert(document.getElementById("tabel"));
        }
    </script>
@endsection

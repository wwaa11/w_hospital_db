@extends("layout")
@section("content")
    <div class="m-auto flex w-2/3 border-x-2 border-t-2 py-3">
        <div class="me-3 flex-grow text-end">
            <button class="m-auto w-36 rounded-s border-2 border-[#78c0b0] p-3 text-[#78c0b0]" onclick="exportFN()">Export</button>
        </div>
    </div>
    <table class="m-auto w-2/3 border-collapse border" id="tabel">
        <tr>
            <td class="border-collapse border p-3 text-center" colspan="5">Query DateTime : {{ date("d-m-Y H:i:s") }}
            </td>
        </tr>
        <tr>
            <td class="border-collapse border p-3 text-center">HN</td>
            <td class="border-collapse border p-3 text-center">Date</td>
            <td class="border-collapse border p-3 text-center">Name</td>
            <td class="border-collapse border p-3 text-center">Clinic</td>
            <td class="border-collapse border p-3 text-center">Doctor</td>
        </tr>
        @foreach ($data as $item)
            @foreach ($item["clinic"] as $index => $clinic)
                <tr @if ($item["count"] > 1) class="bg-red-500" @endif>
                    <td class="border-collapse border p-3 text-center" @if ($item["count"] > 1) data-fill-color="FFFF0000" @endif>{{ $item["hn"] }}</td>
                    <td class="border-collapse border p-3 text-center" @if ($item["count"] > 1) data-fill-color="FFFF0000" @endif>{{ $item["date"] }}</td>
                    <td class="border-collapse border p-3 text-center" @if ($item["count"] > 1) data-fill-color="FFFF0000" @endif>{{ $item["name"] }}</td>
                    <td class="border-collapse border p-3 text-center" @if ($item["count"] > 1) data-fill-color="FFFF0000" @endif>{{ $clinic["clinic"] }}</td>
                    <td class="border-collapse border p-3 text-center" @if ($item["count"] > 1) data-fill-color="FFFF0000" @endif>{{ $clinic["doctor"] }}</td>
                </tr>
            @endforeach
        @endforeach
        <tr>
            <td colspan="4"></td>
            <td class="p-3 text-center">Total : {{ count($data) }}</td>
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

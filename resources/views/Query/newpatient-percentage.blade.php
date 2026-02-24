@extends("layouts.app")
@section("content")
    <div class="overflow-x-auto">
        <button class="items-center rounded bg-red-400 px-6 py-2 text-white hover:bg-red-500" type="button" onclick="exportFN()">Export</button>
        <table class="m-auto w-full max-w-6xl border-collapse overflow-hidden rounded border text-sm shadow" id="tabel">
            <thead class="bg-blue-100">
                <tr>
                    <th class="border p-2">Month</th>
                    <th class="border p-2">Total New Patient</th>
                    <th class="border p-2">SSB</th>
                    <th class="border p-2">Online</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $month => $item)
                    <tr class="transition hover:bg-blue-50">
                        <td class="border text-center">{{ $month }}</td>
                        <td class="border">{{ $item["total"] }}</td>
                        <td class="border">{{ $item["ssb"] }} ({{ $item["ssb-percent"] }})</td>
                        <td class="border">{{ $item["online"] }} ({{ $item["online-percent"] }})</td>
                    </tr>
                    @foreach ($item["online-type"] as $type => $typeItem)
                        <tr class="transition hover:bg-blue-50">
                            <td class="border" colspan="2"></td>
                            <td class="border text-center">{{ $type }}</td>
                            <td class="border">{{ $typeItem["count"] }} ({{ $typeItem["percent"] }})</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
@section("scripts")
    <script>
        function exportFN() {
            TableToExcel.convert(document.getElementById("tabel"));
        }
    </script>
@endsection

@extends("layouts.app")
@section("content")
    <form class="m-auto mb-6 w-full max-w-3xl rounded bg-white p-6 shadow" method="get" action="{{ route("depress") }}">
        <div class="mb-4">
            <h2 class="mb-2 text-lg font-semibold text-blue-700">NewCase Date Range</h2>
            <div class="flex flex-wrap gap-4">
                <label class="flex flex-col">
                    <span class="mb-1">Start Date</span>
                    <input class="rounded border p-2" type="date" name="startdate" value="{{ request("startdate", $date["date_1_form"] ?? "") }}" required>
                </label>
                <label class="flex flex-col">
                    <span class="mb-1">End Date</span>
                    <input class="rounded border p-2" type="date" name="enddate" value="{{ request("enddate", $date["date_1_to"] ?? "") }}" required>
                </label>
            </div>
        </div>
        <div class="mb-4">
            <h2 class="mb-2 text-lg font-semibold text-blue-700">MDD Date Range</h2>
            <div class="flex flex-wrap gap-4">
                <label class="flex flex-col">
                    <span class="mb-1">Start Date</span>
                    <input class="rounded border p-2" type="date" name="recoverystartdate" value="{{ request("recoverystartdate", $date["date_2_form"] ?? "") }}" required>
                </label>
                <label class="flex flex-col">
                    <span class="mb-1">End Date</span>
                    <input class="rounded border p-2" type="date" name="recoveryenddate" value="{{ request("recoveryenddate", $date["date_2_to"] ?? "") }}" required>
                </label>
            </div>
        </div>
        <div class="mt-4 flex gap-4">
            <button class="rounded bg-blue-600 px-6 py-2 text-white hover:bg-blue-700" type="submit">Filter</button>
            <a class="rounded bg-gray-300 px-6 py-2 text-gray-800 hover:bg-gray-400" href="/depress">Reset</a>
        </div>
    </form>
    <div class="flex p-3">
        <button class="m-auto w-1/6 rounded-s border-4 border-green-400 p-3 text-green-400 transition hover:bg-green-50" onclick="exportFN()">Export</button>
    </div>
    <div class="overflow-x-auto">
        <table class="m-auto w-full max-w-3xl border-collapse overflow-hidden rounded border text-sm shadow" id="tabel">
            <thead class="bg-blue-100">
                <tr>
                    <th class="border p-2">#</th>
                    <th class="border p-2">Date</th>
                    <th class="border p-2">HN</th>
                    <th class="border p-2">Name</th>
                    <th class="border p-2">Clinic</th>
                    <th class="border p-2">Doctor</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $label => $item)
                    @switch($label)
                        @case(1)
                            <tr class="bg-blue-50">
                                <td class="p-3 font-semibold text-blue-800" colspan="6">New Case Clinic Mind Center , Mind Center - Telemedicine
                                    {{ $date["date_1_form"] }} - {{ $date["date_1_to"] }}
                                </td>
                            </tr>
                            <tr class="bg-blue-50">
                                <td class="p-3" colspan="6">F32.0 - F32.9 ยกเว้น F32.5</td>
                            </tr>
                        @break

                        @case(2)
                            <tr class="bg-blue-50">
                                <td class="p-3 font-semibold text-blue-800" colspan="6">New Case เดือน {{ $date["date_1_form"] }} - {{ $date["date_1_to"] }}
                                    ผู้ป่วย MDD {{ $date["date_2_form"] }} - {{ $date["date_2_to"] }}
                                </td>
                            </tr>
                            <tr class="bg-blue-50">
                                <td class="p-3" colspan="6">F32.5</td>
                            </tr>
                        @break
                    @endswitch
                    @foreach ($item as $index => $hn)
                        <tr class="{{ $index % 2 == 0 ? "bg-white" : "bg-gray-50" }} transition hover:bg-blue-50">
                            <td class="border text-center">{{ $index + 1 }}</td>
                            <td class="border">{{ $hn["visit"] }}</td>
                            <td class="border">{{ $hn["hn"] }}</td>
                            <td class="border">{{ $hn["name"] }}</td>
                            <td class="border">{{ $hn["clinic"] }}</td>
                            <td class="border">{{ $hn["doctor"] }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="6">&nbsp;</td>
                    </tr>
                @endforeach
                <tr class="bg-blue-100">
                    <td class="p-3 font-semibold text-blue-800" colspan="6">New Case {{ $date["date_1_form"] }} - {{ $date["date_1_to"] }} Appointment Check</td>
                </tr>
                @foreach ($checkFollow as $hn => $app)
                    <tr class="bg-gray-50">
                        <td class="border text-center font-semibold">HN</td>
                        <td class="border" colspan="1">{{ $hn }}</td>
                        <td class="border" colspan="4">{{ $app["name"] }}</td>
                    </tr>
                    @foreach ($app["app"] as $no => $item)
                        <tr class="transition hover:bg-blue-50">
                            <td class="border"></td>
                            <td class="border">{{ $no }}</td>
                            <td class="border" colspan="2">{{ $item["date"] }}</td>
                            <td class="border" colspan="2">{{ $item["status"] }}</td>
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

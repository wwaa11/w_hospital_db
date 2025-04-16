@extends('layout')
@section('content')
    <div class="w-2/3 mt-6 flex m-auto gap-6">
        <a class="flex-grow text-center rounded-t p-3" href="{{ env('APP_URL') }}/a7z94">
            <div class="text-lg">Appointment A7 Dx.Z94.0</div>
        </a>
        <a class="flex-grow text-center border-t-2 border-r-2 border-s-2 rounded-t p-3 bg-[#78c0b0]"
            href="{{ env('APP_URL') }}/a7z94Xray">
            <div class="text-lg">Visit Dx.Z94.0 Xray</div>
        </a>
    </div>
    <div class="w-2/3 m-auto flex py-3 border-t-2 border-x-2">
        <div class="flex-grow gap-6">
            <input class="ms-3 border rounded p-3" id="start" type="date" value="{{ $dateQuery['start'] }}">
            <input class="border rounded p-3" id="end" type="date" value="{{ $dateQuery['end'] }}">
            <button class="p-3 text-center w-32 border text-blue-600 border-blue-600 rounded"
                onclick="query()">Query</button>
        </div>
        <div class="flex-grow text-end me-3">
            <button class="w-36 border-2 border-[#78c0b0] text-[#78c0b0] rounded-s p-3" onclick="exportFN()">Export</button>
        </div>
    </div>
    <table class="w-2/3 m-auto border-collapse border" id="tabel">
        <tr>
            <td colspan="2" class="border-collapse border p-3">
                Query Visit Date {{ date('d-m-Y', strtotime($dateQuery['start'])) }} to
                {{ date('d-m-Y', strtotime($dateQuery['end'])) }}
            </td>
            <td colspan="3" class="border-collapse border text-center p-3">Query DateTime : {{ date('d-m-Y H:i:s') }}
            </td>
        </tr>
        <tr>
            <td class="p-3 border-collapse border text-center">HN</td>
            <td class="p-3 border-collapse border text-center">Name</td>
            <td class="p-3 border-collapse border text-center">Visit Date</td>
            <td class="p-3 border-collapse border text-center">Xray Code</td>
            <td class="p-3 border-collapse border text-center">ICD Z94.0</td>
        </tr>
        @foreach ($outputData as $index => $hn)
            <tr>
                <td class="border-collapse border p-2">{{ $index }}</td>
                <td class="border-collapse border p-2">{{ $hn['name'] }}</td>
                <td class="border-collapse border p-2 text-center">{{ date('d-m-Y', strtotime($hn['date'])) }}</td>
                <td class="border-collapse border p-2 text-center">{{ $hn['xray'] }}</td>
                <td class="border-collapse border p-2">
                    <div>Visit: {{ date('d-m-Y', strtotime($hn['icd']['date'])) }}</div>
                    <div>VN: {{ $hn['icd']['vn'] }}</div>
                    <div>ICD: {{ $hn['icd']['icd'] }}</div>
                </td>
            </tr>
        @endforeach
        <tr>
            <td colspan="4"></td>
            <td class="text-center p-3">Total : {{ $total }}</td>
        </tr>
    </table>
    </div>
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

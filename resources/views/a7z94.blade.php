@extends('layout')
@section('content')
    <div class="w-2/3 mt-6 flex m-auto gap-6">
        <a class="flex-grow text-center border-t-2 border-r-2 border-s-2 rounded-t p-3 bg-[#78c0b0]"
            href="{{ env('APP_URL') }}/a7z94">
            <div class="text-lg">Appointment A7 Dx.Z94.0</div>
        </a>
        <a class="flex-grow text-center rounded-t p-3" href="{{ env('APP_URL') }}/a7z94Xray">
            <div class="text-lg">Visit Dx.Z94.0 Xray</div>
        </a>
    </div>
    <div class="w-2/3 m-auto flex py-3 border-t-2 border-x-2">
        <div class="flex-grow gap-6">
            <input class="ms-3 border rounded p-3" id="start" type="date" value="{{ $dateQuery['start'] }}">
            <input class="border rounded p-3" id="end" type="date" value="{{ $dateQuery['end'] }}">
            <input id="cancel_check" class="m-3" type="checkbox" @if ($dateQuery['cancel'] == 'true') checked @endif>
            <label class="me-3" for="cancel_check">Show Cancel Appointment</label>
            <button class="p-3 text-center w-32 border border-blue-600 text-blue-600 rounded"
                onclick="query()">Query</button>
        </div>
        <div class="flex-grow text-end me-3">
            <button class="w-36 border-2 border-[#78c0b0] text-[#78c0b0] rounded-s p-3 m-auto"
                onclick="exportFN()">Export</button>
        </div>
    </div>
    <table class="w-2/3 m-auto border-collapse border" id="tabel">
        <tr>
            <td colspan="3" class="border-collapse border p-3">
                Query Appointment Date form {{ date('d-m-Y', strtotime($dateQuery['start'])) }} to
                {{ date('d-m-Y', strtotime($dateQuery['end'])) }}
            </td>
            <td colspan="3" class="border-collapse border text-center p-3">Query DateTime : {{ date('d-m-Y H:i:s') }}
            </td>
        </tr>
        <tr>
            <td class="p-3 border-collapse border text-center">HN</td>
            <td class="p-3 border-collapse border text-center">Name</td>
            <td class="p-3 border-collapse border text-center">App Date</td>
            <td class="p-3 border-collapse border text-center">App No</td>
            <td class="p-3 border-collapse border text-center">Diag</td>
            <td class="p-3 border-collapse border text-center">Status</td>
        </tr>
        @foreach ($outputData as $index => $hn)
            @foreach ($hn['appointment'] as $appno => $app)
                <tr>
                    <td class="border-collapse border p-2">{{ $index }}</td>
                    <td class="border-collapse border p-2">{{ $hn['name'] }}</td>
                    <td class="border-collapse border p-2 text-center">{{ date('d-m-Y', strtotime($app['date'])) }}</td>
                    <td class="border-collapse border p-2 text-center">{{ $appno }}</td>
                    <td class="border-collapse border p-2">
                        <div>Visit: {{ date('d-m-Y', strtotime($hn['diagDate']['date'])) }}</div>
                        <div>VN: {{ $hn['diagDate']['vn'] }}</div>
                        <div>ICD: {{ $hn['diagDate']['icd'] }}</div>
                    </td>
                    <td class="border-collapse border text-center">{{ $app['status'] }}</td>
                </tr>
            @endforeach
        @endforeach
        <tr>
            <td colspan="5"></td>
            <td class="text-center p-3">Total : {{ $total }}</td>
        </tr>
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
            check = $('#cancel_check').prop('checked');

            window.location.href = '{{ env('APP_URL') }}/a7z94?start=' + start + '&end=' + end + '&cancel=' + check
        }
    </script>
@endsection

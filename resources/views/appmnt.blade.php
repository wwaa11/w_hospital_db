@extends("layout")
@section("content")
    <div class="m-auto flex w-2/3 p-3">
        <div class="flex-grow text-center">
            <button class="m-auto w-2/6 rounded-s border-4 border-green-400 p-3 text-green-400" onclick="exportFN()">Export</button>
        </div>
    </div>
    <table class="m-auto w-2/3 border-collapse border" id="tabel">
        <tr>
            <td class="border-collapse border p-3 text-center">AppointmentNo</td>
            <td class="border-collapse border p-3 text-center">HN</td>
            <td class="border-collapse border p-3 text-center">Doctor</td>
            <td class="border-collapse border p-3 text-center">Clinic</td>
            <td class="border-collapse border p-3 text-center">MobilePhone</td>
            <td class="border-collapse border p-3 text-center">AppointDateTime</td>
            <td class="border-collapse border p-3 text-center">LastHNAppointmentLogType</td>
            <td class="border-collapse border p-3 text-center">HNAppointmentMsgType</td>
        </tr>
        @foreach ($dataArray as $index => $appmnt)
            <tr>
                <td class="border-collapse border text-center">{{ $appmnt["AppointmentNo"] }}</td>
                <td class="border-collapse border text-center">{{ $appmnt["HN"] }}</td>
                <td class="border-collapse border text-center">{{ $appmnt["Doctor"] }}</td>
                <td class="border-collapse border text-center">{{ $appmnt["Clinic"] }}</td>
                <td class="border-collapse border text-center">{{ $appmnt["MobilePhone"] }}</td>
                <td class="border-collapse border text-center">{{ $appmnt["AppointDateTime"] }}</td>
                <td class="border-collapse border text-center">{{ $appmnt["LastHNAppointmentLogType"] }}</td>
                <td class="border-collapse border text-center">{{ $appmnt["HNAppointmentMsgType"] }}</td>
            </tr>
        @endforeach
    </table>
    </div>
@endsection
@section("scripts")
    <script>
        function exportFN() {
            TableToExcel.convert(document.getElementById("tabel"));
        }

        function query() {
            var start = $('#start').val();
            var end = $('#end').val();
            window.location.href = '{{ env("APP_URL") }}/line?start=' + start + '&end=' + end
        }
    </script>
@endsection

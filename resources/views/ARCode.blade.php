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
            <td class="border-collapse border p-3 text-center">Full Name</td>
            <td class="border-collapse border p-3 text-center">ARCode</td>
            <td class="border-collapse border p-3 text-center">ReferToDoctor</td>
            <td class="border-collapse border p-3 text-center">Doctor Name</td>
        </tr>
        @foreach ($datas as $index => $item)
            <tr>
                <td class="border-collapse border p-3 text-center">{{ $item->HN }}</td>
                <td class="border-collapse border p-3 text-center">{{ $item->fullname }}</td>
                <td class="border-collapse border p-3 text-center">{{ $item->ARCode }}</td>
                <td class="border-collapse border p-3 text-center">{{ $item->ReferToDoctor }}</td>
                <td class="border-collapse border p-3 text-center">{{ $item->DoctorName }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="4"></td>
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

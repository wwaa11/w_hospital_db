@extends('layout')
@section('content')
    <div class="flex p-3">
        <button class="border-4 border-green-400 text-green-400 rounded-s p-3 m-auto" onclick="exportFN()">Export</button>
    </div>
    <table class="w-2/3 m-auto border-collapse border" id="tabel">
        <tr>
            <td class="p-3 border-collapse border text-center">HN</td>
            <td class="p-3 border-collapse border text-center">Name</td>
            <td class="p-3 border-collapse border text-center">PDPA3</td>
            <td class="p-3 border-collapse border text-center">PDPA4</td>
            <td class="p-3 border-collapse border text-center">Witness</td>
            <td class="p-3 border-collapse border text-center">Userlogon</td>
            <td class="p-3 border-collapse border text-center">CreateDateTime</td>
        </tr>
        @foreach ($data as $index => $hn)
            <tr>
                <td class="border-collapse border text-center">{{ $hn['HN'] }}</td>
                <td class="border-collapse border text-center">{{ $hn['Name'] }}</td>
                <td class="border-collapse border text-center">{{ $hn['PDPA3'] }}</td>
                <td class="border-collapse border text-center">{{ $hn['PDPA4'] }}</td>
                <td class="border-collapse border text-center">{{ $hn['Witness'] }}</td>
                <td class="border-collapse border text-center">{{ $hn['Userlogon'] }}</td>
                <td class="border-collapse border text-center">{{ $hn['CreateDateTime'] }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="4"></td>
            <td colspan="2" class="text-center">( {{ date('d M Y') }} ) Total : {{ count($data) }}</td>
        </tr>
    </table>
    </div>
@endsection
@section('scripts')
    <script>
        function exportFN() {
            TableToExcel.convert(document.getElementById("tabel"));
        }
    </script>
@endsection

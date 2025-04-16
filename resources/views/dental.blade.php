@extends('layout')
@section('content')
    <div class="flex p-3">
        <button class="w-1/6 border-4 border-green-400 text-green-400 rounded-s p-3 m-auto"
            onclick="exportFN()">Export</button>
    </div>
    <table class="w-2/3 m-auto border-collapse border" id="tabel">
        <tr>
            <td colspan="8" class="text-center">Visit {{ $dateFilter['start'] }} - {{ $dateFilter['end'] }} : Appointment
                Form :
                {{ $dateFilter['AppDate'] }}</td>
        </tr>
        <tr>
            <td class="border-collapse border text-center">HN</td>
            <td class="border-collapse border text-center">Name</td>
            <td class="border-collapse border text-center">Gender</td>
            <td class="border-collapse border text-center">Age</td>
            <td class="border-collapse border text-center">Visit</td>
            <td class="border-collapse border text-center">Doctor</td>
            <td class="border-collapse border text-center">App-No</td>
            <td class="border-collapse border text-center">App-DateTime</td>
            <td class="border-collapse border text-center">Status</td>
        </tr>
        @foreach ($output as $hn)
            @if (count($hn['app']) > 0)
                {{-- <tr>
                    <td class="border-collapse border">{{ $hn['hn'] }}</td>
                    <td class="border-collapse border">{{ $hn['name'] }}</td>
                    <td class="border-collapse border">{{ $hn['gender'] }}</td>
                    <td class="border-collapse border">{{ $hn['age'] }}</td>
                    <td class="border-collapse border">{{ $hn['visit'] }}</td>
                    <td class="border-collapse border" colspan="3"></td>
                </tr> --}}
                @foreach ($hn['app'] as $app)
                    <tr>
                        <td class="border-collapse border">{{ $hn['hn'] }}</td>
                        <td class="border-collapse border">{{ $hn['name'] }}</td>
                        <td class="border-collapse border">{{ $hn['gender'] }}</td>
                        <td class="border-collapse border">{{ $hn['age'] }}</td>
                        <td class="border-collapse border">{{ $hn['visit'] }}</td>
                        <td class="border-collapse border">{{ $hn['doctor'] }}</td>
                        <td class="border-collapse border">{{ $app['No'] }}</td>
                        <td class="border-collapse border">{{ $app['date'] }}</td>
                        <td class="border-collapse border text-center">{{ $app['status'] }}</td>
                    </tr>
                @endforeach
            @endif
        @endforeach
        <tr>
            <td class="text-center">Total : {{ count($output) }}</td>
            <td colspan="7"></td>
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

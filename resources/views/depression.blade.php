@extends('layout')
@section('content')
    <div class="flex p-3">
        <button class="w-1/6 border-4 border-green-400 text-green-400 rounded-s p-3 m-auto"
            onclick="exportFN()">Export</button>
    </div>
    <table class="w-2/3 m-auto border-collapse border" id="tabel">
        @foreach ($data as $label => $item)
            @switch($label)
                @case(1)
                    <tr class="border-collapse border">
                        <td class="p-3" colspan="6">New Case Clinic Mind Center , Mind Center - Telemedicine
                            {{ $date['date_1_form'] }} - {{ $date['date_1_to'] }}
                        </td>
                    </tr>
                    <tr class="border-collapse border">
                        <td class="p-3" colspan="6">F32.0 - F32.9 ยกเว้น F32.5</td>
                    </tr>
                @break

                @case(2)
                    <tr class="border-collapse border">
                        <td class="p-3" colspan="6">New Case เดือน {{ $date['date_1_form'] }} - {{ $date['date_1_to'] }}
                            ผู้ป่วย MDD {{ $date['date_2_form'] }} - {{ $date['date_2_to'] }}
                        </td>
                    </tr>
                    <tr class="border-collapse border">
                        <td class="p-3" colspan="6">F32.5</td>
                    </tr>
                @break
            @endswitch
            <tr>
                <td class="p-3 border-collapse border text-center"></td>
                <td class="p-3 border-collapse border text-center">Date</td>
                <td class="p-3 border-collapse border text-center">HN</td>
                <td class="p-3 border-collapse border text-center">Name</td>
                <td class="p-3 border-collapse border text-center">Clinic</td>
                <td class="p-3 border-collapse border text-center">Doctor</td>
            </tr>
            @foreach ($item as $index => $hn)
                <tr>
                    <td class="border-collapse border text-center">{{ $index + 1 }}</td>
                    <td class="border-collapse border">{{ $hn['visit'] }}</td>
                    <td class="border-collapse border">{{ $hn['hn'] }}</td>
                    <td class="border-collapse border">{{ $hn['name'] }}</td>
                    <td class="border-collapse border">{{ $hn['clinic'] }}</td>
                    <td class="border-collapse border">{{ $hn['doctor'] }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="6">&nbsp;</td>
            </tr>
        @endforeach
        <tr class="border-collapse border">
            <td class="p-3" colspan="6">New Case {{ $date['date_1_form'] }} - {{ $date['date_1_to'] }} Appointment
                Check</td>
        </tr>
        @foreach ($checkFollow as $hn => $app)
            <tr class="border-collapse border">
                <td class="border-collapse border text-center">HN</td>
                <td colspan="1" class="border-collapse border">{{ $hn }}</td>
                <td class="4" class="border-collapse border">{{ $app['name'] }}</td>
            </tr>
            @foreach ($app['app'] as $no => $item)
                <tr class="border-collapse border">
                    <td class="border-collapse border"></td>
                    <td class="border-collapse border">{{ $no }}</td>
                    <td colspan="2" class="border-collapse border">{{ $item['date'] }}</td>
                    <td colspan="2" class="border-collapse border">{{ $item['status'] }}</td>
                </tr>
            @endforeach
        @endforeach
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

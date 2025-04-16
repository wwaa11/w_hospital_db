@extends('layout')
@section('content')
    <div class="w-2/3 m-auto flex py-3 border-t-2 border-x-2">
        <div class="flex-grow gap-6">
        </div>
        <div class="flex-grow text-end me-3">
            <button class="w-36 border-2 border-[#78c0b0] text-[#78c0b0] rounded-s p-3 m-auto"
                onclick="exportFN()">Export</button>
        </div>
    </div>
    <div class="w-2/3 m-auto flex p-3 text-center text-lg border-2">
        รายงานคนไข้ที่อายุต่ำกว่า 2 ปี ที่ไม่เคยป่วยด้วยโรค RSV มาก่อน ไม่เคยรับวัคซีน RSV มาก่อน
        เพื่อใช้ในการโทรติดตามมารับวัคซีน RSV ตัวใหม่
    </div>
    <table class="w-2/3 m-auto border-collapse border" id="tabel">
        <tr>
            <td colspan="3" class="border-collapse border p-3">
                Query HN born after : {{ date('d-m-Y', strtotime($dateQuery['start'])) }}
            </td>
            <td colspan="3" class="border-collapse border text-center p-3">Query DateTime : {{ date('d-m-Y H:i:s') }}
            </td>
        </tr>
        <tr>
            <td class="p-3 border-collapse border text-center">HN</td>
            <td class="p-3 border-collapse border text-center">Name</td>
            <td class="p-3 border-collapse border text-center">Age</td>
            <td class="p-3 border-collapse border text-center">Mobile phone</td>
            {{-- <td class="p-3 border-collapse border text-center">Status</td> --}}
        </tr>
        @foreach ($dataOutput as $index => $hn)
            @if ($hn['show'])
                <tr>
                    <td class="border-collapse border p-2">{{ $index }}</td>
                    <td class="border-collapse border p-2">{{ $hn['name'] }}</td>
                    <td class="border-collapse border p-2 text-center">{{ $hn['age'] }}</td>
                    <td class="border-collapse border p-2">{{ $hn['phone'] }}</td>
                    <td class="border-collapse border p-2">{{ $hn['lastvisit'] }}</td>
                </tr>
            @endif
        @endforeach
        <tr>
            <td colspan="3"></td>
            <td class="text-center p-3">Total : {{ count($dataOutput) }}</td>
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

            window.location.href = '{{ env('APP_URL') }}/rsv?start=' + start + '&end=' + end
        }
    </script>
@endsection

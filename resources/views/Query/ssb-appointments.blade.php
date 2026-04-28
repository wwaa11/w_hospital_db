@extends("layouts.app")
@section('content')
    <div class="w-2/3 m-auto flex py-3 border-t-2 border-x-2">
        <div class="flex-grow text-end me-3">
            <button class="w-36 border-2 border-[#78c0b0] text-[#78c0b0] rounded-s p-3 m-auto"
                onclick="exportFN()">Export</button>
        </div>
    </div>
    <table class="w-2/3 m-auto border-collapse border" id="tabel">
        <thead>
            <tr>
                <th class="border-collapse border text-center p-3">Clinic</th>
                <th class="border-collapse border text-center p-3">30 Apirl 2026</th>
                <th class="border-collapse border text-center p-3">01 May 2026</th>
                <th class="border-collapse border text-center p-3">02 May 2026</th>
                <th class="border-collapse border text-center p-3">03 May 2026</th>
                <th class="border-collapse border text-center p-3">04 May 2026</th>
                <th class="border-collapse border text-center p-3">05 May 2026</th>
                <th class="border-collapse border text-center p-3">06 May 2026</th>
                <th class="border-collapse border text-center p-3">07 May 2026</th>
                <th class="border-collapse border text-center p-3">08 May 2026</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($data as $clinic => $items)
            <tr>
                <td class="border-collapse border text-center p-3">{{ $clinic }}</td>
                @foreach ($items as $date => $count)
                    <td class="border-collapse border text-center p-3">{{ $count }}</td>
                @endforeach
            </tr>
        @endforeach
        <tr>
            <td class="border-collapse border text-center p-3">Total</td>
            @foreach ($dateTotal as $date => $count)
                <td class="border-collapse border text-center p-3">{{ $count }}</td>
            @endforeach
        </tr>
    </table>
@endsection
@section('scripts')
    <script>
        function exportFN() {
            TableToExcel.convert(document.getElementById("tabel"));
        }
    </script>
@endsection

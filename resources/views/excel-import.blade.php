@extends("layouts.app")
@section("content")
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
        <div class="container mx-auto max-w-7xl px-4 py-8">
            <!-- Header Section -->
            <div class="mb-12 text-center">
                <div class="mb-8 rounded-2xl border border-gray-100 bg-white p-8 shadow-xl">
                    <div class="mb-6 flex items-center justify-center">
                        <div class="rounded-full bg-gradient-to-r from-blue-600 to-purple-600 p-4">
                            <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                            </svg>
                        </div>
                    </div>
                    <h1 class="mb-4 bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-4xl font-bold text-transparent">
                        สุ่มรางวัลสหกรณ์ออมทรัพย์โรงพยาบาลพระรามเก้า จำกัด
                    </h1>
                    <p class="text-xl font-medium text-gray-600">ระบบสุ่มรางวัลอัตโนมัติสำหรับสมาชิกสหกรณ์</p>

                </div>
            </div>

            <!-- Random Results Section -->
            @if (isset($randomData) && count($randomData) > 0)
                <div class="mb-12">
                    <div class="mb-8 rounded-2xl bg-gradient-to-r from-green-400 via-blue-500 to-purple-600 p-8 text-white shadow-2xl">
                        <div class="mb-8 text-center">
                            <div class="mb-4 flex items-center justify-center">
                                <div class="rounded-full bg-white bg-opacity-20 p-4">
                                    <span class="text-4xl">🎉</span>
                                </div>
                            </div>
                            <h3 class="mb-2 text-3xl font-bold">
                                ผลการสุ่มรางวัล
                            </h3>
                            <p class="text-xl opacity-90">ผู้โชคดีที่ได้รับรางวัล {{ count($randomData) }} รายการ</p>
                        </div>

                        <div class="rounded-xl bg-white bg-opacity-10 p-6 backdrop-blur-sm">
                            <div class="overflow-x-auto">
                                <table class="w-full text-white">
                                    <thead>
                                        <tr class="border-b border-white border-opacity-30">
                                            <th class="px-4 py-4 text-left text-lg font-bold">🏆 อันดับ</th>
                                            <th class="px-4 py-4 text-left text-lg font-bold">ทะเบียน</th>
                                            <th class="px-4 py-4 text-left text-lg font-bold">รหัส</th>
                                            <th class="px-4 py-4 text-left text-lg font-bold">เลขที่บัญชี</th>
                                            <th class="px-4 py-4 text-left text-lg font-bold">ชื่อ - นามสกุล</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($randomData as $index => $row)
                                            <tr class="border-b border-white border-opacity-20 transition-all duration-300 hover:bg-white hover:bg-opacity-10">
                                                <td class="px-4 py-4">
                                                    <span class="rounded-full bg-yellow-400 px-3 py-2 text-lg font-bold text-gray-900">
                                                        {{ $index + 1 }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-4 font-semibold">{{ $row["no"] ?? "" }}</td>
                                                <td class="px-4 py-4 font-semibold">{{ $row["code"] ?? "" }}</td>
                                                <td class="px-4 py-4 font-semibold">{{ $row["number"] ?? "" }}</td>
                                                <td class="px-4 py-4 font-semibold">{{ $row["name"] ?? "" }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Random Button Section -->
            @if (isset($data) && count($data) > 0)
                <div class="mb-12 text-center">
                    <form action="{{ route("excel.random") }}" method="POST">
                        @csrf
                        <input type="hidden" name="excel_data" value="{{ json_encode($data) }}">
                        <button class="hover:shadow-3xl group relative inline-flex transform items-center justify-center rounded-2xl bg-gradient-to-r from-pink-500 via-red-500 to-yellow-500 px-12 py-6 text-xl font-bold text-white shadow-2xl transition-all duration-300 hover:scale-105 hover:from-pink-600 hover:via-red-600 hover:to-yellow-600" type="submit">
                            <span class="absolute inset-0 h-full w-full rounded-2xl bg-gradient-to-r from-pink-400 via-red-400 to-yellow-400 opacity-30 blur transition-opacity duration-300 group-hover:opacity-50"></span>
                            <span class="relative flex items-center">
                                <span class="mr-3 animate-bounce text-2xl">🎲</span>
                                สุ่มรางวัล 5 รายการ
                                <span class="ml-3 animate-bounce text-2xl">🎲</span>
                            </span>
                        </button>
                    </form>
                </div>
            @endif

            <!-- Upload Form -->
            <div class="mb-12 rounded-2xl border border-gray-100 bg-white p-8 shadow-xl">
                <div class="mb-8 text-center">
                    <div class="mb-4 flex items-center justify-center">
                        <div class="rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 p-4">
                            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </div>
                    </div>
                    <h5 class="mb-2 text-2xl font-bold text-gray-800">
                        📁 อัพโหลดไฟล์ Excel
                    </h5>
                    <p class="text-gray-600">เลือกไฟล์ Excel ที่มีข้อมูลสมาชิกสหกรณ์</p>
                </div>

                <form class="mx-auto max-w-2xl" id="uploadForm" action="{{ route("excel.import") }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-8">
                        <label class="mb-4 block text-sm font-semibold text-gray-700" for="excelFile">เลือกไฟล์ Excel</label>
                        <div class="relative">
                            <input class="block w-full rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-6 text-sm text-gray-500 transition-colors duration-300 file:mr-4 file:rounded-xl file:border-0 file:bg-gradient-to-r file:from-blue-50 file:to-indigo-50 file:px-6 file:py-4 file:text-sm file:font-semibold file:text-blue-700 file:transition-all file:duration-300 hover:border-blue-400 hover:bg-blue-50 hover:file:bg-gradient-to-r hover:file:from-blue-100 hover:file:to-indigo-100" id="excelFile" type="file" name="excel_file" accept=".xlsx,.xls,.csv" required>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">รองรับไฟล์ .xlsx, .xls, .csv</p>
                    </div>

                    <div class="text-center">
                        <button class="inline-flex transform items-center rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-4 font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:from-blue-700 hover:to-indigo-700 hover:shadow-xl" type="submit">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            อัพโหลด
                        </button>
                    </div>
                </form>
            </div>

            <!-- Display Results -->
            @if (isset($data) && count($data) > 0)
                <div class="mb-12 rounded-2xl border border-gray-100 bg-white p-8 shadow-xl">
                    <div class="mb-8 text-center">
                        <div class="mb-4 flex items-center justify-center">
                            <div class="rounded-full bg-gradient-to-r from-green-500 to-teal-600 p-4">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                        <h5 class="mb-2 text-2xl font-bold text-gray-800">
                            📊 ข้อมูลสมาชิก
                        </h5>
                        <p class="text-lg text-gray-600">รายชื่อสมาชิกสหกรณ์ทั้งหมด <span class="font-bold text-blue-600">{{ count($data) }}</span> คน</p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full overflow-hidden rounded-lg bg-white shadow-sm">
                                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                    <tr>
                                        <th class="border-b border-gray-200 px-6 py-4 text-left font-bold text-gray-700">ลำดับ</th>
                                        <th class="border-b border-gray-200 px-6 py-4 text-left font-bold text-gray-700">ทะเบียน</th>
                                        <th class="border-b border-gray-200 px-6 py-4 text-left font-bold text-gray-700">รหัส</th>
                                        <th class="border-b border-gray-200 px-6 py-4 text-left font-bold text-gray-700">เลขบัตรผู้</th>
                                        <th class="border-b border-gray-200 px-6 py-4 text-left font-bold text-gray-700">ชื่อ - นามสกุล</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $index => $row)
                                        <tr class="{{ $index % 2 == 0 ? "bg-white" : "bg-gray-50" }} transition-colors duration-200 hover:bg-blue-50">
                                            <td class="border-b border-gray-100 px-6 py-4">
                                                <span class="rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-800">
                                                    {{ $index + 1 }}
                                                </span>
                                            </td>
                                            <td class="border-b border-gray-100 px-6 py-4 font-medium text-gray-700">{{ $row["no"] ?? "" }}</td>
                                            <td class="border-b border-gray-100 px-6 py-4 font-medium text-gray-700">{{ $row["code"] ?? "" }}</td>
                                            <td class="border-b border-gray-100 px-6 py-4 font-medium text-gray-700">{{ $row["number"] ?? "" }}</td>
                                            <td class="border-b border-gray-100 px-6 py-4 font-medium text-gray-700">{{ $row["name"] ?? "" }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mb-8 rounded-lg border-l-4 border-red-400 bg-red-50 p-6 shadow-sm">
                    <div class="mb-4 flex items-center">
                        <div class="mr-3 rounded-full bg-red-100 p-2">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h4 class="text-lg font-semibold text-red-800">เกิดข้อผิดพลาด</h4>
                    </div>
                    <ul class="list-inside list-disc space-y-2">
                        @foreach ($errors->all() as $error)
                            <li class="font-medium text-red-700">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Success Messages -->
            @if (session("success"))
                <div class="mb-8 rounded-lg border-l-4 border-green-400 bg-green-50 p-6 shadow-sm">
                    <div class="flex items-center">
                        <div class="mr-3 rounded-full bg-green-100 p-2">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <p class="font-semibold text-green-800">{{ session("success") }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadForm = document.getElementById('uploadForm');
            const fileInput = document.getElementById('excelFile');

            if (uploadForm && fileInput) {
                uploadForm.addEventListener('submit', function(e) {
                    if (!fileInput.files.length) {
                        e.preventDefault();

                        // Show modern alert using SweetAlert2 (already included in layout)
                        Swal.fire({
                            icon: 'warning',
                            title: 'กรุณาเลือกไฟล์',
                            text: 'กรุณาเลือกไฟล์ Excel ก่อนอัพโหลด',
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#3B82F6'
                        });

                        return false;
                    }
                });

                // Add file input change event for better UX
                fileInput.addEventListener('change', function(e) {
                    if (e.target.files.length > 0) {
                        const fileName = e.target.files[0].name;
                        console.log('Selected file:', fileName);
                    }
                });
            }
        });
    </script>
@endsection

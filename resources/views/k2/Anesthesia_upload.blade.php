@extends("layouts.app")

@section("content")
    <div class="container mx-auto px-4 py-8">
        @include("k2.menu")
        <div class="flex justify-center">
            <div class="w-full max-w-4xl">
                <div class="rounded-lg bg-white shadow-sm">
                    <div class="rounded-t-lg bg-blue-600 px-6 py-4 text-white">
                        <h5 class="text-lg font-semibold">
                            <i class="fas fa-upload mr-2 text-red-400"></i>
                            Anesthesia Price Master Upload
                        </h5>
                    </div>

                    <div class="p-6">
                        <!-- Warning Message -->
                        <div class="mb-6 border-l-4 border-yellow-400 bg-yellow-50 p-4 text-yellow-700">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm">
                                        <strong class="font-medium">Important:</strong> Your Excel file must contain only one sheet. If your file has multiple sheets, all sheets will be processed and added to the selected clinic.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Example Table Section -->
                        <div class="mb-8">
                            <div class="mb-3 flex items-center justify-between">
                                <h6 class="text-lg font-semibold text-gray-700">
                                    <i class="fas fa-table mr-2"></i>
                                    Example Excel Format
                                </h6>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full border border-gray-200">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="border px-4 py-2 text-center text-sm font-semibold text-gray-700" rowspan="2">Anesthesia Name</th>
                                            <th class="border px-4 py-2 text-center text-sm font-semibold text-gray-700" rowspan="2">Hours</th>
                                            <th class="border px-4 py-2 text-center text-sm font-semibold text-gray-700" rowspan="2">Hours Used</th>
                                            <th class="border px-4 py-2 text-center text-sm font-semibold text-gray-700" colspan="2">THAI</th>
                                            <th class="border px-4 py-2 text-center text-sm font-semibold text-gray-700" colspan="2">INTER</th>
                                            <th class="border px-4 py-2 text-center text-sm font-semibold text-gray-700" colspan="2">ARAB</th>
                                        </tr>
                                        <tr class="bg-gray-50">
                                            <th class="border px-4 py-2 text-center text-sm font-semibold text-gray-700">DF</th>
                                            <th class="border px-4 py-2 text-center text-sm font-semibold text-gray-700">Total</th>
                                            <th class="border px-4 py-2 text-center text-sm font-semibold text-gray-700">DF</th>
                                            <th class="border px-4 py-2 text-center text-sm font-semibold text-gray-700">Total</th>
                                            <th class="border px-4 py-2 text-center text-sm font-semibold text-gray-700">DF</th>
                                            <th class="border px-4 py-2 text-center text-sm font-semibold text-gray-700">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <tr>
                                            <td class="border-b px-4 py-2 text-sm text-gray-700">Name</td>
                                            <td class="border-b px-4 py-2 text-sm text-gray-700">0.5</td>
                                            <td class="border-b px-4 py-2 text-sm text-gray-700">1</td>
                                            <td class="border-b px-4 py-2 text-sm text-gray-700">1,000-1,200</td>
                                            <td class="border-b px-4 py-2 text-sm text-gray-700">3,000-4,500</td>
                                            <td class="border-b px-4 py-2 text-sm text-gray-700">1,000-1,200</td>
                                            <td class="border-b px-4 py-2 text-sm text-gray-700">3,000-4,500</td>
                                            <td class="border-b px-4 py-2 text-sm text-gray-700">1,000-1,200</td>
                                            <td class="border-b px-4 py-2 text-sm text-gray-700">3,000-4,500</td>
                                        </tr>
                                        <tr>
                                            <td class="border-b px-4 py-2 text-sm text-gray-700">Name</td>
                                            <td class="border-b px-4 py-2 text-sm text-gray-700">1</td>
                                            <td class="border-b px-4 py-2 text-sm text-gray-700">1.5</td>
                                            <td class="border-b px-4 py-2 text-sm text-gray-700">2,000-2,400</td>
                                            <td class="border-b px-4 py-2 text-sm text-gray-700">6,000-7,200</td>
                                            <td class="border-b px-4 py-2 text-sm text-gray-700">2,000-2,400</td>
                                            <td class="border-b px-4 py-2 text-sm text-gray-700">6,000-7,200</td>
                                            <td class="border-b px-4 py-2 text-sm text-gray-700">2,000-2,400</td>
                                            <td class="border-b px-4 py-2 text-sm text-gray-700">6,000-7,200</td>
                                        </tr>
                                        
                                    </tbody>
                                </table>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Please ensure your Excel file follows this format with the correct column headers and data structure.
                            </p>
                        </div>

                        <form class="needs-validation" method="POST" action="{{ env("APP_URL") }}/k2/anesthesia" enctype="multipart/form-data" novalidate>
                            @csrf
                            <div class="mb-6">
                                <label class="mb-2 block text-sm font-medium text-gray-700">
                                    <i class="fas fa-server mr-2"></i>
                                    Environment
                                </label>
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <input class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500" id="env_dev" type="radio" name="environment" value="K2DEV_SUR" {{ old("environment", "K2DEV_SUR") == "K2DEV_SUR" ? "checked" : "" }}>
                                        <label class="ml-3 block text-sm font-medium text-gray-700" for="env_dev">
                                            Development
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500" id="env_prod" type="radio" name="environment" value="K2PROD_SUR" {{ old("environment") == "K2PROD_SUR" ? "checked" : "" }}>
                                        <label class="ml-3 block text-sm font-medium text-gray-700" for="env_prod">
                                            Production
                                        </label>
                                    </div>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    Select the environment where you want to upload the Anesthesia file.
                                </p>
                            </div>

                            <div class="mb-6">
                                <label class="mb-2 block text-sm font-medium text-gray-700" for="file">
                                    <i class="fas fa-file-excel mr-2"></i>
                                    Excel File
                                </label>
                                <div class="relative">
                                    <input class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100" id="file" type="file" name="file" accept=".xlsx,.xls" required>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    Accepted formats: .xlsx, .xls
                                </p>
                            </div>

                            <div class="space-y-3">
                                <button class="w-full rounded-md bg-blue-600 px-6 py-3 text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" type="button" onclick="submitForm()">
                                    <i class="fas fa-upload mr-2"></i>
                                    Upload File
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section("scripts")
    <script>
        // Declare clinicCheckboxes in global scope
        let clinicCheckboxes;

        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAllClinics');
            clinicCheckboxes = document.querySelectorAll('.clinic-checkbox');

            selectAllCheckbox.addEventListener('change', function() {
                clinicCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            clinicCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(clinicCheckboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                });
            });
        });

        // Form validation and confirmation
        window.submitForm = function() {
            const form = document.querySelector('.needs-validation');
            const environment = document.querySelector('input[name="environment"]:checked');
            const file = document.getElementById('file').files[0];

            if (!environment || !file) {
                Swal.fire({
                    title: 'Missing Required Fields',
                    html: `
                        <div class="text-left">
                            <p class="mb-2">Please fill in the following required fields:</p>
                            <ul class="list-disc list-inside">
                                ${!environment ? '<li>Environment</li>' : ''}
                                ${!file ? '<li>Excel File</li>' : ''}
                            </ul>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            if (!file.name.match(/\.(xlsx|xls)$/)) {
                Swal.fire({
                    title: 'Invalid File Type',
                    text: 'Please select a valid Excel file (.xlsx or .xls)',
                    icon: 'error',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            Swal.fire({
                title: 'Confirm Upload',
                html: `
                    <div class="text-left">
                        <p class="mb-2"><strong>Environment:</strong> ${environment.value === 'K2DEV_SUR' ? 'Development' : 'Production'}</p>
                        <p class="mb-2"><strong>File:</strong> ${file.name}</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, proceed with upload',
                cancelButtonText: 'No, cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Please Wait',
                        html: `
                            <div class="text-center">
                                <div class="mb-4">
                                    <i class="fas fa-spinner fa-spin fa-3x text-blue-600"></i>
                                </div>
                                <p class="text-gray-600">Uploading file and processing data...</p>
                            </div>
                        `,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            form.submit();
                        }
                    });
                }
            });
        };

        // Show success message with SweetAlert2 if there's a success flash message
        @if (session("success"))
            Swal.fire({
                title: 'Success!',
                text: "{{ session("success") }}",
                icon: 'success',
                confirmButtonColor: '#3085d6'
            });
        @endif

        // Show error message with SweetAlert2 if there are any errors
        @if ($errors->any())
            Swal.fire({
                title: 'Error!',
                html: `
                    <div class="text-left">
                        <p class="mb-2">Please correct the following errors:</p>
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                `,
                icon: 'error',
                confirmButtonColor: '#3085d6'
            });
        @endif
    </script>
@endsection

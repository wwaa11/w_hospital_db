@extends("layouts.app")

@section("content")
    <div class="container mx-auto px-4 py-8">
        <div class="mx-auto max-w-4xl">
            <!-- Navigation Menu -->
            <div class="mb-8 flex justify-center space-x-4">
                <div class="flex justify-center space-x-4">
                    <a class="rounded-md bg-blue-600 px-6 py-3 text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" href="{{ env("APP_URL") }}/k2/procedure">
                        <i class="fas fa-file-medical mr-2"></i>
                        Procedure Upload
                    </a>
                    <a class="rounded-md bg-gray-100 px-6 py-3 text-gray-700 transition-colors hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2" href="{{ env("APP_URL") }}/k2/med3">
                        <i class="fas fa-pills mr-2"></i>
                        Med3 Upload
                    </a>
                </div>
            </div>

            <div class="flex justify-center">
                <div class="w-full max-w-2xl">
                    <div class="rounded-lg bg-white shadow-sm">
                        <div class="rounded-t-lg bg-blue-600 px-6 py-4 text-white">
                            <h5 class="text-lg font-semibold">
                                <i class="fas fa-upload mr-2 text-red-400"></i>
                                K2 Procedure Upload
                            </h5>
                        </div>

                        <div class="p-6">
                            @if (session("success"))
                                <div class="relative mb-4 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700" role="alert">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    {{ session("success") }}
                                    <button class="absolute bottom-0 right-0 top-0 px-4 py-3" type="button" onclick="this.parentElement.remove()">
                                        <span class="sr-only">Close</span>
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="relative mb-4 rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700" role="alert">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    Please correct the following errors:
                                    <ul class="mt-2 list-inside list-disc">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button class="absolute bottom-0 right-0 top-0 px-4 py-3" type="button" onclick="this.parentElement.remove()">
                                        <span class="sr-only">Close</span>
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif

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
                                    <a class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2" href="{{ asset("K2_Example/Procedure_Example.xlsx") }}">
                                        <i class="fas fa-download mr-2"></i>
                                        Download Example File
                                    </a>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full border border-gray-200">
                                        <thead>
                                            <tr class="bg-gray-50">
                                                <th class="border-b px-4 py-2 text-center text-sm font-semibold text-gray-700" colspan="6">
                                                    ราคาประเมิน Case Operation NEURO
                                                </th>
                                            </tr>
                                            <tr class="bg-gray-50">
                                                <th class="border px-4 py-2 text-left text-sm font-semibold text-gray-700" rowspan="2">No</th>
                                                <th class="border px-4 py-2 text-left text-sm font-semibold text-gray-700" rowspan="2">Procedure</th>
                                                <th class="border-b px-4 py-2 text-center text-sm font-semibold text-gray-700" colspan="3">ค่าใช้จ่ายใน OR</th>
                                            </tr>
                                            <tr class="bg-gray-50">
                                                <th class="border px-4 py-2 text-center text-sm font-semibold text-gray-700">THAI</th>
                                                <th class="border px-4 py-2 text-center text-sm font-semibold text-gray-700">INTER</th>
                                                <th class="border px-4 py-2 text-center text-sm font-semibold text-gray-700">ARAB</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            <tr>
                                                <td class="border-b px-4 py-2 text-sm text-gray-700">1</td>
                                                <td class="border-b px-4 py-2 text-sm text-gray-700">Example Procedure</td>
                                                <td class="border-b px-4 py-2 text-center text-sm text-gray-700">100,000-120,000</td>
                                                <td class="border-b px-4 py-2 text-center text-sm text-gray-700">120,000-130,000</td>
                                                <td class="border-b px-4 py-2 text-center text-sm text-gray-700">200,000-240,000</td>
                                            </tr>
                                            <tr>
                                                <td class="border-b px-4 py-2 text-sm text-gray-700">2</td>
                                                <td class="border-b px-4 py-2 text-sm text-gray-700">Example Procedure</td>
                                                <td class="border-b px-4 py-2 text-center text-sm text-gray-700">75,000-80,000</td>
                                                <td class="border-b px-4 py-2 text-center text-sm text-gray-700">80,000-90,000</td>
                                                <td class="border-b px-4 py-2 text-center text-sm text-gray-700">150,000-160,000</td>
                                            </tr>
                                            <tr>
                                                <td class="border-b px-4 py-2 text-sm text-gray-700">3</td>
                                                <td class="border-b px-4 py-2 text-sm text-gray-700">Example Procedure</td>
                                                <td class="border-b px-4 py-2 text-center text-sm text-gray-700">75,000-80,000</td>
                                                <td class="border-b px-4 py-2 text-center text-sm text-gray-700">80,000-90,000</td>
                                                <td class="border-b px-4 py-2 text-center text-sm text-gray-700">150,000-160,000</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Please ensure your Excel file follows this format with the correct column headers and data structure.
                                </p>
                            </div>

                            <form class="needs-validation" method="POST" action="{{ env("APP_URL") }}/k2/procedure" enctype="multipart/form-data" novalidate>
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
                                        Select the environment where you want to upload the procedure file.
                                    </p>
                                </div>

                                <div class="mb-6">
                                    <label class="mb-2 block text-sm font-medium text-gray-700" for="clinic">
                                        <i class="fas fa-hospital mr-2"></i>
                                        Clinic Name
                                    </label>
                                    <div class="relative">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <i class="fas fa-building text-gray-400"></i>
                                        </div>
                                        <select class="@error("clinic") border-red-500 @enderror block w-full rounded-md border border-gray-300 py-2 pl-10 pr-3 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500" id="clinic" name="clinic" required>
                                            <option value="">Select a clinic</option>
                                            @foreach ($clinics as $clinic)
                                                <option value="{{ $clinic->ClinicShortName }}" {{ old("clinic") == $clinic->ClinicShortName ? "selected" : "" }}>
                                                    {{ $clinic->ClinicNameTH }} ({{ $clinic->ClinicShortName }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("clinic")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Select the clinic for this procedure file.
                                    </p>
                                </div>

                                <div class="mb-6">
                                    <label class="mb-2 block text-sm font-medium text-gray-700" for="file">
                                        <i class="fas fa-file-excel mr-2"></i>
                                        Excel File
                                    </label>
                                    <div class="relative">
                                        <input class="@error("file") border-red-500 @enderror block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100" id="file" type="file" name="file" accept=".xlsx,.xls" required>
                                        @error("file")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
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
                                    <a class="block w-full rounded-md bg-gray-100 px-6 py-3 text-center text-gray-700 transition-colors hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2" href="{{ env("APP_URL") }}/k2">
                                        <i class="fas fa-home mr-2"></i>
                                        Back to Home
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section("scripts")
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation and confirmation
            window.submitForm = function() {
                const form = document.querySelector('.needs-validation');
                const requiredFields = {
                    'environment': 'Environment',
                    'clinic': 'Clinic Name',
                    'file': 'Excel File'
                };

                let missingFields = [];

                // Check environment
                const environment = document.querySelector('input[name="environment"]:checked');
                if (!environment) {
                    missingFields.push('Environment');
                }

                // Check clinic
                const clinic = document.getElementById('clinic');
                if (!clinic.value) {
                    missingFields.push('Clinic Name');
                }

                // Check file
                const file = document.getElementById('file');
                if (!file.files.length) {
                    missingFields.push('Excel File');
                }

                if (missingFields.length > 0) {
                    Swal.fire({
                        title: 'Missing Required Fields',
                        html: `
                            <div class="text-left">
                                <p class="mb-2">Please fill in the following required fields:</p>
                                <ul class="list-disc list-inside">
                                    ${missingFields.map(field => `<li>${field}</li>`).join('')}
                                </ul>
                            </div>
                        `,
                        icon: 'warning',
                        confirmButtonColor: '#3085d6'
                    });
                    form.classList.add('was-validated');
                    return;
                }

                // Get form values for confirmation
                const environmentText = environment.value === 'K2DEV_SUR' ? 'Development' : 'Production';
                const clinicText = clinic.options[clinic.selectedIndex].text;
                const fileName = file.files[0].name;

                // Create confirmation message with SweetAlert2
                Swal.fire({
                    title: 'Confirm Upload',
                    html: `
                        <div class="text-left">
                            <p class="mb-2"><strong>Environment:</strong> ${environmentText}</p>
                            <p class="mb-2"><strong>Clinic:</strong> ${clinicText}</p>
                            <p class="mb-2"><strong>File:</strong> ${fileName}</p>
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
                        form.submit();
                    }
                });
            }

            // File input validation
            const fileInput = document.getElementById('file');
            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    const allowedTypes = ['.xlsx', '.xls'];
                    const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

                    if (!allowedTypes.includes(fileExtension)) {
                        this.value = '';
                        Swal.fire({
                            title: 'Invalid File Type',
                            text: 'Please select a valid Excel file (.xlsx or .xls)',
                            icon: 'error',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            }

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
        });
    </script>
@endsection

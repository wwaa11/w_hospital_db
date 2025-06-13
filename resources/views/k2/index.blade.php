@extends("layouts.app")

@section("content")
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-center">
            <div class="w-full max-w-3xl">
                <div class="rounded-lg bg-white shadow-lg">
                    <div class="rounded-t-lg bg-gradient-to-r from-blue-600 to-blue-800 px-8 py-6 text-white">
                        <h5 class="text-2xl font-bold">
                            <i class="fas fa-cogs mr-3 text-yellow-400"></i>
                            K2 Upload System
                        </h5>
                    </div>

                    <div class="p-8">
                        <div class="text-center">
                            <div class="mb-8">
                                <div class="mb-4 inline-block rounded-full bg-blue-100 p-4">
                                    <i class="fas fa-database text-4xl text-blue-600">Programmer Team</i>
                                </div>
                                <h2 class="mb-4 text-3xl font-bold text-gray-800">Welcome to K2 Upload System</h2>
                                <p class="mx-auto max-w-2xl text-lg text-gray-600">
                                    Choose an option below to manage your data uploads.
                                </p>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <a class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md" href="{{ env("APP_URL") }}/k2/procedure">
                                    <div class="mb-4 text-blue-600">
                                        <i class="fas fa-file-medical text-3xl"></i>
                                    </div>
                                    <h3 class="mb-2 text-xl font-semibold text-gray-800">Procedure Upload</h3>
                                    <p class="text-gray-600">
                                        Upload and manage procedure data for your clinics.
                                    </p>
                                </a>

                                <a class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md" href="{{ env("APP_URL") }}/k2/med3">
                                    <div class="mb-4 text-blue-600">
                                        <i class="fas fa-pills text-3xl"></i>
                                    </div>
                                    <h3 class="mb-2 text-xl font-semibold text-gray-800">Med3 Upload</h3>
                                    <p class="text-gray-600">
                                        Upload and manage medication and supply data.
                                    </p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

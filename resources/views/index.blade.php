@extends("layouts.app")

@section("content")
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-center">
            <div class="w-full max-w-3xl">
                <div class="rounded-lg bg-white shadow-lg">
                    <div class="rounded-t-lg bg-gradient-to-r from-blue-600 to-blue-800 px-8 py-6 text-white">
                        <h5 class="text-2xl font-bold">
                            <i class="fas fa-home mr-3 text-yellow-400"></i>
                            Welcome to Web Query!
                        </h5>
                    </div>

                    <div class="p-8">
                        <div class="text-center">
                            <div class="mb-8">
                                <div class="mb-4 inline-block rounded-full bg-blue-100 p-4">
                                    <i class="fas fa-database text-4xl text-blue-600">Programmer Team</i>
                                </div>
                                <h2 class="mb-4 text-3xl font-bold text-gray-800">Welcome to Our System</h2>
                                <p class="mx-auto max-w-2xl text-lg text-gray-600">
                                    Your central hub for efficient data management and query operations.
                                </p>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <a href="{{ route("depress") }}">
                                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md">
                                        <div class="mb-4 text-blue-600">
                                            <i class="fas fa-search text-3xl"></i>
                                        </div>
                                        <h3 class="mb-2 text-xl font-semibold text-gray-800">Depression</h3>
                                        <p class="text-gray-600">
                                            Export Depression Data.
                                        </p>
                                    </div>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

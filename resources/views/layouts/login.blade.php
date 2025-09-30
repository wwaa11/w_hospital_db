@extends("layouts.app")
@section("content")
    <div class="flex h-screen items-center justify-center">
        <div class="w-96 rounded bg-white p-8 shadow-md">
            <h2 class="mb-6 text-center text-2xl font-bold">Login</h2>
            <form id="loginForm" method="POST" action="{{ route("login.post") }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700" for="userid">User ID</label>
                    <input class="mt-1 block w-full rounded-md border border-gray-300 p-2" id="userid" type="text" name="userid" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700" for="password">Password</label>
                    <input class="mt-1 block w-full rounded-md border border-gray-300 p-2" id="password" type="password" name="password" required>
                </div>
                <div class="mb-4">
                    <button class="w-full rounded-md bg-blue-500 p-2 text-white hover:bg-blue-600" type="submit">Login</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section("scripts")
    <script>
        $(document).ready(function() {
            $("#loginForm").submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route("login.post") }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.status == "success") {
                            Swal.fire({
                                icon: "success",
                                title: "Login Successful",
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function() {
                                window.location.href = "{{ route("k2.index") }}";
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Login Failed",
                                text: response.message
                            });
                        }
                    }
                });
            });
        });
    </script>
@endsection

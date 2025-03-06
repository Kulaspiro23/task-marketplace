<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded shadow-md w-96">
            <h2 class="text-2xl font-semibold mb-6">Login</h2>

            <form id="login-form" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" id="email" class="w-full px-3 py-2 border rounded-lg" required>
                <span id="email-error" class="text-red-500 text-sm error-message"></span>
            </div>
            
            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" id="password" class="w-full px-3 py-2 border rounded-lg" required>
                <span id="password-error" class="text-red-500 text-sm error-message"></span>
            </div>

            <div class="mb-4 text-center">
                <span id="general-error" class="text-red-500 text-sm"></span>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Login</button>
                <a href="{{ route('register') }}" class="text-blue-500 hover:underline">Register</a>
            </div>
        </form>
        </div>
    </div>
    <script src="{{ asset('js/login.js') }}"></script>
</body>
</html>


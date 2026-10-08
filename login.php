<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Library System</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 min-h-screen">
<main class="mx-auto max-w-sm px-4 py-16">
    <h1 class="mb-1 text-2xl font-semibold text-gray-800">Library System</h1>
    <p class="mb-6 text-sm text-gray-500">Log in to search and borrow books.</p>

    <form method="post" action="login.php" novalidate class="space-y-4 rounded-lg bg-white p-6 shadow">
        <div>
            <label for="username" class="mb-1 block text-sm font-medium text-gray-700">Username</label>
            <input type="text" id="username" name="username" required
                   class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
        </div>
        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-gray-700">Password</label>
            <input type="password" id="password" name="password" required
                   class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
        </div>
        <button type="submit"
                class="w-full rounded bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700">
            Log in
        </button>
    </form>

    <p class="mt-4 text-center text-sm text-gray-600">
        No account yet?
        <a href="register.php" class="font-medium text-blue-600 hover:underline">Register here</a>
    </p>
</main>
</body>
</html>
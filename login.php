<?php
require_once __DIR__ . '/includes/auth.php';
    if (currentUser()) {
        header('Location: ' . (currentUser()['role'] === 'admin' ? 'admin-books.php' : 'books.php'));
        exit;
    }

    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Please enter your username and password.';
        } else {
            require_once __DIR__ . '/includes/db.php';

            try {
                $user = getDb()->selectCollection('users')->findOne(['username' => $username]);

                if ($user && password_verify($password, $user['passwordHash'])) {
                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'id'       => (string) $user['_id'],
                        'username' => $user['username'],
                        'role'     => $user['role'],
                    ];
                    header('Location: ' . ($user['role'] === 'admin' ? 'admin-books.php' : 'books.php'));
                    exit;
                }

                $error = 'Incorrect username or password.';
            } catch (Throwable $e) {
                $error = 'Something went wrong. Please try again.';
            }
        }
    }
?>
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
                <?php if ($error !== ''): ?>
                    <div class="rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

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
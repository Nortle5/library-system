<?php
require_once __DIR__ . '/includes/auth.php';

if (currentUser()) {
    header('Location: home.php');
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
                    'id'        => (string) $user['_id'],
                    'username'  => $user['username'],
                    'firstName' => $user['firstName'] ?? $user['username'],
                    'role'      => $user['role'],
                ];
                header('Location: home.php');
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
    <body class="min-h-screen bg-gray-900">
        <section class="relative flex min-h-screen items-center justify-center bg-cover bg-center px-4 py-10"
                style="background-image: url('images/library-home.jpg');">
            <div class="absolute inset-0 bg-black/55"></div>

            <div class="relative z-10 w-full max-w-sm">
                <div class="mb-6 flex flex-col items-center text-center text-white">
                    <img src="images/pcnl-logo.png" alt="PCNL logo" class="h-16 w-auto">
                    <h1 class="mt-3 text-2xl font-bold">PCNL Library System</h1>
                    <p class="mt-1 text-sm text-gray-300">Log in to search and borrow books.</p>
                </div>

                <form method="post" action="login.php" novalidate class="space-y-4 rounded-xl bg-white p-6 shadow-xl">
                    <?php if ($error !== ''): ?>
                        <div class="rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <div>
                        <label for="username" class="mb-1 block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" id="username" name="username" required autofocus autocomplete="username"
                            class="w-full rounded border border-gray-300 px-3 py-2 focus:border-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                            class="w-full rounded border border-gray-300 px-3 py-2 focus:border-orange-500 focus:outline-none">
                    </div>
                    <button type="submit"
                            class="w-full rounded bg-orange-500 px-4 py-2.5 font-semibold text-white hover:bg-orange-600">
                        Log in
                    </button>
                </form>

                <p class="mt-4 text-center text-sm text-gray-200">
                    No account yet?
                    <a href="register.php" class="font-semibold text-orange-400 hover:underline">Register here</a>
                </p>
            </div>
        </section>
    </body>
</html>
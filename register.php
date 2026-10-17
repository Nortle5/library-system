<?php
$errors = [];
$passed = false;
$allowedCourses = ['BSIT', 'BSCrim', 'BSA', 'BSE', 'BSCE', 'BSBA']; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName  = trim($_POST['firstName'] ?? '');
    $lastName   = trim($_POST['lastName'] ?? '');
    $studentId = trim($_POST['studentId'] ?? '');
    $age       = trim($_POST['age'] ?? '');
    $yearLevel = trim($_POST['yearLevel'] ?? '');
    $course    = trim($_POST['course'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm'] ?? '';

    $namePattern = '/^\p{L}[\p{L} .\'-]*$/u';

    if (mb_strlen($firstName) > 50 || !preg_match($namePattern, $firstName)) {
        $errors[] = 'First name must be letters only.';
    }
    if (mb_strlen($lastName) > 50 || !preg_match($namePattern, $lastName)) {
        $errors[] = 'Last name must be letters only.';
    }
    if (!preg_match('/^[A-Za-z0-9-]{4,20}$/', $studentId)) {
        $errors[] = 'Student ID must be 4 to 20 letters, numbers, or dashes.';
    }
    if (filter_var($age, FILTER_VALIDATE_INT) === false || $age < 15 || $age > 100) {
        $errors[] = 'Age must be a whole number from 15 to 100.';
    }
    if (!in_array($yearLevel, ['1', '2', '3', '4'], true)) {
        $errors[] = 'Please choose a year level.';
    }
    if (!in_array($course, $allowedCourses, true)) {
        $errors[] = 'Please choose a course.';
    }
    if (!preg_match('/^[A-Za-z0-9_]{4,20}$/', $username)) {
        $errors[] = 'Username must be 4 to 20 letters, numbers, or underscores.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        require_once __DIR__ . '/includes/db.php';

        try {
            $users = getDb()->selectCollection('users');

            if ($users->findOne(['username' => $username])) {
                $errors[] = 'That username is already taken.';
            }
            if ($users->findOne(['studentId' => $studentId])) {
                $errors[] = 'That student ID is already registered.';
            }

            if (empty($errors)) {
                $users->insertOne([
                    'firstName'    => $firstName,
                    'lastName'     => $lastName,
                    'studentId'    => $studentId,
                    'age'          => (int) $age,
                    'course'       => $course,
                    'yearLevel'    => (int) $yearLevel,
                    'username'     => $username,
                    'passwordHash' => password_hash($password, PASSWORD_DEFAULT),
                    'role'         => 'student',
                    'createdAt'    => new MongoDB\BSON\UTCDateTime(),
                ]);
                $passed = true;
            }
        } catch (Throwable $e) {
            $errors[] = 'Something went wrong while saving. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Register | Library System</title>
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    </head>
    <body class="bg-gray-100 min-h-screen">
        <main class="mx-auto max-w-md px-4 py-10">
            <h1 class="mb-6 text-2xl font-semibold text-gray-800">Student Registration</h1>

            <form method="post" action="register.php" novalidate class="space-y-4 rounded-lg bg-white p-6 shadow">
                
                <?php if (!empty($errors)): ?>
                    <div class="rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700">
                        <ul class="list-disc pl-5">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if ($passed): ?>
                    <div class="rounded border border-green-300 bg-green-50 p-3 text-sm text-green-700">
                        Registered! You can now <a href="login.php" class="font-medium underline">log in</a>.
                    </div>
                <?php endif; ?>


                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="firstName" class="mb-1 block text-sm font-medium text-gray-700">First name</label>
                        <input type="text" id="firstName" name="firstName" required
                            class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label for="lastName" class="mb-1 block text-sm font-medium text-gray-700">Last name</label>
                        <input type="text" id="lastName" name="lastName" required
                            class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label for="studentId" class="mb-1 block text-sm font-medium text-gray-700">Student ID</label>
                    <input type="text" id="studentId" name="studentId" required
                        class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="age" class="mb-1 block text-sm font-medium text-gray-700">Age</label>
                        <input type="number" id="age" name="age" min="15" max="100" required
                            class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label for="yearLevel" class="mb-1 block text-sm font-medium text-gray-700">Year level</label>
                        <select id="yearLevel" name="yearLevel" required
                                class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                            <option value="">Choose...</option>
                            <option>1</option><option>2</option><option>3</option><option>4</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="course" class="mb-1 block text-sm font-medium text-gray-700">Course</label>
                    <select id="course" name="course" required
                            class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                        <option value="">Choose...</option>
                        <option>BSIT</option>
                        <option>BSCE</option>
                        <option>BSCrim</option>
                        <option>BSA</option>
                        <option>BSE</option>
                        <option>BSBA</option>
                    </select>
                </div>

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

                <div>
                    <label for="confirm" class="mb-1 block text-sm font-medium text-gray-700">Confirm password</label>
                    <input type="password" id="confirm" name="confirm" required
                        class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none">
                </div>

                <button type="submit"
                        class="w-full rounded bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700">
                    Register
                </button>
                
            </form>
            <p class="mt-4 text-center text-sm text-gray-600">
                Already registered?
                <a href="login.php" class="font-medium text-blue-600 hover:underline">Log in</a>
            </p>
        </main>
        <script src="js/script.js"></script>
    </body>
</html>
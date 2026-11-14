<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$errors = [];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (mb_strlen($name) < 2) {
        $errors[] = 'Please enter your full name.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        try {
            if (emailExists($email)) {
                $errors[] = 'An account with that email already exists.';
            } else {
                registerUser($name, $email, $password);
                flash('success', 'Account created. You can sign in now.');
                header('Location: login.php');
                exit();
            }
        } catch (Throwable $e) {
            $errors[] = 'Could not create the account. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create account — <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Public Sans', system-ui, sans-serif; background: #eeeeee; color: #000; }
        .field-input { width:100%; height:2.9rem; padding:0 1rem 0 2.8rem; border:1px solid #000; border-radius:.75rem; background:#fff; outline:none; font-size:.95rem; transition:box-shadow .15s; }
        .field-input:focus { box-shadow: 0 0 0 3px rgba(79,122,58,.35); }
        .btn { background:#4f7a3a; transition:background .15s; }
        .btn:hover { background:#3d6130; }
        .btn:focus-visible { outline:3px solid #000; outline-offset:2px; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <main class="w-full max-w-[400px] bg-white border border-black rounded-[1.5rem] px-2 pt-5 pb-6">

        <header class="text-center">
            <svg class="mx-auto" width="44" height="41" viewBox="0 0 62 58" aria-hidden="true">
                <path d="M13 8C28 15 31 32 24 46 9 40 4 22 13 8Z" fill="#8bc34a"/>
                <path d="M13 8C20 20 22 32 24 46" stroke="#fff" stroke-width="1.2" fill="none" opacity=".6"/>
                <path d="M41 1C58 12 61 34 42 56 27 44 25 19 41 1Z" fill="#006b3c"/>
                <path d="M41 1C38 20 40 38 42 56" stroke="#fff" stroke-width="1.2" fill="none" opacity=".5"/>
            </svg>
            <h1 class="text-2xl font-extrabold leading-tight mt-1">Create account</h1>
            <p class="text-sm leading-tight">Join <?= htmlspecialchars(APP_NAME) ?></p>
        </header>

        <?php if ($errors): ?>
            <div class="mx-3 mt-5 p-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm" role="alert">
                <?php foreach ($errors as $err): ?>
                    <p class="flex items-start gap-2"><i class="fa-solid fa-circle-exclamation mt-0.5"></i><span><?= htmlspecialchars($err) ?></span></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="mt-6 px-3">
            <label for="name" class="block text-sm font-bold mb-1.5">Full name</label>
            <div class="relative mb-5">
                <i class="fa-solid fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-base text-slate-400"></i>
                <input type="text" id="name" name="name" required autocomplete="name" class="field-input" value="<?= htmlspecialchars($name) ?>">
            </div>
            <label for="email" class="block text-sm font-bold mb-1.5">Email</label>
            <div class="relative mb-5">
                <i class="fa-solid fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-base text-slate-400"></i>
                <input type="email" id="email" name="email" required autocomplete="username" class="field-input" value="<?= htmlspecialchars($email) ?>">
            </div>
            <label for="password" class="block text-sm font-bold mb-1.5">Password</label>
            <div class="relative mb-5">
                <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-base text-slate-400"></i>
                <input type="password" id="password" name="password" required autocomplete="new-password" minlength="8" class="field-input">
            </div>
            <label for="confirm" class="block text-sm font-bold mb-1.5">Confirm password</label>
            <div class="relative mb-5">
                <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-base text-slate-400"></i>
                <input type="password" id="confirm" name="confirm" required autocomplete="new-password" minlength="8" class="field-input">
            </div>

            <p class="text-xs text-gray-500 -mt-2 mb-4">At least 8 characters. New accounts are created as Staff.</p>

            <button type="submit" class="btn block w-[76%] mx-auto text-white font-bold py-2.5 text-sm rounded-lg">
                <i class="fa-solid fa-user-plus mr-1"></i> Create account
            </button>
        </form>

        <p class="mt-5 text-center text-sm text-gray-600">
            Already have an account? <a href="login.php" class="font-semibold text-[#4f7a3a] hover:underline">Sign in</a>
        </p>
    </main>
</body>
</html>

<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

// Allow revisiting the login form (e.g., via the browser Back button)
// without forcing a redirect to the dashboard.

$error = '';
$notice = getFlash('success');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } elseif (login($email, $password)) {
        $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
        unset($_SESSION['redirect_after_login']);
        $redirect = strpos($redirect, '/') === 0 ? $redirect : '/' . ltrim($redirect, '/');
        header('Location: ' . $redirect);
        exit();
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — <?= htmlspecialchars(APP_NAME) ?></title>
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
            <h1 class="text-2xl font-extrabold leading-tight mt-1"><?= htmlspecialchars(APP_NAME) ?></h1>
            <p class="text-sm leading-tight"><?= htmlspecialchars(APP_TAGLINE) ?></p>
        </header>

        <?php if ($notice): ?>
            <div class="mx-1 mt-6 p-3 bg-[#4f7a3a]/10 border border-[#4f7a3a]/30 text-[#3b5f2b] rounded-xl flex items-start gap-2 text-sm" role="status">
                <i class="fa-solid fa-circle-check mt-0.5"></i>
                <span><?= htmlspecialchars($notice) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mx-1 mt-6 p-3 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-start gap-2 text-sm" role="alert">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" class="mt-8 px-3">
            <label for="email" class="block text-sm font-bold mb-1.5">Email</label>
            <div class="relative mb-5">
                <i class="fa-solid fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-base text-slate-400"></i>
                <input type="email" id="email" name="email" required autocomplete="username"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" class="field-input">
            </div>

            <label for="password" class="block text-sm font-bold mb-1.5">Password</label>
            <div class="relative">
                <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-base text-slate-400"></i>
                <input type="password" id="password" name="password" required autocomplete="current-password" class="field-input">
            </div>

            <button type="submit" class="btn block w-[76%] mx-auto mt-6 text-white font-bold py-2.5 text-sm rounded-lg">
                <i class="fa-solid fa-right-to-bracket mr-1"></i> Sign In
            </button>
        </form>

        <p class="mt-5 text-center text-sm text-gray-600">
            New here? <a href="register.php" class="font-semibold text-[#4f7a3a] hover:underline">Create account</a>
        </p>

        <p class="mt-5 text-center text-[11px] text-gray-500 leading-relaxed">
            Demo: <code>admin@example.com</code> / <code>admin123</code><br>
            <code>staff@example.com</code> / <code>staff123</code>
        </p>
    </main>
    <script>
        (function () {
            var form = document.querySelector('form');
            if (!form) return;
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                try {
                    var res = await fetch(window.location.href, { method: 'POST', body: new FormData(form) });
                    if (res.url && res.url.indexOf('index.php') !== -1) {
                        window.location.href = 'index.php';
                    } else {
                        form.submit();
                    }
                } catch (err) {
                    form.submit();
                }
            });
        })();
    </script>
</body>
</html>
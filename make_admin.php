<?php
// php make_admin.php name@example.com
$email = strtolower(trim($argv[1] ?? ''));
if ($email === '') {
    exit("Usage: php make_admin.php name@example.com\n");
}

$pdo = new PDO('sqlite:' . __DIR__ . '/database/agri.db');
$stmt = $pdo->prepare("UPDATE app_user SET role = 'administrator' WHERE email = ?");
$stmt->execute([$email]);

echo $stmt->rowCount()
    ? "Done: $email is now an administrator. Sign out and sign in again.\n"
    : "No account found with that email. Check the spelling.\n";
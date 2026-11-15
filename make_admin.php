<?php
// Usage: php make_admin.php name@example.com
require __DIR__ . '/includes/database.php';

$email = strtolower(trim($argv[1] ?? ''));
if ($email === '') {
    exit("Usage: php make_admin.php name@example.com\n");
}

$db = new Database();
$rows = $db->select('app_user', 'user_id', ['email=eq.' . rawurlencode($email)], ['limit' => 1]);
if (empty($rows)) {
    exit("No account found with that email. Check the spelling.\n");
}

$db->update('app_user', ['role' => 'administrator'], ['email=eq.' . rawurlencode($email)]);
echo "Done: $email is now an administrator. Sign out and sign in again.\n";
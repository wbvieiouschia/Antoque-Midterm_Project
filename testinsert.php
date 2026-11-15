<?php
require __DIR__ . '/includes/database.php';
$db = new Database();
$data = [
    'first_name' => 'RenderTest',
    'last_name' => 'Farmer',
    'contact_number' => null,
    'address' => null,
    'date_registered' => '2026-01-01',
];
$res = $db->insert('farmer', $data);
var_dump($res);
$all = $db->select('farmer', 'farmer_id,first_name', ['first_name=eq.RenderTest']);
var_dump($all);

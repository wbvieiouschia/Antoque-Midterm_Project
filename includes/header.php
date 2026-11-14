<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/models.php';
$db = new Database();

// Determine active nav item based on current request path
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$activeNav = 'dashboard';
if (strpos($currentPath, '/pages/farmers.php') !== false) {
    $activeNav = 'farmers';
} elseif (strpos($currentPath, '/pages/farms.php') !== false) {
    $activeNav = 'farms';
} elseif (strpos($currentPath, '/pages/crops.php') !== false) {
    $activeNav = 'crops';
} elseif (strpos($currentPath, '/pages/planting.php') !== false) {
    $activeNav = 'planting';
} elseif (strpos($currentPath, '/pages/harvest.php') !== false) {
    $activeNav = 'harvest';
} elseif (strpos($currentPath, '/pages/reports.php') !== false) {
    $activeNav = 'reports';
} elseif ($currentPath === '/' || strpos($currentPath, 'index.php') !== false) {
    $activeNav = 'dashboard';
}

$navClass = function (string $key) use ($activeNav): string {
    $base = 'px-3 py-2 text-sm font-semibold rounded-md transition-colors';
    return $activeNav === $key
        ? $base . ' bg-[#f2c14e] text-[#3b2f33]'
        : $base . ' text-white/90 hover:bg-white/10 hover:text-white';
};

// Flash message config
$flashConfig = [
    'success' => ['bg-green-50 border-green-200 text-green-800', 'fa-check-circle text-green-600'],
    'error' => ['bg-red-50 border-red-200 text-red-800', 'fa-circle-exclamation text-red-600'],
    'warning' => ['bg-amber-50 border-amber-200 text-amber-800', 'fa-triangle-exclamation text-amber-600'],
    'info' => ['bg-blue-50 border-blue-200 text-blue-800', 'fa-info-circle text-blue-600'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(($pageTitle ?? APP_NAME) . ' — ' . APP_NAME); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .fade-in { animation: fadeIn 0.2s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
        .status-badge { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600; }
        /* Subtle accent on table headers (all pages) */
        thead tr { background-color: rgba(242, 193, 78, 0.18); border-bottom: 2px solid #f2c14e; }
        thead th { color: #3b2f33 !important; font-weight: 600; }
        .site-header a:focus-visible { box-shadow: 0 0 0 2px #4f7a3a, 0 0 0 4px #f2c14e; }
        /* Consistent focus ring for all interactive elements */
        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
            outline: none;
            box-shadow: 0 0 0 2px #fff, 0 0 0 4px #4f7a3a;
        }

        /* Brand palette: remap Tailwind blue/green utilities used across pages
           (--brand #4f7a3a, --brand-dark #3d6130, --accent #f2c14e) */
        .bg-blue-50, .hover\:bg-blue-50:hover, .bg-green-50 { background-color: rgba(79, 122, 58, 0.10) !important; }
        .bg-blue-100, .hover\:bg-blue-100:hover, .bg-green-100 { background-color: rgba(79, 122, 58, 0.18) !important; }
        .bg-blue-500, .bg-blue-600, .bg-green-500, .bg-green-600 { background-color: #4f7a3a !important; }
        .bg-blue-700, .hover\:bg-blue-600:hover, .hover\:bg-blue-700:hover,
        .hover\:bg-green-600:hover, .hover\:bg-green-700:hover, .bg-green-700 { background-color: #3d6130 !important; }
        .text-blue-500, .text-blue-600, .text-blue-700, .text-green-600, .text-green-700,
        .hover\:text-blue-600:hover, .hover\:text-blue-700:hover, .hover\:text-blue-800:hover { color: #4f7a3a !important; }
        .border-blue-200, .border-blue-300, .border-blue-500, .border-green-200, .border-green-300,
        .focus\:border-blue-500:focus, .focus\:border-green-500:focus { border-color: #4f7a3a !important; }
        .focus\:ring-blue-500:focus, .focus\:ring-green-500:focus, .focus\:ring-blue-400:focus { --tw-ring-color: rgba(79, 122, 58, 0.45) !important; }
        /* Soft green hover for table rows and light buttons */
        .hover\:bg-gray-50:hover, .hover\:bg-gray-100:hover { background-color: rgba(79, 122, 58, 0.08) !important; }
        /* keep white text on solid brand buttons */
        .bg-blue-500.text-white, .bg-blue-600.text-white, .bg-green-500.text-white, .bg-green-600.text-white,
        .bg-blue-700.text-white, .bg-green-700.text-white { color: #fff !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased">

<?php if (isLoggedIn()): ?>
    <header class="site-header bg-[#4f7a3a] shadow-sm sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/index.php" class="flex items-center gap-2 text-white">
                    <span class="w-9 h-9 rounded-full bg-white flex items-center justify-center">
                        <svg width="22" height="21" viewBox="0 0 62 58" aria-hidden="true">
                            <path d="M13 8C28 15 31 32 24 46 9 40 4 22 13 8Z" fill="#8bc34a"/>
                            <path d="M13 8C20 20 22 32 24 46" stroke="#fff" stroke-width="1.2" fill="none" opacity=".6"/>
                            <path d="M41 1C58 12 61 34 42 56 27 44 25 19 41 1Z" fill="#006b3c"/>
                            <path d="M41 1C38 20 40 38 42 56" stroke="#fff" stroke-width="1.2" fill="none" opacity=".5"/>
                        </svg>
                    </span>
                    <span class="font-bold text-lg tracking-tight"><?php echo htmlspecialchars(APP_NAME); ?></span>
                </a>
                <nav class="hidden md:flex items-center gap-1" aria-label="Main navigation">
                    <a href="/index.php" class="<?php echo $navClass('dashboard'); ?>">Dashboard</a>
                    <a href="/pages/farmers.php" class="<?php echo $navClass('farmers'); ?>">Farmers</a>
                    <a href="/pages/farms.php" class="<?php echo $navClass('farms'); ?>">Farms</a>
                    <a href="/pages/crops.php" class="<?php echo $navClass('crops'); ?>">Crops</a>
                    <a href="/pages/planting.php" class="<?php echo $navClass('planting'); ?>">Planting</a>
                    <a href="/pages/harvest.php" class="<?php echo $navClass('harvest'); ?>">Harvest</a>
                    <?php if (isAdmin()): ?>
                        <a href="/pages/reports.php" class="<?php echo $navClass('reports'); ?>">Reports</a>
                    <?php endif; ?>
                </nav>
                <div class="flex items-center gap-3">
                    <span class="hidden md:inline-flex items-center gap-2 text-sm text-white/90">
                        <span class="w-7 h-7 rounded-full bg-[#f2c14e] flex items-center justify-center text-xs font-bold text-[#3b2f33]">
                            <?php echo strtoupper(substr(currentUser()['name'] ?? 'U', 0, 1)); ?>
                        </span>
                        <span class="hidden lg:inline"><?php echo htmlspecialchars(
                            currentUser()['name'] ?? '',
                        ); ?></span>
                        <span class="text-xs font-medium uppercase text-white/60"><?php echo htmlspecialchars(
                            currentUser()['role'] ?? '',
                        ); ?></span>
                    </span>
                    <a href="/logout.php" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-white/90 hover:text-[#3b2f33] hover:bg-[#f2c14e] rounded-md transition-colors">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span class="hidden sm:inline">Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </header>
<?php endif; ?>

<?php foreach (['success', 'error', 'warning', 'info'] as $type) {
    $msg = getFlash($type);
    if ($msg) {
        [$cls, $iconCls] = $flashConfig[$type]; ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 fade-in" role="alert">
            <div class="flex items-start gap-3 px-4 py-3 rounded-lg border <?php echo $cls; ?>">
                <i class="fa-solid <?php echo $iconCls; ?> mt-0.5"></i>
                <p class="text-sm font-medium"><?php echo htmlspecialchars($msg); ?></p>
            </div>
        </div>
<?php
    }
} ?>

<?php if (isLoggedIn()): ?>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
<?php else: ?>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
<?php endif; ?>
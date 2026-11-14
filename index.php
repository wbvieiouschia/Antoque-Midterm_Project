<?php
require_once __DIR__ . '/includes/header.php';
requireAuth();

$db = new Database();
$farmerModel = new Farmer($db);
$farmModel = new Farm($db);
$cropModel = new Crop($db);
$plantingModel = new Planting($db);
$harvestModel = new Harvest($db);

// Summary counts
$counts = [
    'farmers' => $farmerModel->count(),
    'farms' => $farmModel->count(),
    'crops' => $cropModel->count(),
    'planting' => $plantingModel->count(),
    'harvest' => $harvestModel->count(),
];

// Recent records
$recentPlanting = $plantingModel->all(
    [],
    ['limit' => 5, 'order' => 'planting_id.desc'],
);
$recentHarvest = $harvestModel->all(
    [],
    ['limit' => 5, 'order' => 'harvest_id.desc'],
);

// Plantings by status for chart
$plantingStatus = $plantingModel->byStatus();

// Total harvested quantity
$totalHarvest = $harvestModel->totalQuantity();

$pageTitle = 'Dashboard';
?>
<h1 class="text-2xl font-bold text-gray-800 mb-6 fade-in">Dashboard</h1>

<!-- Summary Cards -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-4 fade-in">
    <div class="bg-white px-4 py-3 rounded-lg border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Total Farmers</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-0.5"><?php echo number_format($counts['farmers']); ?></h3>
            </div>
        </div>
    </div>
    <div class="bg-white px-4 py-3 rounded-lg border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Total Farms</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-0.5"><?php echo number_format($counts['farms']); ?></h3>
            </div>
        </div>
    </div>
    <div class="bg-white px-4 py-3 rounded-lg border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Total Crops</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-0.5"><?php echo number_format($counts['crops']); ?></h3>
            </div>
        </div>
    </div>
    <div class="bg-white px-4 py-3 rounded-lg border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Total Plantings</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-0.5"><?php echo number_format($counts['planting']); ?></h3>
            </div>
        </div>
    </div>
    <div class="bg-white px-4 py-3 rounded-lg border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Total Harvests</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-0.5"><?php echo number_format($counts['harvest']); ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Harvest totals -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8 fade-in">
    <div class="bg-white px-4 py-3 rounded-lg border border-gray-200">
        <p class="text-xs text-gray-500 uppercase font-semibold">Total Harvested Quantity</p>
        <h2 class="text-2xl font-bold text-gray-900 mt-0.5"><?php echo number_format($totalHarvest, 2); ?> kg</h2>
    </div>
    <div class="bg-white px-4 py-3 rounded-lg border border-gray-200">
        <p class="text-xs text-gray-500 uppercase font-semibold">Estimated Harvest Value</p>
        <h2 class="text-2xl font-bold text-gray-900 mt-0.5">₱ <?php echo number_format($totalHarvest * 25, 2); ?></h2>
    </div>
</div>

<!-- Plantings by status -->
<div class="mb-8 fade-in">
    <div class="bg-white p-6 rounded-lg border border-gray-200">
        <h3 class="font-semibold text-gray-800 mb-4">Plantings by status</h3>
        <?php
        $statusCounts = [];
        foreach ($plantingStatus as $r) {
            $statusCounts[$r['status']] = (int) $r['count'];
        }
        $statusList = PLANTING_STATUSES;
        $maxStatus = max(1, ...array_map(fn($st) => $statusCounts[$st] ?? 0, $statusList));
        ?>
        <div class="space-y-3">
            <?php foreach ($statusList as $st):
                $n = $statusCounts[$st] ?? 0;
                $pct = round(($n / $maxStatus) * 100); ?>
                <div class="flex items-center gap-4 text-sm">
                    <span class="w-20 text-gray-700"><?php echo $st; ?></span>
                    <div class="flex-1 h-3 rounded-full bg-gray-100 border border-gray-200 overflow-hidden"
                         role="progressbar" aria-label="<?php echo $st; ?>" aria-valuenow="<?php echo $n; ?>" aria-valuemin="0" aria-valuemax="<?php echo $maxStatus; ?>">
                        <div class="h-full rounded-full bg-[#4f7a3a]" style="width: <?php echo $pct; ?>%"></div>
                    </div>
                    <span class="w-8 text-right font-semibold text-gray-900"><?php echo $n; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Recent Records -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8 fade-in">
    <div class="bg-white p-6 rounded-lg border border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Recent Plantings</h3>
            <a href="/pages/planting.php" class="text-sm font-medium text-[#4f7a3a] hover:underline">View all</a>
        </div>
        <?php if (empty($recentPlanting)): ?>
            <p class="text-gray-500 text-sm">No planting records yet.</p>
        <?php else: ?>
            <div class="overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left">
                            <th class="py-2 px-3 font-semibold">ID</th>
                            <th class="py-2 px-3 font-semibold">Farm</th>
                            <th class="py-2 px-3 font-semibold">Crop</th>
                            <th class="py-2 px-3 font-semibold">Date</th>
                            <th class="py-2 px-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentPlanting as $p): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="py-2 px-3 text-gray-500"><?php echo (int) ($p['planting_id'] ?? 0); ?></td>
                                <td class="py-2 px-3 text-gray-700"><?php echo htmlspecialchars(
                                    $p['farm']['farm_name'] ?? '—',
                                ); ?></td>
                                <td class="py-2 px-3 text-gray-700"><?php echo htmlspecialchars(
                                    $p['crop']['crop_name'] ?? '—',
                                ); ?></td>
                                <td class="py-2 px-3 text-gray-700"><?php echo htmlspecialchars(
                                    $p['planting_date'] ?? '',
                                ); ?></td>
                                <td class="py-2">
                                    <span class="status-badge <?php echo $p['status'] === 'Harvested'
                                        ? 'bg-[#4f7a3a]/15 text-[#3b5f2b]'
                                        : 'bg-[#f2c14e]/30 text-[#6b4e0a]'; ?>">
                                        <?php echo htmlspecialchars($p['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white p-6 rounded-lg border border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Recent Harvests</h3>
            <a href="/pages/harvest.php" class="text-sm font-medium text-[#4f7a3a] hover:underline">View all</a>
        </div>
        <?php if (empty($recentHarvest)): ?>
            <p class="text-gray-500 text-sm">No harvest records yet.</p>
        <?php else: ?>
            <div class="overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left">
                            <th class="py-2 px-3 font-semibold">ID</th>
                            <th class="py-2 px-3 font-semibold">Date</th>
                            <th class="py-2 px-3 font-semibold">Quantity</th>
                            <th class="py-2 px-3 font-semibold">Price/kg</th>
                            <th class="py-2 px-3 font-semibold">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentHarvest as $h): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="py-2 px-3 text-gray-500"><?php echo (int) ($h['harvest_id'] ?? 0); ?></td>
                                <td class="py-2 px-3 text-gray-700"><?php echo htmlspecialchars(
                                    $h['harvest_date'] ?? '',
                                ); ?></td>
                                <td class="py-2 px-3 text-gray-700 font-medium"><?php echo number_format(
                                    $h['quantity_kg'] ?? 0,
                                    2,
                                ); ?> kg</td>
                                <td class="py-2 px-3 text-gray-700">₱<?php echo number_format(
                                    $h['selling_price_per_kg'] ?? 0,
                                    2,
                                ); ?></td>
                                <td class="py-2">
                                    <span class="status-badge <?php echo $h['quality_grade'] === 'Grade A'
                                        ? 'bg-[#4f7a3a]/15 text-[#3b5f2b]'
                                        : ($h['quality_grade'] === 'Grade B'
                                            ? 'bg-[#f2c14e]/30 text-[#6b4e0a]'
                                            : 'bg-stone-100 text-stone-600'); ?>">
                                        <?php echo htmlspecialchars($h['quality_grade']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
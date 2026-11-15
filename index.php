<?php
require_once __DIR__ . '/includes/auth.php';
requireAuth();
require_once __DIR__ . '/includes/header.php';

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
$totalHarvestValue = $harvestModel->totalValue();

$pageTitle = 'Dashboard';
?>
<h1 class="text-2xl font-bold text-gray-800 mb-6 fade-in">Hello, <?php echo htmlspecialchars(currentUser()['name'] ?? 'there'); ?></h1>

<!-- Summary Cards -->
<div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8 fade-in">
    <div class="bg-white px-5 py-4 rounded-lg border border-gray-200 text-center">
        <h3 class="text-3xl font-bold text-[#4f7a3a]"><?php echo number_format($counts['farmers']); ?></h3>
        <p class="text-sm text-gray-500 uppercase font-semibold mt-1">Total Farmers</p>
    </div>
    <div class="bg-white px-5 py-4 rounded-lg border border-gray-200 text-center">
        <h3 class="text-3xl font-bold text-[#4f7a3a]"><?php echo number_format($counts['farms']); ?></h3>
        <p class="text-sm text-gray-500 uppercase font-semibold mt-1">Total Farms</p>
    </div>
    <div class="bg-white px-5 py-4 rounded-lg border border-gray-200 text-center">
        <h3 class="text-3xl font-bold text-[#4f7a3a]"><?php echo number_format($counts['crops']); ?></h3>
        <p class="text-sm text-gray-500 uppercase font-semibold mt-1">Total Crops</p>
    </div>
    <div class="bg-white px-5 py-4 rounded-lg border border-gray-200 text-center">
        <h3 class="text-3xl font-bold text-[#4f7a3a]"><?php echo number_format($counts['harvest']); ?></h3>
        <p class="text-sm text-gray-500 uppercase font-semibold mt-1">Total Harvests</p>
    </div>
</div>

<!-- Three columns: harvest totals + lists + plantings by status -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 fade-in">
    <!-- Column 1: total harvested quantity + recently harvested by farmer -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200">
            <p class="text-xs text-gray-500 uppercase font-semibold">Total Harvested Quantity</p>
            <h2 class="text-2xl font-bold text-[#d4a017] mt-0.5"><?php echo number_format($totalHarvest, 2); ?> kg</h2>
        </div>
        <div class="p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Recently Harvested</h3>
            <?php
            $recentHarvests = $harvestModel->all([], ['limit' => 5, 'order' => 'harvest_id.desc']);
            $allPlantings3 = $plantingModel->all();
            $allFarms3 = $farmModel->all();
            $farmFarmer3 = [];
            foreach ($allFarms3 as $f) {
                $farmFarmer3[$f['farm_id']] = trim(($f['farmer']['first_name'] ?? '') . ' ' . ($f['farmer']['last_name'] ?? ''));
            }
            $plantingFarmer3 = [];
            foreach ($allPlantings3 as $p) {
                $plantingFarmer3[$p['planting_id']] = $farmFarmer3[$p['farm_id'] ?? 0] ?? 'Unknown';
            }
            ?>
            <?php if (empty($recentHarvests)): ?>
                <p class="text-gray-500 text-sm">No harvest records yet.</p>
            <?php else: ?>
                <ul class="space-y-2 text-sm">
                    <?php foreach ($recentHarvests as $h):
                        $pid = $h['planting']['planting_id'] ?? 0;
                        $farmerName = $plantingFarmer3[$pid] ?? 'Unknown';
                        if ($farmerName === '') {
                            $farmerName = 'Unknown';
                        }
                        $cropName = $h['planting']['crop']['crop_name'] ?? '—';
                        $qty = (float) ($h['quantity_kg'] ?? 0);
                    ?>
                    <li class="text-sm">
                        <div class="grid grid-cols-3 items-center gap-2">
                            <span class="text-gray-700 truncate"><?php echo htmlspecialchars($farmerName); ?></span>
                            <span class="text-center text-xs text-gray-500 truncate"><?php echo htmlspecialchars($cropName); ?></span>
                            <span class="text-right font-semibold text-gray-900"><?php echo number_format($qty, 2); ?> kg</span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Column 2: estimated harvest value + biggest value by farmer -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200">
            <p class="text-xs text-gray-500 uppercase font-semibold">Estimated Harvest Value</p>
            <h2 class="text-2xl font-bold text-[#d4a017] mt-0.5">₱ <?php echo number_format($totalHarvestValue, 2); ?></h2>
        </div>
        <div class="p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Biggest Harvest Value</h3>
            <?php
            $allHarvests3 = $harvestModel->all();
            $farmerValue3 = [];
            foreach ($allHarvests3 as $h) {
                $pid = $h['planting']['planting_id'] ?? 0;
                $name = $plantingFarmer3[$pid] ?? 'Unknown';
                if ($name === '') {
                    $name = 'Unknown';
                }
                $qty = (float) ($h['quantity_kg'] ?? 0);
                $price = (float) ($h['selling_price_per_kg'] ?? 0);
                $farmerValue3[$name] = ($farmerValue3[$name] ?? 0) + ($qty * $price);
            }
            arsort($farmerValue3);
            $topValues3 = array_slice($farmerValue3, 0, 5, true);
            ?>
            <?php if (empty($topValues3)): ?>
                <p class="text-gray-500 text-sm">No harvest records yet.</p>
            <?php else: ?>
                <ul class="space-y-2 text-sm">
                    <?php foreach ($topValues3 as $name => $val): ?>
                    <li class="flex justify-between gap-2">
                        <span class="text-gray-700 truncate"><?php echo htmlspecialchars($name); ?></span>
                        <span class="font-semibold text-gray-900">₱ <?php echo number_format($val, 2); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Column 3: total plantings + plantings by status -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200">
            <p class="text-xs text-gray-500 uppercase font-semibold">Total Plantings</p>
            <h2 class="text-2xl font-bold text-[#d4a017] mt-0.5"><?php echo number_format($counts['planting']); ?></h2>
        </div>
        <div class="p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Plantings by status</h3>
        <?php
        $statusCounts = [];
        foreach ($plantingModel->all() as $r) {
            $statusName = $r['planting_status']['status_name'] ?? 'Unknown';
            $statusCounts[$statusName] = ($statusCounts[$statusName] ?? 0) + 1;
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
                                    <span class="status-badge <?php echo ($p['planting_status']['status_name'] ?? '') === 'Harvested'
                                        ? 'bg-[#4f7a3a]/15 text-[#3b5f2b]'
                                        : 'bg-[#f2c14e]/30 text-[#6b4e0a]'; ?>">
                                        <?php echo htmlspecialchars($p['planting_status']['status_name'] ?? ''); ?>
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
                                    <span class="status-badge <?php echo ($h['quality_grade']['grade_name'] ?? '') === 'Grade A'
                                        ? 'bg-[#4f7a3a]/15 text-[#3b5f2b]'
                                        : (($h['quality_grade']['grade_name'] ?? '') === 'Grade B'
                                            ? 'bg-[#f2c14e]/30 text-[#6b4e0a]'
                                            : 'bg-stone-100 text-stone-600'); ?>">
                                        <?php echo htmlspecialchars($h['quality_grade']['grade_name'] ?? ''); ?>
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
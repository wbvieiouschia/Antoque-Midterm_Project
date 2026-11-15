<?php
require_once __DIR__ . '/../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud.php';

$db = new Database();
$cropModel = new Crop($db);
$plantingModel = new Planting($db);
$action = $_GET['action'] ?? 'list';
$id = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'crop_name' => trim($_POST['crop_name'] ?? ''),
        'variety' => trim($_POST['variety'] ?? ''),
        'crop_category_id' => (int) ($_POST['crop_category_id'] ?? 0),
        'growing_period_days' => (int) ($_POST['growing_period_days'] ?? 0),
        'season_id' => (int) ($_POST['season_id'] ?? 0),
    ];
    $errors = [];
    if (empty($data['crop_name'])) {
        $errors[] = 'Crop name is required.';
    }
    if (empty($data['variety'])) {
        $errors[] = 'Variety is required.';
    }
    if (!$data['crop_category_id']) {
        $errors[] = 'Category is required.';
    }
    if (!$data['growing_period_days']) {
        $errors[] = 'Growing period (days) is required.';
    }
    if (!$data['season_id']) {
        $errors[] = 'Recommended season is required.';
    }
    if ($errors) {
        flash('error', implode(' ', $errors));
        header('Location: /pages/crops.php' . ($action === 'edit' ? '?action=edit&id=' . $id : '?action=create'));
        exit();
    }
    if ($action === 'create') {
        $cropModel->create($data);
        flash('success', 'Crop created.');
        header('Location: /pages/crops.php');
        exit();
    }
    if ($action === 'edit' && $id > 0) {
        $cropModel->update($id, $data);
        flash('success', 'Crop updated.');
        header('Location: /pages/crops.php');
        exit();
    }
}

if ($action === 'delete' && $id > 0) {
    $plantings = $db->select('planting', 'planting_id', ['crop_id=eq.' . $id]);
    if (!empty($plantings)) {
        flash('error', 'Cannot delete crop: has planting records.');
    } else {
        $cropModel->delete($id);
        flash('success', 'Crop deleted.');
    }
    header('Location: /pages/crops.php');
    exit();
}

$pageTitle = 'Crops';
$categories = $db->select('crop_category', '*', [], ['order' => 'category_name.asc']);
$seasons = $db->select('season', '*', [], ['order' => 'season_name.asc']);

$search = trim($_GET['search'] ?? '');
$catFilter = $_GET['category'] ?? '';
$seasonFilter = $_GET['season'] ?? '';
$sort = $_GET['sort'] ?? 'crop_id';
$dir = $_GET['dir'] ?? 'asc';
$page = (int) ($_GET['page'] ?? 1);

$filters = [];
if ($search !== '') {
    foreach (preg_split('/\s+/', trim($search)) as $word) {
        if ($word === '') continue;
        $w = rawurlencode($word);
        $filters[] = 'or=(crop_name.ilike.%' . $w . '%,variety.ilike.%' . $w . '%)';
    }
}
if ($catFilter !== '') {
    $filters[] = 'crop_category_id=eq.' . (int) $catFilter;
}
if ($seasonFilter !== '') {
    $filters[] = 'season_id=eq.' . (int) $seasonFilter;
}

$countRows = $db->select('crop', 'crop_id', $filters);
$total = count($countRows);
$p = pagination($page, $total);
$rows = $cropModel->all(
    $filters,
    ['order' => $sort . '.' . $dir, 'limit' => $p['limit'], 'offset' => $p['offset']],
);

$editRow = null;
if ($action === 'edit' && $id > 0) {
    $editRow = $cropModel->find($id);
}

// View data
$vBadge = function ($status) {
    if ($status === 'Harvested') { return 'bg-[#4f7a3a]/15 text-[#3b5f2b]'; }
    if ($status === 'Growing') { return 'bg-[#f2c14e]/30 text-[#6b4e0a]'; }
    return 'bg-stone-100 text-stone-600';
};
$viewRow = null;
$viewPlantings = [];
if ($action === 'view' && $id > 0) {
    $vr = $cropModel->all(['crop_id=eq.' . $id], ['limit' => 1]);
    $viewRow = $vr[0] ?? null;
    if ($viewRow) {
        $viewPlantings = $plantingModel->all(['crop_id=eq.' . $id], ['order' => 'planting_id.desc']);
    }
}

$catOptions = ['Cereal', 'Vegetable', 'Fruit', 'Legume', 'Root Crop'];
$seasonOptions = ['Wet Season', 'Dry Season', 'Year Round'];
?>
<?php if ($action === 'view'): ?>
    <?php if (!$viewRow): ?>
        <div class="max-w-lg fade-in">
            <p class="text-gray-600 mb-3">Crop not found.</p>
            <a href="crops.php" class="text-blue-600 hover:underline">&larr; Back to list</a>
        </div>
    <?php else: ?>
        <div class="fade-in">
            <div class="flex justify-between items-end mb-4">
                <div>
                    <a href="crops.php" class="text-sm text-blue-600 hover:underline">&larr; Back to list</a>
                    <h1 class="text-2xl font-bold text-gray-800 mt-1"><?php echo e($viewRow['crop_name'] ?? ''); ?></h1>
                    <p class="text-gray-500 text-sm">Crop ID <?php echo (int) $viewRow['crop_id']; ?></p>
                </div>
                <a href="crops.php?action=edit&id=<?php echo (int) $viewRow['crop_id']; ?>" class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition flex items-center gap-2"><i class="fa-solid fa-pen"></i> Edit Crop</a>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg border border-gray-200 p-6 lg:col-span-1 h-fit">
                    <h2 class="font-semibold text-gray-800 mb-4">Details</h2>
                    <dl class="space-y-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase">Variety</dt>
                            <dd class="text-gray-800 mt-0.5"><?php echo e($viewRow['variety'] ?? '') ?: '—'; ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase">Category</dt>
                            <dd class="text-gray-800 mt-0.5"><?php echo e($viewRow['crop_category']['category_name'] ?? '') ?: '—'; ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase">Recommended season</dt>
                            <dd class="text-gray-800 mt-0.5"><?php echo e($viewRow['season']['season_name'] ?? '') ?: '—'; ?></dd>
                        </div>
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                            <div>
                                <dt class="text-xs font-semibold text-gray-500 uppercase">Growing period</dt>
                                <dd class="text-2xl font-bold text-gray-900 mt-0.5"><?php echo (int) ($viewRow['growing_period_days'] ?? 0); ?> <span class="text-sm font-medium text-gray-500">days</span></dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-gray-500 uppercase">Plantings</dt>
                                <dd class="text-2xl font-bold text-gray-900 mt-0.5"><?php echo count($viewPlantings); ?></dd>
                            </div>
                        </div>
                    </dl>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden lg:col-span-2">
                    <div class="px-6 py-4 border-b border-gray-100"><h2 class="font-semibold text-gray-800">Plantings</h2></div>
                    <?php if (empty($viewPlantings)): ?>
                        <p class="px-6 py-8 text-sm text-gray-500">This crop has not been planted yet.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead><tr class="text-left text-gray-500 uppercase text-xs"><th class="px-6 py-3">Farm</th><th class="px-6 py-3">Planted</th><th class="px-6 py-3">Exp. harvest</th><th class="px-6 py-3">Area (ha)</th><th class="px-6 py-3">Status</th></tr></thead>
                                <tbody class="divide-y divide-gray-50">
                                    <?php foreach ($viewPlantings as $vp): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-3 text-gray-700"><a href="farms.php?action=view&id=<?php echo (int) ($vp['farm_id'] ?? 0); ?>" class="text-[#4f7a3a] font-medium hover:underline"><?php echo e($vp['farm']['farm_name'] ?? '—'); ?></a></td>
                                            <td class="px-6 py-3 text-gray-700"><a href="planting.php?action=view&id=<?php echo (int) $vp['planting_id']; ?>" class="text-[#4f7a3a] font-medium hover:underline"><?php echo e($vp['planting_date'] ?? ''); ?></a></td>
                                            <td class="px-6 py-3 text-gray-700"><?php echo e($vp['expected_harvest_date'] ?? ''); ?></td>
                                            <td class="px-6 py-3 text-gray-700"><?php echo number_format((float) ($vp['area_planted'] ?? 0), 2); ?></td>
                                            <td class="px-6 py-3 "><span class="status-badge <?php echo $vBadge($vp['planting_status']['status_name'] ?? ''); ?>"><?php echo e($vp['planting_status']['status_name'] ?? ''); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php elseif ($action === 'create' || $action === 'edit'): ?>
    <div class="max-w-lg fade-in">
        <div class="bg-white p-6 rounded-lg border border-gray-200 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4"><?php echo $action === 'create'
                ? 'Add Crop'
                : 'Edit Crop'; ?></h2>
            <form method="POST" action="crops.php?action=<?php
            echo $action;
            echo $id > 0 ? '&id=' . $id : '';
            ?>" class="space-y-4">
                <input type="hidden" name="action" value="<?php echo $action; ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Crop Name <span class="text-red-500">*</span></label>
                    <input type="text" name="crop_name" required value="<?php echo e(
                        $editRow['crop_name'] ?? ($_POST['crop_name'] ?? ''),
                    ); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Variety <span class="text-red-500">*</span></label>
                    <input type="text" name="variety" required value="<?php echo e(
                        $editRow['variety'] ?? ($_POST['variety'] ?? ''),
                    ); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="crop_category_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                        <option value="">Select category</option>
                        <?php foreach ($categories as $c): ?><option value="<?php echo $c['crop_category_id']; ?>"<?php echo ($editRow['crop_category_id'] ?? 0) == $c['crop_category_id'] ? ' selected' : ''; ?>><?php echo e($c['category_name']); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Growing Period (days) <span class="text-red-500">*</span></label>
                        <input type="number" name="growing_period_days" required value="<?php echo e(
                            $editRow['growing_period_days'] ?? ($_POST['growing_period_days'] ?? ''),
                        ); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Recommended Season <span class="text-red-500">*</span></label>
                        <select name="season_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                            <option value="">Select season</option>
                            <?php foreach ($seasons as $s): ?><option value="<?php echo $s['season_id']; ?>"<?php echo ($editRow['season_id'] ?? 0) == $s['season_id'] ? ' selected' : ''; ?>><?php echo e($s['season_name']); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition"><?php echo $action ===
                    'create'
                        ? 'Create Crop'
                        : 'Update Crop'; ?></button>
                    <a href="crops.php" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-100 transition">Cancel</a>
                </div>
            </form>
        </div>
        <a href="crops.php" class="text-blue-600 hover:underline">&larr; Back to list</a>
    </div>
<?php else: ?>
    <div class="flex justify-between items-end mb-4 fade-in">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Crops</h1>
            <p class="text-gray-500 text-sm">Manage crop master data</p>
        </div>
        <a href="crops.php?action=create" class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition flex items-center gap-2"><i class="fa-solid fa-plus"></i> Add Crop</a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <form method="GET" action="crops.php" class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row gap-4 items-end">
            <div class="relative w-full md:w-1/3">
                <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
                <input type="text" id="searchInput" name="search" value="<?php echo e(
                    $search,
                ); ?>" placeholder="Search crop name or variety..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
            </div>
            <div class="relative w-full md:w-1/3">
                <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
                <select id="categoryFilter" name="category" onchange="this.form.submit()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                    <option value="">All</option>
                    <?php foreach ($categories as $c): ?><option value="<?php echo $c['crop_category_id']; ?>"<?php echo $catFilter == $c['crop_category_id'] ? ' selected' : ''; ?>><?php echo e($c['category_name']); ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="relative w-full md:w-1/3">
                <label class="block text-xs font-medium text-gray-500 mb-1">Season</label>
                <select id="seasonFilter" name="season" onchange="this.form.submit()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                    <option value="">All</option>
                    <?php foreach ($seasons as $s): ?><option value="<?php echo $s['season_id']; ?>"<?php echo $seasonFilter == $s['season_id'] ? ' selected' : ''; ?>><?php echo e($s['season_name']); ?></option><?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition flex items-center gap-2"><i class="fa-solid fa-search"></i> Search</button>
            <div class="flex gap-2">
                <a href="crops.php?search=<?php echo rawurlencode($search); ?>&category=<?php echo rawurlencode(
    $catFilter,
); ?>&season=<?php echo rawurlencode(
    $seasonFilter,
); ?>&sort=crop_id&dir=asc" class="px-4 py-2 text-sm font-medium rounded transition <?php echo $sort === 'crop_id'
    ? 'bg-blue-50 text-blue-600'
    : 'border border-gray-200 text-gray-700 hover:bg-gray-50'; ?>">Sort by ID</a>
                <a href="crops.php?search=<?php echo rawurlencode($search); ?>&category=<?php echo rawurlencode(
    $catFilter,
); ?>&season=<?php echo rawurlencode(
    $seasonFilter,
); ?>&sort=crop_name&dir=asc" class="px-4 py-2 text-sm font-medium rounded transition <?php echo $sort === 'crop_name'
    ? 'bg-blue-50 text-blue-600'
    : 'border border-gray-200 text-gray-700 hover:bg-gray-50'; ?>">Sort by Name</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-500 uppercase text-xs">
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Crop Name</th>
                        <th class="px-6 py-3">Variety</th>
                        <th class="px-6 py-3">Category</th>
                        <th class="px-6 py-3">Growing Period</th>
                        <th class="px-6 py-3">Season</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($rows as $r): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-gray-900"><?php echo $r['crop_id']; ?></td>
                            <td class="px-6 py-3 text-gray-700 font-medium"><?php echo e($r['crop_name']); ?></td>
                            <td class="px-6 py-3 text-gray-700"><?php echo e($r['variety']); ?></td>
                            <td class="px-6 py-3 text-gray-700"><?php echo e($r['crop_category']['category_name'] ?? '—'); ?></td>
                            <td class="px-6 py-3 text-gray-700"><?php echo $r['growing_period_days']; ?> days</td>
                            <td class="px-6 py-3 text-gray-700"><?php echo e($r['season']['season_name'] ?? '—'); ?></td>
                            <td class="px-6 py-3 text-right whitespace-nowrap">
                                <a href="crops.php?action=view&id=<?php echo $r['crop_id']; ?>" class="px-2.5 py-1 border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 mr-3">View</a>
                                <a href="crops.php?action=edit&id=<?php echo $r[
                                    'crop_id'
                                ]; ?>" class="text-blue-600 hover:underline text-sm">Edit</a>
                                <a href="crops.php?action=delete&id=<?php echo $r[
                                    'crop_id'
                                ]; ?>" class="text-red-600 hover:underline text-sm ml-4" data-confirm="Delete this crop? Related planting records will be affected.">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total > 0): ?>
            <div class="px-6 py-4 border-t border-gray-100">
                <?php echo renderPager(
                    $_SERVER['PHP_SELF'] .
                        '?' .
                        http_build_query(
                            array_filter([
                                'search' => $search,
                                'category' => $catFilter,
                                'season' => $seasonFilter,
                                'sort' => $sort,
                                'dir' => $dir,
                            ]),
                        ),
                    $p['page'],
                    $p['pages'],
                ); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($total === 0): ?>
        <div class="text-center py-12 text-gray-500">
            <i class="fa-solid fa-carrot text-4xl mb-3 opacity-30"></i>
            <p>No crops found.</p>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
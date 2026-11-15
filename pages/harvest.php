<?php
require_once __DIR__ . '/../includes/auth.php';
requireAuth();
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/crud.php';

$db = new Database();
$harvestModel = new Harvest($db);
$plantingModel = new Planting($db);
$action = $_GET['action'] ?? 'list';
$id = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'planting_id' => (int) ($_POST['planting_id'] ?? 0),
        'harvest_date' => trim($_POST['harvest_date'] ?? ''),
        'quantity_kg' => trim($_POST['quantity_kg'] ?? ''),
        'selling_price_per_kg' => trim($_POST['selling_price_per_kg'] ?? ''),
        'quality_grade_id' => (int) ($_POST['quality_grade_id'] ?? 0),
        'remark_id' => (int) ($_POST['remark_id'] ?? 0) ?: null,
        'remarks' => trim($_POST['remarks'] ?? ''),
    ];
    $errors = [];
    if (!$data['planting_id']) {
        $errors[] = 'Planting record is required.';
    }
    if (!$data['harvest_date'] || !DateTime::createFromFormat('Y-m-d', $data['harvest_date'])) {
        $errors[] = 'Valid harvest date is required.';
    }
    if (!is_numeric($data['quantity_kg']) || $data['quantity_kg'] <= 0) {
        $errors[] = 'Quantity must be positive.';
    }
    if (!is_numeric($data['selling_price_per_kg']) || $data['selling_price_per_kg'] < 0) {
        $errors[] = 'Selling price must be valid.';
    }
    if (!$data['quality_grade_id']) {
        $errors[] = 'Quality grade is required.';
    }
    if ($errors) {
        flash('error', implode(' ', $errors));
        header('Location: /pages/harvest.php' . ($action === 'edit' ? '?action=edit&id=' . $id : '?action=create'));
        exit();
    }
    if ($action === 'create') {
        $harvestModel->create($data);
        flash('success', 'Harvest record created.');
        header('Location: /pages/harvest.php');
        exit();
    }
    if ($action === 'edit' && $id > 0) {
        $harvestModel->update($id, $data);
        flash('success', 'Harvest updated.');
        header('Location: /pages/harvest.php');
        exit();
    }
}

if ($action === 'delete' && $id > 0) {
    $harvestModel->delete($id);
    flash('success', 'Harvest deleted.');
    header('Location: /pages/harvest.php');
    exit();
}

$pageTitle = 'Harvest Records';
$qualityGrades = $db->select('quality_grade', '*', [], ['order' => 'quality_grade_id.asc']);

$plantings = $plantingModel->all([], ['limit' => 100]);
$remarks = $db->select('harvest_remarks', 'remark_id,remark_description');

$search = trim($_GET['search'] ?? '');
$gradeFilter = $_GET['grade'] ?? '';
$sort = $_GET['sort'] ?? 'harvest_id';
$dir = $_GET['dir'] ?? 'asc';
$page = (int) ($_GET['page'] ?? 1);

$filters = [];
if ($search !== '') {
    $matchedGrade = null;
    foreach ($qualityGrades as $g) {
        if (stripos($g['grade_name'], $search) !== false) {
            $matchedGrade = $g['quality_grade_id'];
            break;
        }
    }
    if ($matchedGrade !== null) {
        $filters[] = 'quality_grade_id=eq.' . (int) $matchedGrade;
    } else {
        $filters[] = 'remarks=ilike.%' . rawurlencode($search) . '%';
    }
}
if ($gradeFilter !== '') {
    $filters[] = 'quality_grade_id=eq.' . (int) $gradeFilter;
}

$countRows = $db->select('harvest', 'harvest_id', $filters);
$total = count($countRows);
$p = pagination($page, $total);
$rows = $harvestModel->all(
    $filters,
    ['order' => $sort . '.' . $dir, 'limit' => $p['limit'], 'offset' => $p['offset']],
);

$editRow = null;
if ($action === 'edit' && $id > 0) {
    $editRow = $harvestModel->find($id);
}

// View data
$vGrade = function ($g) {
    if ($g === 'Grade A') { return 'bg-[#4f7a3a]/15 text-[#3b5f2b]'; }
    if ($g === 'Grade B') { return 'bg-[#f2c14e]/30 text-[#6b4e0a]'; }
    return 'bg-stone-100 text-stone-600';
};
$viewRow = null;
if ($action === 'view' && $id > 0) {
    $vr = $harvestModel->all(['harvest_id=eq.' . $id], ['limit' => 1]);
    $viewRow = $vr[0] ?? null;
}

$gradeOptions = ['Grade A', 'Grade B', 'Grade C'];
?>
<?php if ($action === 'view'): ?>
    <?php if (!$viewRow): ?>
        <div class="max-w-lg fade-in">
            <p class="text-gray-600 mb-3">Harvest not found.</p>
            <a href="harvest.php" class="text-blue-600 hover:underline">&larr; Back to list</a>
        </div>
    <?php else: ?>
        <div class="fade-in">
            <div class="flex justify-between items-end mb-4">
                <div>
                    <a href="harvest.php" class="text-sm text-blue-600 hover:underline">&larr; Back to list</a>
                    <h1 class="text-2xl font-bold text-gray-800 mt-1"><?php echo 'Harvest ' . (int) $viewRow['harvest_id']; ?></h1>
                    <p class="text-gray-500 text-sm"><?php echo e(($viewRow['planting']['farm']['farm_name'] ?? '—') . ' — ' . ($viewRow['planting']['crop']['crop_name'] ?? '—')); ?></p>
                </div>
                <a href="harvest.php?action=edit&id=<?php echo (int) $viewRow['harvest_id']; ?>" class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition flex items-center gap-2"><i class="fa-solid fa-pen"></i> Edit Harvest</a>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg border border-gray-200 p-6 lg:col-span-1 h-fit">
                    <h2 class="font-semibold text-gray-800 mb-4">Details</h2>
                    <dl class="space-y-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase">Farm</dt>
                            <dd class="text-gray-800 mt-0.5"><?php echo e($viewRow['planting']['farm']['farm_name'] ?? '—'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase">Crop</dt>
                            <dd class="text-gray-800 mt-0.5"><?php echo e($viewRow['planting']['crop']['crop_name'] ?? '—'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase">Planting</dt>
                            <dd class="text-gray-800 mt-0.5"><a href="planting.php?action=view&id=<?php echo (int) ($viewRow['planting_id'] ?? 0); ?>" class="text-[#4f7a3a] font-medium hover:underline">Planting <?php echo (int) ($viewRow['planting_id'] ?? 0); ?></a></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase">Harvest date</dt>
                            <dd class="text-gray-800 mt-0.5"><?php echo e($viewRow['harvest_date'] ?? '') ?: '—'; ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase">Quality grade</dt>
                            <dd class="text-gray-800 mt-0.5"><span class="status-badge <?php echo $vGrade($viewRow['quality_grade']['grade_name'] ?? ''); ?>"><?php echo e($viewRow['quality_grade']['grade_name'] ?? ''); ?></span></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase">Remarks</dt>
                            <dd class="text-gray-800 mt-0.5"><?php echo e($viewRow['remarks'] ?? '') ?: '—'; ?></dd>
                        </div>
                    </dl>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 p-6 lg:col-span-2 h-fit">
                    <h2 class="font-semibold text-gray-800 mb-4">Quantity and value</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Quantity</p>
                            <p class="text-2xl font-bold text-gray-900 mt-0.5"><?php echo number_format((float) ($viewRow['quantity_kg'] ?? 0), 2); ?> <span class="text-sm font-medium text-gray-500">kg</span></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Price per kg</p>
                            <p class="text-2xl font-bold text-gray-900 mt-0.5">₱<?php echo number_format((float) ($viewRow['selling_price_per_kg'] ?? 0), 2); ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Total value</p>
                            <p class="text-2xl font-bold text-gray-900 mt-0.5">₱<?php echo number_format((float) ($viewRow['quantity_kg'] ?? 0) * (float) ($viewRow['selling_price_per_kg'] ?? 0), 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php elseif ($action === 'create' || $action === 'edit'): ?>
    <div class="max-w-xl fade-in">
        <div class="bg-white p-6 rounded-lg border border-gray-200 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4"><?php echo $action === 'create'
                ? 'Add Harvest Record'
                : 'Edit Harvest Record'; ?></h2>
            <form method="POST" action="harvest.php?action=<?php
            echo $action;
            echo $id > 0 ? '&id=' . $id : '';
            ?>" class="space-y-4">
                <input type="hidden" name="action" value="<?php echo $action; ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Planting Record <span class="text-red-500">*</span></label>
                    <select name="planting_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                        <option value="">Select planting</option>
                        <?php foreach ($plantings as $pl): ?>
                            <option value="<?php echo $pl['planting_id']; ?>"<?php echo ($editRow['planting_id'] ??
    0) ==
$pl['planting_id']
    ? ' selected'
    : ''; ?>>
                                <?php echo $pl['planting_id']; ?> — <?php echo e(
     $pl['farm']['farm_name'] ?? '?',
 ); ?> / <?php echo e($pl['crop']['crop_name'] ?? '?'); ?> (<?php echo e($pl['planting_date']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harvest Date <span class="text-red-500">*</span></label>
                        <input type="date" name="harvest_date" required value="<?php echo e(
                            $editRow['harvest_date'] ?? ($_POST['harvest_date'] ?? ''),
                        ); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity (kg) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="quantity_kg" required value="<?php echo e(
                            $editRow['quantity_kg'] ?? ($_POST['quantity_kg'] ?? ''),
                        ); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price (per kg) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="selling_price_per_kg" required value="<?php echo e(
                            $editRow['selling_price_per_kg'] ?? ($_POST['selling_price_per_kg'] ?? ''),
                        ); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quality Grade <span class="text-red-500">*</span></label>
                        <select name="quality_grade_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                            <option value="">Select grade</option>
                            <?php foreach ($qualityGrades as $g): ?><option value="<?php echo $g['quality_grade_id']; ?>"<?php echo ($editRow['quality_grade_id'] ?? 0) == $g['quality_grade_id'] ? ' selected' : ''; ?>><?php echo e($g['grade_name']); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harvest Remark (lookup)</label>
                    <select name="remark_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                        <option value="">— None —</option>
                        <?php foreach ($remarks as $rm): ?><option value="<?php echo $rm[
    'remark_id'
]; ?>"<?php echo ($editRow['remark_id'] ?? 0) == $rm['remark_id'] ? ' selected' : ''; ?>><?php echo e(
    $rm['remark_id'] . ' — ' . $rm['remark_description'],
); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Remarks (free text)</label>
                    <textarea name="remarks" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none"><?php echo e(
                        $editRow['remarks'] ?? ($_POST['remarks'] ?? ''),
                    ); ?></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition"><?php echo $action ===
                    'create'
                        ? 'Create Record'
                        : 'Update Record'; ?></button>
                    <a href="harvest.php" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-100 transition">Cancel</a>
                </div>
            </form>
        </div>
        <a href="harvest.php" class="text-blue-600 hover:underline">&larr; Back to list</a>
    </div>
<?php else: ?>
    <div class="flex justify-between items-end mb-4 fade-in">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Harvest Records</h1>
            <p class="text-gray-500 text-sm">Track harvests and quality</p>
        </div>
        <a href="harvest.php?action=create" class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition flex items-center gap-2"><i class="fa-solid fa-plus"></i> Add Record</a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <form method="GET" action="harvest.php" class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row gap-4 items-end">
            <div class="relative w-full md:w-1/3">
                <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
                <input type="text" id="searchInput" name="search" value="<?php echo e(
                    $search,
                ); ?>" placeholder="Search by quality grade..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
            </div>
            <div class="relative w-full md:w-1/3">
                <label class="block text-xs font-medium text-gray-500 mb-1">Quality Grade</label>
                <select id="gradeFilter" name="grade" onchange="this.form.submit()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                    <option value="">All</option>
                    <?php foreach ($qualityGrades as $g): ?><option value="<?php echo $g['quality_grade_id']; ?>"<?php echo $gradeFilter == $g['quality_grade_id'] ? ' selected' : ''; ?>><?php echo e($g['grade_name']); ?></option><?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition flex items-center gap-2"><i class="fa-solid fa-search"></i> Search</button>
            <div class="flex gap-2">
                <a href="harvest.php?search=<?php echo rawurlencode($search); ?>&grade=<?php echo rawurlencode(
    $gradeFilter,
); ?>&sort=harvest_id&dir=asc" class="px-4 py-2 text-sm font-medium rounded transition <?php echo $sort === 'harvest_id'
    ? 'bg-blue-50 text-blue-600'
    : 'border border-gray-200 text-gray-700 hover:bg-gray-50'; ?>">Sort by ID</a>
                <a href="harvest.php?search=<?php echo rawurlencode($search); ?>&grade=<?php echo rawurlencode(
    $gradeFilter,
); ?>&sort=harvest_date&dir=asc" class="px-4 py-2 text-sm font-medium rounded transition <?php echo $sort ===
'harvest_date'
    ? 'bg-blue-50 text-blue-600'
    : 'border border-gray-200 text-gray-700 hover:bg-gray-50'; ?>">Sort by Date</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-500 uppercase text-xs">
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Planting</th>
                        <th class="px-6 py-3">Harvest Date</th>
                        <th class="px-6 py-3">Quantity (kg)</th>
                        <th class="px-6 py-3">Price/kg</th>
                        <th class="px-6 py-3">Grade</th>
                        <th class="px-6 py-3">Value</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($rows as $r): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-gray-900"><?php echo $r['harvest_id']; ?></td>
                            <td class="px-6 py-3 text-gray-700"><?php echo $r['planting_id']; ?> (<?php echo e(
     $r['planting']['farm']['farm_name'] ?? '—',
 ); ?>)</td>
                            <td class="px-6 py-3 text-gray-700"><?php echo e($r['harvest_date']); ?></td>
                            <td class="px-6 py-3 text-gray-700 font-medium"><?php echo number_format(
                                $r['quantity_kg'],
                                2,
                            ); ?></td>
                            <td class="px-6 py-3 text-gray-700">₱<?php echo number_format(
                                $r['selling_price_per_kg'],
                                2,
                            ); ?></td>
                            <td class="px-6 py-3">
                                <span class="status-badge <?php echo ($r['quality_grade']['grade_name'] ?? '') === 'Grade A'
                                    ? 'bg-green-100 text-green-700'
                                    : (($r['quality_grade']['grade_name'] ?? '') === 'Grade B'
                                        ? 'bg-blue-100 text-blue-700'
                                        : 'bg-gray-100 text-gray-700'); ?>">
                                    <?php echo e($r['quality_grade']['grade_name'] ?? ''); ?>
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-700 font-medium">₱<?php echo number_format(
                                $r['quantity_kg'] * $r['selling_price_per_kg'],
                                2,
                            ); ?></td>
                            <td class="px-6 py-3 text-right">
                                <a href="harvest.php?action=view&id=<?php echo $r['harvest_id']; ?>" class="px-2.5 py-1 border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 mr-3">View</a>
                                <a href="harvest.php?action=edit&id=<?php echo $r[
                                    'harvest_id'
                                ]; ?>" class="text-blue-600 hover:underline text-sm">Edit</a>
                                <a href="harvest.php?action=delete&id=<?php echo $r[
                                    'harvest_id'
                                ]; ?>" class="text-red-600 hover:underline text-sm ml-4" data-confirm="Delete this harvest record?">Delete</a>
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
                                'grade' => $gradeFilter,
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
            <i class="fa-solid fa-wheat-awn text-4xl mb-3 opacity-30"></i>
            <p>No harvest records found.</p>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
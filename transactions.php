<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $pdo = get_db();

    if ($_POST['action'] === 'add_transaction') {
        $date = $_POST['date'] ?? date(DATE_FORMAT);
        $type = $_POST['type'] ?? 'expense';
        $category = $_POST['category'] ?? '';
        $amount = $_POST['amount'] ?? 0;
        $notes = $_POST['notes'] ?? '';

        if ($date && $type && $category && $amount > 0) {
            $stmt = $pdo->prepare("
                INSERT INTO transactions (date, type, category, amount, notes)
                VALUES (:date, :type, :category, :amount, :notes)
            ");

            if ($stmt->execute([
                ':date' => $date,
                ':type' => $type,
                ':category' => $category,
                ':amount' => (float)$amount,
                ':notes' => $notes
            ])) {
                set_flash_message('Transaction added successfully!', 'success');
                header('Location: transactions.php');
                exit;
            } else {
                set_flash_message('Error adding transaction', 'error');
            }
        } else {
            set_flash_message('Please fill in all required fields', 'error');
        }
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (delete_transaction($_POST['delete_id'])) {
        set_flash_message('Transaction deleted successfully!', 'success');
        header('Location: transactions.php');
        exit;
    }
}

// Get filters
$filter_type = $_GET['type'] ?? null;
$filter_category = $_GET['category'] ?? null;
$filter_start_date = $_GET['start_date'] ?? null;
$filter_end_date = $_GET['end_date'] ?? null;

// Get transactions with balance
$transactions = get_transactions_with_balance($filter_type, $filter_category, $filter_start_date, $filter_end_date);

require_once 'includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">💳 Transactions</h1>
    <p class="page-subtitle">Manage your income and expenses</p>
</div>

<!-- Add Transaction Form -->
<div class="form-card">
    <div class="form-card-title">➕ Add New Transaction</div>

    <form method="POST" action="">
        <input type="hidden" name="action" value="add_transaction">

        <div class="form-row">
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" value="<?php echo date(DATE_FORMAT); ?>" required>
            </div>

            <div class="form-group">
                <label for="type">Type</label>
                <select id="type" name="type" required onchange="updateCategories()">
                    <option value="expense">Expense</option>
                    <option value="income">Income</option>
                </select>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <?php foreach (EXPENSE_CATEGORIES as $cat): ?>
                        <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="amount">Amount ($)</label>
                <input type="number" id="amount" name="amount" placeholder="0.00" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="notes">Notes (Optional)</label>
                <input type="text" id="notes" name="notes" placeholder="Add notes...">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">✓ Add Transaction</button>
    </form>
</div>

<!-- Filters -->
<div class="filter-section">
    <div class="filter-title">🔍 Filter Transactions</div>

    <form method="GET" action="" style="display: contents;">
        <div class="filter-row">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="filter_type">Type</label>
                <select id="filter_type" name="type">
                    <option value="">All Types</option>
                    <option value="income" <?php echo $filter_type === 'income' ? 'selected' : ''; ?>>Income</option>
                    <option value="expense" <?php echo $filter_type === 'expense' ? 'selected' : ''; ?>>Expense</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="filter_category">Category</label>
                <select id="filter_category" name="category">
                    <option value="">All Categories</option>
                    <?php
                    $all_categories = array_merge(INCOME_CATEGORIES, EXPENSE_CATEGORIES);
                    $all_categories = array_unique($all_categories);
                    sort($all_categories);
                    foreach ($all_categories as $cat):
                    ?>
                        <option value="<?php echo $cat; ?>" <?php echo $filter_category === $cat ? 'selected' : ''; ?>>
                            <?php echo $cat; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="filter_start_date">From Date</label>
                <input type="date" id="filter_start_date" name="start_date" value="<?php echo $filter_start_date; ?>">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="filter_end_date">To Date</label>
                <input type="date" id="filter_end_date" name="end_date" value="<?php echo $filter_end_date; ?>">
            </div>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary btn-sm">🔍 Filter</button>
            <a href="transactions.php" class="btn btn-secondary btn-sm">↺ Reset</a>
        </div>
    </form>
</div>

<!-- Transactions Table -->
<div class="form-card">
    <div class="form-card-title">📊 All Transactions</div>

    <?php if (empty($transactions)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <div class="text-muted">No transactions found</div>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Notes</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Balance</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td><?php echo format_date($tx['date']); ?></td>
                            <td>
                                <span style="background: <?php echo $tx['type'] === 'income' ? 'var(--success-color)' : 'var(--danger-color)'; ?>; color: white; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                    <?php echo ucfirst($tx['type']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($tx['category']); ?></td>
                            <td class="text-muted"><?php echo !empty($tx['notes']) ? htmlspecialchars($tx['notes']) : '—'; ?></td>
                            <td class="text-right <?php echo $tx['type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ($tx['type'] === 'income' ? '+' : '−') . format_currency($tx['amount']); ?>
                            </td>
                            <td class="text-right">
                                <strong><?php echo format_currency($tx['balance']); ?></strong>
                            </td>
                            <td class="text-center">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="delete_id" value="<?php echo $tx['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirmDelete()">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
function updateCategories() {
    const type = document.getElementById('type').value;
    const categorySelect = document.getElementById('category');
    categorySelect.innerHTML = '<option value="">Select Category</option>';

    const categories = type === 'income'
        ? <?php echo json_encode(INCOME_CATEGORIES); ?>
        : <?php echo json_encode(EXPENSE_CATEGORIES); ?>;

    categories.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat;
        option.textContent = cat;
        categorySelect.appendChild(option);
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>

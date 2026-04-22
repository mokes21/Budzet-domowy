<?php
// transactions.php
require_once 'includes/header.php';

$is_child = $_SESSION['role'] === 'child';
$child_filter = $is_child ? " AND by_child = 1" : "";
$by_child_val = $is_child ? 1 : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_transaction') {
        $type = $_POST['type'];
        $category = trim($_POST['category']);
        $amount = floatval($_POST['amount']);
        $description = trim($_POST['description']);
        $date = $_POST['date'];
        
        // Validation
        if (!empty($category) && $amount > 0 && !empty($date)) {
            $stmt = $pdo->prepare("INSERT INTO transactions (type, category, amount, description, date, by_child) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$type, $category, $amount, $description, $date, $by_child_val]);
            
            // Redirect to avoid form resubmission
            header("Location: transactions.php?success=1");
            exit;
        } else {
            $error = "Please fill in all required fields with valid values.";
        }
    }
}

// Fetch all transactions
$transactions = $pdo->query("SELECT * FROM transactions WHERE 1=1 $child_filter ORDER BY date DESC, id DESC LIMIT 50")->fetchAll();
?>

<div class="dashboard-grid">
    <!-- Form to Add Transaction -->
    <div class="card glass" style="grid-column: 1 / 2; height: fit-content;">
        <div class="card-header">
            <h3 class="card-title">Add New Transaction</h3>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div style="background: rgba(76,175,80,0.2); border: 1px solid var(--primary); padding: 10px; border-radius: var(--radius-md); margin-bottom: 20px; color: var(--text-main);">
                Transaction added successfully!
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div style="background: rgba(255,82,82,0.2); border: 1px solid var(--accent-red); padding: 10px; border-radius: var(--radius-md); margin-bottom: 20px; color: var(--text-main);">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="transactions.php">
            <input type="hidden" name="action" value="add_transaction">
            
            <div class="form-group flex gap-2" style="gap: 16px;">
                <div style="flex: 1;">
                    <label>Type *</label>
                    <select name="type" class="form-control" required>
                        <option value="expense">Expense</option>
                        <option value="income">Income</option>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label>Amount (PLN) *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required placeholder="0.00">
                </div>
            </div>

            <div class="form-group">
                <label>Category *</label>
                <!-- Datalist allows free text but suggests these important ones -->
                <input type="text" name="category" list="categories" class="form-control" required placeholder="e.g. food, taxes, bus pass">
                <datalist id="categories">
                    <option value="food">
                    <option value="taxes">
                    <option value="bus pass">
                    <option value="books">
                    <option value="clothes">
                    <option value="utilities">
                    <option value="entertainment">
                    <option value="allowance">
                </datalist>
            </div>

            <div class="form-group">
                <label>Date *</label>
                <input type="date" name="date" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" class="form-control" placeholder="Optional details...">
            </div>

            <div class="form-group mt-4">
                <button type="submit" class="btn" style="width: 100%;">Save Transaction</button>
            </div>
        </form>
    </div>

    <!-- Recent Transactions List -->
    <div class="card glass" style="grid-column: 2 / 3;">
        <div class="card-header">
            <h3 class="card-title">Recent Activity</h3>
        </div>
        
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <?php if (count($transactions) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Details</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td style="white-space: nowrap; font-size: 0.85rem; color: var(--text-muted);">
                            <?= htmlspecialchars($t['date']) ?>
                        </td>
                        <td>
                            <div style="margin-bottom: 4px;">
                                <span class="badge <?= $t['type'] === 'income' ? 'income' : 'expense' ?>">
                                    <?= htmlspecialchars($t['category']) ?>
                                </span>
                                <?php if (!$is_child && $t['by_child'] == 1): ?>
                                    <span class="badge" style="background: rgba(255,255,255,0.1); color: #fff;">Child</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 0.9rem;">
                                <?= htmlspecialchars($t['description']) ?: '—' ?>
                            </div>
                        </td>
                        <td class="amount text-right <?= $t['type'] === 'income' ? 'income' : 'expense' ?>">
                            <?= $t['type'] === 'income' ? '+' : '-' ?><?= number_format($t['amount'], 2) ?> PLN
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">No transactions recorded yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

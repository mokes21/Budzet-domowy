<?php
// parents.php
require_once 'includes/header.php';

// strictly parents only
if ($_SESSION['role'] === 'child') {
    header("Location: index.php");
    exit;
}

// Fetch all child data
$child_income_total = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type='income' AND by_child=1")->fetchColumn() ?: 0;
$child_expense_total = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type='expense' AND by_child=1")->fetchColumn() ?: 0;
$child_savings = $pdo->query("SELECT amount FROM savings WHERE for_child=1")->fetchColumn() ?: 0;

$goals = $pdo->query("SELECT * FROM goals WHERE for_child=1")->fetchAll();
$transactions = $pdo->query("SELECT * FROM transactions WHERE by_child=1 ORDER BY date DESC LIMIT 20")->fetchAll();
?>

<div class="dashboard-grid">
    
    <!-- Child's Overview -->
    <div class="card glass" style="grid-column: 1 / -1;">
        <div class="card-header">
            <h3 class="card-title">Child's Financial Overview</h3>
        </div>
        <div class="flex gap-2" style="justify-content: space-around; flex-wrap: wrap;">
            <div class="text-center p-4">
                <div class="label" style="color: var(--text-muted); font-size: 0.9rem;">Total Income</div>
                <div class="amount income" style="font-size: 1.8rem;"><?= number_format($child_income_total, 2) ?> PLN</div>
            </div>
            <div class="text-center p-4">
                <div class="label" style="color: var(--text-muted); font-size: 0.9rem;">Total Spent</div>
                <div class="amount expense" style="font-size: 1.8rem;"><?= number_format($child_expense_total, 2) ?> PLN</div>
            </div>
            <div class="text-center p-4">
                <div class="label" style="color: var(--text-muted); font-size: 0.9rem;">Savings Lock</div>
                <div class="amount" style="font-size: 1.8rem; color: var(--accent-blue);"><?= number_format($child_savings, 2) ?> PLN</div>
            </div>
        </div>
    </div>

    <!-- Child's Recent Transactions -->
    <div class="card glass">
        <div class="card-header">
            <h3 class="card-title">Recent Activity</h3>
        </div>
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <?php if (count($transactions) > 0): ?>
            <table>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td style="font-size: 0.85rem; color: var(--text-muted); white-space: nowrap;">
                            <?= htmlspecialchars($t['date']) ?>
                        </td>
                        <td>
                            <span class="badge <?= $t['type'] === 'income' ? 'income' : 'expense' ?>">
                                <?= htmlspecialchars($t['category']) ?>
                            </span>
                            <div style="font-size: 0.85rem; margin-top: 4px;">
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
            <div class="empty-state">No activity by child found.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Child's Goals -->
    <div class="card glass">
        <div class="card-header">
            <h3 class="card-title">Child's Active Goals</h3>
        </div>
        <div>
            <?php if (count($goals) > 0): ?>
                <?php foreach ($goals as $g): ?>
                    <div style="background: rgba(0,0,0,0.2); padding: 16px; border-radius: var(--radius-md); margin-bottom: 12px;">
                        <div class="flex justify-between items-center mb-2">
                            <strong style="color: var(--text-main); font-size: 1rem;"><?= htmlspecialchars($g['title']) ?></strong>
                            <span class="badge goal">
                                <?= number_format($g['current_amount'], 0) ?> / <?= number_format($g['target_amount'], 0) ?> PLN
                            </span>
                        </div>
                        <div class="progress-tube" style="height: 6px;">
                            <?php $gpct = $g['target_amount'] > 0 ? min(100, ($g['current_amount'] / $g['target_amount']) * 100) : 0; ?>
                            <div class="progress-fill" style="width: <?= $gpct ?>%; background: var(--accent-blue)"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">Child hasn't set any goals yet.</div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>

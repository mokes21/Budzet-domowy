<?php
// index.php
require_once 'includes/header.php';

$is_child = $_SESSION['role'] === 'child';
$child_filter = $is_child ? " WHERE by_child = 1" : "";
$and_child_filter = $is_child ? " AND by_child = 1" : "";
$goals_filter = $is_child ? " WHERE for_child = 1" : "";
$savings_filter = $is_child ? " WHERE for_child = 1" : "";

// Calculate totals
$total_income = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type='income' $and_child_filter")->fetchColumn() ?: 0;
$total_expense = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type='expense' $and_child_filter")->fetchColumn() ?: 0;

// Important expenses
$important_expenses = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type='expense' AND category IN ('food', 'books', 'bus passes', 'bus_passes') $and_child_filter")->fetchColumn() ?: 0;
$other_expenses = $total_expense - $important_expenses;

// Goals & Savings totals
$goals_total = $pdo->query("SELECT SUM(current_amount) FROM goals $goals_filter")->fetchColumn() ?: 0;
$savings_total = $pdo->query("SELECT SUM(amount) FROM savings $savings_filter")->fetchColumn() ?: 0;

$total_saved = $goals_total + $savings_total;
$leftover = $total_income - $total_expense - $total_saved;

// Recent spendings
$recent_expenses = $pdo->query("SELECT * FROM transactions WHERE type='expense' $and_child_filter ORDER BY id DESC LIMIT 5")->fetchAll();

// Child stats (for parent) or Monthly stats (for child)
if (!$is_child) {
    $child_inc = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type='income' AND by_child=1")->fetchColumn() ?: 0;
    $child_exp = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type='expense' AND by_child=1")->fetchColumn() ?: 0;
    $child_sv = $pdo->query("SELECT SUM(amount) FROM savings WHERE for_child=1")->fetchColumn() ?: 0;
    $child_gl = $pdo->query("SELECT SUM(current_amount) FROM goals WHERE for_child=1")->fetchColumn() ?: 0;
    $child_left = $child_inc - $child_exp - $child_sv - $child_gl;
} else {
    // Current month stats
    $current_month = date('Y-m');
    $month_inc = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type='income' AND by_child=1 AND date LIKE '$current_month%'")->fetchColumn() ?: 0;
    $month_exp = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type='expense' AND by_child=1 AND date LIKE '$current_month%'")->fetchColumn() ?: 0;
}
?>

<div class="dashboard-grid">
    <!-- Block 1: Main Chart -->
    <div class="card glass">
        <div class="card-header">
            <h3 class="card-title">Budget Overview</h3>
        </div>
        <div class="chart-container-half">
            <canvas id="budgetChart" 
                data-total="<?= $total_income ?>" 
                data-left="<?= $leftover ?>" 
                data-savings="<?= $total_saved ?>" 
                data-important="<?= $important_expenses ?>" 
                data-other="<?= $other_expenses ?>">
            </canvas>
            <div class="chart-center-text">
                <div class="total-budget"><?= number_format($total_income, 2) ?> PLN</div>
                <div class="label">Total Budget</div>
            </div>
        </div>
        <div class="mt-4 flex justify-between">
            <div class="text-center">
                <div class="amount income"><?= number_format($leftover, 2) ?> PLN</div>
                <div class="label" style="font-size: 0.8rem; color: var(--text-muted)">Leftover</div>
            </div>
            <div class="text-center">
                <div class="amount" style="color: var(--accent-blue)"><?= number_format($total_saved, 2) ?> PLN</div>
                <div class="label" style="font-size: 0.8rem; color: var(--text-muted)">Saved</div>
            </div>
        </div>
    </div>

    <!-- Block 2: Recent Spendings -->
    <div class="card glass">
        <div class="card-header">
            <h3 class="card-title">Recent Spendings</h3>
            <a href="transactions.php" class="btn" style="padding: 6px 12px; font-size: 0.8rem">View All</a>
        </div>
        <div class="table-responsive">
            <?php if (count($recent_expenses) > 0): ?>
            <table>
                <?php foreach ($recent_expenses as $exp): ?>
                <tr>
                    <td>
                        <span class="badge expense"><?= htmlspecialchars($exp['category']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($exp['description']) ?: 'No description' ?></td>
                    <td class="amount expense text-right">-<?= number_format($exp['amount'], 2) ?> PLN</td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
            <div class="empty-state">No recent spendings recorded.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Block 3: Contextual Summary -->
    <div class="card glass">
        <?php if (!$is_child): ?>
            <div class="card-header">
                <h3 class="card-title">Child's Budget Overview</h3>
            </div>
            <div class="flex justify-between mt-4">
                <div>
                    <div style="font-size: 0.9rem; color: var(--text-muted)">Total Allowed/Income</div>
                    <div class="amount income" style="font-size: 1.5rem"><?= number_format($child_inc, 2) ?> PLN</div>
                </div>
                <div class="text-right">
                    <div style="font-size: 0.9rem; color: var(--text-muted)">Spent</div>
                    <div class="amount expense" style="font-size: 1.5rem"><?= number_format($child_exp, 2) ?> PLN</div>
                </div>
            </div>
            <div class="progress-tube">
                <?php $pct = $child_inc > 0 ? min(100, ($child_exp / $child_inc) * 100) : 0; ?>
                <div class="progress-fill" style="width: <?= $pct ?>%; background: <?= $pct > 80 ? 'var(--accent-red)' : 'var(--primary)' ?>"></div>
            </div>
            <div class="mt-4">
                <div style="font-size: 0.9rem; color: var(--text-muted)">Remaining to spend: <strong><?= number_format($child_left, 2) ?> PLN</strong></div>
            </div>
        <?php else: ?>
            <div class="card-header">
                <h3 class="card-title">This Month's Summary</h3>
            </div>
            <div class="flex justify-between mt-4">
                <div>
                    <div style="font-size: 0.9rem; color: var(--text-muted)">Received</div>
                    <div class="amount income" style="font-size: 1.5rem"><?= number_format($month_inc, 2) ?> PLN</div>
                </div>
                <div class="text-right">
                    <div style="font-size: 0.9rem; color: var(--text-muted)">Spent</div>
                    <div class="amount expense" style="font-size: 1.5rem"><?= number_format($month_exp, 2) ?> PLN</div>
                </div>
            </div>
            <div class="progress-tube">
                <?php $pct = $month_inc > 0 ? min(100, ($month_exp / $month_inc) * 100) : 0; ?>
                <div class="progress-fill" style="width: <?= $pct ?>%; background: <?= $pct > 80 ? 'var(--accent-red)' : 'var(--primary)' ?>"></div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Block 4: Goals & Savings -->
    <div class="card glass">
        <div class="card-header">
            <h3 class="card-title">Goals & Savings Locked</h3>
            <a href="savings.php" class="btn" style="padding: 6px 12px; font-size: 0.8rem">Manage</a>
        </div>
        <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
                <span>General Savings</span>
                <span class="amount income"><?= number_format($savings_total, 2) ?> PLN</span>
            </div>
            <div class="flex justify-between items-center">
                <span>Total Towards Goals</span>
                <span class="amount goal"><?= number_format($goals_total, 2) ?> PLN</span>
            </div>
        </div>
        
        <?php
        $goals = $pdo->query("SELECT * FROM goals $goals_filter LIMIT 2")->fetchAll();
        if (count($goals) > 0):
        ?>
            <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 8px;">Active Goals Preview:</div>
            <?php foreach($goals as $g): ?>
                <div class="mb-2">
                    <div class="flex justify-between" style="font-size: 0.85rem">
                        <span><?= htmlspecialchars($g['title']) ?></span>
                        <span><?= number_format($g['current_amount'], 0) ?> / <?= number_format($g['target_amount'], 0) ?> PLN</span>
                    </div>
                    <div class="progress-tube" style="height: 6px;">
                        <?php $gpct = $g['target_amount'] > 0 ? min(100, ($g['current_amount'] / $g['target_amount']) * 100) : 0; ?>
                        <div class="progress-fill" style="width: <?= $gpct ?>%; background: var(--accent-blue)"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state" style="padding: 10px;">No goals set.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

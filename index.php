<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Get dashboard data
$monthly_income = get_monthly_income();
$monthly_expenses = get_monthly_expenses();
$total_savings = get_total_savings();
$net_balance = $monthly_income - $monthly_expenses;

$recent_transactions = get_recent_transactions(10);
$spending_by_category = get_spending_by_category();
$goals = get_goals();

require_once 'includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">📊 Dashboard</h1>
    <p class="page-subtitle">Welcome to your Family Budget Manager</p>
</div>

<!-- Summary Cards -->
<div class="cards-grid">
    <div class="card">
        <div class="card-icon">💵</div>
        <div class="card-label">Total Income</div>
        <div class="card-value positive"><?php echo format_currency($monthly_income); ?></div>
        <div class="text-muted">This month</div>
    </div>

    <div class="card">
        <div class="card-icon">💸</div>
        <div class="card-label">Total Expenses</div>
        <div class="card-value negative"><?php echo format_currency($monthly_expenses); ?></div>
        <div class="text-muted">This month</div>
    </div>

    <div class="card">
        <div class="card-icon">📈</div>
        <div class="card-label">Net Balance</div>
        <div class="card-value <?php echo $net_balance >= 0 ? 'positive' : 'negative'; ?>">
            <?php echo format_currency($net_balance); ?>
        </div>
        <div class="text-muted">Income - Expenses</div>
    </div>

    <div class="card">
        <div class="card-icon">🏦</div>
        <div class="card-label">Total Savings</div>
        <div class="card-value"><?php echo format_currency($total_savings); ?></div>
        <div class="text-muted">All time</div>
    </div>
</div>

<!-- Spending Breakdown Section -->
<div class="form-card">
    <div class="form-card-title">💰 Spending Breakdown by Category</div>

    <?php if (empty($spending_by_category)): ?>
        <div class="empty-state">
            <div class="text-muted">No expenses recorded this month</div>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <?php foreach ($spending_by_category as $category): ?>
                <div>
                    <div style="font-weight: 500; margin-bottom: 5px;">
                        <?php echo htmlspecialchars($category['category']); ?>
                    </div>
                    <div class="progress-label">
                        <span><?php echo format_currency($category['total']); ?></span>
                        <span class="text-muted">
                            <?php
                            $percentage = ($category['total'] / max(1, $monthly_expenses)) * 100;
                            echo round($percentage, 1) . '%';
                            ?>
                        </span>
                    </div>
                    <div class="progress-bar-wrapper">
                        <div class="progress-bar" style="width: <?php echo min(100, $percentage); ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Recent Transactions -->
<div class="form-card">
    <div class="form-card-title">📋 Recent Transactions</div>

    <?php if (empty($recent_transactions)): ?>
        <div class="empty-state">
            <div class="text-muted">No transactions yet. <a href="transactions.php" style="color: var(--primary-color); text-decoration: none;">Add your first transaction →</a></div>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_transactions as $tx): ?>
                        <tr>
                            <td><?php echo format_date($tx['date']); ?></td>
                            <td><?php echo htmlspecialchars($tx['category']); ?></td>
                            <td class="text-muted">
                                <?php echo !empty($tx['notes']) ? htmlspecialchars($tx['notes']) : 'N/A'; ?>
                            </td>
                            <td class="text-right <?php echo $tx['type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ($tx['type'] === 'income' ? '+' : '-') . format_currency($tx['amount']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Savings Goals Progress -->
<div class="form-card">
    <div class="form-card-title">🎯 Savings Goals</div>

    <?php if (empty($goals)): ?>
        <div class="empty-state">
            <div class="text-muted">No savings goals yet. <a href="goals.php" style="color: var(--primary-color); text-decoration: none;">Create your first goal →</a></div>
        </div>
    <?php else: ?>
        <div style="display: grid; gap: 15px;">
            <?php foreach ($goals as $goal): ?>
                <?php $progress = get_goal_progress($goal['id']); ?>
                <div style="border: 1px solid var(--border-color); padding: 15px; border-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                        <div>
                            <div style="font-weight: 600; font-size: 16px;">
                                <?php echo htmlspecialchars($goal['name']); ?>
                                <?php if ($goal['is_complete']): ?>
                                    <span style="color: var(--success-color); font-size: 12px; margin-left: 10px;">✓ Complete</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-muted">
                                Deadline: <?php echo format_date($goal['deadline']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="progress-label">
                        <span><?php echo format_currency($goal['saved_amount']); ?> of <?php echo format_currency($goal['target_amount']); ?></span>
                        <span class="text-muted"><?php echo $progress; ?>%</span>
                    </div>
                    <div class="progress-bar-wrapper">
                        <div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>

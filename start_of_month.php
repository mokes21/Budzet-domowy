<?php
// start_of_month.php
require_once 'includes/header.php';

$is_child = $_SESSION['role'] === 'child';
$for_child_val = $is_child ? 1 : 0;
$role_title = $is_child ? "Child's" : "Parent's";

// Handle form submission to update the budget
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_budget'])) {
    $new_amount = floatval($_POST['budget_amount']);
    if ($new_amount >= 0) {
        $stmt = $pdo->prepare("UPDATE budgets SET amount = ? WHERE for_child = ?");
        $stmt->execute([$new_amount, $for_child_val]);
        header("Location: start_of_month.php?success=1");
        exit;
    }
}

// Fetch current budget
$stmt = $pdo->prepare("SELECT amount FROM budgets WHERE for_child = ?");
$stmt->execute([$for_child_val]);
$current_budget = $stmt->fetchColumn() ?: 0;
?>

<div class="dashboard-grid">
    <div class="card glass" style="grid-column: 1 / -1; max-width: 600px; margin: 0 auto;">
        <div class="card-header">
            <h3 class="card-title">Set Monthly Budget (<?= $role_title ?> View)</h3>
        </div>
        
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 20px;">
            Set your main starting budget for the month. This will be considered your "Total Budget" on the dashboard.
        </p>

        <?php if (isset($_GET['success'])): ?>
            <div style="background: rgba(76,175,80,0.2); border: 1px solid var(--primary); padding: 10px; border-radius: var(--radius-md); margin-bottom: 20px; color: var(--text-main);">
                Monthly budget has been updated successfully!
            </div>
        <?php endif; ?>

        <div style="margin-bottom: 24px;">
            <div class="label" style="color: var(--text-muted); font-size: 0.9rem;">Current Monthly Budget</div>
            <div class="amount income" style="font-size: 2rem;"><?= number_format($current_budget, 2) ?> PLN</div>
        </div>

        <form method="POST" action="start_of_month.php">
            <input type="hidden" name="set_budget" value="1">
            <div class="form-group">
                <label>New Budget Amount (PLN)</label>
                <input type="number" step="0.01" min="0" name="budget_amount" class="form-control" placeholder="e.g. 5000" required value="<?= htmlspecialchars($current_budget) ?>">
            </div>
            <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">Save Monthly Budget</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

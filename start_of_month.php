<?php
// start_of_month.php
require_once 'includes/header.php';

// strictly parents only
if ($_SESSION['role'] === 'child') {
    header("Location: index.php");
    exit;
}

// Handle form submission to update the budgets
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_budget'])) {
    $parent_amount = floatval($_POST['parent_budget_amount']);
    $child_amount = floatval($_POST['child_budget_amount']);
    
    if ($parent_amount >= 0 && $child_amount >= 0) {
        // Update Parent Budget (for_child = 0)
        $stmt_p = $pdo->prepare("UPDATE budgets SET amount = ? WHERE for_child = 0");
        $stmt_p->execute([$parent_amount]);
        
        // Update Child Budget (for_child = 1)
        $stmt_c = $pdo->prepare("UPDATE budgets SET amount = ? WHERE for_child = 1");
        $stmt_c->execute([$child_amount]);
        
        header("Location: start_of_month.php?success=1");
        exit;
    }
}

// Fetch current budgets
$stmt = $pdo->query("SELECT amount FROM budgets WHERE for_child = 0");
$current_parent_budget = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->query("SELECT amount FROM budgets WHERE for_child = 1");
$current_child_budget = $stmt->fetchColumn() ?: 0;
?>

<div class="dashboard-grid">
    <div class="card glass" style="grid-column: 1 / -1; max-width: 800px; margin: 0 auto; width: 100%;">
        <div class="card-header">
            <h3 class="card-title">Setup Start of Month</h3>
        </div>
        
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 20px;">
            Set the starting monthly budgets. The Parent budget determines your dashboard limits, and the Child budget sets the constraints for the child's dashboard.
        </p>

        <?php if (isset($_GET['success'])): ?>
            <div style="background: rgba(76,175,80,0.2); border: 1px solid var(--primary); padding: 10px; border-radius: var(--radius-md); margin-bottom: 20px; color: var(--text-main);">
                Budgets have been updated successfully!
            </div>
        <?php endif; ?>

        <form method="POST" action="start_of_month.php">
            <input type="hidden" name="set_budget" value="1">
            
            <div class="dashboard-grid" style="gap: 24px; margin-bottom: 24px;">
                
                <!-- Parent Setup -->
                <div style="background: rgba(0,0,0,0.2); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                    <h4 style="margin-bottom: 15px; color: var(--primary);">Parent's Budget</h4>
                    <div style="margin-bottom: 15px;">
                        <span style="color: var(--text-muted); font-size: 0.9rem;">Current:</span>
                        <span class="amount income"><?= number_format($current_parent_budget, 2) ?> PLN</span>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>New Parent Budget</label>
                        <input type="number" step="0.01" min="0" name="parent_budget_amount" class="form-control" required value="<?= htmlspecialchars($current_parent_budget) ?>">
                    </div>
                </div>

                <!-- Child Setup -->
                <div style="background: rgba(0,0,0,0.2); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                    <h4 style="margin-bottom: 15px; color: var(--text-main);">Child's Budget</h4>
                    <div style="margin-bottom: 15px;">
                        <span style="color: var(--text-muted); font-size: 0.9rem;">Current:</span>
                        <span class="amount" style="color: var(--text-main);"><?= number_format($current_child_budget, 2) ?> PLN</span>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>New Child Budget</label>
                        <input type="number" step="0.01" min="0" name="child_budget_amount" class="form-control" required value="<?= htmlspecialchars($current_child_budget) ?>">
                    </div>
                </div>

            </div>

            <button type="submit" class="btn" style="width: 100%; font-size: 1.1rem; padding: 14px;">Save Budgets</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<?php
// savings.php
require_once 'includes/header.php';

$is_child = $_SESSION['role'] === 'child';
$by_child_val = $is_child ? 1 : 0;
$filter = $is_child ? " WHERE for_child=1" : " WHERE for_child=0";

// Handle Posts
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_goal'])) {
        $title = trim($_POST['title']);
        $target = floatval($_POST['target_amount']);
        if (!empty($title) && $target > 0) {
            $stmt = $pdo->prepare("INSERT INTO goals (title, target_amount, for_child) VALUES (?, ?, ?)");
            $stmt->execute([$title, $target, $by_child_val]);
            header("Location: savings.php?success=goal_added");
            exit;
        }
    } elseif (isset($_POST['fund_goal'])) {
        $goal_id = intval($_POST['goal_id']);
        $amount = floatval($_POST['amount']);
        if ($goal_id && $amount > 0) {
            $stmt = $pdo->prepare("UPDATE goals SET current_amount = current_amount + ? WHERE id = ? AND for_child = ?");
            $stmt->execute([$amount, $goal_id, $by_child_val]);
            header("Location: savings.php?success=goal_funded");
            exit;
        }
    } elseif (isset($_POST['add_savings'])) {
        $amount = floatval($_POST['amount']);
        if ($amount > 0) {
            $stmt = $pdo->prepare("UPDATE savings SET amount = amount + ? WHERE for_child = ?");
            $stmt->execute([$amount, $by_child_val]);
            header("Location: savings.php?success=savings_added");
            exit;
        }
    }
}

// Fetch Data
$goals = $pdo->query("SELECT * FROM goals $filter")->fetchAll();
$savings = $pdo->query("SELECT amount FROM savings $filter")->fetchColumn() ?: 0;
?>

<div class="dashboard-grid">
    
    <!-- General Savings Block -->
    <div class="card glass" style="grid-column: 1 / 3;">
        <div class="card-header">
            <h3 class="card-title">General Savings</h3>
            <div class="amount income" style="font-size: 1.5rem;"><?= number_format($savings, 2) ?> PLN</div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div style="background: rgba(76,175,80,0.2); border: 1px solid var(--primary); padding: 10px; border-radius: var(--radius-md); margin-bottom: 20px; color: var(--text-main);">
                Action completed successfully!
            </div>
        <?php endif; ?>

        <form method="POST" action="savings.php" class="flex gap-2 items-center" style="gap: 16px;">
            <input type="hidden" name="add_savings" value="1">
            <div class="form-group" style="margin: 0; flex: 1;">
                <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="Amount to divert to savings (PLN)" required>
            </div>
            <button type="submit" class="btn" style="white-space: nowrap;">Divert to Savings</button>
        </form>
    </div>

    <!-- Active Goals -->
    <div class="card glass">
        <div class="card-header">
            <h3 class="card-title">Manage Goals</h3>
        </div>
        
        <?php if (count($goals) > 0): ?>
            <?php foreach ($goals as $g): ?>
                <div style="background: rgba(0,0,0,0.2); padding: 16px; border-radius: var(--radius-md); margin-bottom: 16px;">
                    <div class="flex justify-between items-center mb-2">
                        <strong style="color: var(--text-main); font-size: 1.1rem;"><?= htmlspecialchars($g['title']) ?></strong>
                        <span class="badge goal">
                            <?= number_format($g['current_amount'], 2) ?> / <?= number_format($g['target_amount'], 2) ?> PLN
                        </span>
                    </div>
                    
                    <div class="progress-tube" style="height: 8px; margin-bottom: 16px;">
                        <?php $gpct = $g['target_amount'] > 0 ? min(100, ($g['current_amount'] / $g['target_amount']) * 100) : 0; ?>
                        <div class="progress-fill" style="width: <?= $gpct ?>%; background: var(--accent-blue)"></div>
                    </div>

                    <?php if ($g['current_amount'] < $g['target_amount']): ?>
                        <form method="POST" action="savings.php" class="flex gap-2" style="gap: 8px;">
                            <input type="hidden" name="fund_goal" value="1">
                            <input type="hidden" name="goal_id" value="<?= $g['id'] ?>">
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="Fund Amount" style="padding: 8px; font-size: 0.9rem;" required>
                            <button type="submit" class="btn" style="padding: 8px 16px; font-size: 0.9rem;">Add Funds</button>
                        </form>
                    <?php else: ?>
                        <div style="color: var(--primary); font-weight: 600; font-size: 0.9rem;">🎉 Goal Achieved!</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">No goals set yet.</div>
        <?php endif; ?>
    </div>

    <!-- Create New Goal -->
    <div class="card glass" style="height: fit-content;">
        <div class="card-header">
            <h3 class="card-title">Create New Goal</h3>
        </div>
        <form method="POST" action="savings.php">
            <input type="hidden" name="add_goal" value="1">
            
            <div class="form-group">
                <label>Goal Title</label>
                <input type="text" name="title" class="form-control" required placeholder="e.g. New Laptop">
            </div>
            
            <div class="form-group">
                <label>Target Amount (PLN)</label>
                <input type="number" step="0.01" min="1" name="target_amount" class="form-control" required placeholder="1000.00">
            </div>
            
            <div class="form-group mt-4">
                <button type="submit" class="btn" style="width: 100%;">Create Goal</button>
            </div>
        </form>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>

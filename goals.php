<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $pdo = get_db();

    if ($_POST['action'] === 'add_goal') {
        $name = $_POST['name'] ?? '';
        $target_amount = $_POST['target_amount'] ?? 0;
        $deadline = $_POST['deadline'] ?? null;
        $notes = $_POST['notes'] ?? '';

        if ($name && $target_amount > 0) {
            $stmt = $pdo->prepare("
                INSERT INTO goals (name, target_amount, deadline, notes, saved_amount)
                VALUES (:name, :target_amount, :deadline, :notes, 0)
            ");

            if ($stmt->execute([
                ':name' => $name,
                ':target_amount' => (float)$target_amount,
                ':deadline' => !empty($deadline) ? $deadline : null,
                ':notes' => $notes
            ])) {
                set_flash_message('Goal created successfully!', 'success');
                header('Location: goals.php');
                exit;
            } else {
                set_flash_message('Error creating goal', 'error');
            }
        } else {
            set_flash_message('Please fill in all required fields', 'error');
        }
    } elseif ($_POST['action'] === 'contribute_goal') {
        $goal_id = $_POST['goal_id'] ?? 0;
        $amount = $_POST['amount'] ?? 0;
        $notes = $_POST['notes'] ?? '';

        if ($goal_id && $amount > 0) {
            // Add savings entry
            $stmt = $pdo->prepare("
                INSERT INTO savings (date, type, amount, notes, goal_id)
                VALUES (:date, :type, :amount, :notes, :goal_id)
            ");

            if ($stmt->execute([
                ':date' => date(DATE_FORMAT),
                ':type' => 'deposit',
                ':amount' => (float)$amount,
                ':notes' => $notes,
                ':goal_id' => (int)$goal_id
            ])) {
                // Update goal's saved_amount
                $stmt = $pdo->prepare("
                    UPDATE goals
                    SET saved_amount = saved_amount + :amount
                    WHERE id = :goal_id
                ");
                $stmt->execute([':amount' => (float)$amount, ':goal_id' => (int)$goal_id]);

                set_flash_message('Contribution added successfully!', 'success');
                header('Location: goals.php');
                exit;
            } else {
                set_flash_message('Error adding contribution', 'error');
            }
        } else {
            set_flash_message('Please fill in all required fields', 'error');
        }
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (delete_goal($_POST['delete_id'])) {
        set_flash_message('Goal deleted successfully!', 'success');
        header('Location: goals.php');
        exit;
    }
}

// Handle mark complete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_id'])) {
    $pdo = get_db();
    $stmt = $pdo->prepare("UPDATE goals SET is_complete = 1 WHERE id = :id");
    if ($stmt->execute([':id' => $_POST['complete_id']])) {
        set_flash_message('Goal marked as complete!', 'success');
        header('Location: goals.php');
        exit;
    }
}

// Get data
$goals = get_goals();
$active_goals = array_filter($goals, fn($g) => !$g['is_complete']);
$completed_goals = array_filter($goals, fn($g) => $g['is_complete']);

require_once 'includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">🎯 Savings Goals</h1>
    <p class="page-subtitle">Set and track your financial goals</p>
</div>

<!-- Create Goal Form -->
<div class="form-card">
    <div class="form-card-title">➕ Create New Goal</div>

    <form method="POST" action="">
        <input type="hidden" name="action" value="add_goal">

        <div class="form-row">
            <div class="form-group">
                <label for="name">Goal Name</label>
                <input type="text" id="name" name="name" placeholder="e.g., Vacation, Emergency Fund" required>
            </div>

            <div class="form-group">
                <label for="target_amount">Target Amount ($)</label>
                <input type="number" id="target_amount" name="target_amount" placeholder="0.00" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="deadline">Deadline (Optional)</label>
                <input type="date" id="deadline" name="deadline">
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" placeholder="Add notes about this goal..." rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">✓ Create Goal</button>
    </form>
</div>

<!-- Active Goals Section -->
<div>
    <h2 style="font-size: 20px; margin-bottom: 20px; margin-top: 30px;">
        📌 Active Goals (<?php echo count($active_goals); ?>)
    </h2>

    <?php if (empty($active_goals)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🎯</div>
            <div class="text-muted">No active goals yet. Create one above!</div>
        </div>
    <?php else: ?>
        <div style="display: grid; gap: 20px;">
            <?php foreach ($active_goals as $goal): ?>
                <?php $progress = get_goal_progress($goal['id']); ?>
                <div class="goal-card">
                    <div class="goal-header">
                        <div>
                            <div class="goal-title"><?php echo htmlspecialchars($goal['name']); ?></div>
                            <div class="goal-deadline">
                                📅 Deadline: <?php echo format_date($goal['deadline']); ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($goal['notes'])): ?>
                        <div class="text-muted" style="margin: 10px 0; font-size: 13px;">
                            📝 <?php echo htmlspecialchars($goal['notes']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="goal-amount">
                        <span><?php echo format_currency($goal['saved_amount']); ?> / <?php echo format_currency($goal['target_amount']); ?></span>
                        <span><?php echo $progress; ?>%</span>
                    </div>

                    <div class="progress-bar-wrapper">
                        <div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
                    </div>

                    <!-- Contribute Form -->
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border-color);">
                        <form method="POST" action="" style="display: grid; grid-template-columns: 1fr auto auto; gap: 10px; align-items: end;">
                            <input type="hidden" name="action" value="contribute_goal">
                            <input type="hidden" name="goal_id" value="<?php echo $goal['id']; ?>">

                            <div>
                                <label style="font-size: 12px; color: var(--text-light);">Contribution Amount</label>
                                <input type="number" name="amount" placeholder="0.00" step="0.01" min="0" required style="width: 100%;">
                            </div>

                            <input type="text" name="notes" placeholder="Notes (optional)" style="padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 6px;">

                            <button type="submit" class="btn btn-success btn-sm">💾 Add</button>
                        </form>
                    </div>

                    <!-- Actions -->
                    <div class="goal-actions" style="margin-top: 15px;">
                        <form method="POST" style="display: inline; flex: 1;">
                            <input type="hidden" name="complete_id" value="<?php echo $goal['id']; ?>">
                            <button type="submit" class="btn btn-success" style="width: 100%;">✓ Mark Complete</button>
                        </form>

                        <form method="POST" style="display: inline; flex: 1;">
                            <input type="hidden" name="delete_id" value="<?php echo $goal['id']; ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirmDelete()" style="width: 100%;">🗑️ Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Completed Goals Section -->
<?php if (!empty($completed_goals)): ?>
    <div style="margin-top: 40px;">
        <h2 style="font-size: 20px; margin-bottom: 20px;">
            ✓ Completed Goals (<?php echo count($completed_goals); ?>)
        </h2>

        <div style="display: grid; gap: 20px;">
            <?php foreach ($completed_goals as $goal): ?>
                <div class="goal-card goal-complete" style="opacity: 0.7;">
                    <div class="goal-header">
                        <div>
                            <div class="goal-title">✓ <?php echo htmlspecialchars($goal['name']); ?></div>
                            <div class="goal-deadline">
                                📅 Deadline: <?php echo format_date($goal['deadline']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="goal-amount">
                        <span><?php echo format_currency($goal['saved_amount']); ?> / <?php echo format_currency($goal['target_amount']); ?></span>
                        <span>100%</span>
                    </div>

                    <div class="progress-bar-wrapper">
                        <div class="progress-bar" style="width: 100%"; background: var(--success-color);""></div>
                    </div>

                    <!-- Delete Only -->
                    <div style="margin-top: 15px;">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $goal['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirmDelete()">🗑️ Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>

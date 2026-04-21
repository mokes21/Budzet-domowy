<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $pdo = get_db();

    if ($_POST['action'] === 'add_savings') {
        $date = $_POST['date'] ?? date(DATE_FORMAT);
        $type = $_POST['type'] ?? 'deposit';
        $amount = $_POST['amount'] ?? 0;
        $notes = $_POST['notes'] ?? '';
        $goal_id = $_POST['goal_id'] ?? null;

        if ($date && $type && $amount > 0) {
            $stmt = $pdo->prepare("
                INSERT INTO savings (date, type, amount, notes, goal_id)
                VALUES (:date, :type, :amount, :notes, :goal_id)
            ");

            if ($stmt->execute([
                ':date' => $date,
                ':type' => $type,
                ':amount' => (float)$amount,
                ':notes' => $notes,
                ':goal_id' => !empty($goal_id) ? (int)$goal_id : null
            ])) {
                // Update goal's saved_amount if goal_id is set
                if (!empty($goal_id)) {
                    $update_amount = $type === 'deposit' ? $amount : -$amount;
                    $stmt = $pdo->prepare("
                        UPDATE goals
                        SET saved_amount = saved_amount + :amount
                        WHERE id = :goal_id
                    ");
                    $stmt->execute([':amount' => $update_amount, ':goal_id' => (int)$goal_id]);
                }

                set_flash_message('Savings entry added successfully!', 'success');
                header('Location: savings.php');
                exit;
            } else {
                set_flash_message('Error adding savings entry', 'error');
            }
        } else {
            set_flash_message('Please fill in all required fields', 'error');
        }
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (delete_savings($_POST['delete_id'])) {
        set_flash_message('Savings entry deleted successfully!', 'success');
        header('Location: savings.php');
        exit;
    }
}

// Get data
$total_savings = get_total_savings();
$savings_history = get_savings_history();
$goals = get_goals();

require_once 'includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">🏦 Savings</h1>
    <p class="page-subtitle">Track your savings and reach your goals</p>
</div>

<!-- Total Savings Card -->
<div class="cards-grid">
    <div class="card">
        <div class="card-icon">💰</div>
        <div class="card-label">Total Savings Balance</div>
        <div class="card-value positive"><?php echo format_currency($total_savings); ?></div>
        <div class="text-muted">All time</div>
    </div>
</div>

<!-- Add Savings Form -->
<div class="form-card">
    <div class="form-card-title">➕ Add Savings Entry</div>

    <form method="POST" action="">
        <input type="hidden" name="action" value="add_savings">

        <div class="form-row">
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" value="<?php echo date(DATE_FORMAT); ?>" required>
            </div>

            <div class="form-group">
                <label for="type">Type</label>
                <select id="type" name="type" required>
                    <option value="deposit">Deposit</option>
                    <option value="withdrawal">Withdrawal</option>
                </select>
            </div>

            <div class="form-group">
                <label for="amount">Amount ($)</label>
                <input type="number" id="amount" name="amount" placeholder="0.00" step="0.01" min="0" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="goal_id">Link to Goal (Optional)</label>
                <select id="goal_id" name="goal_id">
                    <option value="">No Goal</option>
                    <?php foreach ($goals as $goal): ?>
                        <?php if (!$goal['is_complete']): ?>
                            <option value="<?php echo $goal['id']; ?>">
                                <?php echo htmlspecialchars($goal['name']); ?> -
                                <?php echo format_currency($goal['target_amount']); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="notes">Notes (Optional)</label>
                <input type="text" id="notes" name="notes" placeholder="Add notes...">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">✓ Add Entry</button>
    </form>
</div>

<!-- Savings History -->
<div class="form-card">
    <div class="form-card-title">📊 Savings History</div>

    <?php if (empty($savings_history)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <div class="text-muted">No savings entries yet</div>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Goal</th>
                        <th>Notes</th>
                        <th class="text-right">Amount</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($savings_history as $entry): ?>
                        <tr>
                            <td><?php echo format_date($entry['date']); ?></td>
                            <td>
                                <span style="background: <?php echo $entry['type'] === 'deposit' ? 'var(--success-color)' : 'var(--warning-color)'; ?>; color: white; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                    <?php echo ucfirst($entry['type']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo !empty($entry['goal_name']) ? htmlspecialchars($entry['goal_name']) : '—'; ?>
                            </td>
                            <td class="text-muted">
                                <?php echo !empty($entry['notes']) ? htmlspecialchars($entry['notes']) : '—'; ?>
                            </td>
                            <td class="text-right <?php echo $entry['type'] === 'deposit' ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ($entry['type'] === 'deposit' ? '+' : '−') . format_currency($entry['amount']); ?>
                            </td>
                            <td class="text-center">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="delete_id" value="<?php echo $entry['id']; ?>">
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

<?php require_once 'includes/footer.php'; ?>

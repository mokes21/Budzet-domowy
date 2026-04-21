<?php
/**
 * Helper functions for Family Budget Manager
 */

require_once __DIR__ . '/../db.php';

/**
 * Format currency amount with symbol
 */
function format_currency($amount) {
    return CURRENCY_SYMBOL . number_format($amount, 2);
}

/**
 * Format date for display
 */
function format_date($date) {
    if (empty($date)) {
        return '';
    }
    return date(DISPLAY_DATE_FORMAT, strtotime($date));
}

/**
 * Set flash message
 */
function set_flash_message($message, $type = 'success') {
    $_SESSION['flash'][] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get and clear flash messages
 */
function get_flash_messages() {
    $messages = $_SESSION['flash'] ?? [];
    $_SESSION['flash'] = [];
    return $messages;
}

/**
 * Get total income for current month
 */
function get_monthly_income() {
    $pdo = get_db();
    $year = date('Y');
    $month = date('m');

    $stmt = $pdo->prepare("
        SELECT SUM(amount) as total
        FROM transactions
        WHERE type = 'income'
        AND strftime('%Y', date) = :year
        AND strftime('%m', date) = :month
    ");
    $stmt->execute(['year' => $year, 'month' => $month]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result['total'] ?? 0;
}

/**
 * Get total expenses for current month
 */
function get_monthly_expenses() {
    $pdo = get_db();
    $year = date('Y');
    $month = date('m');

    $stmt = $pdo->prepare("
        SELECT SUM(amount) as total
        FROM transactions
        WHERE type = 'expense'
        AND strftime('%Y', date) = :year
        AND strftime('%m', date) = :month
    ");
    $stmt->execute(['year' => $year, 'month' => $month]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result['total'] ?? 0;
}

/**
 * Get current total savings
 */
function get_total_savings() {
    $pdo = get_db();

    $stmt = $pdo->query("
        SELECT
            COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE -amount END), 0) as total
        FROM savings
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result['total'] ?? 0;
}

/**
 * Get monthly savings total
 */
function get_monthly_savings() {
    $pdo = get_db();
    $year = date('Y');
    $month = date('m');

    $stmt = $pdo->prepare("
        SELECT
            COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE -amount END), 0) as total
        FROM savings
        WHERE strftime('%Y', date) = :year
        AND strftime('%m', date) = :month
    ");
    $stmt->execute(['year' => $year, 'month' => $month]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result['total'] ?? 0;
}

/**
 * Get spending breakdown by category for current month
 */
function get_spending_by_category() {
    $pdo = get_db();
    $year = date('Y');
    $month = date('m');

    $stmt = $pdo->prepare("
        SELECT category, SUM(amount) as total
        FROM transactions
        WHERE type = 'expense'
        AND strftime('%Y', date) = :year
        AND strftime('%m', date) = :month
        GROUP BY category
        ORDER BY total DESC
    ");
    $stmt->execute(['year' => $year, 'month' => $month]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get recent transactions (last N entries)
 */
function get_recent_transactions($limit = 10) {
    $pdo = get_db();

    $stmt = $pdo->prepare("
        SELECT *
        FROM transactions
        ORDER BY date DESC, created_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all transactions with optional filters
 */
function get_transactions($type = null, $category = null, $start_date = null, $end_date = null) {
    $pdo = get_db();

    $sql = "SELECT * FROM transactions WHERE 1=1";
    $params = [];

    if ($type) {
        $sql .= " AND type = :type";
        $params[':type'] = $type;
    }

    if ($category) {
        $sql .= " AND category = :category";
        $params[':category'] = $category;
    }

    if ($start_date) {
        $sql .= " AND date >= :start_date";
        $params[':start_date'] = $start_date;
    }

    if ($end_date) {
        $sql .= " AND date <= :end_date";
        $params[':end_date'] = $end_date;
    }

    $sql .= " ORDER BY date DESC, created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Calculate running balance for transactions
 */
function get_transactions_with_balance($type = null, $category = null, $start_date = null, $end_date = null) {
    $transactions = get_transactions($type, $category, $start_date, $end_date);

    $balance = 0;
    foreach ($transactions as &$t) {
        if ($t['type'] == 'income') {
            $balance += $t['amount'];
        } else {
            $balance -= $t['amount'];
        }
        $t['balance'] = $balance;
    }
    unset($t);

    return array_reverse($transactions);
}

/**
 * Get all goals
 */
function get_goals() {
    $pdo = get_db();
    $stmt = $pdo->query("SELECT * FROM goals ORDER BY deadline ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get goal progress percentage
 */
function get_goal_progress($goal_id) {
    $pdo = get_db();

    $stmt = $pdo->prepare("SELECT saved_amount, target_amount FROM goals WHERE id = :id");
    $stmt->execute([':id' => $goal_id]);
    $goal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$goal || $goal['target_amount'] == 0) {
        return 0;
    }

    return min(100, round(($goal['saved_amount'] / $goal['target_amount']) * 100, 1));
}

/**
 * Get all savings entries
 */
function get_savings_history() {
    $pdo = get_db();
    $stmt = $pdo->query("
        SELECT s.*, COALESCE(g.name, '') as goal_name
        FROM savings s
        LEFT JOIN goals g ON s.goal_id = g.id
        ORDER BY s.date DESC, s.created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Delete transaction
 */
function delete_transaction($id) {
    $pdo = get_db();
    $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

/**
 * Delete savings entry
 */
function delete_savings($id) {
    $pdo = get_db();

    // Get the savings entry to find its amount and goal
    $stmt = $pdo->prepare("SELECT * FROM savings WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $savings = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($savings && $savings['goal_id']) {
        // Subtract from goal's saved_amount
        $amount = $savings['type'] == 'deposit' ? $savings['amount'] : -$savings['amount'];
        $stmt = $pdo->prepare("
            UPDATE goals
            SET saved_amount = saved_amount - :amount
            WHERE id = :goal_id
        ");
        $stmt->execute([':amount' => $amount, ':goal_id' => $savings['goal_id']]);
    }

    // Delete the savings entry
    $stmt = $pdo->prepare("DELETE FROM savings WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

/**
 * Delete goal
 */
function delete_goal($id) {
    $pdo = get_db();

    // Delete associated savings entries
    $pdo->prepare("DELETE FROM savings WHERE goal_id = :id")->execute([':id' => $id]);

    // Delete goal
    $stmt = $pdo->prepare("DELETE FROM goals WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

?>

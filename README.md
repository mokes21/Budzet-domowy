# Family Budget Manager

A clean, modern web application for managing family finances built with PHP and SQLite.

## Features

### 📊 Dashboard
- **Summary Cards**: Track Total Income, Total Expenses, Net Balance, and Total Savings for the current month
- **Spending Breakdown**: Visual breakdown of expenses by category
- **Recent Transactions**: Display the last 10 transactions at a glance
- **Savings Goals Progress**: Monitor progress toward financial goals with visual progress bars

### 💳 Transactions
- **Add Transactions**: Create new income or expense entries with date, category, amount, and notes
- **Categories**: Predefined categories for expenses (Groceries, Rent, Salary, Entertainment, Transport, Medical, Other) and income (Salary, Bonus, Investment, Other)
- **Filters**: Filter transactions by type, category, and date range
- **Running Balance**: Track cumulative balance across all transactions
- **Delete**: Remove transactions with confirmation

### 🏦 Savings
- **Track Savings**: Monitor total savings balance across all time
- **Add Entries**: Record deposits and withdrawals with optional goal links
- **Goal Connection**: Link savings entries to specific financial goals
- **Full History**: View complete savings transaction history
- **Delete**: Remove savings entries with confirmation

### 🎯 Goals
- **Create Goals**: Set savings goals with target amounts and deadlines
- **Track Progress**: Visual progress bars showing current savings vs. target
- **Direct Contributions**: Contribute to goals directly from the goals page
- **Complete Goals**: Mark goals as complete and view them in a separate section
- **Manage Goals**: Delete or archive goals at any time

## Technical Stack

- **Backend**: PHP 8.0+
- **Database**: SQLite (single file - `budget.db`)
- **Frontend**: Vanilla CSS and JavaScript (no frameworks)
- **Security**: PDO with prepared statements for all database queries

## Installation

1. **Prerequisites**: PHP 7.4+ with PDO SQLite support

2. **Setup**:
   ```bash
   # The application will automatically create the database on first run
   # Simply access the application in your web browser
   ```

3. **File Structure**:
   ```
   budget-manager/
   ├── config.php              # Configuration constants
   ├── db.php                  # Database initialization and connection
   ├── index.php               # Dashboard page
   ├── transactions.php        # Transactions management
   ├── savings.php             # Savings tracking
   ├── goals.php               # Goals management
   ├── includes/
   │   ├── header.php          # Navigation header with sidebar
   │   ├── footer.php          # Footer with flash messages
   │   └── functions.php       # Helper functions
   ├── css/
   │   └── style.css           # Responsive styling
   └── budget.db               # SQLite database (auto-created)
   ```

## Database Schema

### transactions
- `id` (INTEGER PRIMARY KEY)
- `date` (DATE)
- `type` (TEXT: 'income' or 'expense')
- `category` (TEXT)
- `amount` (REAL)
- `notes` (TEXT)
- `created_at` (DATETIME)

### savings
- `id` (INTEGER PRIMARY KEY)
- `date` (DATE)
- `type` (TEXT: 'deposit' or 'withdrawal')
- `amount` (REAL)
- `notes` (TEXT)
- `goal_id` (INTEGER, nullable FK)
- `created_at` (DATETIME)

### goals
- `id` (INTEGER PRIMARY KEY)
- `name` (TEXT)
- `target_amount` (REAL)
- `saved_amount` (REAL)
- `deadline` (DATE)
- `notes` (TEXT)
- `is_complete` (INTEGER)
- `created_at` (DATETIME)

## Usage

### Adding a Transaction
1. Go to **Transactions** page
2. Fill in the form with:
   - **Date**: When the transaction occurred
   - **Type**: Income or Expense
   - **Category**: Select appropriate category
   - **Amount**: Transaction amount in dollars
   - **Notes**: Optional description
3. Click **Add Transaction**

### Creating a Savings Goal
1. Go to **Goals** page
2. Fill in the Create Goal form with:
   - **Goal Name**: e.g., "Emergency Fund"
   - **Target Amount**: Goal amount in dollars
   - **Deadline**: Target date (optional)
   - **Notes**: Optional description
3. Click **Create Goal**

### Contributing to a Goal
1. On **Goals** page, find the active goal
2. Enter the **Contribution Amount**
3. Add optional **Notes**
4. Click **Add** - this will record a savings deposit linked to the goal

### Recording Savings
1. Go to **Savings** page
2. Fill in the form with:
   - **Date**: When the savings action occurred
   - **Type**: Deposit or Withdrawal
   - **Amount**: Amount in dollars
   - **Goal**: (Optional) Link to a savings goal
   - **Notes**: Optional description
3. Click **Add Entry**

## Features

- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Fixed Sidebar Navigation**: Quick access to all pages
- **Flash Messages**: Success/error notifications for all actions
- **Date Formatting**: Consistent date formatting across the application
- **Currency Formatting**: All amounts displayed as $X,XXX.XX
- **No JavaScript Frameworks**: Pure vanilla CSS and minimal JavaScript
- **Data Persistence**: All data stored in SQLite database

## Currency & Formatting

- **Currency Symbol**: $ (dollar sign)
- **Decimal Places**: 2 (e.g., $1,234.56)
- **Date Format**: M d, Y (e.g., Apr 21, 2026)
- **Database Format**: Y-m-d

## Security

- **SQL Injection Prevention**: All database queries use prepared statements
- **Data Validation**: Form submissions are validated
- **XSS Prevention**: All user input is escaped with htmlspecialchars()

## Troubleshooting

### Database Not Creating
- Ensure the directory has write permissions
- Check PHP error logs for PDO errors
- Verify PHP SQLite extension is installed: `php -m | grep sqlite`

### Sidebar Navigation Not Showing Links
- Check that all PHP files are in the correct directory
- Verify database.db file was created

### Flash Messages Not Displaying
- Ensure session_start() is called (it is in config.php)
- Check browser console for JavaScript errors

## Future Enhancements

- Monthly budget planning and tracking against actuals
- Category-based spending limits with alerts
- Export data to CSV/PDF
- Multi-user accounts
- Bill reminders and recurring transactions
- Charts and advanced reporting
- Mobile app version

## License

This project is open source and available for personal use.
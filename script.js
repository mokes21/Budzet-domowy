document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('.sidebar a');
    const pages = document.querySelectorAll('.page');

    // Navigation
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const pageId = this.getAttribute('data-page');

            // Remove active from all links and pages
            links.forEach(l => l.classList.remove('active'));
            pages.forEach(page => {
                page.classList.remove('active');
            });

            // Add active to clicked link and corresponding page
            this.classList.add('active');
            document.getElementById(pageId).classList.add('active');
        });
    });

    // Budget data
    const totalBudget = 2000;
    const expenses = 1500;
    const savings = 500;

    const expensesPercent = Math.round((expenses / totalBudget) * 100);
    const savingsPercent = Math.round((savings / totalBudget) * 100);

    // Set text
    document.getElementById('expensesPercent').textContent = expensesPercent;
    document.getElementById('expensesAmount').textContent = expenses;
    document.getElementById('savingsPercent').textContent = savingsPercent;
    document.getElementById('savingsAmount').textContent = savings;

    // Set half-circle progress
    const progressCircle = document.getElementById('progressCircle');
    const progressPercent = expensesPercent;
    const rotation = (progressPercent / 100) * 180;
    progressCircle.style.clipPath = `polygon(0% 0%, ${progressPercent}% 0%, ${progressPercent}% 100%, 0% 100%)`;
});
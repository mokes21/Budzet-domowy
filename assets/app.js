/* assets/app.js */

document.addEventListener('DOMContentLoaded', () => {
    // If there is a canvas element with id 'budgetChart', render the Chart.js
    const ctx = document.getElementById('budgetChart');
    if (ctx) {
        // Read data attributes from the canvas or a hidden element
        const rawData = ctx.dataset;
        
        let total = parseFloat(rawData.total) || 0;
        let left = parseFloat(rawData.left) || 0;
        let savings = parseFloat(rawData.savings) || 0;
        let important = parseFloat(rawData.important) || 0;
        let otherExpenses = parseFloat(rawData.other) || 0;

        // Ensure leftover doesn't go below 0 for visual purposes
        left = Math.max(0, left);

        const data = {
            labels: ['Leftover', 'Savings', 'Important Expenses', 'Other Expenses'],
            datasets: [{
                data: [left, savings, important, otherExpenses],
                backgroundColor: [
                    '#00E676', // vibrant green
                    '#448AFF', // blue
                    '#FFD740', // yellow
                    '#FF5252'  // red
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        };

        const config = {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                rotation: -90,
                circumference: 180,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#A5D6A7',
                            padding: 20,
                            font: {
                                family: "'Outfit', sans-serif"
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'PLN' }).format(context.parsed);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        };

        new Chart(ctx, config);
    }
});

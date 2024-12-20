document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar toggle
    document.getElementById('sidebarCollapse').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
    });

    // Initialize charts
    initializeSalesChart();
    initializeProductsChart();

    // Load initial data
    loadDashboardData();
    loadRecentTransactions();
});

function initializeSalesChart() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Monthly Sales',
                data: [0, 0, 0, 0, 0, 0],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            }
        }
    });

    // Update chart with real data
    fetchSalesData().then(data => {
        salesChart.data.labels = data.labels;
        salesChart.data.datasets[0].data = data.values;
        salesChart.update();
    });
}

function initializeProductsChart() {
    const ctx = document.getElementById('productsChart').getContext('2d');
    const productsChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Coffee', 'Tea', 'Pastries', 'Others'],
            datasets: [{
                data: [0, 0, 0, 0],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 206, 86)',
                    'rgb(75, 192, 192)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Update chart with real data
    fetchProductData().then(data => {
        productsChart.data.labels = data.labels;
        productsChart.data.datasets[0].data = data.values;
        productsChart.update();
    });
}

async function loadDashboardData() {
    try {
        const response = await fetch('/api/admin/dashboard-stats');
        const data = await response.json();

        // Update dashboard cards
        document.getElementById('totalUsers').textContent = data.totalUsers;
        document.getElementById('totalProducts').textContent = data.totalProducts;
        document.getElementById('todaySales').textContent = formatCurrency(data.todaySales);
        document.getElementById('pendingOrders').textContent = data.pendingOrders;
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

async function loadRecentTransactions() {
    try {
        const response = await fetch('/api/admin/recent-transactions');
        const transactions = await response.json();
        const tbody = document.getElementById('recentTransactions');
        tbody.innerHTML = '';

        transactions.forEach(transaction => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${transaction.orderId}</td>
                <td>${transaction.customer}</td>
                <td>${transaction.products}</td>
                <td>${formatCurrency(transaction.amount)}</td>
                <td><span class="badge bg-${getStatusColor(transaction.status)}">${transaction.status}</span></td>
                <td>${formatDate(transaction.date)}</td>
            `;
            tbody.appendChild(row);
        });
    } catch (error) {
        console.error('Error loading transactions:', error);
    }
}

// Helper functions
async function fetchSalesData() {
    try {
        const response = await fetch('/api/admin/sales-data');
        return await response.json();
    } catch (error) {
        console.error('Error fetching sales data:', error);
        return { labels: [], values: [] };
    }
}

async function fetchProductData() {
    try {
        const response = await fetch('/api/admin/product-stats');
        return await response.json();
    } catch (error) {
        console.error('Error fetching product data:', error);
        return { labels: [], values: [] };
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function getStatusColor(status) {
    const colors = {
        'completed': 'success',
        'pending': 'warning',
        'cancelled': 'danger'
    };
    return colors[status.toLowerCase()] || 'secondary';
}

// Event Listeners for interactive elements
document.addEventListener('click', function(e) {
    // Handle notification clicks
    if (e.target.matches('[data-notification]')) {
        markNotificationAsRead(e.target.dataset.notificationId);
    }
});

// Product Operations
async function deleteProduct(productId) {
    if (!confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
        return;
    }

    try {
        const response = await fetch('/api/products/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ product_id: productId })
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            // Reload the page to refresh the product list
            window.location.reload();
        } else {
            alert(result.message || 'Gagal menghapus produk');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus produk');
    }
}

function editProduct(productId) {
    window.location.href = `/admin/edit_product.php?id=${productId}`;
}

// Sidebar Toggle
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.querySelector('#sidebar').classList.toggle('collapsed');
            document.querySelector('main').classList.toggle('expanded');
        });
    }
}); 
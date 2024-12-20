document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar toggle
    document.getElementById('sidebarCollapse').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
    });

    // Load initial data
    loadUserData();
    loadFeaturedProducts();
    loadRecentOrders();
    updateCartCount();
});

async function loadUserData() {
    try {
        const response = await fetch('/api/user/dashboard-data');
        const data = await response.json();

        // Update user information
        document.getElementById('userName').textContent = data.name;
        document.getElementById('totalOrders').textContent = data.totalOrders;
        document.getElementById('loyaltyPoints').textContent = data.loyaltyPoints;
        document.getElementById('activeOrders').textContent = data.activeOrders;
    } catch (error) {
        console.error('Error loading user data:', error);
    }
}

async function loadFeaturedProducts() {
    try {
        const response = await fetch('/api/user/featured-products');
        const products = await response.json();
        const container = document.getElementById('featuredProducts');
        container.innerHTML = '';

        products.forEach(product => {
            const col = document.createElement('div');
            col.className = 'col-md-4 mb-4';
            col.innerHTML = `
                <div class="product-card">
                    <img src="${product.image}" alt="${product.name}" class="product-image">
                    <h5 class="card-title">${product.name}</h5>
                    <p class="card-text">${product.description}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h5 mb-0">${formatCurrency(product.price)}</span>
                        <button class="btn btn-primary btn-sm" onclick="addToCart(${product.id})">
                            Add to Cart
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(col);
        });
    } catch (error) {
        console.error('Error loading featured products:', error);
    }
}

async function loadRecentOrders() {
    try {
        const response = await fetch('/api/user/recent-orders');
        const orders = await response.json();
        const tbody = document.getElementById('recentOrders');
        tbody.innerHTML = '';

        orders.forEach(order => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${order.orderId}</td>
                <td>${order.products.join(', ')}</td>
                <td>${formatCurrency(order.total)}</td>
                <td><span class="badge bg-${getStatusColor(order.status)}">${order.status}</span></td>
                <td>${formatDate(order.date)}</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="viewOrder(${order.orderId})">
                        View
                    </button>
                    ${order.status === 'completed' ? `
                        <button class="btn btn-sm btn-success" onclick="reorder(${order.orderId})">
                            Reorder
                        </button>
                    ` : ''}
                </td>
            `;
            tbody.appendChild(row);
        });
    } catch (error) {
        console.error('Error loading recent orders:', error);
    }
}

async function updateCartCount() {
    try {
        const response = await fetch('/api/user/cart-count');
        const data = await response.json();
        document.getElementById('cartCount').textContent = data.count;
    } catch (error) {
        console.error('Error updating cart count:', error);
    }
}

// Cart functions
async function addToCart(productId) {
    try {
        const response = await fetch('/api/user/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ productId })
        });

        if (response.ok) {
            updateCartCount();
            showToast('Product added to cart successfully!');
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        showToast('Failed to add product to cart', 'error');
    }
}

// Order functions
function viewOrder(orderId) {
    window.location.href = `/user/orders.php?id=${orderId}`;
}

async function reorder(orderId) {
    try {
        const response = await fetch('/api/user/reorder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ orderId })
        });

        if (response.ok) {
            updateCartCount();
            showToast('Items added to cart successfully!');
        }
    } catch (error) {
        console.error('Error reordering:', error);
        showToast('Failed to reorder items', 'error');
    }
}

// Helper functions
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
        'cancelled': 'danger',
        'processing': 'info'
    };
    return colors[status.toLowerCase()] || 'secondary';
}

function showToast(message, type = 'success') {
    // Implement your preferred toast notification system
    alert(message);
} 
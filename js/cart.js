// Cart Page Functionality
document.addEventListener('DOMContentLoaded', function() {
    displayCart();
    updateCartSummary();
});

function displayCart() {
    const cartItemsContainer = document.getElementById('cartItems');
    const cart = JSON.parse(localStorage.getItem('cart')) || [];

    if (cart.length === 0) {
        cartItemsContainer.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p>Add some items to get started!</p>
                <a href="shop.html" class="btn btn-primary" style="margin-top: 1rem;">Shop Now</a>
            </div>
        `;
        return;
    }

    cartItemsContainer.innerHTML = cart.map(item => `
        <div class="cart-item" data-product-id="${item.id}">
            <div class="cart-item-image">
                <i class="fas fa-tshirt"></i>
            </div>
            <div class="cart-item-details">
                <h3>${item.name}</h3>
                <p class="cart-item-price">$${item.price.toFixed(2)}</p>
                <div class="quantity-controls">
                    <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">
                        <i class="fas fa-minus"></i>
                    </button>
                    <span class="quantity-display">${item.quantity}</span>
                    <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="cart-item-actions">
                <button class="remove-btn" onclick="removeFromCart(${item.id})">
                    <i class="fas fa-trash"></i>
                </button>
                <p style="margin-top: 1rem; font-weight: 600;">$${(item.price * item.quantity).toFixed(2)}</p>
            </div>
        </div>
    `).join('');
}

function updateQuantity(productId, change) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    const item = cart.find(item => item.id === productId);

    if (item) {
        item.quantity += change;
        
        if (item.quantity <= 0) {
            removeFromCart(productId);
            return;
        }

        localStorage.setItem('cart', JSON.stringify(cart));
        displayCart();
        updateCartSummary();
        updateCartCount();
        
        // Track quantity change
        trackEvent('cart_quantity_updated', { product_id: productId, new_quantity: item.quantity });
    }
}

function removeFromCart(productId) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    cart = cart.filter(item => item.id !== productId);
    
    localStorage.setItem('cart', JSON.stringify(cart));
    displayCart();
    updateCartSummary();
    updateCartCount();
    showNotification('Item removed from cart');
    
    // Track removal
    trackEvent('cart_item_removed', { product_id: productId });
}

function updateCartSummary() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const shipping = subtotal > 50 ? 0 : 5.99;
    const tax = subtotal * 0.10;
    const total = subtotal + shipping + tax;

    document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('shipping').textContent = shipping === 0 ? 'FREE' : `$${shipping.toFixed(2)}`;
    document.getElementById('tax').textContent = `$${tax.toFixed(2)}`;
    document.getElementById('total').textContent = `$${total.toFixed(2)}`;
}

function proceedToCheckout() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    if (cart.length === 0) {
        showNotification('Your cart is empty!');
        return;
    }

    // Track checkout initiation
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    trackEvent('begin_checkout', { 
        cart_value: subtotal,
        item_count: cart.length 
    });

    // Simulate checkout process
    showNotification('Redirecting to checkout... (Demo Mode)');
    
    setTimeout(() => {
        alert('Checkout Demo:\n\nThis is a mock checkout page for demonstration purposes.\n\nIn a production environment, this would redirect to a secure payment gateway.\n\nThank you for exploring Fabricon.shop!');
        
        // Track checkout completion (demo)
        trackEvent('checkout_completed_demo', { 
            cart_value: subtotal 
        });
    }, 1500);
}

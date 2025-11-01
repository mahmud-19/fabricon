// Cart Management
let cart = JSON.parse(localStorage.getItem('cart')) || [];

// Product Database
const products = {
    1: { id: 1, name: 'Classic Cotton T-Shirt', price: 29.99, category: 'Men', image: 'tshirt.jpg' },
    2: { id: 2, name: 'Elegant Summer Dress', price: 49.99, category: 'Women', image: 'dress.jpg' },
    3: { id: 3, name: 'Kids Casual Hoodie', price: 34.99, category: 'Children', image: 'hoodie.jpg' },
    4: { id: 4, name: 'Premium Denim Jeans', price: 79.99, category: 'Men', image: 'jeans.jpg' },
    5: { id: 5, name: 'Casual Blazer', price: 89.99, category: 'Men', image: 'blazer.jpg' },
    6: { id: 6, name: 'Floral Maxi Dress', price: 59.99, category: 'Women', image: 'maxi.jpg' },
    7: { id: 7, name: 'Kids Denim Jacket', price: 44.99, category: 'Children', image: 'jacket.jpg' },
    8: { id: 8, name: 'Sports Polo Shirt', price: 39.99, category: 'Men', image: 'polo.jpg' }
};

// Update cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    
    // Track page view for analytics
    trackPageView();
});

// Add to Cart Function
function addToCart(productId) {
    const product = products[productId];
    if (!product) return;

    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            quantity: 1,
            image: product.image
        });
    }

    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    showNotification('Product added to cart!');
    
    // Track add to cart event
    trackEvent('add_to_cart', { product_id: productId, product_name: product.name });
}

// Update Cart Count
function updateCartCount() {
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartBadges = document.querySelectorAll('.cart-badge, .cart-count');
    cartBadges.forEach(badge => {
        badge.textContent = totalItems;
    });
}

// Show Notification
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background-color: #F28B50;
        color: white;
        padding: 1rem 2rem;
        border-radius: 5px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Chatbot Functionality
function toggleChatbot() {
    const chatbot = document.getElementById('chatbot');
    chatbot.classList.toggle('active');
    
    // Track chatbot interaction
    if (chatbot.classList.contains('active')) {
        trackEvent('chatbot_opened');
    }
}

function sendMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    
    if (!message) return;
    
    const chatBody = document.getElementById('chatbotBody');
    
    // Add user message
    const userMessage = document.createElement('div');
    userMessage.className = 'chat-message user';
    userMessage.innerHTML = `<p>${message}</p>`;
    chatBody.appendChild(userMessage);
    
    input.value = '';
    
    // Track chatbot message
    trackEvent('chatbot_message_sent', { message: message });
    
    // Simulate bot response
    setTimeout(() => {
        const botMessage = document.createElement('div');
        botMessage.className = 'chat-message bot';
        botMessage.innerHTML = `<p>${getBotResponse(message)}</p>`;
        chatBody.appendChild(botMessage);
        chatBody.scrollTop = chatBody.scrollHeight;
    }, 1000);
    
    chatBody.scrollTop = chatBody.scrollHeight;
}

function getBotResponse(message) {
    const lowerMessage = message.toLowerCase();
    
    if (lowerMessage.includes('hello') || lowerMessage.includes('hi')) {
        return 'Hello! Welcome to Fabricon.shop. How can I assist you today?';
    } else if (lowerMessage.includes('price') || lowerMessage.includes('cost')) {
        return 'Our products range from $29.99 to $89.99. Check out our Shop page for detailed pricing!';
    } else if (lowerMessage.includes('shipping')) {
        return 'We offer free shipping on orders over $50. Standard shipping takes 3-5 business days.';
    } else if (lowerMessage.includes('return')) {
        return 'We have a 30-day return policy. Items must be unworn and in original condition.';
    } else if (lowerMessage.includes('size')) {
        return 'Please check our Size Guide page for detailed measurements. If you need help, feel free to ask!';
    } else if (lowerMessage.includes('track') || lowerMessage.includes('order')) {
        return 'You can track your order by logging into your account and visiting the Orders section.';
    } else {
        return 'Thank you for your question! For more detailed assistance, please contact our customer service at support@fabricon.shop or call us at 1-800-FABRICON.';
    }
}

// Allow Enter key to send message
document.addEventListener('DOMContentLoaded', function() {
    const chatInput = document.getElementById('chatInput');
    if (chatInput) {
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }
});

// Mobile Menu Toggle
function toggleMobileMenu() {
    const navMenu = document.querySelector('.nav-menu');
    navMenu.classList.toggle('active');
}

// Analytics Tracking Functions
function trackPageView() {
    const page = window.location.pathname;
    console.log('Page View:', page);
    
    // This would integrate with Google Analytics
    if (typeof gtag !== 'undefined') {
        gtag('event', 'page_view', {
            page_path: page
        });
    }
}

function trackEvent(eventName, eventParams = {}) {
    console.log('Event:', eventName, eventParams);
    
    // This would integrate with Google Analytics
    if (typeof gtag !== 'undefined') {
        gtag('event', eventName, eventParams);
    }
}

// Product Click Tracking
document.addEventListener('click', function(e) {
    if (e.target.closest('.product-card')) {
        const productCard = e.target.closest('.product-card');
        const productId = productCard.dataset.productId;
        if (productId) {
            trackEvent('product_click', { product_id: productId });
        }
    }
});

// Newsletter Form
document.addEventListener('DOMContentLoaded', function() {
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            showNotification('Thank you for subscribing!');
            this.reset();
            trackEvent('newsletter_signup', { email: email });
        });
    }
});

// Smooth Scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
    
    .nav-menu.active {
        display: flex;
        flex-direction: column;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background-color: white;
        padding: 1rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
`;
document.head.appendChild(style);

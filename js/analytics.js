/**
 * Google Analytics Integration for Fabricon.shop
 * 
 * This file handles all analytics tracking for the e-commerce platform.
 * It integrates with Google Analytics 4 (GA4) and tracks various user interactions.
 * 
 * To activate:
 * 1. Create a Google Analytics 4 property
 * 2. Replace 'G-XXXXXXXXXX' with your actual Measurement ID
 * 3. Include this script in your HTML pages
 */

// Google Analytics Configuration
const GA_MEASUREMENT_ID = 'G-XXXXXXXXXX'; // Replace with your actual GA4 Measurement ID

/**
 * Initialize Google Analytics
 */
function initializeAnalytics() {
    // Load Google Analytics script
    const script1 = document.createElement('script');
    script1.async = true;
    script1.src = `https://www.googletagmanager.com/gtag/js?id=${GA_MEASUREMENT_ID}`;
    document.head.appendChild(script1);

    // Initialize gtag
    window.dataLayer = window.dataLayer || [];
    function gtag() {
        dataLayer.push(arguments);
    }
    window.gtag = gtag;
    
    gtag('js', new Date());
    gtag('config', GA_MEASUREMENT_ID, {
        'send_page_view': true,
        'anonymize_ip': true // Privacy-friendly
    });

    console.log('Google Analytics initialized');
}

/**
 * Track page views
 * @param {string} pageTitle - Title of the page
 * @param {string} pagePath - Path of the page
 */
function trackPageView(pageTitle, pagePath) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'page_view', {
            page_title: pageTitle,
            page_path: pagePath,
            page_location: window.location.href
        });
        console.log('Page view tracked:', pageTitle);
    }
}

/**
 * Track product views
 * @param {object} product - Product information
 */
function trackProductView(product) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'view_item', {
            currency: 'USD',
            value: product.price,
            items: [{
                item_id: product.id,
                item_name: product.name,
                item_category: product.category,
                price: product.price
            }]
        });
        console.log('Product view tracked:', product.name);
    }
}

/**
 * Track add to cart events
 * @param {object} product - Product information
 * @param {number} quantity - Quantity added
 */
function trackAddToCart(product, quantity = 1) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'add_to_cart', {
            currency: 'USD',
            value: product.price * quantity,
            items: [{
                item_id: product.id,
                item_name: product.name,
                item_category: product.category,
                price: product.price,
                quantity: quantity
            }]
        });
        console.log('Add to cart tracked:', product.name);
    }
}

/**
 * Track remove from cart events
 * @param {object} product - Product information
 * @param {number} quantity - Quantity removed
 */
function trackRemoveFromCart(product, quantity = 1) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'remove_from_cart', {
            currency: 'USD',
            value: product.price * quantity,
            items: [{
                item_id: product.id,
                item_name: product.name,
                item_category: product.category,
                price: product.price,
                quantity: quantity
            }]
        });
        console.log('Remove from cart tracked:', product.name);
    }
}

/**
 * Track begin checkout event
 * @param {array} cartItems - Array of cart items
 * @param {number} totalValue - Total cart value
 */
function trackBeginCheckout(cartItems, totalValue) {
    if (typeof gtag !== 'undefined') {
        const items = cartItems.map(item => ({
            item_id: item.id,
            item_name: item.name,
            item_category: item.category,
            price: item.price,
            quantity: item.quantity
        }));

        gtag('event', 'begin_checkout', {
            currency: 'USD',
            value: totalValue,
            items: items
        });
        console.log('Begin checkout tracked:', totalValue);
    }
}

/**
 * Track purchase event
 * @param {string} transactionId - Unique transaction ID
 * @param {array} cartItems - Array of purchased items
 * @param {number} totalValue - Total purchase value
 * @param {number} tax - Tax amount
 * @param {number} shipping - Shipping cost
 */
function trackPurchase(transactionId, cartItems, totalValue, tax, shipping) {
    if (typeof gtag !== 'undefined') {
        const items = cartItems.map(item => ({
            item_id: item.id,
            item_name: item.name,
            item_category: item.category,
            price: item.price,
            quantity: item.quantity
        }));

        gtag('event', 'purchase', {
            transaction_id: transactionId,
            currency: 'USD',
            value: totalValue,
            tax: tax,
            shipping: shipping,
            items: items
        });
        console.log('Purchase tracked:', transactionId);
    }
}

/**
 * Track search events
 * @param {string} searchTerm - Search query
 */
function trackSearch(searchTerm) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'search', {
            search_term: searchTerm
        });
        console.log('Search tracked:', searchTerm);
    }
}

/**
 * Track user signup
 * @param {string} method - Signup method (email, google, facebook)
 */
function trackSignup(method) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'sign_up', {
            method: method
        });
        console.log('Signup tracked:', method);
    }
}

/**
 * Track user login
 * @param {string} method - Login method (email, google, facebook)
 */
function trackLogin(method) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'login', {
            method: method
        });
        console.log('Login tracked:', method);
    }
}

/**
 * Track form submissions
 * @param {string} formName - Name of the form
 * @param {string} formType - Type of form (contact, newsletter, etc.)
 */
function trackFormSubmission(formName, formType) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'form_submission', {
            form_name: formName,
            form_type: formType
        });
        console.log('Form submission tracked:', formName);
    }
}

/**
 * Track chatbot interactions
 * @param {string} action - Chatbot action (opened, message_sent, closed)
 * @param {string} message - User message (optional)
 */
function trackChatbot(action, message = '') {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'chatbot_interaction', {
            action: action,
            message: message
        });
        console.log('Chatbot interaction tracked:', action);
    }
}

/**
 * Track newsletter subscription
 * @param {string} email - Subscriber email
 */
function trackNewsletterSubscription(email) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'newsletter_subscription', {
            email_domain: email.split('@')[1] // Track domain only for privacy
        });
        console.log('Newsletter subscription tracked');
    }
}

/**
 * Track custom events
 * @param {string} eventName - Name of the event
 * @param {object} eventParams - Event parameters
 */
function trackCustomEvent(eventName, eventParams = {}) {
    if (typeof gtag !== 'undefined') {
        gtag('event', eventName, eventParams);
        console.log('Custom event tracked:', eventName);
    }
}

/**
 * Track user engagement time
 */
function trackEngagement() {
    let startTime = Date.now();
    
    window.addEventListener('beforeunload', function() {
        const timeSpent = Math.round((Date.now() - startTime) / 1000); // in seconds
        
        if (typeof gtag !== 'undefined') {
            gtag('event', 'user_engagement', {
                engagement_time_msec: timeSpent * 1000
            });
        }
    });
}

/**
 * Track scroll depth
 */
function trackScrollDepth() {
    let maxScroll = 0;
    const milestones = [25, 50, 75, 100];
    const tracked = new Set();

    window.addEventListener('scroll', function() {
        const scrollPercent = Math.round(
            (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100
        );
        
        maxScroll = Math.max(maxScroll, scrollPercent);
        
        milestones.forEach(milestone => {
            if (maxScroll >= milestone && !tracked.has(milestone)) {
                tracked.add(milestone);
                
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'scroll', {
                        percent_scrolled: milestone
                    });
                }
                console.log('Scroll depth tracked:', milestone + '%');
            }
        });
    });
}

/**
 * Track outbound links
 */
function trackOutboundLinks() {
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        
        if (link && link.hostname !== window.location.hostname) {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'click', {
                    event_category: 'outbound',
                    event_label: link.href,
                    transport_type: 'beacon'
                });
            }
            console.log('Outbound link tracked:', link.href);
        }
    });
}

/**
 * Initialize all tracking
 */
function initAllTracking() {
    initializeAnalytics();
    trackEngagement();
    trackScrollDepth();
    trackOutboundLinks();
    
    // Track initial page view
    trackPageView(document.title, window.location.pathname);
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAllTracking);
} else {
    initAllTracking();
}

// Export functions for use in other scripts
window.FabriconAnalytics = {
    trackPageView,
    trackProductView,
    trackAddToCart,
    trackRemoveFromCart,
    trackBeginCheckout,
    trackPurchase,
    trackSearch,
    trackSignup,
    trackLogin,
    trackFormSubmission,
    trackChatbot,
    trackNewsletterSubscription,
    trackCustomEvent
};

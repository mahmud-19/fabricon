<?php
/**
 * Fabricon.shop Chatbot API
 * 
 * This PHP script handles chatbot interactions for the Fabricon e-commerce platform.
 * It processes user messages and returns appropriate responses.
 * 
 * Future enhancements:
 * - Integration with AI/ML services
 * - Database logging of conversations
 * - User session tracking
 * - Advanced natural language processing
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get the input data
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = isset($input['message']) ? trim($input['message']) : '';

if (empty($userMessage)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit();
}

// Process the message and generate response
$response = generateResponse($userMessage);

// Log the conversation (future feature - requires database)
// logConversation($userMessage, $response);

// Return the response
echo json_encode([
    'success' => true,
    'message' => $response,
    'timestamp' => date('Y-m-d H:i:s')
]);

/**
 * Generate chatbot response based on user input
 * 
 * @param string $message User's message
 * @return string Bot's response
 */
function generateResponse($message) {
    $message = strtolower($message);
    
    // Greeting patterns
    if (preg_match('/\b(hello|hi|hey|good morning|good afternoon|good evening)\b/', $message)) {
        return "Hello! Welcome to Fabricon.shop. How can I assist you today? I can help with questions about products, shipping, returns, sizing, and orders.";
    }
    
    // Product and pricing questions
    if (preg_match('/\b(price|cost|how much|expensive|cheap|affordable)\b/', $message)) {
        return "Our products range from $29.99 to $89.99, offering great value for quality fashion. Check out our Shop page for detailed pricing and current sales!";
    }
    
    // Shipping questions
    if (preg_match('/\b(shipping|delivery|ship|deliver|how long)\b/', $message)) {
        return "We offer free shipping on orders over $50! Standard shipping takes 3-5 business days, and express shipping is available for 1-2 day delivery. International shipping is also available.";
    }
    
    // Return policy
    if (preg_match('/\b(return|refund|exchange|money back)\b/', $message)) {
        return "We have a hassle-free 30-day return policy. Items must be unworn, unwashed, and in their original condition with tags attached. Visit our Returns page for more details.";
    }
    
    // Size and fit questions
    if (preg_match('/\b(size|sizing|fit|measurement|too small|too big)\b/', $message)) {
        return "Please check our Size Guide page for detailed measurements for all our products. If you need personalized sizing advice, our customer service team is happy to help!";
    }
    
    // Order tracking
    if (preg_match('/\b(track|tracking|order status|where is my order|delivery status)\b/', $message)) {
        return "You can track your order by logging into your account and visiting the Orders section. You'll also receive a tracking number via email once your order ships.";
    }
    
    // Payment questions
    if (preg_match('/\b(payment|pay|credit card|paypal|checkout)\b/', $message)) {
        return "We accept all major credit cards, PayPal, and other secure payment methods. All transactions are encrypted and secure. Your payment information is never stored on our servers.";
    }
    
    // Product availability
    if (preg_match('/\b(available|in stock|out of stock|restock)\b/', $message)) {
        return "Product availability is shown on each product page. If an item is out of stock, you can sign up for restock notifications. We typically restock popular items within 2-3 weeks.";
    }
    
    // Categories
    if (preg_match('/\b(men|women|children|kids|category|categories)\b/', $message)) {
        return "We offer collections for Men, Women, and Children. Each category features a wide range of clothing items from casual wear to formal attire. Browse our Shop page to explore all categories!";
    }
    
    // Discount and promotions
    if (preg_match('/\b(discount|sale|promo|coupon|offer|deal)\b/', $message)) {
        return "We regularly offer special promotions and discounts! Sign up for our newsletter to receive exclusive offers and be the first to know about sales. Check our homepage for current deals.";
    }
    
    // Contact information
    if (preg_match('/\b(contact|phone|email|call|reach)\b/', $message)) {
        return "You can reach us at:\n📞 Phone: 1-800-FABRICON\n📧 Email: support@fabricon.shop\n⏰ Hours: Mon-Fri 9AM-6PM, Sat 10AM-4PM\nOr visit our Contact page for more options!";
    }
    
    // Account questions
    if (preg_match('/\b(account|login|sign up|register|password)\b/', $message)) {
        return "You can create an account or login on our Login page. Benefits include order tracking, faster checkout, exclusive offers, and a personalized wishlist. Forgot your password? Use the 'Forgot Password' link on the login page.";
    }
    
    // Quality and materials
    if (preg_match('/\b(quality|material|fabric|cotton|denim)\b/', $message)) {
        return "We're committed to quality! All our products are made from premium materials and undergo rigorous quality checks. Product descriptions include detailed material information.";
    }
    
    // Sustainability
    if (preg_match('/\b(sustainable|eco|environment|green|ethical)\b/', $message)) {
        return "Sustainability is important to us! We're committed to eco-friendly practices, from sourcing materials to packaging. Learn more about our sustainability initiatives on our About page.";
    }
    
    // Thank you
    if (preg_match('/\b(thank|thanks|appreciate)\b/', $message)) {
        return "You're very welcome! Is there anything else I can help you with today?";
    }
    
    // Goodbye
    if (preg_match('/\b(bye|goodbye|see you|later)\b/', $message)) {
        return "Thank you for visiting Fabricon.shop! Have a great day and happy shopping! 👋";
    }
    
    // Default response
    return "Thank you for your question! For more detailed assistance, please contact our customer service team at support@fabricon.shop or call us at 1-800-FABRICON. Our team is available Mon-Fri 9AM-6PM.";
}

/**
 * Log conversation to database (future implementation)
 * 
 * @param string $userMessage User's message
 * @param string $botResponse Bot's response
 */
function logConversation($userMessage, $botResponse) {
    // TODO: Implement database logging
    // This will require MySQL connection and a conversations table
    /*
    $db = new mysqli('localhost', 'username', 'password', 'fabricon_db');
    $stmt = $db->prepare("INSERT INTO chatbot_logs (user_message, bot_response, timestamp) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $userMessage, $botResponse);
    $stmt->execute();
    $stmt->close();
    $db->close();
    */
}

/**
 * Get user session information (future implementation)
 * 
 * @return array User session data
 */
function getUserSession() {
    // TODO: Implement session tracking
    session_start();
    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'session_id' => session_id()
    ];
}
?>

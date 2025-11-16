<?php
/**
 * Customer Review Request Email Template
 * This is the actual email sent to customers
 * File: templates/customer-email.php
 */
if (!defined('ABSPATH')) exit;

// Get settings
$settings = get_option('arm_settings');
$email_subject = $settings['email_subject'] ?? "We'd love your feedback!";
$email_heading = $settings['email_heading'] ?? 'How was your recent purchase?';
// $email_message is now pre-processed from the calling function with replacements applied
if (!isset($email_message)) {
    $email_message = $settings['email_message'] ?? 'Hi {customer_name},\n\nThank you for your recent order! We hope you\'re loving your purchase.\n\nWould you mind taking a moment to share your experience? Your feedback helps us improve and helps other customers make informed decisions.';
}
$button_text = $settings['button_text'] ?? 'Write a Review';
$button_color = $settings['button_color'] ?? '#ff6b6b';

// Variables that will be replaced when email is sent
// {customer_name} - Customer's first name
// {product_name} - Name of the product
// {product_names} - List of all products (for multi-product orders)
// {review_link} - Link to leave a review
// {order_number} - Order number
// {site_name} - Your store name
// {store_url} - Your store URL

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($email_subject); ?></title>
    <style>
        /* Reset styles */
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f4f4;
            line-height: 1.6;
        }
        
        /* Container */
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        
        /* Header */
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
        }
        
        .email-header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
        }
        
        .email-icon {
            font-size: 48px;
            margin-bottom: 10px;
            display: block;
        }
        
        /* Body */
        .email-body {
            padding: 40px 30px;
            color: #333333;
        }
        
        .email-body p {
            margin: 0 0 20px 0;
            font-size: 16px;
            line-height: 1.8;
            color: #555555;
        }
        
        .email-body p:last-of-type {
            margin-bottom: 30px;
        }
        
        /* Product Info */
        .product-info {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 25px 0;
            border-radius: 8px;
        }
        
        .product-info h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 18px;
        }
        
        .product-list {
            list-style: none;
            padding: 0;
            margin: 10px 0 0 0;
        }
        
        .product-list li {
            padding: 8px 0;
            color: #555;
            font-weight: 500;
        }
        
        .product-list li:before {
            content: "✓ ";
            color: #667eea;
            font-weight: bold;
            margin-right: 8px;
        }
        
        /* CTA Button */
        .cta-container {
            text-align: center;
            margin: 35px 0;
        }
        
        .cta-button {
            display: inline-block;
            padding: 16px 40px;
            background-color: <?php echo esc_attr($button_color); ?>;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 17px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }
        
        /* Star Rating Visual */
        .star-rating {
            text-align: center;
            margin: 30px 0;
            font-size: 32px;
            color: #FFD700;
        }
        
        .rating-text {
            text-align: center;
            color: #999;
            font-size: 14px;
            margin-top: 10px;
        }
        
        /* Incentive Banner */
        .incentive-banner {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: #ffffff;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin: 25px 0;
        }
        
        .incentive-banner h3 {
            margin: 0 0 8px 0;
            font-size: 20px;
        }
        
        .incentive-banner p {
            margin: 0;
            font-size: 15px;
            color: #ffffff;
        }
        
        .incentive-icon {
            font-size: 36px;
            display: block;
            margin-bottom: 10px;
        }
        
        /* Footer */
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 3px solid #667eea;
        }
        
        .email-footer p {
            margin: 0 0 10px 0;
            color: #999999;
            font-size: 13px;
        }
        
        .email-footer a {
            color: #667eea;
            text-decoration: none;
        }
        
        .social-links {
            margin: 20px 0 10px 0;
        }
        
        .social-links a {
            display: inline-block;
            margin: 0 8px;
            font-size: 24px;
            text-decoration: none;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-header {
                padding: 30px 20px;
            }
            
            .email-header h1 {
                font-size: 24px;
            }
            
            .email-body {
                padding: 30px 20px;
            }
            
            .cta-button {
                display: block;
                padding: 14px 30px;
            }
            
            .product-info {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="email-header">
            <span class="email-icon">⭐</span>
            <h1><?php echo esc_html($email_heading); ?></h1>
        </div>
        
        <!-- Body -->
        <div class="email-body">
            <p><?php echo nl2br(wp_kses_post($email_message)); ?></p>
            
            <!-- Product Info (will be populated dynamically) -->
            <div class="product-info">
                <h3>Your Recent Order #{order_number}</h3>
                <ul class="product-list">
                    {product_names}
                </ul>
            </div>
            
            <!-- Incentive Banner (shown if incentives enabled) -->
            {incentive_section}
            
            <!-- CTA Button -->
            <div class="cta-container">
                <a href="{review_link}" class="cta-button"><?php echo esc_html($button_text); ?></a>
            </div>
            
            <!-- QR Code (shown if enabled) -->
            {qr_code}
            
            <!-- Star Rating Visual -->
            <div class="star-rating">
                ★★★★★
            </div>
            <p class="rating-text">Click the button above to leave your review</p>
            
            <p style="margin-top: 30px; font-size: 14px; color: #999;">
                It only takes a minute and your feedback truly makes a difference!
            </p>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <p><strong>{site_name}</strong></p>
            <p>
                <a href="{store_url}">Visit Our Store</a> | 
                <a href="{store_url}/my-account">My Account</a>
            </p>
            <p style="margin-top: 20px;">
                You received this email because you recently made a purchase from {site_name}.<br>
                Questions? <a href="{store_url}/contact">Contact Us</a>
            </p>
            <p style="font-size: 12px; margin-top: 15px;">
                &copy; <?php echo date('Y'); ?> {site_name}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>

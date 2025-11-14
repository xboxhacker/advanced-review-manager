<?php
/**
 * Plugin Name: Advanced Review Manager Pro
 * Plugin URI: 
 * Description: Ultimate WooCommerce review management system with multi-product reviews, photo/video uploads, SMS integration, A/B testing, social proof widgets, incentives, advanced analytics, and automated follow-ups
 * Version: 2.0.0
 * Author: Xboxhacker
 * Author URI: 
 * Text Domain: advanced-review-manager
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 9.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ARM_VERSION', '2.0.0');
define('ARM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ARM_PLUGIN_URL', plugin_dir_url(__FILE__));

class Advanced_Review_Manager {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Check if WooCommerce is active
        if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Activation/Deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Check for migrations
        add_action('admin_init', array($this, 'check_migrations'));
        
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // WooCommerce hooks
        add_action('woocommerce_order_status_completed', array($this, 'schedule_review_reminder'), 10, 1);
        add_filter('manage_edit-shop_order_columns', array($this, 'add_review_reminder_column'));
        add_action('manage_shop_order_posts_custom_column', array($this, 'render_review_reminder_column'), 10, 2);
        
        // AJAX handlers
        add_action('wp_ajax_arm_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_arm_send_test_email', array($this, 'ajax_send_test_email'));
        add_action('wp_ajax_arm_generate_fake_review', array($this, 'ajax_generate_fake_review'));
        add_action('wp_ajax_arm_send_instant_reminder', array($this, 'ajax_send_instant_reminder'));
        add_action('wp_ajax_arm_schedule_reminder', array($this, 'ajax_schedule_reminder'));
        add_action('wp_ajax_arm_get_analytics', array($this, 'ajax_get_analytics'));
        add_action('wp_ajax_arm_save_email_template', array($this, 'ajax_save_email_template'));
        add_action('wp_ajax_arm_reset_email_template', array($this, 'ajax_reset_email_template'));
        add_action('wp_ajax_arm_save_multi_product_template', array($this, 'ajax_save_multi_product_template'));
        add_action('wp_ajax_arm_save_followup_templates', array($this, 'ajax_save_followup_templates'));
        add_action('wp_ajax_arm_generate_bulk_reviews', array($this, 'ajax_generate_bulk_reviews'));
        add_action('wp_ajax_arm_save_review_templates', array($this, 'ajax_save_review_templates'));
        add_action('wp_ajax_arm_get_advanced_analytics', array($this, 'ajax_get_advanced_analytics'));
        add_action('wp_ajax_arm_get_ab_test_results', array($this, 'ajax_get_ab_test_results'));
        add_action('wp_ajax_arm_get_generated_stats', array($this, 'ajax_get_generated_stats'));
        add_action('wp_ajax_arm_export_analytics', array($this, 'ajax_export_analytics'));
        add_action('wp_ajax_arm_track_email_open', array($this, 'ajax_track_email_open'));
        add_action('wp_ajax_arm_track_email_click', array($this, 'ajax_track_email_click'));
        add_action('wp_ajax_nopriv_arm_track_email_open', array($this, 'ajax_track_email_open'));
        add_action('wp_ajax_nopriv_arm_track_email_click', array($this, 'ajax_track_email_click'));
        add_action('wp_ajax_arm_bulk_send_reminders', array($this, 'ajax_bulk_send_reminders'));
        add_action('wp_ajax_arm_add_to_blacklist', array($this, 'ajax_add_to_blacklist'));
        add_action('wp_ajax_arm_remove_from_blacklist', array($this, 'ajax_remove_from_blacklist'));
        add_action('wp_ajax_arm_sync_google_reviews', array($this, 'ajax_sync_google_reviews'));
        add_action('wp_ajax_arm_import_google_review', array($this, 'ajax_import_google_review'));
        add_action('wp_ajax_arm_get_optimal_send_time', array($this, 'ajax_get_optimal_send_time'));
        
        // Frontend actions
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('wp_footer', array($this, 'render_social_proof_widgets'));
        add_filter('woocommerce_product_review_comment_form_args', array($this, 'add_media_upload_to_review_form'));
        add_action('comment_post', array($this, 'handle_review_media_upload'), 10, 2);
        add_action('comment_post', array($this, 'handle_review_incentive'), 10, 2);
        add_action('comment_post', array($this, 'handle_review_moderation'), 10, 2);
        add_action('comment_post', array($this, 'handle_review_gating'), 10, 2);
        add_action('comment_post', array($this, 'track_review_submission'), 10, 2);
        
        // WooCommerce product page enhancements
        add_action('woocommerce_after_shop_loop_item', array($this, 'display_review_badge'), 15);
        add_action('woocommerce_after_single_product_summary', array($this, 'display_review_carousel'), 15);
        
        // Email tracking
        add_action('init', array($this, 'handle_email_tracking'));
        
        // Cron hook
        add_action('arm_send_review_reminder', array($this, 'send_review_reminder'), 10, 1);
        
        // Shortcode for review submission
        add_shortcode('arm_review_form', array($this, 'review_form_shortcode'));
    }
    
    public function woocommerce_missing_notice() {
        echo '<div class="error"><p><strong>Advanced Review Manager Pro</strong> requires WooCommerce to be installed and active.</p></div>';
    }
    
    public function check_migrations() {
        $db_version = get_option('arm_db_version', '1.0.0');
        
        // Migration for version 2.0.0 - Add email template defaults
        if (version_compare($db_version, '2.0.0', '<')) {
            $this->migrate_to_2_0_0();
            update_option('arm_db_version', '2.0.0');
        }
    }
    
    private function migrate_to_2_0_0() {
        $settings = get_option('arm_settings', array());
        
        // Default email templates - only add if missing
        $email_defaults = array(
            'email_subject' => 'How was your recent order with {store_name}?',
            'email_heading' => 'How was your experience?',
            'email_message' => 'Hi {customer_name},

Thank you for choosing {store_name} for your recent purchase! We hope you\'re enjoying your order.

Your feedback means everything to us and helps other customers make confident decisions. We\'d be incredibly grateful if you could take just a moment to share your experience with:

<strong>{product_names}</strong>

<strong>What to expect:</strong>
• Quick and easy - takes less than 2 minutes
• Help fellow shoppers make better decisions  
• Your honest feedback helps us improve our products and service

We truly value your opinion and thank you for being a valued customer!

Best regards,
The {store_name} Team',
            
            'email_subject_b' => 'We\'d love to hear about your {store_name} experience! 💬',
            'email_heading_b' => 'Share Your Thoughts! 💭',
            'email_message_b' => 'Hey {customer_name}! 👋

We hope you\'re loving your recent order from {store_name}!

Got a minute? We\'d absolutely love to hear your thoughts on:

<strong>{product_names}</strong>

<strong>Why share a review?</strong>
✨ Helps fellow shoppers make great choices
✨ Shows us what we\'re doing right (and where we can improve!)
✨ Takes less than 2 minutes of your time

Your feedback is like gold to us and our community. Thanks for being awesome!

Cheers,
{store_name} Team 🌟',
            
            'email_subject_c' => '⏰ Quick favor? We need your input!',
            'email_heading_c' => 'Your Opinion Matters! ⭐',
            'email_message_c' => 'Hi {customer_name},

<strong>We need your help!</strong>

Your recent purchase of <strong>{product_names}</strong> from {store_name} could help countless other shoppers make the right decision.

<strong>Here\'s why your review matters:</strong>

📊 Reviews influence 93% of shopping decisions
⭐ Products with reviews sell 3x more than those without
🎯 Your opinion directly shapes what we offer

<strong>It takes just 60 seconds</strong> to share your experience and make a real impact.

Can you help us out?

Thank you for your time!
{store_name} Team',

            'button_text' => 'Leave a Review',
            'button_color' => '#7c3aed',
            'button_text_b' => 'Share My Thoughts',
            'button_color_b' => '#7c3aed',
            'button_text_c' => 'Write My Review Now',
            'button_color_c' => '#dc2626'
        );
        
        // Only add missing email template fields
        foreach ($email_defaults as $key => $value) {
            if (!isset($settings[$key]) || empty($settings[$key])) {
                $settings[$key] = $value;
            }
        }
        
        update_option('arm_settings', $settings);
    }
    
    public function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Table for review reminders tracking
        $table_name = $wpdb->prefix . 'arm_reminders';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            customer_email varchar(100) NOT NULL,
            reminder_sent tinyint(1) DEFAULT 0,
            sent_date datetime DEFAULT NULL,
            review_submitted tinyint(1) DEFAULT 0,
            email_variant varchar(10) DEFAULT 'a',
            followup_count int DEFAULT 0,
            last_followup datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY order_id (order_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Table for A/B testing tracking
        $ab_table = $wpdb->prefix . 'arm_ab_tests';
        $sql = "CREATE TABLE IF NOT EXISTS $ab_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            reminder_id bigint(20) NOT NULL,
            variant varchar(10) NOT NULL,
            sent_date datetime DEFAULT NULL,
            opened tinyint(1) DEFAULT 0,
            opened_date datetime DEFAULT NULL,
            clicked tinyint(1) DEFAULT 0,
            clicked_date datetime DEFAULT NULL,
            converted tinyint(1) DEFAULT 0,
            converted_date datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY reminder_id (reminder_id),
            KEY variant (variant)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Table for review media (photos/videos)
        $media_table = $wpdb->prefix . 'arm_review_media';
        $sql = "CREATE TABLE IF NOT EXISTS $media_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            comment_id bigint(20) NOT NULL,
            attachment_id bigint(20) NOT NULL,
            media_type varchar(20) DEFAULT 'image',
            file_url text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY comment_id (comment_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Table for incentives/rewards
        $incentives_table = $wpdb->prefix . 'arm_incentives';
        $sql = "CREATE TABLE IF NOT EXISTS $incentives_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            comment_id bigint(20) NOT NULL,
            order_id bigint(20) NOT NULL,
            customer_email varchar(100) NOT NULL,
            incentive_type varchar(50) DEFAULT 'coupon',
            coupon_code varchar(100),
            points_awarded int DEFAULT 0,
            claimed tinyint(1) DEFAULT 0,
            claimed_date datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY comment_id (comment_id),
            KEY order_id (order_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Table for email tracking
        $tracking_table = $wpdb->prefix . 'arm_email_tracking';
        $sql = "CREATE TABLE IF NOT EXISTS $tracking_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            reminder_id bigint(20) NOT NULL,
            tracking_token varchar(64) NOT NULL,
            opened tinyint(1) DEFAULT 0,
            opened_date datetime DEFAULT NULL,
            opened_count int DEFAULT 0,
            clicked tinyint(1) DEFAULT 0,
            clicked_date datetime DEFAULT NULL,
            clicked_count int DEFAULT 0,
            user_agent text,
            ip_address varchar(45),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY tracking_token (tracking_token),
            KEY reminder_id (reminder_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Table for SMS tracking
        $sms_table = $wpdb->prefix . 'arm_sms_log';
        $sql = "CREATE TABLE IF NOT EXISTS $sms_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            phone_number varchar(20) NOT NULL,
            message text,
            status varchar(20) DEFAULT 'sent',
            twilio_sid varchar(100),
            sent_date datetime DEFAULT CURRENT_TIMESTAMP,
            error_message text,
            PRIMARY KEY (id),
            KEY order_id (order_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Table for review moderation queue
        $moderation_table = $wpdb->prefix . 'arm_moderation';
        $sql = "CREATE TABLE IF NOT EXISTS $moderation_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            comment_id bigint(20) NOT NULL,
            flagged_reason varchar(100),
            flagged_date datetime DEFAULT CURRENT_TIMESTAMP,
            reviewed tinyint(1) DEFAULT 0,
            reviewed_by bigint(20),
            reviewed_date datetime DEFAULT NULL,
            action_taken varchar(50),
            PRIMARY KEY (id),
            KEY comment_id (comment_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Table for blacklisted products
        $blacklist_table = $wpdb->prefix . 'arm_product_blacklist';
        $sql = "CREATE TABLE IF NOT EXISTS $blacklist_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            reason text,
            added_date datetime DEFAULT CURRENT_TIMESTAMP,
            added_by bigint(20),
            PRIMARY KEY (id),
            UNIQUE KEY product_id (product_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Table for Google Reviews sync
        $google_reviews_table = $wpdb->prefix . 'arm_google_reviews';
        $sql = "CREATE TABLE IF NOT EXISTS $google_reviews_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            google_review_id varchar(255) NOT NULL,
            product_id bigint(20) DEFAULT NULL,
            author_name varchar(255),
            author_photo varchar(500),
            rating int NOT NULL,
            review_text text,
            review_date datetime,
            imported_date datetime DEFAULT CURRENT_TIMESTAMP,
            synced tinyint(1) DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY google_review_id (google_review_id),
            KEY product_id (product_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Default settings with all new features
        $default_settings = array(
            // Basic settings
            'reminder_days' => 7,
            
            // Template A - Default/Professional
            'email_subject' => 'We\'d love your feedback on your recent order!',
            'email_subject_a' => 'We\'d love your feedback on your recent order!',
            'email_heading' => 'How was your experience?',
            'email_heading_a' => 'How was your experience?',
            'email_message' => 'Hi {customer_name},

Thank you for choosing us for your recent purchase! We hope you\'re enjoying your order.

Your opinion matters to us and helps other customers make informed decisions. We\'d be grateful if you could take a moment to share your experience.

<strong>What to expect:</strong>
• Quick and easy review process
• Help others discover great products
• Your honest feedback helps us improve

Click the button below to leave your review. It only takes a minute!',
            'email_message_a' => 'Hi {customer_name},

Thank you for choosing us for your recent purchase! We hope you\'re enjoying your order.

Your opinion matters to us and helps other customers make informed decisions. We\'d be grateful if you could take a moment to share your experience.

<strong>What to expect:</strong>
• Quick and easy review process
• Help others discover great products
• Your honest feedback helps us improve

Click the button below to leave your review. It only takes a minute!',
            'button_text' => 'Leave a Review',
            'button_text_a' => 'Leave a Review',
            'button_color' => '#667eea',
            'button_color_a' => '#667eea',
            'show_incentive_a' => false,
            'incentive_message_a' => 'Leave a review and get 10% off your next purchase!',
            
            // Template B - Friendly/Casual
            'email_subject_b' => 'How\'s your order treating you?',
            'email_heading_b' => 'We\'d Love Your Feedback! 💜',
            'email_message_b' => 'Hey {customer_name}! 👋

We hope you\'re loving your recent order from {store_name}!

We\'re always working to make shopping with us even better, and your thoughts really help. Got a minute to share what you think?

<strong>Why your review matters:</strong>
✨ Helps fellow shoppers find their perfect products
✨ Shows us what we\'re doing right (and what we can improve!)
✨ Takes less than 60 seconds

Ready to share your experience? Just click below!',
            'button_text_b' => 'Share My Thoughts',
            'button_color_b' => '#4CAF50',
            
            // Template C - Urgent/FOMO
            'email_subject_c' => '⭐ Quick favor? We need your input!',
            'email_heading_c' => 'Your Opinion = Big Impact',
            'email_message_c' => 'Hi {customer_name},

You recently ordered from us, and we\'re on a mission to become even better!

<strong>Here\'s the deal:</strong> We read every single review. Your feedback directly influences what products we carry, how we package items, and the overall shopping experience.

<strong>Quick stats:</strong>
📊 Reviews influence 93% of shopping decisions
⭐ Products with reviews sell 3x more
💡 Your insight helps us stock what YOU want

Make your voice heard - it takes just 30 seconds!',
            'button_text_c' => 'Write My Review Now',
            'button_color_c' => '#9C27B0',
            
            'enable_reminders' => true,
            'fake_review_enabled' => false,
            
            // SMS Integration
            'enable_sms' => false,
            'twilio_account_sid' => '',
            'twilio_auth_token' => '',
            'twilio_phone_number' => '',
            'sms_message' => 'Hi {customer_name}! Thanks for your order. We\'d love your feedback: {review_url}',
            
            // Multi-Product Reviews
            'enable_multi_product' => true,
            'max_products_per_email' => 5,
            'multi_email_subject' => 'How did we do? Review your items',
            'multi_email_intro' => 'Hi {customer_name},\n\nThank you for your order! We\'d love to hear your thoughts on each of your items:',
            'multi_product_prompt' => 'How would you rate this product?',
            
            // Photo/Video Reviews
            'enable_photo_reviews' => true,
            'enable_video_reviews' => true,
            'max_media_files' => 5,
            'max_file_size' => 10,
            
            // Incentives & Rewards
            'enable_incentives' => false,
            'incentive_type' => 'coupon',
            'coupon_amount' => 10,
            'coupon_type' => 'percent',
            'coupon_expiry_days' => 30,
            'points_amount' => 100,
            
            // A/B Testing
            'enable_ab_testing' => false,
            
            // QR Code in Emails
            'enable_qr_code' => true,
            'qr_code_size' => 200,
            
            // Google Reviews Integration
            'enable_google_reviews' => false,
            'google_place_id' => '',
            'google_api_key' => '',
            'auto_import_google_reviews' => false,
            'google_sync_interval' => 'daily',
            
            // Bulk Actions
            'bulk_send_limit' => 50,
            
            // Smart Timing
            'enable_smart_timing' => false,
            'preferred_send_hour' => 10,
            
            // Product Blacklist
            'enable_product_blacklist' => true,
            
            // Follow-up Sequences
            'enable_followup' => false,
            'followup_count' => 2,
            'followup_interval' => 7,
            'followup1_subject' => 'Still waiting to hear from you...',
            'followup1_message' => 'Hi {customer_name},\n\nWe noticed you haven\'t left a review yet. Your feedback is incredibly valuable to us!\n\nIt only takes a minute and helps us improve.',
            'followup2_subject' => 'Last chance - Share your feedback!',
            'followup2_message' => 'Hi {customer_name},\n\nThis is our final request for your review. We really want to hear your thoughts!\n\nAs a thank you, we\'re offering a special discount on your next order.',
            
            // Review Moderation
            'enable_moderation' => false,
            'enable_profanity_filter' => true,
            'enable_spam_detection' => true,
            
            // Review Gating
            'enable_review_gating' => false,
            'gating_threshold' => 3,
            'support_email' => get_option('admin_email'),
            
            // Google Shopping
            'enable_google_shopping' => false,
            'google_merchant_id' => '',
            
            // Social Proof Widgets
            'enable_floating_widget' => true,
            'enable_review_badge' => true,
            'enable_review_carousel' => true,
            'widget_position' => 'bottom-left',
        );
        
        if (!get_option('arm_settings')) {
            update_option('arm_settings', $default_settings);
        }
    }
    
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('arm_send_review_reminder');
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Review Manager',
            'Review Manager',
            'manage_options',
            'advanced-review-manager',
            array($this, 'render_dashboard'),
            'dashicons-star-filled',
            56
        );
        
        add_submenu_page(
            'advanced-review-manager',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'advanced-review-manager',
            array($this, 'render_dashboard')
        );
        
        add_submenu_page(
            'advanced-review-manager',
            'Settings',
            'Settings',
            'manage_options',
            'arm-settings',
            array($this, 'render_settings')
        );
        
        add_submenu_page(
            'advanced-review-manager',
            'Email Template',
            'Email Template',
            'manage_options',
            'arm-email-template',
            array($this, 'render_email_template')
        );
        
        add_submenu_page(
            'advanced-review-manager',
            'Analytics',
            'Analytics',
            'manage_options',
            'arm-analytics',
            array($this, 'render_analytics')
        );
        
        add_submenu_page(
            'advanced-review-manager',
            'Fake Reviews',
            'Fake Reviews',
            'manage_options',
            'arm-fake-reviews',
            array($this, 'render_fake_reviews')
        );
        
        add_submenu_page(
            'advanced-review-manager',
            'Bulk Actions',
            'Bulk Actions',
            'manage_options',
            'arm-bulk-actions',
            array($this, 'render_bulk_actions')
        );
        
        add_submenu_page(
            'advanced-review-manager',
            'Product Blacklist',
            'Product Blacklist',
            'manage_options',
            'arm-product-blacklist',
            array($this, 'render_product_blacklist')
        );
        
        add_submenu_page(
            'advanced-review-manager',
            'Google Reviews',
            'Google Reviews',
            'manage_options',
            'arm-google-reviews',
            array($this, 'render_google_reviews')
        );
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'advanced-review-manager') === false && strpos($hook, 'arm-') === false && $hook !== 'edit.php') {
            return;
        }
        
        // Add timestamp to bust cache
        $version = ARM_VERSION . '.' . time();
        
        wp_enqueue_style('arm-admin-css', ARM_PLUGIN_URL . 'assets/admin-style.css', array(), $version);
        wp_enqueue_script('arm-admin-js', ARM_PLUGIN_URL . 'assets/admin-script.js', array('jquery'), $version, true);
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        wp_localize_script('arm-admin-js', 'armAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('arm_nonce')
        ));
    }
    
    public function schedule_review_reminder($order_id) {
        $settings = get_option('arm_settings');
        
        if (!isset($settings['enable_reminders']) || !$settings['enable_reminders']) {
            return;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        // Check product blacklist
        if (isset($settings['enable_product_blacklist']) && $settings['enable_product_blacklist']) {
            global $wpdb;
            $blacklist_table = $wpdb->prefix . 'arm_product_blacklist';
            
            foreach ($order->get_items() as $item) {
                $product_id = $item->get_product_id();
                $is_blacklisted = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM $blacklist_table WHERE product_id = %d",
                    $product_id
                ));
                
                if ($is_blacklisted) {
                    return; // Don't send reminder if any product is blacklisted
                }
            }
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'arm_reminders';
        
        // Check if reminder already exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE order_id = %d",
            $order_id
        ));
        
        if (!$existing) {
            $wpdb->insert(
                $table_name,
                array(
                    'order_id' => $order_id,
                    'customer_email' => $order->get_billing_email(),
                    'reminder_sent' => 0,
                    'created_at' => current_time('mysql')
                ),
                array('%d', '%s', '%d', '%s')
            );
        }
        
        // Schedule reminder with smart timing if enabled
        $reminder_days = isset($settings['reminder_days']) ? intval($settings['reminder_days']) : 7;
        
        if (isset($settings['enable_smart_timing']) && $settings['enable_smart_timing']) {
            $optimal_hour = $this->get_optimal_send_time();
            $timestamp = strtotime("+{$reminder_days} days {$optimal_hour}:00:00");
        } else {
            $timestamp = time() + ($reminder_days * DAY_IN_SECONDS);
        }
        
        wp_schedule_single_event($timestamp, 'arm_send_review_reminder', array($order_id));
    }
    
    public function send_review_reminder($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'arm_reminders';
        
        // Check if already sent
        $reminder = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE order_id = %d",
            $order_id
        ));
        
        if (!$reminder || $reminder->reminder_sent) {
            return;
        }
        
        $this->send_review_email($order);
        
        // Update reminder status
        $wpdb->update(
            $table_name,
            array(
                'reminder_sent' => 1,
                'sent_date' => current_time('mysql')
            ),
            array('order_id' => $order_id),
            array('%d', '%s'),
            array('%d')
        );
    }
    
    private function send_review_email($order) {
        $settings = get_option('arm_settings');
        $customer_email = $order->get_billing_email();
        $customer_name = $order->get_billing_first_name();
        
        // Generate review token and URL
        $review_url = add_query_arg(array(
            'order_id' => $order->get_id(),
            'email' => $customer_email,
            'token' => $this->generate_review_token($order->get_id(), $customer_email)
        ), home_url('/review-submission/'));
        
        // Generate QR code if enabled
        $qr_code_html = '';
        if (isset($settings['enable_qr_code']) && $settings['enable_qr_code']) {
            $qr_size = isset($settings['qr_code_size']) ? intval($settings['qr_code_size']) : 200;
            // Use Google Charts API for QR code generation
            $qr_url = 'https://chart.googleapis.com/chart?cht=qr&chs=' . $qr_size . 'x' . $qr_size . '&chl=' . urlencode($review_url);
            $qr_code_html = '
            <div style="text-align: center; margin: 30px 0;">
                <p style="font-size: 14px; color: #666; margin-bottom: 10px;">Or scan this QR code:</p>
                <a href="' . esc_url($review_url) . '" style="display: inline-block;">
                    <img src="' . esc_url($qr_url) . '" alt="QR Code" style="max-width: ' . $qr_size . 'px; border: 2px solid #e0e0e0; border-radius: 8px; padding: 10px; background: white;" />
                </a>
                <p style="font-size: 12px; color: #999; margin-top: 10px;">Scan with your phone camera</p>
            </div>';
        }
        
        // Get order products
        $items = $order->get_items();
        $product_names_html = '';
        foreach ($items as $item) {
            $product = $item->get_product();
            if ($product) {
                $product_names_html .= '<li>' . esc_html($product->get_name()) . '</li>';
            }
        }
        
        // Build incentive section if enabled
        $incentive_section = '';
        if (!empty($settings['enable_incentives']) && !empty($settings['show_incentive_a'])) {
            $incentive_message = $settings['incentive_message_a'] ?? 'Leave a review and get 10% off your next purchase!';
            $incentive_section = '
            <div class="incentive-banner">
                <span class="incentive-icon">🎁</span>
                <h3>Special Reward</h3>
                <p>' . esc_html($incentive_message) . '</p>
            </div>';
        }
        
        // Get site info
        $store_name = get_bloginfo('name');
        $site_name = $store_name; // for backward compatibility
        $store_url = home_url();
        $order_number = $order->get_order_number();
        
        // Load email template
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/customer-email.php';
        $email_html = ob_get_clean();
        
        // Replace placeholders
            $replacements = array(
                '{customer_name}'   => $customer_name,
                '{order_number}'   => $order_number,
                '{product_names}'  => $product_names_html,
                '{review_link}'    => $review_url,
                '{site_name}'      => $site_name,
                '{store_name}'     => $store_name,
                '{store_url}'      => $store_url,
                '{incentive_section}' => $incentive_section,
                '{qr_code}'        => $qr_code_html,
            );

            // Replace all placeholders
            foreach ($replacements as $placeholder => $value) {
                $email_html = str_replace($placeholder, $value, $email_html);
            }
        
        // Subject line
        $subject = str_replace('{customer_name}', $customer_name, $settings['email_subject']);
        $subject = str_replace('{store_name}', $store_name, $subject);
        
        // Send email
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($customer_email, $subject, $email_html, $headers);
    }
    
    private function generate_review_token($order_id, $email) {
        return hash('sha256', $order_id . $email . AUTH_KEY);
    }
    
    public function add_review_reminder_column($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            if ($key === 'order_status') {
                $new_columns['review_reminder'] = 'Review Reminder';
            }
        }
        
        return $new_columns;
    }
    
    public function render_review_reminder_column($column, $post_id) {
        if ($column !== 'review_reminder') {
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'arm_reminders';
        $order = wc_get_order($post_id);
        
        if (!$order) {
            return;
        }
        
        $reminder = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE order_id = %d",
            $post_id
        ));
        
        if (!$reminder) {
            // Check if order is completed - offer to schedule
            if ($order->get_status() === 'completed') {
                echo '<div style="display: flex; flex-direction: column; gap: 4px;">';
                echo '<span style="display: inline-flex; align-items: center; gap: 4px; color: #999; font-size: 11px;">';
                echo '<span style="font-size: 14px;">⏸️</span>';
                echo '<span>Not Set</span>';
                echo '</span>';
                echo '<a href="#" class="arm-schedule-reminder" data-order-id="' . esc_attr($post_id) . '" style="display: inline-flex; align-items: center; gap: 3px; padding: 3px 6px; background: #0073aa; color: #ffffff !important; border-radius: 3px; font-weight: 600; text-decoration: none; font-size: 10px; margin-top: 2px;">';
                echo '<span>�</span>';
                echo '<span>Send Now</span>';
                echo '</a>';
                echo '</div>';
            } else {
                echo '<span style="color: #ccc; font-size: 11px;">—</span>';
            }
            return;
        }
        
        if ($reminder->reminder_sent) {
            // Email has been sent
            $sent_date = date('M j, Y', strtotime($reminder->sent_date));
            echo '<div style="display: flex; flex-direction: column; gap: 3px;">';
            echo '<span style="display: inline-flex; align-items: center; gap: 4px; color: #46b450; font-weight: 600; font-size: 11px;">';
            echo '<span style="font-size: 14px;">✅</span>';
            echo '<span>Sent</span>';
            echo '</span>';
            echo '<small style="color: #666; font-size: 10px;">' . esc_html($sent_date) . '</small>';
            echo '</div>';
        } else {
            // Reminder scheduled but not sent yet
            echo '<div style="display: flex; flex-direction: column; gap: 4px;">';
            echo '<span style="display: inline-flex; align-items: center; gap: 4px; color: #f0ad4e; font-weight: 600; font-size: 11px;">';
            echo '<span style="font-size: 14px;">⏰</span>';
            echo '<span>Pending</span>';
            echo '</span>';
            echo '<a href="#" class="arm-send-instant-reminder" data-order-id="' . esc_attr($post_id) . '" style="display: inline-flex; align-items: center; gap: 3px; padding: 3px 6px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff !important; border-radius: 3px; font-weight: 600; text-decoration: none; font-size: 10px; margin-top: 2px;">';
            echo '<span>⚡</span>';
            echo '<span>Send</span>';
            echo '</a>';
            echo '</div>';
        }
    }
    
    public function ajax_send_instant_reminder() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $order_id = intval($_POST['order_id']);
        
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error('Order not found');
        }
        
        $this->send_review_email($order);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'arm_reminders';
        
        $wpdb->update(
            $table_name,
            array(
                'reminder_sent' => 1,
                'sent_date' => current_time('mysql')
            ),
            array('order_id' => $order_id),
            array('%d', '%s'),
            array('%d')
        );
        
        wp_send_json_success('Reminder sent successfully!');
    }
    
    public function ajax_schedule_reminder() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $order_id = intval($_POST['order_id']);
        
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error('Order not found');
        }
        
        // Schedule the reminder for this old order
        $this->schedule_review_reminder($order_id);
        
        wp_send_json_success('Reminder scheduled successfully!');
    }
    
    public function render_dashboard() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'arm_reminders';
        
        $total_reminders = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $sent_reminders = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE reminder_sent = 1");
        $pending_reminders = $total_reminders - $sent_reminders;
        
        $comments_table = $wpdb->prefix . 'comments';
        $total_reviews = $wpdb->get_var("SELECT COUNT(*) FROM $comments_table WHERE comment_type = 'review'");
        
        // Get settings for reminder days threshold
        $settings = get_option('arm_settings');
        $reminder_days = isset($settings['reminder_days']) ? intval($settings['reminder_days']) : 7;
        
        // Find old orders without reminders (completed more than reminder_days ago)
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$reminder_days} days"));
        
        $old_orders = $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, p.post_date
            FROM {$wpdb->posts} p
            LEFT JOIN {$table_name} r ON p.ID = r.order_id
            WHERE p.post_type = 'shop_order'
            AND p.post_status = 'wc-completed'
            AND p.post_date < %s
            AND r.order_id IS NULL
            ORDER BY p.post_date DESC
            LIMIT 20
        ", $cutoff_date));
        
        include ARM_PLUGIN_DIR . 'templates/dashboard.php';
    }
    
    public function render_settings() {
        $settings = get_option('arm_settings');
        include ARM_PLUGIN_DIR . 'templates/settings.php';
    }
    
    public function render_email_template() {
        $settings = get_option('arm_settings');
        include ARM_PLUGIN_DIR . 'templates/email-template.php';
    }
    
    public function render_analytics() {
        include ARM_PLUGIN_DIR . 'templates/analytics.php';
    }
    
    public function render_fake_reviews() {
        include ARM_PLUGIN_DIR . 'templates/fake-reviews.php';
    }
    
    public function render_bulk_actions() {
        include ARM_PLUGIN_DIR . 'templates/bulk-actions.php';
    }
    
    public function render_product_blacklist() {
        include ARM_PLUGIN_DIR . 'templates/product-blacklist.php';
    }
    
    public function render_google_reviews() {
        include ARM_PLUGIN_DIR . 'templates/google-reviews.php';
    }
    
    public function ajax_save_settings() {
        // Enable error logging for debugging
        error_log('ARM: ajax_save_settings called');
        error_log('ARM POST data: ' . print_r($_POST, true));
        
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            error_log('ARM: Unauthorized user');
            wp_send_json_error('Unauthorized');
        }
        
        // Get existing settings to preserve any values
        $existing_settings = get_option('arm_settings', array());
        error_log('ARM: Existing settings count: ' . count($existing_settings));
        
        // Merge with new settings
        $settings = array_merge($existing_settings, array(
            // Basic settings
            'enable_reminders' => isset($_POST['enable_reminders']) ? true : false,
            'reminder_days' => isset($_POST['reminder_days']) ? intval($_POST['reminder_days']) : 7,
            'email_subject' => isset($_POST['email_subject']) ? sanitize_text_field($_POST['email_subject']) : '',
            'email_heading' => isset($_POST['email_heading']) ? sanitize_text_field($_POST['email_heading']) : '',
            'email_message' => isset($_POST['email_message']) ? sanitize_textarea_field($_POST['email_message']) : '',
            'button_text' => isset($_POST['button_text']) ? sanitize_text_field($_POST['button_text']) : '',
            'button_color' => isset($_POST['button_color']) ? sanitize_hex_color($_POST['button_color']) : '#ff6b6b',
            
            // SMS Integration
            'enable_sms' => isset($_POST['enable_sms']) ? true : false,
            'twilio_account_sid' => isset($_POST['twilio_account_sid']) ? sanitize_text_field($_POST['twilio_account_sid']) : '',
            'twilio_auth_token' => isset($_POST['twilio_auth_token']) ? sanitize_text_field($_POST['twilio_auth_token']) : '',
            'twilio_phone_number' => isset($_POST['twilio_phone_number']) ? sanitize_text_field($_POST['twilio_phone_number']) : '',
            'sms_message' => isset($_POST['sms_message']) ? sanitize_textarea_field($_POST['sms_message']) : '',
            
            // Multi-Product Reviews
            'enable_multi_product' => isset($_POST['enable_multi_product']) ? true : false,
            'max_products_per_email' => isset($_POST['max_products_per_email']) ? intval($_POST['max_products_per_email']) : 5,
            
            // Photo/Video Reviews
            'enable_photo_reviews' => isset($_POST['enable_photo_reviews']) ? true : false,
            'enable_video_reviews' => isset($_POST['enable_video_reviews']) ? true : false,
            'max_media_files' => isset($_POST['max_media_files']) ? intval($_POST['max_media_files']) : 5,
            'max_file_size' => isset($_POST['max_file_size']) ? intval($_POST['max_file_size']) : 10,
            
            // Incentives & Rewards
            'enable_incentives' => isset($_POST['enable_incentives']) ? true : false,
            'incentive_type' => isset($_POST['incentive_type']) ? sanitize_text_field($_POST['incentive_type']) : 'coupon',
            'coupon_amount' => isset($_POST['coupon_amount']) ? floatval($_POST['coupon_amount']) : 10,
            'coupon_type' => isset($_POST['coupon_type']) ? sanitize_text_field($_POST['coupon_type']) : 'percent',
            'coupon_expiry_days' => isset($_POST['coupon_expiry_days']) ? intval($_POST['coupon_expiry_days']) : 30,
            'points_amount' => isset($_POST['points_amount']) ? intval($_POST['points_amount']) : 100,
            'incentive_message_a' => isset($_POST['incentive_message_a']) ? sanitize_text_field($_POST['incentive_message_a']) : '',
            'show_incentive_a' => isset($_POST['show_incentive_a']) ? true : false,
            
            // A/B Testing
            'enable_ab_testing' => isset($_POST['enable_ab_testing']) ? true : false,
            
            // Follow-up Sequences
            'enable_followup' => isset($_POST['enable_followup']) ? true : false,
            'followup_count' => isset($_POST['followup_count']) ? intval($_POST['followup_count']) : 2,
            'followup_interval' => isset($_POST['followup_interval']) ? intval($_POST['followup_interval']) : 7,
            
            // Review Moderation
            'enable_moderation' => isset($_POST['enable_moderation']) ? true : false,
            'enable_profanity_filter' => isset($_POST['enable_profanity_filter']) ? true : false,
            'enable_spam_detection' => isset($_POST['enable_spam_detection']) ? true : false,
            'auto_approve_verified' => isset($_POST['auto_approve_verified']) ? true : false,
            
            // Review Gating
            'enable_review_gating' => isset($_POST['enable_review_gating']) ? true : false,
            'gating_threshold' => isset($_POST['gating_threshold']) ? intval($_POST['gating_threshold']) : 3,
            'support_email' => isset($_POST['support_email']) ? sanitize_email($_POST['support_email']) : get_option('admin_email'),
            
            // Google Shopping
            'enable_google_shopping' => isset($_POST['enable_google_shopping']) ? true : false,
            'google_merchant_id' => isset($_POST['google_merchant_id']) ? sanitize_text_field($_POST['google_merchant_id']) : '',
            
            // Social Proof Widgets
            'enable_floating_widget' => isset($_POST['enable_floating_widget']) ? true : false,
            'enable_review_badge' => isset($_POST['enable_review_badge']) ? true : false,
            'enable_review_carousel' => isset($_POST['enable_review_carousel']) ? true : false,
            'widget_position' => isset($_POST['widget_position']) ? sanitize_text_field($_POST['widget_position']) : 'bottom-left',
            'widget_display_duration' => isset($_POST['widget_display_duration']) ? intval($_POST['widget_display_duration']) : 5,
            
            // Fake Reviews (for testing)
            'fake_review_enabled' => isset($_POST['fake_review_enabled']) ? true : false,
        ));
        
        $result = update_option('arm_settings', $settings);
        error_log('ARM: Settings saved. Result: ' . ($result ? 'success' : 'failed'));
        error_log('ARM: New settings count: ' . count($settings));
        
        wp_send_json_success('Settings saved successfully!');
    }
    
    public function ajax_send_test_email() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $test_email = sanitize_email($_POST['test_email']);
        
        if (empty($test_email) || !is_email($test_email)) {
            wp_send_json_error('Please enter a valid email address');
        }
        
        // Get current settings
        $settings = get_option('arm_settings');
        
        // Build test product list HTML
        $product_names_html = '<li>Sample Product 1 - Blue Widget</li><li>Sample Product 2 - Premium Gadget</li>';
        
        // Build incentive section if enabled
        $incentive_section = '';
        if (!empty($settings['enable_incentives']) && !empty($settings['show_incentive_a'])) {
            $incentive_message = $settings['incentive_message_a'] ?? 'Leave a review and get 10% off your next purchase!';
            $incentive_section = '
            <div class="incentive-banner">
                <span class="incentive-icon">🎁</span>
                <h3>Special Reward</h3>
                <p>' . esc_html($incentive_message) . '</p>
            </div>';
        }
        
        // Get site info
        $site_name = get_bloginfo('name');
        $store_url = home_url();
        $order_number = '12345';
        $review_url = home_url('/review-submission/?test=1');
        
        // Load email template
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/customer-email.php';
        $email_html = ob_get_clean();
        
        // Generate test QR code if enabled
        $qr_code_html = '';
        if (isset($settings['enable_qr_code']) && $settings['enable_qr_code']) {
            $qr_size = isset($settings['qr_code_size']) ? intval($settings['qr_code_size']) : 200;
            $qr_url = 'https://chart.googleapis.com/chart?cht=qr&chs=' . $qr_size . 'x' . $qr_size . '&chl=' . urlencode($review_url);
            $qr_code_html = '
            <div style="text-align: center; margin: 30px 0;">
                <p style="font-size: 14px; color: #666; margin-bottom: 10px;">Or scan this QR code:</p>
                <a href="' . esc_url($review_url) . '" style="display: inline-block;">
                    <img src="' . esc_url($qr_url) . '" alt="QR Code" style="max-width: ' . $qr_size . 'px; border: 2px solid #e0e0e0; border-radius: 8px; padding: 10px; background: white;" />
                </a>
                <p style="font-size: 12px; color: #999; margin-top: 10px;">Scan with your phone camera</p>
            </div>';
        }
        
        // Replace placeholders
            $replacements = array(
                '{customer_name}'   => 'Test User',
                '{order_number}'   => $order_number,
                '{product_names}'  => $product_names_html,
                '{review_link}'    => $review_url,
                '{site_name}'      => $site_name,
                '{store_name}'     => $site_name,
                '{store_url}'      => $store_url,
                '{incentive_section}' => $incentive_section,
                '{qr_code}'        => $qr_code_html,
            );

            foreach ($replacements as $placeholder => $value) {
                $email_html = str_replace($placeholder, $value, $email_html);
            }
        
        // Subject line
        $subject = str_replace('{customer_name}', 'Test User', $settings['email_subject']);
        $subject = str_replace('{store_name}', $site_name, $subject);
        $subject = '[TEST] ' . $subject;
        
        // Send email
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $result = wp_mail($test_email, $subject, $email_html, $headers);
        
        if ($result) {
            wp_send_json_success('Test email sent successfully to ' . $test_email);
        } else {
            wp_send_json_error('Failed to send test email. Check your mail settings.');
        }
    }
    
    public function ajax_generate_fake_review() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $product_id = intval($_POST['product_id']);
        $rating = intval($_POST['rating']);
        $author_name = sanitize_text_field($_POST['author_name']);
        $review_text = sanitize_textarea_field($_POST['review_text']);
        
        $fake_reviews = array(
            5 => array(
                "Absolutely love this product! Exceeded all my expectations.",
                "Outstanding quality and fast shipping. Highly recommend!",
                "Best purchase I've made in a long time. Five stars!",
                "Perfect! Exactly what I was looking for.",
                "Incredible value for money. Will buy again!"
            ),
            4 => array(
                "Really good product. Very satisfied with my purchase.",
                "Great quality, though shipping took a bit longer than expected.",
                "Solid product that does what it promises.",
                "Very happy with this. Minor issues but overall excellent.",
                "Good value and quality. Would recommend."
            ),
            3 => array(
                "It's okay. Does the job but nothing special.",
                "Average product. Met my basic expectations.",
                "Not bad, not great. Decent for the price."
            )
        );
        
        // Use provided review text or get a random one
        if (empty($review_text)) {
            $reviews_for_rating = isset($fake_reviews[$rating]) ? $fake_reviews[$rating] : $fake_reviews[5];
            $review_text = $reviews_for_rating[array_rand($reviews_for_rating)];
        }
        
        // Create the fake review
        $comment_data = array(
            'comment_post_ID' => $product_id,
            'comment_author' => $author_name,
            'comment_content' => $review_text,
            'comment_type' => 'review',
            'comment_parent' => 0,
            'user_id' => 0,
            'comment_approved' => 1,
        );
        
        $comment_id = wp_insert_comment($comment_data);
        
        if ($comment_id) {
            update_comment_meta($comment_id, 'rating', $rating);
            wp_send_json_success('Fake review created successfully!');
        } else {
            wp_send_json_error('Failed to create fake review.');
        }
    }
    
    public function review_form_shortcode($atts) {
        // Handle review submission form
        ob_start();
        ?>
        <div class="arm-review-form">
            <h2>Leave a Review</h2>
            <!-- Review form HTML here -->
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * NEW METHODS FOR ADVANCED FEATURES
     */
    
    // Save email template variants
    public function ajax_save_email_template() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        error_log('ARM: Saving email template');
        error_log('ARM: POST data: ' . print_r($_POST, true));
        
        $variant = sanitize_text_field($_POST['variant']);
        $settings = get_option('arm_settings');
        
        if (!$settings) {
            $settings = array();
        }
        
        error_log('ARM: Variant: ' . $variant);
        
        // Template A uses base field names without suffix, variants B and C use suffixed names
        if ($variant === 'a') {
            // For variant A, save to both base names and suffixed names
            $settings['email_subject'] = sanitize_text_field($_POST['email_subject']);
            $settings['email_subject_a'] = $settings['email_subject'];
            
            $settings['email_heading'] = sanitize_text_field($_POST['email_heading']);
            $settings['email_heading_a'] = $settings['email_heading'];
            
            $settings['email_message'] = wp_kses_post(stripslashes($_POST['email_message']));
            $settings['email_message_a'] = $settings['email_message'];
            
            $settings['button_text'] = sanitize_text_field($_POST['button_text']);
            $settings['button_text_a'] = $settings['button_text'];
            
            $settings['button_color'] = sanitize_hex_color($_POST['button_color']);
            $settings['button_color_a'] = $settings['button_color'];
            
            $settings['show_incentive_a'] = isset($_POST['show_incentive_a']);
            $settings['incentive_message_a'] = sanitize_textarea_field(stripslashes($_POST['incentive_message']));
            
            error_log('ARM: Saved Template A fields');
        } else {
            // For variants B and C, use suffixed field names
            $settings['email_subject_' . $variant] = sanitize_text_field($_POST['email_subject_' . $variant]);
            $settings['email_heading_' . $variant] = sanitize_text_field($_POST['email_heading_' . $variant]);
            $settings['email_message_' . $variant] = wp_kses_post(stripslashes($_POST['email_message_' . $variant]));
            $settings['button_text_' . $variant] = sanitize_text_field($_POST['button_text_' . $variant]);
            $settings['button_color_' . $variant] = sanitize_hex_color($_POST['button_color_' . $variant]);
            $settings['show_incentive_' . $variant] = isset($_POST['show_incentive_' . $variant]);
            $settings['incentive_message_' . $variant] = sanitize_textarea_field(stripslashes($_POST['incentive_message_' . $variant]));
            
            error_log('ARM: Saved Template ' . strtoupper($variant) . ' fields');
        }
        
        $result = update_option('arm_settings', $settings);
        error_log('ARM: Update result: ' . ($result ? 'success' : 'failed'));
        
        wp_send_json_success('Email template saved successfully!');
    }
    
    // Reset email template to default
    public function ajax_reset_email_template() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $variant = sanitize_text_field($_POST['variant']);
        $settings = get_option('arm_settings');
        
        if (!$settings) {
            $settings = array();
        }
        
        // Default templates based on variant
        $defaults = array(
            'a' => array(
                'email_subject' => 'How was your recent order with {store_name}?',
                'email_heading' => 'How was your experience?',
                'email_message' => 'Hi {customer_name},

Thank you for choosing {store_name} for your recent purchase! We hope you\'re enjoying your order.

Your feedback means everything to us and helps other customers make confident decisions. We\'d be incredibly grateful if you could take just a moment to share your experience with:

<strong>{product_names}</strong>

<strong>What to expect:</strong>
• Quick and easy - takes less than 2 minutes
• Help fellow shoppers make better decisions  
• Your honest feedback helps us improve our products and service

We truly value your opinion and thank you for being a valued customer!

Best regards,
The {store_name} Team',
                'button_text' => 'Leave a Review',
                'button_color' => '#7c3aed',
                'show_incentive' => false,
                'incentive_message' => 'Leave a review and get 10% off your next purchase!'
            ),
            'b' => array(
                'email_subject' => 'We\'d love to hear about your {store_name} experience! 💬',
                'email_heading' => 'Share Your Thoughts! 💭',
                'email_message' => 'Hey {customer_name}! 👋

We hope you\'re loving your recent order from {store_name}!

Got a minute? We\'d absolutely love to hear your thoughts on:

<strong>{product_names}</strong>

<strong>Why share a review?</strong>
✨ Helps fellow shoppers make great choices
✨ Shows us what we\'re doing right (and where we can improve!)
✨ Takes less than 2 minutes of your time

Your feedback is like gold to us and our community. Thanks for being awesome!

Cheers,
{store_name} Team 🌟',
                'button_text' => 'Share My Thoughts',
                'button_color' => '#7c3aed',
                'show_incentive' => false,
                'incentive_message' => '🎁 Review now and get 15% off your next order!'
            ),
            'c' => array(
                'email_subject' => '⏰ Quick favor? We need your input!',
                'email_heading' => 'Your Opinion Matters! ⭐',
                'email_message' => 'Hi {customer_name},

<strong>We need your help!</strong>

Your recent purchase of <strong>{product_names}</strong> from {store_name} could help countless other shoppers make the right decision.

<strong>Here\'s why your review matters:</strong>

📊 Reviews influence 93% of shopping decisions
⭐ Products with reviews sell 3x more than those without
🎯 Your opinion directly shapes what we offer

<strong>It takes just 60 seconds</strong> to share your experience and make a real impact.

Can you help us out?

Thank you for your time!
{store_name} Team',
                'button_text' => 'Write My Review Now',
                'button_color' => '#dc2626',
                'show_incentive' => false,
                'incentive_message' => '⚡ Limited time: Review in the next 48 hours for a special bonus!'
            )
        );
        
        if (!isset($defaults[$variant])) {
            wp_send_json_error('Invalid variant');
        }
        
        $default = $defaults[$variant];
        
        // Apply defaults based on variant
        if ($variant === 'a') {
            // Template A uses base names
            $settings['email_subject'] = $default['email_subject'];
            $settings['email_subject_a'] = $default['email_subject'];
            $settings['email_heading'] = $default['email_heading'];
            $settings['email_heading_a'] = $default['email_heading'];
            $settings['email_message'] = $default['email_message'];
            $settings['email_message_a'] = $default['email_message'];
            $settings['button_text'] = $default['button_text'];
            $settings['button_text_a'] = $default['button_text'];
            $settings['button_color'] = $default['button_color'];
            $settings['button_color_a'] = $default['button_color'];
            $settings['show_incentive_a'] = $default['show_incentive'];
            $settings['incentive_message_a'] = $default['incentive_message'];
        } else {
            // Templates B and C use suffixed names
            $settings['email_subject_' . $variant] = $default['email_subject'];
            $settings['email_heading_' . $variant] = $default['email_heading'];
            $settings['email_message_' . $variant] = $default['email_message'];
            $settings['button_text_' . $variant] = $default['button_text'];
            $settings['button_color_' . $variant] = $default['button_color'];
            $settings['show_incentive_' . $variant] = $default['show_incentive'];
            $settings['incentive_message_' . $variant] = $default['incentive_message'];
        }
        
        update_option('arm_settings', $settings);
        
        // Return the default values so the form can be updated
        wp_send_json_success(array(
            'message' => 'Template reset to default successfully!',
            'defaults' => $default
        ));
    }
    
    // Save multi-product template
    public function ajax_save_multi_product_template() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $settings = get_option('arm_settings');
        $settings['multi_email_subject'] = sanitize_text_field($_POST['multi_email_subject']);
        $settings['multi_email_intro'] = wp_kses_post($_POST['multi_email_intro']);
        $settings['multi_product_prompt'] = sanitize_text_field($_POST['multi_product_prompt']);
        
        update_option('arm_settings', $settings);
        wp_send_json_success('Multi-product template saved!');
    }
    
    // Save follow-up templates
    public function ajax_save_followup_templates() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $settings = get_option('arm_settings');
        $settings['followup1_subject'] = sanitize_text_field($_POST['followup1_subject']);
        $settings['followup1_message'] = wp_kses_post($_POST['followup1_message']);
        $settings['followup2_subject'] = sanitize_text_field($_POST['followup2_subject']);
        $settings['followup2_message'] = wp_kses_post($_POST['followup2_message']);
        
        update_option('arm_settings', $settings);
        wp_send_json_success('Follow-up templates saved!');
    }
    
    // Generate bulk fake reviews
    public function ajax_generate_bulk_reviews() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $products = array_map('intval', $_POST['bulk_product_id']);
        $count = intval($_POST['bulk_count']);
        $distribution = sanitize_text_field($_POST['rating_distribution']);
        $date_spread = intval($_POST['date_spread']);
        $verified = isset($_POST['bulk_verified']);
        $add_photos = isset($_POST['bulk_add_photos']);
        
        $total_generated = 0;
        
        foreach ($products as $product_id) {
            for ($i = 0; $i < $count; $i++) {
                $rating = $this->get_random_rating($distribution);
                $this->generate_single_fake_review($product_id, $rating, $verified, $add_photos, $date_spread);
                $total_generated++;
            }
        }
        
        wp_send_json_success(array('generated' => $total_generated));
    }
    
    // Helper for rating distribution
    private function get_random_rating($distribution) {
        switch ($distribution) {
            case 'excellent':
                return 5;
            case 'positive':
                return (rand(1, 10) > 3) ? 5 : 4;
            case 'realistic':
                $rand = rand(1, 100);
                if ($rand <= 50) return 5;
                if ($rand <= 75) return 4;
                if ($rand <= 90) return 3;
                if ($rand <= 97) return 2;
                return 1;
            case 'mixed':
                return rand(1, 5);
            default:
                return 5;
        }
    }
    
    // Generate single fake review
    private function generate_single_fake_review($product_id, $rating, $verified, $add_photos, $date_spread) {
        $templates = get_option('arm_review_templates_' . $rating, $this->get_default_templates($rating));
        $templates_array = explode("\n", trim($templates));
        $review_text = $templates_array[array_rand($templates_array)];
        
        $names = array("Sarah M.", "John D.", "Emily R.", "Michael B.", "Jessica L.",
            "David W.", "Amanda K.", "Robert T.", "Lisa P.", "James H.",
            "Maria G.", "Thomas S.", "Jennifer C.", "Christopher N.", "Ashley F.");
        
        $author_name = $names[array_rand($names)];
        
        $date_offset = rand(0, $date_spread * DAY_IN_SECONDS);
        $review_date = date('Y-m-d H:i:s', current_time('timestamp') - $date_offset);
        
        $comment_data = array(
            'comment_post_ID' => $product_id,
            'comment_author' => $author_name,
            'comment_author_email' => 'noreply@' . $_SERVER['HTTP_HOST'],
            'comment_content' => $review_text,
            'comment_type' => 'review',
            'comment_parent' => 0,
            'user_id' => 0,
            'comment_approved' => 1,
            'comment_date' => $review_date
        );
        
        $comment_id = wp_insert_comment($comment_data);
        
        if ($comment_id) {
            update_comment_meta($comment_id, 'rating', $rating);
            update_comment_meta($comment_id, 'verified', $verified ? 1 : 0);
            
            // Add photos if enabled
            if ($add_photos && rand(1, 100) <= 30) {
                $this->attach_random_product_images($comment_id, $product_id);
            }
        }
        
        return $comment_id;
    }
    
    // Get default review templates
    private function get_default_templates($rating) {
        $templates = array(
            5 => "Absolutely love this product! Exceeded all my expectations.\nOutstanding quality and fast shipping. Highly recommend!\nBest purchase I've made in a long time. Five stars!\nPerfect! Exactly what I was looking for.\nIncredible value for money. Will buy again!",
            4 => "Really good product. Very satisfied with my purchase.\nGreat quality, though shipping took a bit longer than expected.\nSolid product that does what it promises.\nVery happy with this. Minor issues but overall excellent.\nGood value and quality. Would recommend.",
            3 => "It's okay. Does the job but nothing special.\nAverage product. Met my basic expectations.\nNot bad, not great. Decent for the price.\nIt works, but could be better.\nAcceptable quality. Some room for improvement.",
            2 => "Disappointed with this purchase. Quality not as expected.\nNot what I was hoping for. Several issues.\nBelow average. Had problems from the start.\nNot satisfied. Quality is poor for the price.\nWould not recommend. Multiple defects.",
            1 => "Terrible product. Complete waste of money.\nVery disappointed. Does not work at all.\nPoor quality and doesn't match description.\nAwful experience. Would not buy again.\nCompletely unsatisfied. Requesting refund."
        );
        
        return isset($templates[$rating]) ? $templates[$rating] : $templates[3];
    }
    
    // Save review templates
    public function ajax_save_review_templates() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $rating = intval($_POST['rating']);
        $templates = sanitize_textarea_field($_POST['templates']);
        
        update_option('arm_review_templates_' . $rating, $templates);
        wp_send_json_success('Templates saved successfully!');
    }
    
    // Get advanced analytics
    public function ajax_get_advanced_analytics() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        global $wpdb;
        
        $data = array(
            'sms_sent' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}arm_sms_log"),
            'photo_reviews' => $wpdb->get_var("SELECT COUNT(DISTINCT comment_id) FROM {$wpdb->prefix}arm_review_media WHERE media_type = 'image'"),
            'video_reviews' => $wpdb->get_var("SELECT COUNT(DISTINCT comment_id) FROM {$wpdb->prefix}arm_review_media WHERE media_type = 'video'"),
            'incentives_claimed' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}arm_incentives WHERE claimed = 1"),
            'sentiment' => $this->analyze_sentiment()
        );
        
        wp_send_json_success($data);
    }
    
    // Sentiment analysis
    private function analyze_sentiment() {
        global $wpdb;
        
        $positive_words = array('love', 'great', 'excellent', 'amazing', 'perfect', 'best', 'awesome', 'fantastic', 'wonderful', 'outstanding');
        $negative_words = array('bad', 'terrible', 'awful', 'poor', 'worst', 'horrible', 'disappointing', 'useless', 'waste', 'never');
        
        // Simplified sentiment analysis
        $reviews = $wpdb->get_results("
            SELECT comment_content, meta_value as rating 
            FROM {$wpdb->prefix}comments c
            LEFT JOIN {$wpdb->prefix}commentmeta cm ON c.comment_ID = cm.comment_id AND cm.meta_key = 'rating'
            WHERE c.comment_type = 'review' 
            AND c.comment_approved = '1'
        ");
        
        $sentiment = array('positive' => 0, 'neutral' => 0, 'negative' => 0);
        
        foreach ($reviews as $review) {
            $rating = intval($review->rating);
            if ($rating >= 4) {
                $sentiment['positive']++;
            } elseif ($rating <= 2) {
                $sentiment['negative']++;
            } else {
                $sentiment['neutral']++;
            }
        }
        
        return $sentiment;
    }
    
    // Get A/B test results
    public function ajax_get_ab_test_results() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        global $wpdb;
        $ab_table = $wpdb->prefix . 'arm_ab_tests';
        
        $results = array();
        
        foreach (array('a', 'b', 'c') as $variant) {
            $stats = $wpdb->get_row($wpdb->prepare("
                SELECT 
                    COUNT(*) as sent,
                    SUM(opened) as opened,
                    SUM(clicked) as clicked,
                    SUM(converted) as converted
                FROM $ab_table
                WHERE variant = %s
            ", $variant));
            
            $conversion = $stats->sent > 0 ? round(($stats->converted / $stats->sent) * 100, 2) : 0;
            
            $results[$variant] = array(
                'sent' => $stats->sent,
                'opened' => $stats->opened,
                'clicked' => $stats->clicked,
                'reviewed' => $stats->converted,
                'conversion' => $conversion,
                'is_winner' => false
            );
        }
        
        // Determine winner
        $max_conversion = max(array_column($results, 'conversion'));
        foreach ($results as $variant => &$data) {
            if ($data['conversion'] == $max_conversion && $max_conversion > 0) {
                $data['is_winner'] = true;
                break;
            }
        }
        
        wp_send_json_success($results);
    }
    
    // Get generated review stats
    public function ajax_get_generated_stats() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        global $wpdb;
        
        $total = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->prefix}comments 
            WHERE comment_type = 'review' 
            AND comment_author_email LIKE '%noreply@%'
        ");
        
        $last = $wpdb->get_var("
            SELECT MAX(comment_date) FROM {$wpdb->prefix}comments 
            WHERE comment_type = 'review' 
            AND comment_author_email LIKE '%noreply@%'
        ");
        
        wp_send_json_success(array(
            'total' => $total,
            'last' => $last ? date('M j, Y', strtotime($last)) : 'Never'
        ));
    }
    
    // Export analytics
    public function ajax_export_analytics() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        global $wpdb;
        
        // Generate CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="review-analytics-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Date', 'Product', 'Customer', 'Rating', 'Review', 'Verified'));
        
        $reviews = $wpdb->get_results("
            SELECT c.comment_date, c.comment_post_ID, c.comment_author, c.comment_content, cm.meta_value as rating, cm2.meta_value as verified
            FROM {$wpdb->prefix}comments c
            LEFT JOIN {$wpdb->prefix}commentmeta cm ON c.comment_ID = cm.comment_id AND cm.meta_key = 'rating'
            LEFT JOIN {$wpdb->prefix}commentmeta cm2 ON c.comment_ID = cm2.comment_id AND cm2.meta_key = 'verified'
            WHERE c.comment_type = 'review'
            ORDER BY c.comment_date DESC
        ");
        
        foreach ($reviews as $review) {
            $product = get_the_title($review->comment_post_ID);
            fputcsv($output, array(
                $review->comment_date,
                $product,
                $review->comment_author,
                $review->rating,
                $review->comment_content,
                $review->verified ? 'Yes' : 'No'
            ));
        }
        
        fclose($output);
        exit;
    }
    
    // Send SMS reminder
    private function send_sms_reminder($order) {
        $settings = get_option('arm_settings');
        
        if (!isset($settings['enable_sms']) || !$settings['enable_sms']) {
            return false;
        }
        
        if (empty($settings['twilio_account_sid']) || empty($settings['twilio_auth_token'])) {
            return false;
        }
        
        $phone = $order->get_billing_phone();
        if (empty($phone)) {
            return false;
        }
        
        $customer_name = $order->get_billing_first_name();
        $review_url = $this->get_review_url($order);
        
        $message = str_replace(
            array('{customer_name}', '{review_url}', '{store_name}'),
            array($customer_name, $review_url, get_bloginfo('name')),
            $settings['sms_message']
        );
        
        // Send via Twilio
        $response = $this->send_twilio_sms($phone, $message, $settings);
        
        // Log SMS
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'arm_sms_log',
            array(
                'order_id' => $order->get_id(),
                'phone_number' => $phone,
                'message' => $message,
                'status' => $response['success'] ? 'sent' : 'failed',
                'twilio_sid' => $response['sid'],
                'error_message' => $response['error'],
                'sent_date' => current_time('mysql')
            )
        );
        
        return $response['success'];
    }
    
    // Send via Twilio
    private function send_twilio_sms($to, $message, $settings) {
        $account_sid = $settings['twilio_account_sid'];
        $auth_token = $settings['twilio_auth_token'];
        $from = $settings['twilio_phone_number'];
        
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$account_sid}/Messages.json";
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode("{$account_sid}:{$auth_token}")
            ),
            'body' => array(
                'From' => $from,
                'To' => $to,
                'Body' => $message
            )
        ));
        
        if (is_wp_error($response)) {
            return array('success' => false, 'error' => $response->get_error_message(), 'sid' => null);
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        return array(
            'success' => isset($body['sid']),
            'sid' => isset($body['sid']) ? $body['sid'] : null,
            'error' => isset($body['message']) ? $body['message'] : null
        );
    }
    
    // Frontend asset enqueue
    public function enqueue_frontend_assets() {
        wp_enqueue_style('arm-frontend-css', ARM_PLUGIN_URL . 'assets/frontend-style.css', array(), ARM_VERSION);
        wp_enqueue_script('arm-frontend-js', ARM_PLUGIN_URL . 'assets/frontend-script.js', array('jquery'), ARM_VERSION, true);
        
        wp_localize_script('arm-frontend-js', 'armFrontend', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('arm_frontend_nonce')
        ));
    }
    
    // Render social proof widgets
    public function render_social_proof_widgets() {
        $settings = get_option('arm_settings');
        
        if (!isset($settings['enable_floating_widget']) || !$settings['enable_floating_widget']) {
            return;
        }
        
        global $wpdb;
        
        // Get recent reviews
        $recent_reviews = $wpdb->get_results("
            SELECT c.comment_author, c.comment_post_ID, cm.meta_value as rating, c.comment_date
            FROM {$wpdb->prefix}comments c
            LEFT JOIN {$wpdb->prefix}commentmeta cm ON c.comment_ID = cm.comment_id AND cm.meta_key = 'rating'
            WHERE c.comment_type = 'review' 
            AND c.comment_approved = '1'
            AND cm.meta_value >= 4
            ORDER BY c.comment_date DESC
            LIMIT 10
        ");
        
        if (empty($recent_reviews)) {
            return;
        }
        
        $position = isset($settings['widget_position']) ? $settings['widget_position'] : 'bottom-left';
        
        ?>
        <div class="arm-floating-widget arm-floating-<?php echo esc_attr($position); ?>" id="arm-floating-widget">
            <div class="arm-widget-content">
                <span class="arm-widget-close">&times;</span>
                <div class="arm-widget-icon">⭐</div>
                <div class="arm-widget-text">
                    <strong class="arm-widget-customer"></strong>
                    <span class="arm-widget-action">just reviewed</span>
                    <strong class="arm-widget-product"></strong>
                </div>
                <div class="arm-widget-stars"></div>
                <div class="arm-widget-time"></div>
            </div>
        </div>
        
        <script>
        var armRecentReviews = <?php echo json_encode($recent_reviews); ?>;
        </script>
        <?php
    }
    
    // Display review badge on product
    public function display_review_badge() {
        $settings = get_option('arm_settings');
        
        if (!isset($settings['enable_review_badge']) || !$settings['enable_review_badge']) {
            return;
        }
        
        global $product;
        $rating = $product->get_average_rating();
        $count = $product->get_review_count();
        
        if ($count > 0) {
            echo '<div class="arm-review-badge">';
            echo '<span class="arm-badge-stars">' . str_repeat('⭐', round($rating)) . '</span>';
            echo '<span class="arm-badge-count">(' . $count . ')</span>';
            echo '</div>';
        }
    }
    
    // Display review carousel
    public function display_review_carousel() {
        $settings = get_option('arm_settings');
        
        if (!isset($settings['enable_review_carousel']) || !$settings['enable_review_carousel']) {
            return;
        }
        
        global $product, $wpdb;
        
        $reviews = $wpdb->get_results($wpdb->prepare("
            SELECT c.*, cm.meta_value as rating
            FROM {$wpdb->prefix}comments c
            LEFT JOIN {$wpdb->prefix}commentmeta cm ON c.comment_ID = cm.comment_id AND cm.meta_key = 'rating'
            WHERE c.comment_post_ID = %d 
            AND c.comment_type = 'review'
            AND c.comment_approved = '1'
            AND cm.meta_value >= 4
            ORDER BY c.comment_date DESC
            LIMIT 5
        ", $product->get_id()));
        
        if (empty($reviews)) {
            return;
        }
        
        ?>
        <div class="arm-review-carousel">
            <h3>Customer Reviews</h3>
            <div class="arm-carousel-container">
                <?php foreach ($reviews as $review): ?>
                    <div class="arm-carousel-item">
                        <div class="arm-carousel-rating"><?php echo str_repeat('⭐', intval($review->rating)); ?></div>
                        <p class="arm-carousel-text"><?php echo esc_html(wp_trim_words($review->comment_content, 20)); ?></p>
                        <p class="arm-carousel-author">— <?php echo esc_html($review->comment_author); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
    
    // Add media upload to review form
    public function add_media_upload_to_review_form($args) {
        $settings = get_option('arm_settings');
        
        if (isset($settings['enable_photo_reviews']) && $settings['enable_photo_reviews']) {
            $args['comment_field'] .= '<p class="comment-form-photos"><label for="review_photos">Upload Photos (Optional)</label><input type="file" name="review_photos[]" id="review_photos" multiple accept="image/*"></p>';
        }
        
        if (isset($settings['enable_video_reviews']) && $settings['enable_video_reviews']) {
            $args['comment_field'] .= '<p class="comment-form-videos"><label for="review_videos">Upload Video (Optional)</label><input type="file" name="review_videos" id="review_videos" accept="video/*"></p>';
        }
        
        return $args;
    }
    
    // Handle media upload when review is posted
    public function handle_review_media_upload($comment_id, $approved) {
        if ($approved !== 1) {
            return;
        }
        
        $settings = get_option('arm_settings');
        
        // Handle photo uploads
        if (isset($_FILES['review_photos']) && !empty($_FILES['review_photos']['name'][0])) {
            $max_files = isset($settings['max_media_files']) ? intval($settings['max_media_files']) : 5;
            
            foreach ($_FILES['review_photos']['name'] as $key => $value) {
                if ($key >= $max_files) break;
                
                if ($_FILES['review_photos']['error'][$key] === 0) {
                    $file = array(
                        'name' => $_FILES['review_photos']['name'][$key],
                        'type' => $_FILES['review_photos']['type'][$key],
                        'tmp_name' => $_FILES['review_photos']['tmp_name'][$key],
                        'error' => $_FILES['review_photos']['error'][$key],
                        'size' => $_FILES['review_photos']['size'][$key]
                    );
                    
                    $upload = wp_handle_upload($file, array('test_form' => false));
                    
                    if (!isset($upload['error'])) {
                        $attachment_id = wp_insert_attachment(array(
                            'post_title' => sanitize_file_name($file['name']),
                            'post_content' => '',
                            'post_status' => 'inherit',
                            'post_mime_type' => $upload['type']
                        ), $upload['file']);
                        
                        global $wpdb;
                        $wpdb->insert(
                            $wpdb->prefix . 'arm_review_media',
                            array(
                                'comment_id' => $comment_id,
                                'attachment_id' => $attachment_id,
                                'media_type' => 'image',
                                'file_url' => $upload['url']
                            )
                        );
                    }
                }
            }
        }
        
        // Handle video upload
        if (isset($_FILES['review_videos']) && $_FILES['review_videos']['error'] === 0) {
            $upload = wp_handle_upload($_FILES['review_videos'], array('test_form' => false));
            
            if (!isset($upload['error'])) {
                $attachment_id = wp_insert_attachment(array(
                    'post_title' => sanitize_file_name($_FILES['review_videos']['name']),
                    'post_content' => '',
                    'post_status' => 'inherit',
                    'post_mime_type' => $upload['type']
                ), $upload['file']);
                
                global $wpdb;
                $wpdb->insert(
                    $wpdb->prefix . 'arm_review_media',
                    array(
                        'comment_id' => $comment_id,
                        'attachment_id' => $attachment_id,
                        'media_type' => 'video',
                        'file_url' => $upload['url']
                    )
                );
            }
        }
    }
    
    // Handle review incentive
    public function handle_review_incentive($comment_id, $approved) {
        if ($approved !== 1) {
            return;
        }
        
        $settings = get_option('arm_settings');
        
        if (!isset($settings['enable_incentives']) || !$settings['enable_incentives']) {
            return;
        }
        
        $comment = get_comment($comment_id);
        $order_id = get_post_meta($comment->comment_post_ID, '_order_id', true);
        
        if (!$order_id) {
            return;
        }
        
        $incentive_type = isset($settings['incentive_type']) ? $settings['incentive_type'] : 'coupon';
        
        if ($incentive_type === 'coupon') {
            $coupon_code = 'REVIEW' . strtoupper(wp_generate_password(8, false));
            $amount = isset($settings['coupon_amount']) ? floatval($settings['coupon_amount']) : 10;
            $type = isset($settings['coupon_type']) ? $settings['coupon_type'] : 'percent';
            $expiry_days = isset($settings['coupon_expiry_days']) ? intval($settings['coupon_expiry_days']) : 30;
            
            $coupon = array(
                'post_title' => $coupon_code,
                'post_content' => 'Thank you for your review!',
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'shop_coupon'
            );
            
            $new_coupon_id = wp_insert_post($coupon);
            
            update_post_meta($new_coupon_id, 'discount_type', $type === 'percent' ? 'percent' : 'fixed_cart');
            update_post_meta($new_coupon_id, 'coupon_amount', $amount);
            update_post_meta($new_coupon_id, 'individual_use', 'yes');
            update_post_meta($new_coupon_id, 'usage_limit', '1');
            update_post_meta($new_coupon_id, 'expiry_date', date('Y-m-d', strtotime("+{$expiry_days} days")));
            update_post_meta($new_coupon_id, 'customer_email', array($comment->comment_author_email));
            
            global $wpdb;
            $wpdb->insert(
                $wpdb->prefix . 'arm_incentives',
                array(
                    'comment_id' => $comment_id,
                    'order_id' => $order_id,
                    'customer_email' => $comment->comment_author_email,
                    'incentive_type' => 'coupon',
                    'coupon_code' => $coupon_code,
                    'claimed' => 0
                )
            );
            
            // Send email with coupon
            $this->send_incentive_email($comment->comment_author_email, $coupon_code, $amount, $type);
        }
    }
    
    // Send incentive email
    private function send_incentive_email($email, $coupon_code, $amount, $type) {
        $subject = 'Thank you for your review! Here\'s your reward';
        $discount_text = $type === 'percent' ? $amount . '%' : '$' . $amount;
        
        $message = "Thank you for taking the time to leave a review!\n\n";
        $message .= "As a token of our appreciation, here's your exclusive discount code:\n\n";
        $message .= "Coupon Code: {$coupon_code}\n";
        $message .= "Discount: {$discount_text} off your next purchase\n\n";
        $message .= "Use it on your next order!\n\n";
        $message .= "Best regards,\n" . get_bloginfo('name');
        
        wp_mail($email, $subject, $message);
    }
    
    // Handle review moderation
    public function handle_review_moderation($comment_id, $approved) {
        $settings = get_option('arm_settings');
        
        if (!isset($settings['enable_moderation']) || !$settings['enable_moderation']) {
            return;
        }
        
        $comment = get_comment($comment_id);
        $flagged = false;
        $reason = '';
        
        // Profanity filter
        if (isset($settings['enable_profanity_filter']) && $settings['enable_profanity_filter']) {
            if ($this->contains_profanity($comment->comment_content)) {
                $flagged = true;
                $reason = 'profanity';
            }
        }
        
        // Spam detection
        if (isset($settings['enable_spam_detection']) && $settings['enable_spam_detection']) {
            if ($this->is_spam($comment->comment_content)) {
                $flagged = true;
                $reason = 'spam';
            }
        }
        
        if ($flagged) {
            wp_set_comment_status($comment_id, 'hold');
            
            global $wpdb;
            $wpdb->insert(
                $wpdb->prefix . 'arm_moderation',
                array(
                    'comment_id' => $comment_id,
                    'flagged_reason' => $reason,
                    'reviewed' => 0
                )
            );
        }
    }
    
    // Simple profanity check
    private function contains_profanity($text) {
        $bad_words = array('damn', 'hell', 'crap', 'shit', 'fuck'); // Add more as needed
        $text_lower = strtolower($text);
        
        foreach ($bad_words as $word) {
            if (strpos($text_lower, $word) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    // Simple spam detection
    private function is_spam($text) {
        // Check for excessive links
        if (substr_count($text, 'http') > 2) {
            return true;
        }
        
        // Check for excessive capitals
        $capitals = preg_match_all('/[A-Z]/', $text);
        if ($capitals > strlen($text) * 0.5) {
            return true;
        }
        
        return false;
    }
    
    // Handle review gating
    public function handle_review_gating($comment_id, $approved) {
        $settings = get_option('arm_settings');
        
        if (!isset($settings['enable_review_gating']) || !$settings['enable_review_gating']) {
            return;
        }
        
        $comment = get_comment($comment_id);
        $rating = get_comment_meta($comment_id, 'rating', true);
        $threshold = isset($settings['gating_threshold']) ? intval($settings['gating_threshold']) : 3;
        
        if ($rating <= $threshold) {
            // Route to support instead of publishing
            wp_set_comment_status($comment_id, 'hold');
            
            $support_email = isset($settings['support_email']) ? $settings['support_email'] : get_option('admin_email');
            
            $subject = 'Low-rated review needs attention';
            $message = "A customer left a {$rating}-star review:\n\n";
            $message .= "Customer: {$comment->comment_author}\n";
            $message .= "Email: {$comment->comment_author_email}\n";
            $message .= "Product: " . get_the_title($comment->comment_post_ID) . "\n\n";
            $message .= "Review:\n{$comment->comment_content}\n\n";
            $message .= "Please reach out to this customer to address their concerns.";
            
            wp_mail($support_email, $subject, $message);
        }
    }
    
    // Track when a review is submitted
    public function track_review_submission($comment_id, $approved) {
        global $wpdb;
        
        $comment = get_comment($comment_id);
        
        // Check if this is a product review
        if ($comment->comment_type !== 'review') {
            return;
        }
        
        // Get the product and try to find associated order
        $product_id = $comment->comment_post_ID;
        $customer_email = $comment->comment_author_email;
        
        // Find orders by this customer that contain this product
        $orders = wc_get_orders(array(
            'billing_email' => $customer_email,
            'limit' => -1,
            'status' => array('wc-completed'),
            'return' => 'ids'
        ));
        
        if (empty($orders)) {
            return;
        }
        
        // Check each order to see if it contains the product being reviewed
        $order_id = null;
        foreach ($orders as $oid) {
            $order = wc_get_order($oid);
            foreach ($order->get_items() as $item) {
                if ($item->get_product_id() == $product_id || $item->get_variation_id() == $product_id) {
                    $order_id = $oid;
                    break 2;
                }
            }
        }
        
        if (!$order_id) {
            return;
        }
        
        // Update the arm_reminders table to mark review as submitted
        $table_name = $wpdb->prefix . 'arm_reminders';
        $wpdb->update(
            $table_name,
            array(
                'review_submitted' => 1
            ),
            array(
                'order_id' => $order_id
            ),
            array('%d'),
            array('%d')
        );
    }
    
    // Email tracking
    public function handle_email_tracking() {
        if (isset($_GET['arm_track'])) {
            $token = sanitize_text_field($_GET['arm_track']);
            $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'open';
            
            global $wpdb;
            $tracking_table = $wpdb->prefix . 'arm_email_tracking';
            
            if ($action === 'open') {
                $wpdb->query($wpdb->prepare("
                    UPDATE $tracking_table 
                    SET opened = 1, opened_date = NOW(), opened_count = opened_count + 1
                    WHERE tracking_token = %s
                ", $token));
                
                // Serve 1x1 transparent pixel
                header('Content-Type: image/gif');
                echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
                exit;
            } elseif ($action === 'click') {
                $wpdb->query($wpdb->prepare("
                    UPDATE $tracking_table 
                    SET clicked = 1, clicked_date = NOW(), clicked_count = clicked_count + 1
                    WHERE tracking_token = %s
                ", $token));
                
                // Redirect to review page
                $url = isset($_GET['url']) ? esc_url_raw($_GET['url']) : home_url();
                wp_redirect($url);
                exit;
            }
        }
    }
    
    // Get review URL with tracking
    private function get_review_url($order) {
        $review_url = add_query_arg(array(
            'order_id' => $order->get_id(),
            'email' => $order->get_billing_email(),
            'token' => $this->generate_review_token($order->get_id(), $order->get_billing_email())
        ), home_url('/review-submission/'));
        
        return $review_url;
    }
    
    // Attach random product images
    private function attach_random_product_images($comment_id, $product_id) {
        $product = wc_get_product($product_id);
        $gallery_ids = $product->get_gallery_image_ids();
        
        if (!empty($gallery_ids)) {
            $random_image = $gallery_ids[array_rand($gallery_ids)];
            
            global $wpdb;
            $wpdb->insert(
                $wpdb->prefix . 'arm_review_media',
                array(
                    'comment_id' => $comment_id,
                    'attachment_id' => $random_image,
                    'media_type' => 'image',
                    'file_url' => wp_get_attachment_url($random_image)
                )
            );
        }
    }
    
    // ===== NEW FEATURES =====
    
    // Get optimal send time based on open rates
    public function get_optimal_send_time() {
        global $wpdb;
        $tracking_table = $wpdb->prefix . 'arm_email_tracking';
        
        $settings = get_option('arm_settings');
        $default_hour = isset($settings['preferred_send_hour']) ? intval($settings['preferred_send_hour']) : 10;
        
        // Get hour with highest open rate
        $optimal = $wpdb->get_var("
            SELECT HOUR(opened_date) as hour
            FROM $tracking_table
            WHERE opened = 1
            GROUP BY HOUR(opened_date)
            ORDER BY COUNT(*) DESC
            LIMIT 1
        ");
        
        return $optimal ? $optimal : $default_hour;
    }
    
    public function ajax_get_optimal_send_time() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $optimal_time = $this->get_optimal_send_time();
        wp_send_json_success(array('optimal_hour' => $optimal_time));
    }
    
    // Bulk send reminders
    public function ajax_bulk_send_reminders() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $order_ids = isset($_POST['order_ids']) ? array_map('intval', $_POST['order_ids']) : array();
        
        if (empty($order_ids)) {
            wp_send_json_error('No orders selected');
        }
        
        $settings = get_option('arm_settings');
        $limit = isset($settings['bulk_send_limit']) ? intval($settings['bulk_send_limit']) : 50;
        
        if (count($order_ids) > $limit) {
            wp_send_json_error("Bulk send limit is {$limit} orders at a time");
        }
        
        $success_count = 0;
        $failed_count = 0;
        
        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            if ($order) {
                $this->send_review_email($order);
                $success_count++;
            } else {
                $failed_count++;
            }
        }
        
        wp_send_json_success(array(
            'message' => "Sent {$success_count} reminders successfully. {$failed_count} failed.",
            'success_count' => $success_count,
            'failed_count' => $failed_count
        ));
    }
    
    // Product blacklist functions
    public function ajax_add_to_blacklist() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $reason = isset($_POST['reason']) ? sanitize_textarea_field($_POST['reason']) : '';
        
        if (!$product_id) {
            wp_send_json_error('Invalid product ID');
        }
        
        global $wpdb;
        $blacklist_table = $wpdb->prefix . 'arm_product_blacklist';
        
        $result = $wpdb->insert(
            $blacklist_table,
            array(
                'product_id' => $product_id,
                'reason' => $reason,
                'added_by' => get_current_user_id(),
                'added_date' => current_time('mysql')
            ),
            array('%d', '%s', '%d', '%s')
        );
        
        if ($result) {
            wp_send_json_success('Product added to blacklist');
        } else {
            wp_send_json_error('Failed to add product to blacklist');
        }
    }
    
    public function ajax_remove_from_blacklist() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        
        if (!$product_id) {
            wp_send_json_error('Invalid product ID');
        }
        
        global $wpdb;
        $blacklist_table = $wpdb->prefix . 'arm_product_blacklist';
        
        $result = $wpdb->delete(
            $blacklist_table,
            array('product_id' => $product_id),
            array('%d')
        );
        
        if ($result) {
            wp_send_json_success('Product removed from blacklist');
        } else {
            wp_send_json_error('Failed to remove product from blacklist');
        }
    }
    
    // Google Reviews integration
    public function ajax_sync_google_reviews() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $settings = get_option('arm_settings');
        
        if (empty($settings['google_place_id']) || empty($settings['google_api_key'])) {
            wp_send_json_error('Please configure Google API credentials in settings');
        }
        
        $place_id = $settings['google_place_id'];
        $api_key = $settings['google_api_key'];
        
        // Fetch reviews from Google Places API
        $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$place_id}&fields=reviews&key={$api_key}";
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            wp_send_json_error('Failed to connect to Google API');
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['result']['reviews'])) {
            wp_send_json_error('No reviews found');
        }
        
        global $wpdb;
        $google_reviews_table = $wpdb->prefix . 'arm_google_reviews';
        $imported_count = 0;
        
        foreach ($data['result']['reviews'] as $review) {
            $review_id = $review['author_name'] . '_' . $review['time'];
            
            // Check if already imported
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $google_reviews_table WHERE google_review_id = %s",
                $review_id
            ));
            
            if (!$exists) {
                $wpdb->insert(
                    $google_reviews_table,
                    array(
                        'google_review_id' => $review_id,
                        'author_name' => $review['author_name'],
                        'author_photo' => isset($review['profile_photo_url']) ? $review['profile_photo_url'] : '',
                        'rating' => $review['rating'],
                        'review_text' => $review['text'],
                        'review_date' => date('Y-m-d H:i:s', $review['time']),
                        'imported_date' => current_time('mysql'),
                        'synced' => 0
                    ),
                    array('%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d')
                );
                $imported_count++;
            }
        }
        
        wp_send_json_success(array(
            'message' => "Imported {$imported_count} new Google reviews",
            'count' => $imported_count
        ));
    }
    
    public function ajax_import_google_review() {
        check_ajax_referer('arm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $review_id = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        
        if (!$review_id) {
            wp_send_json_error('Invalid review ID');
        }
        
        global $wpdb;
        $google_reviews_table = $wpdb->prefix . 'arm_google_reviews';
        
        // Get Google review
        $google_review = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $google_reviews_table WHERE id = %d",
            $review_id
        ));
        
        if (!$google_review) {
            wp_send_json_error('Review not found');
        }
        
        // Create WooCommerce product review
        $comment_data = array(
            'comment_post_ID' => $product_id,
            'comment_author' => $google_review->author_name,
            'comment_content' => $google_review->review_text,
            'comment_type' => 'review',
            'comment_parent' => 0,
            'user_id' => 0,
            'comment_approved' => 1,
            'comment_date' => $google_review->review_date
        );
        
        $comment_id = wp_insert_comment($comment_data);
        
        if ($comment_id) {
            // Add rating meta
            add_comment_meta($comment_id, 'rating', $google_review->rating);
            add_comment_meta($comment_id, 'verified', 0);
            add_comment_meta($comment_id, 'google_import', 1);
            
            // Mark as synced
            $wpdb->update(
                $google_reviews_table,
                array('synced' => 1, 'product_id' => $product_id),
                array('id' => $review_id),
                array('%d', '%d'),
                array('%d')
            );
            
            wp_send_json_success('Google review imported successfully');
        } else {
            wp_send_json_error('Failed to import review');
        }
    }
}

// Initialize the plugin
function arm_init() {
    return Advanced_Review_Manager::get_instance();
}

add_action('plugins_loaded', 'arm_init');

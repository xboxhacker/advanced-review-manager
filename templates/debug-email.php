<?php
/**
 * DEBUG EMAIL TESTING PAGE
 * TEMPORARY - REMOVE BEFORE PRODUCTION
 * File: templates/debug-email.php
 */
if (!defined('ABSPATH')) exit;

// Get recent orders for quick selection
$recent_orders = wc_get_orders(array(
    'limit' => 20,
    'orderby' => 'date',
    'order' => 'DESC',
    'status' => array('completed', 'processing'),
    'type' => 'shop_order' // Exclude refunds and other types
));

$settings = get_option('arm_settings');
?>

<div class="arm-wrapper">
    <div class="arm-header">
        <h1>
            <span class="arm-icon">🔧</span>
            DEBUG: Email Testing
        </h1>
        <p class="arm-subtitle" style="color: #e74c3c; font-weight: bold;">⚠️ TEMPORARY DEBUG PAGE - REMOVE BEFORE PRODUCTION</p>
    </div>

    <div class="arm-card">
        <div class="arm-card-header">
            <h2>🚀 Force Send Review Email</h2>
        </div>
        <div class="arm-card-body">
            <div class="arm-alert" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px; border-radius: 8px;">
                <strong>⚠️ Warning:</strong> This will send a REAL email to the customer using the current saved email templates from the database.
            </div>

            <form id="arm-debug-email-form" class="arm-form">
                <?php wp_nonce_field('arm_nonce', 'arm_nonce_field'); ?>
                
                <div class="arm-form-group">
                    <label for="debug_order_id">Order Number</label>
                    <input type="text" id="debug_order_id" name="order_id" placeholder="Enter order ID (e.g., 12345)" class="arm-input-wide" required>
                    <p class="arm-field-description">Enter the WooCommerce order ID to resend the review email.</p>
                </div>

                <div class="arm-form-group">
                    <label>Quick Select Recent Orders:</label>
                    <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 8px; background: #f9f9f9;">
                        <?php if ($recent_orders): ?>
                            <?php foreach ($recent_orders as $order): ?>
                                <?php 
                                // Skip if not a valid order object
                                if (!is_a($order, 'WC_Order') || !method_exists($order, 'get_billing_first_name')) {
                                    continue;
                                }
                                
                                $order_id = $order->get_id();
                                $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                                $customer_email = $order->get_billing_email();
                                $order_date = $order->get_date_created() ? $order->get_date_created()->date('M j, Y') : 'N/A';
                                $order_total = $order->get_formatted_order_total();
                                ?>
                                <div style="padding: 10px; margin: 5px 0; background: white; border-radius: 5px; border-left: 3px solid #667eea; cursor: pointer;" 
                                     onclick="document.getElementById('debug_order_id').value = '<?php echo $order_id; ?>'">
                                    <strong>Order #<?php echo $order_id; ?></strong> - 
                                    <?php echo esc_html($customer_name); ?> 
                                    (<?php echo esc_html($customer_email); ?>) - 
                                    <?php echo $order_date; ?> - 
                                    <?php echo $order_total; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #999;">No recent orders found.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="arm-form-actions">
                    <button type="submit" class="arm-btn arm-btn-primary">
                        <span class="arm-btn-icon">📧</span>
                        Force Send Email to Customer
                    </button>
                </div>
            </form>

            <div id="arm-debug-result" style="margin-top: 20px; display: none;"></div>
        </div>
    </div>

    <div class="arm-card" style="margin-top: 30px; border: 3px solid #dc3545;">
        <div class="arm-card-header" style="background: #dc3545; color: white;">
            <h2>☢️ NUCLEAR OPTION: Reset Plugin Database</h2>
        </div>
        <div class="arm-card-body">
            <div class="arm-alert" style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin-bottom: 20px; border-radius: 8px; color: #721c24;">
                <strong>⚠️ DANGER:</strong> This will completely wipe ALL plugin settings and data from the database, including:
                <ul style="margin: 10px 0;">
                    <li>All email template settings (subjects, messages, variants A/B/C)</li>
                    <li>Email tracking data</li>
                    <li>Analytics data</li>
                    <li>Product blacklist</li>
                    <li>Review media</li>
                    <li>ALL plugin options</li>
                </ul>
                <strong>This will remove any [TEST] text stored in the database.</strong><br>
                <strong>You will need to reconfigure all settings after this.</strong>
            </div>

            <form id="arm-nuclear-reset-form" class="arm-form">
                <?php wp_nonce_field('arm_nonce', 'arm_nonce_field_nuclear'); ?>
                
                <div class="arm-form-group">
                    <label style="color: #dc3545; font-weight: bold;">Type "RESET" to confirm:</label>
                    <input type="text" id="nuclear_confirm" name="confirm" placeholder="Type RESET" class="arm-input-wide" style="border: 2px solid #dc3545;">
                </div>

                <div class="arm-form-actions">
                    <button type="submit" class="arm-btn" style="background: #dc3545; color: white; font-weight: bold;">
                        <span class="arm-btn-icon">☢️</span>
                        WIPE PLUGIN DATABASE
                    </button>
                </div>
            </form>

            <div id="arm-nuclear-result" style="margin-top: 20px; display: none;"></div>
        </div>
    </div>

    <div class="arm-card" style="margin-top: 30px;">
        <div class="arm-card-header">
            <h2>📊 Current Email Template Settings</h2>
        </div>
        <div class="arm-card-body">
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; font-family: monospace; font-size: 13px; overflow-x: auto;">
                <h3 style="margin-top: 0;">Template A (Default):</h3>
                <p><strong>Subject:</strong> <?php echo esc_html($settings['email_subject'] ?? 'NOT SET'); ?></p>
                <p><strong>Heading:</strong> <?php echo esc_html($settings['email_heading'] ?? 'NOT SET'); ?></p>
                <p><strong>Button Text:</strong> <?php echo esc_html($settings['button_text'] ?? 'NOT SET'); ?></p>
                
                <h3>Template B (Variant):</h3>
                <p><strong>Subject:</strong> <?php echo esc_html($settings['email_subject_b'] ?? 'NOT SET'); ?></p>
                <p><strong>Heading:</strong> <?php echo esc_html($settings['email_heading_b'] ?? 'NOT SET'); ?></p>
                
                <h3>Template C (Variant 2):</h3>
                <p><strong>Subject:</strong> <?php echo esc_html($settings['email_subject_c'] ?? 'NOT SET'); ?></p>
                <p><strong>Heading:</strong> <?php echo esc_html($settings['email_heading_c'] ?? 'NOT SET'); ?></p>
                
                <h3 style="margin-top: 20px; color: #e74c3c;">🔍 Look for [TEST] in subjects above!</h3>
                <p>If you see [TEST] in any subject line, go to <strong>Email Templates</strong> and remove it manually.</p>
            </div>
        </div>
    </div>
</div>

<script>
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

jQuery(document).ready(function($) {
    $('#arm-debug-email-form').on('submit', function(e) {
        e.preventDefault();
        
        var orderId = $('#debug_order_id').val();
        var $result = $('#arm-debug-result');
        var $btn = $(this).find('button[type="submit"]');
        
        if (!orderId) {
            $result.html('<div class="arm-alert" style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; border-radius: 8px; color: #721c24;">Please enter an order ID.</div>').show();
            return;
        }
        
        $btn.prop('disabled', true).html('<span class="arm-btn-icon">⏳</span> Sending...');
        $result.html('<div style="padding: 15px; background: #d1ecf1; border-radius: 8px; color: #0c5460;">Sending email to customer...</div>').show();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'arm_debug_force_send_email',
                nonce: $('#arm_nonce_field').val(),
                order_id: orderId
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<div class="arm-alert" style="background: #d4edda; border-left: 4px solid #28a745; padding: 15px; border-radius: 8px; color: #155724;">' +
                        '<strong>✅ Success!</strong> ' + response.data.message + 
                        '<br><br><strong>Debug Info:</strong><br>' +
                        '<pre style="background: white; padding: 10px; margin-top: 10px; border-radius: 5px; overflow-x: auto;">' + 
                        JSON.stringify(response.data.debug, null, 2) + 
                        '</pre></div>');
                } else {
                    $result.html('<div class="arm-alert" style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; border-radius: 8px; color: #721c24;">' +
                        '<strong>❌ Error:</strong> ' + response.data.message + '</div>');
                }
                $btn.prop('disabled', false).html('<span class="arm-btn-icon">📧</span> Force Send Email to Customer');
            },
            error: function() {
                $result.html('<div class="arm-alert" style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; border-radius: 8px; color: #721c24;">' +
                    '<strong>❌ Error:</strong> AJAX request failed.</div>');
                $btn.prop('disabled', false).html('<span class="arm-btn-icon">📧</span> Force Send Email to Customer');
            }
        });
    });
    
    // Nuclear reset handler
    $('#arm-nuclear-reset-form').on('submit', function(e) {
        e.preventDefault();
        
        var confirm = $('#nuclear_confirm').val();
        var $result = $('#arm-nuclear-result');
        var $btn = $(this).find('button[type="submit"]');
        
        if (confirm !== 'RESET') {
            $result.html('<div class="arm-alert" style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; border-radius: 8px; color: #721c24;">You must type "RESET" to confirm.</div>').show();
            return;
        }
        
        if (!window.confirm('ARE YOU ABSOLUTELY SURE? This will delete ALL plugin data and settings. This cannot be undone!')) {
            return;
        }
        
        $btn.prop('disabled', true).html('<span class="arm-btn-icon">⏳</span> Wiping database...');
        $result.html('<div style="padding: 15px; background: #fff3cd; border-radius: 8px; color: #856404;">Deleting all plugin data...</div>').show();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'arm_nuclear_reset',
                nonce: $('#arm_nonce_field_nuclear').val()
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<div class="arm-alert" style="background: #d4edda; border-left: 4px solid #28a745; padding: 15px; border-radius: 8px; color: #155724;">' +
                        '<strong>✅ Database Wiped!</strong><br>' + response.data.message + 
                        '<br><br><strong>Deleted:</strong><br>' +
                        '<pre style="background: white; padding: 10px; margin-top: 10px; border-radius: 5px; overflow-x: auto;">' + 
                        JSON.stringify(response.data.deleted, null, 2) + 
                        '</pre>' +
                        '<br><strong>⚠️ Go to Email Templates and reconfigure all settings now!</strong></div>');
                    $('#nuclear_confirm').val('');
                } else {
                    $result.html('<div class="arm-alert" style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; border-radius: 8px; color: #721c24;">' +
                        '<strong>❌ Error:</strong> ' + response.data.message + '</div>');
                }
                $btn.prop('disabled', false).html('<span class="arm-btn-icon">☢️</span> WIPE PLUGIN DATABASE');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.error('Response:', jqXHR.responseText);
                $result.html('<div class="arm-alert" style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; border-radius: 8px; color: #721c24;">' +
                    '<strong>❌ AJAX Error:</strong> ' + textStatus + '<br>' +
                    '<strong>Error:</strong> ' + errorThrown + '<br>' +
                    '<pre style="background: white; padding: 10px; margin-top: 10px; border-radius: 5px; overflow-x: auto; max-height: 200px;">' + 
                    jqXHR.responseText.substring(0, 500) + 
                    '</pre></div>');
                $btn.prop('disabled', false).html('<span class="arm-btn-icon">☢️</span> WIPE PLUGIN DATABASE');
            }
        });
    });
});
</script>

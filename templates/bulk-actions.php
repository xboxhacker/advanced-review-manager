<?php
/**
 * Bulk Actions Template
 * File: templates/bulk-actions.php
 */
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_name = $wpdb->prefix . 'arm_reminders';

// Get settings for reminder days threshold
$settings = get_option('arm_settings');
$reminder_days = isset($settings['reminder_days']) ? intval($settings['reminder_days']) : 7;

// Get orders without reminders sent
$pending_orders = $wpdb->get_results("
    SELECT r.order_id, r.customer_email, r.created_at
    FROM $table_name r
    WHERE r.reminder_sent = 0
    ORDER BY r.created_at DESC
    LIMIT 100
");

// Find old completed orders - show status of whether reminder was sent
$cutoff_date = date('Y-m-d H:i:s', strtotime("-{$reminder_days} days"));
$old_orders = $wpdb->get_results($wpdb->prepare("
    SELECT p.ID as order_id, pm.meta_value as customer_email, p.post_date as created_at,
           r.reminder_sent, r.sent_date
    FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_billing_email'
    LEFT JOIN {$table_name} r ON p.ID = r.order_id
    WHERE p.post_type = 'shop_order'
    AND p.post_status IN ('wc-completed', 'wc-processing', 'publish', 'completed')
    AND p.post_date < %s
    ORDER BY p.post_date DESC
    LIMIT 100
", $cutoff_date));
?>

<div class="arm-wrapper">
    <div class="arm-header">
        <h1>
            <span class="arm-icon">📤</span>
            Bulk Actions
        </h1>
        <p class="arm-subtitle">Send review reminders to multiple orders at once</p>
    </div>

    <div class="arm-card">
        <div class="arm-card-header">
            <h2>📧 Bulk Send Reminders</h2>
            <p style="font-size: 13px; color: #666; margin-top: 5px;">Select orders to send review reminder emails</p>
        </div>
        <div class="arm-card-body">
            <?php if (!empty($pending_orders)): ?>
                <div style="margin-bottom: 15px;">
                    <button id="arm-select-all" class="arm-btn arm-btn-secondary">Select All</button>
                    <button id="arm-deselect-all" class="arm-btn arm-btn-secondary" style="margin-left: 10px;">Deselect All</button>
                </div>
                
                <form id="arm-bulk-send-form">
                    <table class="arm-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="bulk-select-all" />
                                </th>
                                <th>Order ID</th>
                                <th>Customer Email</th>
                                <th>Products</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_orders as $reminder): 
                                $order = wc_get_order($reminder->order_id);
                                if (!$order) continue;
                                
                                $products = array();
                                foreach ($order->get_items() as $item) {
                                    $products[] = $item->get_name();
                                }
                            ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="order_ids[]" value="<?php echo $reminder->order_id; ?>" class="bulk-order-checkbox" />
                                    </td>
                                    <td><strong>#<?php echo $reminder->order_id; ?></strong></td>
                                    <td><?php echo esc_html($reminder->customer_email); ?></td>
                                    <td><?php echo esc_html(implode(', ', array_slice($products, 0, 2))); ?><?php echo count($products) > 2 ? '...' : ''; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($reminder->created_at)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="arm-form-actions" style="margin-top: 20px;">
                        <button type="submit" class="arm-btn arm-btn-primary">
                            <span class="arm-btn-icon">📤</span>
                            Send Reminders to Selected Orders
                        </button>
                        <span id="selected-count" style="margin-left: 15px; color: #666;">0 selected</span>
                    </div>
                </form>
            <?php else: ?>
                <p class="arm-empty-state">No pending reminders found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Old Orders Section -->
    <div class="arm-card" style="margin-top: 30px;">
        <div class="arm-card-header">
            <h2>📦 Old Orders (Before Plugin Installation)</h2>
            <p style="font-size: 13px; color: #666; margin-top: 5px;">Send review reminders for orders completed before the plugin was installed</p>
        </div>
        <div class="arm-card-body">
            <?php if (!empty($old_orders)): ?>
                <div class="arm-alert arm-alert-info" style="margin-bottom: 20px;">
                    <strong>ℹ️ Note:</strong> These orders were completed before the plugin was active. Sending reminders now will request reviews for past purchases.
                </div>

                <div style="margin-bottom: 15px;">
                    <button id="arm-select-all-old" class="arm-btn arm-btn-secondary">Select All</button>
                    <button id="arm-deselect-all-old" class="arm-btn arm-btn-secondary" style="margin-left: 10px;">Deselect All</button>
                </div>
                
                <form id="arm-bulk-send-old-form">
                    <table class="arm-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="bulk-select-all-old" />
                                </th>
                                <th>Order ID</th>
                                <th>Customer Email</th>
                                <th>Products</th>
                                <th>Order Date</th>
                                <th>Reminder Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($old_orders as $old_order): 
                                $order = wc_get_order($old_order->order_id);
                                if (!$order) continue;
                                
                                $products = array();
                                foreach ($order->get_items() as $item) {
                                    $products[] = $item->get_name();
                                }
                                
                                // Determine reminder status
                                $status_html = '';
                                $checkbox_disabled = '';
                                if ($old_order->reminder_sent == 1) {
                                    $sent_date = date('M j, Y', strtotime($old_order->sent_date));
                                    $status_html = '<span class="arm-badge arm-badge-success" title="Sent on ' . $sent_date . '">✅ Sent</span>';
                                    $checkbox_disabled = 'disabled';
                                } elseif (!is_null($old_order->reminder_sent)) {
                                    $status_html = '<span class="arm-badge" style="background: #f59e0b; color: white;">⏰ Scheduled</span>';
                                } else {
                                    $status_html = '<span class="arm-badge" style="background: #6b7280; color: white;">⏸️ Not Sent</span>';
                                }
                            ?>
                                <tr <?php echo $old_order->reminder_sent == 1 ? 'style="opacity: 0.5;"' : ''; ?>>
                                    <td>
                                        <input type="checkbox" name="old_order_ids[]" value="<?php echo $old_order->order_id; ?>" class="bulk-old-order-checkbox" <?php echo $checkbox_disabled; ?> />
                                    </td>
                                    <td><strong>#<?php echo $old_order->order_id; ?></strong></td>
                                    <td><?php echo esc_html($old_order->customer_email); ?></td>
                                    <td><?php echo esc_html(implode(', ', array_slice($products, 0, 2))); ?><?php echo count($products) > 2 ? '...' : ''; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($old_order->created_at)); ?></td>
                                    <td><?php echo $status_html; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="arm-form-actions" style="margin-top: 20px;">
                        <button type="submit" class="arm-btn arm-btn-primary">
                            <span class="arm-btn-icon">📤</span>
                            Send Reminders to Selected Old Orders
                        </button>
                        <span id="selected-old-count" style="margin-left: 15px; color: #666;">0 selected</span>
                    </div>
                </form>
                
                <!-- Manual Mark as Sent Section -->
                <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                    <h3 style="margin-top: 0; color: #856404;">⚠️ Manual Override: Mark Orders as Sent</h3>
                    <p style="color: #856404; margin-bottom: 15px;">
                        Use this to mark orders as "already sent" without actually sending emails. This will:
                        <br>• Update the database to show reminder as sent
                        <br>• Remove orders from the dashboard "Old Orders" list
                        <br>• Show "✅ Sent" status on WooCommerce orders page
                    </p>
                    <form id="arm-bulk-mark-sent-form">
                        <div style="margin-bottom: 10px;">
                            <button type="button" id="arm-select-all-mark" class="arm-btn arm-btn-secondary">Select All Not Sent</button>
                            <button type="button" id="arm-deselect-all-mark" class="arm-btn arm-btn-secondary" style="margin-left: 10px;">Deselect All</button>
                        </div>
                        <table class="arm-table" style="background: white;">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">
                                        <input type="checkbox" id="bulk-select-all-mark" />
                                    </th>
                                    <th>Order ID</th>
                                    <th>Customer Email</th>
                                    <th>Order Date</th>
                                    <th>Current Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($old_orders as $old_order): 
                                    if ($old_order->reminder_sent == 1) continue; // Skip already sent
                                    
                                    $order = wc_get_order($old_order->order_id);
                                    if (!$order) continue;
                                ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="mark_sent_order_ids[]" value="<?php echo $old_order->order_id; ?>" class="bulk-mark-sent-checkbox" />
                                        </td>
                                        <td><strong>#<?php echo $old_order->order_id; ?></strong></td>
                                        <td><?php echo esc_html($old_order->customer_email); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($old_order->created_at)); ?></td>
                                        <td>
                                            <?php if (!is_null($old_order->reminder_sent)): ?>
                                                <span class="arm-badge" style="background: #f59e0b; color: white;">⏰ Scheduled</span>
                                            <?php else: ?>
                                                <span class="arm-badge" style="background: #6b7280; color: white;">⏸️ Not Sent</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="arm-form-actions" style="margin-top: 15px;">
                            <button type="submit" class="arm-btn" style="background: #ffc107; color: #000;">
                                <span class="arm-btn-icon">✓</span>
                                Mark Selected as Sent (No Email)
                            </button>
                            <span id="selected-mark-count" style="margin-left: 15px; color: #856404; font-weight: 600;">0 selected</span>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <p class="arm-empty-state">No old orders found without reminders.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Select all checkbox - Pending Orders
    $('#bulk-select-all, #arm-select-all').on('click', function(e) {
        if ($(this).is('button')) e.preventDefault();
        $('.bulk-order-checkbox').prop('checked', true);
        updateSelectedCount();
    });
    
    // Deselect all - Pending Orders
    $('#arm-deselect-all').on('click', function(e) {
        e.preventDefault();
        $('.bulk-order-checkbox').prop('checked', false);
        updateSelectedCount();
    });
    
    // Update count - Pending Orders
    $('.bulk-order-checkbox').on('change', updateSelectedCount);
    
    function updateSelectedCount() {
        var count = $('.bulk-order-checkbox:checked').length;
        $('#selected-count').text(count + ' selected');
    }
    
    // Select all checkbox - Old Orders
    $('#bulk-select-all-old, #arm-select-all-old').on('click', function(e) {
        if ($(this).is('button')) e.preventDefault();
        $('.bulk-old-order-checkbox').prop('checked', true);
        updateOldSelectedCount();
    });
    
    // Deselect all - Old Orders
    $('#arm-deselect-all-old').on('click', function(e) {
        e.preventDefault();
        $('.bulk-old-order-checkbox').prop('checked', false);
        updateOldSelectedCount();
    });
    
    // Update count - Old Orders
    $('.bulk-old-order-checkbox').on('change', updateOldSelectedCount);
    
    function updateOldSelectedCount() {
        var count = $('.bulk-old-order-checkbox:checked').length;
        $('#selected-old-count').text(count + ' selected');
    }
    
    // Mark as Sent checkboxes
    $('#bulk-select-all-mark, #arm-select-all-mark').on('click', function(e) {
        if ($(this).is('button')) e.preventDefault();
        $('.bulk-mark-sent-checkbox').prop('checked', true);
        updateMarkSentCount();
    });
    
    $('#arm-deselect-all-mark').on('click', function(e) {
        e.preventDefault();
        $('.bulk-mark-sent-checkbox').prop('checked', false);
        updateMarkSentCount();
    });
    
    $('.bulk-mark-sent-checkbox').on('change', updateMarkSentCount);
    
    function updateMarkSentCount() {
        var count = $('.bulk-mark-sent-checkbox:checked').length;
        $('#selected-mark-count').text(count + ' selected');
    }
    
    // Handle bulk send - Pending Orders
    $('#arm-bulk-send-form').on('submit', function(e) {
        e.preventDefault();
        
        var orderIds = [];
        $('.bulk-order-checkbox:checked').each(function() {
            orderIds.push($(this).val());
        });
        
        if (orderIds.length === 0) {
            alert('Please select at least one order');
            return;
        }
        
        if (!confirm('Send review reminders to ' + orderIds.length + ' orders?')) {
            return;
        }
        
        var $button = $(this).find('button[type="submit"]');
        var originalText = $button.html();
        $button.html('<span class="arm-btn-icon">⏳</span> Sending...').prop('disabled', true);
        
        $.ajax({
            url: armAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'arm_bulk_send_reminders',
                order_ids: orderIds,
                nonce: armAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data || 'Failed to send reminders');
                    $button.html(originalText).prop('disabled', false);
                }
            },
            error: function() {
                alert('Error sending reminders');
                $button.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Handle bulk send - Old Orders
    $('#arm-bulk-send-old-form').on('submit', function(e) {
        e.preventDefault();
        
        var orderIds = [];
        $('.bulk-old-order-checkbox:checked').each(function() {
            orderIds.push($(this).val());
        });
        
        if (orderIds.length === 0) {
            alert('Please select at least one order');
            return;
        }
        
        if (!confirm('Send review reminders to ' + orderIds.length + ' old orders?\n\nNote: These customers placed orders before the plugin was installed.')) {
            return;
        }
        
        var $button = $(this).find('button[type="submit"]');
        var originalText = $button.html();
        $button.html('<span class="arm-btn-icon">⏳</span> Sending...').prop('disabled', true);
        
        $.ajax({
            url: armAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'arm_bulk_send_reminders',
                order_ids: orderIds,
                is_old_orders: true,
                nonce: armAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data || 'Failed to send reminders');
                    $button.html(originalText).prop('disabled', false);
                }
            },
            error: function() {
                alert('Error sending reminders');
                $button.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Handle bulk mark as sent (no email)
    $('#arm-bulk-mark-sent-form').on('submit', function(e) {
        e.preventDefault();
        
        var orderIds = [];
        $('.bulk-mark-sent-checkbox:checked').each(function() {
            orderIds.push($(this).val());
        });
        
        if (orderIds.length === 0) {
            alert('Please select at least one order');
            return;
        }
        
        if (!confirm('⚠️ Mark ' + orderIds.length + ' orders as "Sent" WITHOUT sending emails?\n\nThis will:\n• Update database to show as sent\n• Remove from dashboard "Old Orders" list\n• Show "✅ Sent" on WooCommerce orders page\n\nNo actual emails will be sent.')) {
            return;
        }
        
        var $button = $(this).find('button[type="submit"]');
        var originalText = $button.html();
        $button.html('<span class="arm-btn-icon">⏳</span> Marking...').prop('disabled', true);
        
        $.ajax({
            url: armAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'arm_bulk_mark_as_sent',
                order_ids: orderIds,
                nonce: armAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data || 'Failed to mark orders as sent');
                    $button.html(originalText).prop('disabled', false);
                }
            },
            error: function() {
                alert('Error marking orders as sent');
                $button.html(originalText).prop('disabled', false);
            }
        });
    });
});
</script>

<?php
/**
 * Bulk Actions Template
 * File: templates/bulk-actions.php
 */
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_name = $wpdb->prefix . 'arm_reminders';

// Get orders without reminders sent
$pending_orders = $wpdb->get_results("
    SELECT r.order_id, r.customer_email, r.created_at
    FROM $table_name r
    WHERE r.reminder_sent = 0
    ORDER BY r.created_at DESC
    LIMIT 100
");
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
</div>

<script>
jQuery(document).ready(function($) {
    // Select all checkbox
    $('#bulk-select-all, #arm-select-all').on('click', function(e) {
        if ($(this).is('button')) e.preventDefault();
        $('.bulk-order-checkbox').prop('checked', true);
        updateSelectedCount();
    });
    
    // Deselect all
    $('#arm-deselect-all').on('click', function(e) {
        e.preventDefault();
        $('.bulk-order-checkbox').prop('checked', false);
        updateSelectedCount();
    });
    
    // Update count on checkbox change
    $('.bulk-order-checkbox').on('change', updateSelectedCount);
    
    function updateSelectedCount() {
        var count = $('.bulk-order-checkbox:checked').length;
        $('#selected-count').text(count + ' selected');
    }
    
    // Handle bulk send
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
});
</script>

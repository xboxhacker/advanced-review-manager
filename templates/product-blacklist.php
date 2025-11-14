<?php
/**
 * Product Blacklist Template
 * File: templates/product-blacklist.php
 */
if (!defined('ABSPATH')) exit;

global $wpdb;
$blacklist_table = $wpdb->prefix . 'arm_product_blacklist';

// Get blacklisted products
$blacklisted = $wpdb->get_results("
    SELECT b.*, p.post_title as product_name
    FROM $blacklist_table b
    LEFT JOIN {$wpdb->posts} p ON b.product_id = p.ID
    ORDER BY b.added_date DESC
");
?>

<div class="arm-wrapper">
    <div class="arm-header">
        <h1>
            <span class="arm-icon">🚫</span>
            Product Blacklist
        </h1>
        <p class="arm-subtitle">Prevent review reminders for specific products</p>
    </div>

    <div class="arm-grid arm-grid-2col">
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>➕ Add Product to Blacklist</h2>
            </div>
            <div class="arm-card-body">
                <form id="arm-add-blacklist-form">
                    <?php wp_nonce_field('arm_nonce', 'arm_nonce_field'); ?>
                    
                    <div class="arm-form-group">
                        <label for="product_search">Search Product</label>
                        <select id="product_search" name="product_id" class="arm-input-wide" required>
                            <option value="">-- Select a product --</option>
                            <?php
                            $products = get_posts(array(
                                'post_type' => 'product',
                                'posts_per_page' => -1,
                                'orderby' => 'title',
                                'order' => 'ASC'
                            ));
                            
                            foreach ($products as $product) {
                                echo '<option value="' . $product->ID . '">' . esc_html($product->post_title) . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="arm-form-group">
                        <label for="blacklist_reason">Reason (Optional)</label>
                        <textarea id="blacklist_reason" name="reason" rows="3" class="arm-input-wide" placeholder="Why should this product not receive review reminders?"></textarea>
                    </div>

                    <div class="arm-form-actions">
                        <button type="submit" class="arm-btn arm-btn-primary">
                            <span class="arm-btn-icon">➕</span>
                            Add to Blacklist
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="arm-card">
            <div class="arm-card-header">
                <h2>📋 Blacklist Info</h2>
            </div>
            <div class="arm-card-body">
                <div class="arm-tip">
                    <span class="arm-tip-icon">ℹ️</span>
                    <h4>How it works</h4>
                    <p>Products added to the blacklist will not trigger review reminder emails. This is useful for:</p>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li>Products with known quality issues</li>
                        <li>Sample or gift products</li>
                        <li>Products being discontinued</li>
                        <li>Digital products that don't need reviews</li>
                    </ul>
                </div>
                
                <div style="margin-top: 15px; padding: 12px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                    <strong>⚠️ Note:</strong> Blacklisted products will still allow manual review submission through the product page.
                </div>
            </div>
        </div>
    </div>

    <div class="arm-card">
        <div class="arm-card-header">
            <h2>🚫 Blacklisted Products (<?php echo count($blacklisted); ?>)</h2>
        </div>
        <div class="arm-card-body">
            <?php if (!empty($blacklisted)): ?>
                <table class="arm-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Reason</th>
                            <th>Added Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blacklisted as $item): ?>
                            <tr data-product-id="<?php echo $item->product_id; ?>">
                                <td><strong><?php echo esc_html($item->product_name); ?></strong></td>
                                <td><?php echo esc_html($item->reason ? $item->reason : 'No reason provided'); ?></td>
                                <td><?php echo date('M j, Y', strtotime($item->added_date)); ?></td>
                                <td>
                                    <button class="arm-btn-small arm-remove-blacklist" data-product-id="<?php echo $item->product_id; ?>" style="background: #dc2626; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="arm-empty-state">No products blacklisted yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Add to blacklist
    $('#arm-add-blacklist-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $button = $form.find('button[type="submit"]');
        var originalText = $button.html();
        
        $button.html('<span class="arm-btn-icon">⏳</span> Adding...').prop('disabled', true);
        
        $.ajax({
            url: armAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'arm_add_to_blacklist',
                product_id: $('#product_search').val(),
                reason: $('#blacklist_reason').val(),
                nonce: armAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Product added to blacklist successfully!');
                    location.reload();
                } else {
                    alert(response.data || 'Failed to add product to blacklist');
                    $button.html(originalText).prop('disabled', false);
                }
            },
            error: function() {
                alert('Error adding product to blacklist');
                $button.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Remove from blacklist
    $('.arm-remove-blacklist').on('click', function() {
        var productId = $(this).data('product-id');
        var $row = $(this).closest('tr');
        
        if (!confirm('Remove this product from blacklist?')) {
            return;
        }
        
        var $button = $(this);
        var originalText = $button.text();
        $button.text('Removing...').prop('disabled', true);
        
        $.ajax({
            url: armAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'arm_remove_from_blacklist',
                product_id: productId,
                nonce: armAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(400, function() {
                        $(this).remove();
                    });
                } else {
                    alert(response.data || 'Failed to remove product from blacklist');
                    $button.text(originalText).prop('disabled', false);
                }
            },
            error: function() {
                alert('Error removing product from blacklist');
                $button.text(originalText).prop('disabled', false);
            }
        });
    });
});
</script>

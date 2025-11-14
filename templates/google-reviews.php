<?php
/**
 * Google Reviews Integration Template
 * File: templates/google-reviews.php
 */
if (!defined('ABSPATH')) exit;

$settings = get_option('arm_settings');
global $wpdb;
$google_reviews_table = $wpdb->prefix . 'arm_google_reviews';

// Get imported reviews
$imported_reviews = $wpdb->get_results("
    SELECT * FROM $google_reviews_table
    ORDER BY review_date DESC
    LIMIT 50
");
?>

<div class="arm-wrapper">
    <div class="arm-header">
        <h1>
            <span class="arm-icon">🌐</span>
            Google Reviews Integration
        </h1>
        <p class="arm-subtitle">Import and sync reviews from Google My Business</p>
    </div>

    <div class="arm-grid arm-grid-2col">
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>⚙️ Google API Configuration</h2>
            </div>
            <div class="arm-card-body">
                <form id="arm-google-config-form">
                    <?php wp_nonce_field('arm_nonce', 'arm_nonce_field'); ?>
                    
                    <div class="arm-form-group">
                        <label for="google_place_id">Google Place ID</label>
                        <input type="text" id="google_place_id" name="google_place_id" value="<?php echo esc_attr($settings['google_place_id'] ?? ''); ?>" class="arm-input-wide" placeholder="ChIJN1t_tDeuEmsRUsoyG83frY4" />
                        <p class="arm-field-description">Find your Place ID: <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank">Google Place ID Finder</a></p>
                    </div>

                    <div class="arm-form-group">
                        <label for="google_api_key">Google API Key</label>
                        <input type="password" id="google_api_key" name="google_api_key" value="<?php echo esc_attr($settings['google_api_key'] ?? ''); ?>" class="arm-input-wide" placeholder="AIzaSy..." />
                        <p class="arm-field-description">Create API key: <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a></p>
                    </div>

                    <div class="arm-form-group">
                        <label class="arm-toggle-label">
                            <input type="checkbox" name="enable_google_reviews" <?php checked(isset($settings['enable_google_reviews']) && $settings['enable_google_reviews'], true); ?> />
                            <span class="arm-toggle-slider"></span>
                            <span class="arm-toggle-text">Enable Google Reviews Integration</span>
                        </label>
                    </div>

                    <div class="arm-form-actions">
                        <button type="submit" class="arm-btn arm-btn-primary">
                            <span class="arm-btn-icon">💾</span>
                            Save Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="arm-card">
            <div class="arm-card-header">
                <h2>🔄 Sync Reviews</h2>
            </div>
            <div class="arm-card-body">
                <div class="arm-tip">
                    <span class="arm-tip-icon">ℹ️</span>
                    <h4>How to use</h4>
                    <p>1. Configure your Google Place ID and API Key<br>
                    2. Click "Sync Now" to fetch reviews from Google<br>
                    3. Manually import reviews and assign to products<br>
                    4. Reviews will appear on your product pages</p>
                </div>
                
                <div style="margin-top: 20px;">
                    <button id="arm-sync-google" class="arm-btn arm-btn-primary" style="width: 100%;">
                        <span class="arm-btn-icon">🔄</span>
                        Sync Now from Google
                    </button>
                </div>
                
                <div style="margin-top: 15px; padding: 12px; background: #d1fae5; border-left: 4px solid #10b981; border-radius: 4px;">
                    <strong>✅ Benefits:</strong> Build trust, increase conversion rates, and showcase social proof with Google reviews.
                </div>
            </div>
        </div>
    </div>

    <div class="arm-card">
        <div class="arm-card-header">
            <h2>📋 Imported Google Reviews (<?php echo count($imported_reviews); ?>)</h2>
        </div>
        <div class="arm-card-body">
            <?php if (!empty($imported_reviews)): ?>
                <table class="arm-table">
                    <thead>
                        <tr>
                            <th>Author</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($imported_reviews as $review): ?>
                            <tr>
                                <td>
                                    <?php if ($review->author_photo): ?>
                                        <img src="<?php echo esc_url($review->author_photo); ?>" style="width: 30px; height: 30px; border-radius: 50%; vertical-align: middle; margin-right: 8px;" />
                                    <?php endif; ?>
                                    <strong><?php echo esc_html($review->author_name); ?></strong>
                                </td>
                                <td>
                                    <span style="color: #FFD700; font-size: 16px;">
                                        <?php echo str_repeat('★', $review->rating) . str_repeat('☆', 5 - $review->rating); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(substr($review->review_text, 0, 100)) . (strlen($review->review_text) > 100 ? '...' : ''); ?></td>
                                <td><?php echo date('M j, Y', strtotime($review->review_date)); ?></td>
                                <td>
                                    <?php if ($review->synced): ?>
                                        <span class="arm-badge arm-badge-success">✓ Imported</span>
                                    <?php else: ?>
                                        <span class="arm-badge" style="background: #fbbf24; color: white;">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$review->synced): ?>
                                        <button class="arm-btn-small arm-import-review" data-review-id="<?php echo $review->id; ?>" style="background: #7c3aed; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">
                                            Import to Product
                                        </button>
                                    <?php else: ?>
                                        <a href="<?php echo get_edit_post_link($review->product_id); ?>" class="arm-btn-small" style="display: inline-block; padding: 5px 12px; background: #e5e7eb; color: #374151; text-decoration: none; border-radius: 4px; font-size: 12px;">
                                            View Product
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="arm-empty-state">No Google reviews imported yet. Click "Sync Now" to fetch reviews.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Product Selection Modal -->
<div id="product-select-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 30px; border-radius: 8px; max-width: 500px; width: 90%;">
        <h2 style="margin-top: 0;">Select Product</h2>
        <select id="product-select" class="arm-input-wide">
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
        <div style="margin-top: 20px;">
            <button id="confirm-import" class="arm-btn arm-btn-primary">Import</button>
            <button id="cancel-import" class="arm-btn arm-btn-secondary" style="margin-left: 10px;">Cancel</button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var currentReviewId = null;
    
    // Sync Google reviews
    $('#arm-sync-google').on('click', function() {
        var $button = $(this);
        var originalText = $button.html();
        $button.html('<span class="arm-btn-icon">⏳</span> Syncing...').prop('disabled', true);
        
        $.ajax({
            url: armAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'arm_sync_google_reviews',
                nonce: armAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data || 'Failed to sync Google reviews');
                    $button.html(originalText).prop('disabled', false);
                }
            },
            error: function() {
                alert('Error syncing Google reviews');
                $button.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Import review
    $('.arm-import-review').on('click', function() {
        currentReviewId = $(this).data('review-id');
        $('#product-select-modal').css('display', 'flex');
    });
    
    $('#cancel-import').on('click', function() {
        $('#product-select-modal').hide();
        currentReviewId = null;
    });
    
    $('#confirm-import').on('click', function() {
        var productId = $('#product-select').val();
        
        if (!productId) {
            alert('Please select a product');
            return;
        }
        
        var $button = $(this);
        var originalText = $button.text();
        $button.text('Importing...').prop('disabled', true);
        
        $.ajax({
            url: armAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'arm_import_google_review',
                review_id: currentReviewId,
                product_id: productId,
                nonce: armAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Review imported successfully!');
                    location.reload();
                } else {
                    alert(response.data || 'Failed to import review');
                    $button.text(originalText).prop('disabled', false);
                }
            },
            error: function() {
                alert('Error importing review');
                $button.text(originalText).prop('disabled', false);
            }
        });
    });
    
    // Save configuration
    $('#arm-google-config-form').on('submit', function(e) {
        e.preventDefault();
        alert('Configuration saved! (You would implement the settings save handler)');
    });
});
</script>

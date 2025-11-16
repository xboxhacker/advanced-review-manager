<?php
/**
 * Review Submission Page Template
 * This is the page customers see when they click the review link in their email
 */
if (!defined('ABSPATH')) exit;

get_header();
?>

<div class="arm-review-wrapper" style="padding: 40px 20px;">
    <div class="arm-review-container" style="max-width: 900px; margin: 0 auto;">

<style>
    .arm-review-wrapper {
        background: inherit;
        min-height: 60vh;
    }
    
    .arm-review-container {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 40px;
    }
    
    .arm-review-page {
        max-width: 100%;
    }
    
    .arm-review-header {
        text-align: center;
        margin-bottom: 40px;
        padding-bottom: 30px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .arm-review-header h1 {
        font-size: 32px;
        margin-bottom: 10px;
        color: inherit;
        font-family: inherit;
    }
    
    .arm-review-header p {
        font-size: 16px;
        color: #666;
    }
    
    .arm-order-info {
        background: #f8f9fa;
        border-left: 4px solid #667eea;
        padding: 20px;
        margin-bottom: 30px;
        border-radius: 8px;
    }
    
    .arm-order-info h3 {
        margin: 0 0 15px 0;
        font-size: 18px;
        color: inherit;
        font-family: inherit;
    }
    
    .arm-product-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .arm-product-item {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .arm-product-item:last-child {
        border-bottom: none;
    }
    
    .arm-product-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 5px;
        margin-right: 15px;
    }
    
    .arm-product-name {
        flex: 1;
        font-size: 16px;
        font-weight: 600;
        color: #333;
    }
    
    .arm-review-button {
        display: inline-block;
        padding: 12px 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #ffffff;
        text-decoration: none;
        border-radius: 5px;
        font-weight: 600;
        font-size: 16px;
        transition: transform 0.2s;
    }
    
    .arm-review-button:hover {
        transform: translateY(-2px);
        color: #ffffff;
    }
    
    .arm-review-actions {
        text-align: center;
        margin-top: 30px;
    }
    
    .arm-thank-you {
        text-align: center;
        padding: 60px 20px;
    }
    
    .arm-thank-you-icon {
        font-size: 64px;
        margin-bottom: 20px;
    }
    
    .arm-thank-you h2 {
        font-size: 28px;
        margin-bottom: 15px;
        color: #333;
    }
    
    .arm-thank-you p {
        font-size: 16px;
        color: #666;
        margin-bottom: 30px;
    }
</style>

<div class="arm-review-page">
    <div class="arm-review-header">
        <h1>✨ Share Your Experience</h1>
        <p>We'd love to hear your thoughts on your recent order!</p>
    </div>
    
    <div class="arm-order-info">
        <h3>📦 Order #<?php echo esc_html($order->get_order_number()); ?></h3>
        <p style="margin: 0; color: #666;">Order Date: <?php echo esc_html($order->get_date_created()->date('F j, Y')); ?></p>
    </div>
    
    <?php if ($items && count($items) > 0): ?>
        <p style="font-size: 16px; margin-bottom: 30px; color: #555;">
            Please share your experience with each product below. Your honest feedback helps us improve and helps other customers make informed decisions.
        </p>

        <?php foreach ($items as $item): 
            $product = $item->get_product();
            if (!$product) continue;
            $product_id = $product->get_id();
            $image_id = $product->get_image_id();
            $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : wc_placeholder_img_src();
            
            // Check if customer already reviewed this product
            $existing_review = get_comments(array(
                'post_id' => $product_id,
                'author_email' => $email,
                'type' => 'review',
                'status' => 'approve',
                'number' => 1
            ));
        ?>
            <div class="arm-product-review-card" style="background: #fff; border: 2px solid #e0e0e0; border-radius: 12px; padding: 30px; margin-bottom: 30px;">
                <div style="display: flex; gap: 20px; margin-bottom: 20px; align-items: start;">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid #e0e0e0;">
                    <div style="flex: 1;">
                        <h3 style="margin: 0 0 10px 0; font-size: 22px; color: #333;">
                            <?php echo esc_html($product->get_name()); ?>
                        </h3>
                        <?php if ($existing_review): ?>
                            <div style="background: #e7f5e8; padding: 15px; border-radius: 8px; border-left: 4px solid #4caf50;">
                                <strong style="color: #2e7d32;">✅ You already reviewed this product</strong>
                                <p style="margin: 10px 0 0 0; color: #555;">Thank you for your feedback!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!$existing_review): ?>
                    <!-- Review Form -->
                    <form class="arm-inline-review-form" data-product-id="<?php echo $product_id; ?>" data-order-id="<?php echo $order->get_id(); ?>" data-customer-email="<?php echo esc_attr($email); ?>">
                        <?php wp_nonce_field('arm_submit_review_' . $product_id, 'arm_review_nonce'); ?>
                        <?php wp_nonce_field('arm_review_nonce_' . $product_id, 'arm_media_nonce'); ?>
                        
                        <!-- Star Rating -->
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 10px; font-size: 16px;">
                                Your Rating: <span class="required" style="color: #f44336;">*</span>
                            </label>
                            <div class="arm-star-rating" style="font-size: 32px; color: #ddd; cursor: pointer;">
                                <span class="arm-star" data-rating="1">★</span>
                                <span class="arm-star" data-rating="2">★</span>
                                <span class="arm-star" data-rating="3">★</span>
                                <span class="arm-star" data-rating="4">★</span>
                                <span class="arm-star" data-rating="5">★</span>
                            </div>
                            <input type="hidden" name="rating" class="arm-rating-input" value="" required>
                        </div>

                        <!-- Review Title -->
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 10px; font-size: 16px;">
                                Review Title: <span class="required" style="color: #f44336;">*</span>
                            </label>
                            <input type="text" name="review_title" class="arm-review-title" placeholder="Sum up your experience in one line" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px;">
                        </div>

                        <!-- Review Text -->
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 10px; font-size: 16px;">
                                Your Review: <span class="required" style="color: #f44336;">*</span>
                            </label>
                            <textarea name="review_text" class="arm-review-text" rows="5" placeholder="Tell us about your experience with this product..." required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; resize: vertical;"></textarea>
                        </div>

                        <?php
                        $settings = get_option('arm_settings');
                        if (!empty($settings['enable_photo_reviews'])):
                            $max_files = isset($settings['max_media_files']) ? intval($settings['max_media_files']) : 3;
                            $max_size = isset($settings['max_file_size']) ? intval($settings['max_file_size']) : 5;
                        ?>
                        <!-- Photo Upload (Optional) -->
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 10px; font-size: 16px;">
                                Add Photos (Optional)
                            </label>
                            <p style="font-size: 13px; color: #666; margin-bottom: 10px;">
                                Max <?php echo $max_files; ?> photos, <?php echo $max_size; ?>MB each. JPG or PNG only
                            </p>
                            <input type="file" class="arm-media-upload" accept="image/jpeg,image/jpg,image/png" multiple style="display: none;">
                            <button type="button" class="arm-upload-trigger" style="background: #f5f5f5; color: #333; padding: 12px 24px; border: 2px dashed #ccc; border-radius: 8px; font-size: 14px; cursor: pointer; transition: all 0.2s;">
                                📷 Choose Photos
                            </button>
                            <div class="arm-media-preview" style="margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap;"></div>
                            <div class="arm-upload-status" style="margin-top: 10px; font-size: 13px; color: #666;"></div>
                        </div>
                        <?php endif; ?>

                        <!-- Submit Button -->
                        <button type="submit" class="arm-submit-review-btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 40px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: transform 0.2s;">
                            Submit Review
                        </button>
                        <span class="arm-review-loading" style="display: none; margin-left: 15px; color: #666;">Submitting...</span>
                        <div class="arm-review-message" style="margin-top: 15px;"></div>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <style>
        .arm-star:hover,
        .arm-star.active {
            color: #ffc107 !important;
        }
        .arm-submit-review-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Star rating interaction
            $('.arm-star-rating').each(function() {
                var $rating = $(this);
                var $input = $rating.siblings('.arm-rating-input');
                
                $rating.find('.arm-star').on('mouseenter', function() {
                    var rating = $(this).data('rating');
                    $rating.find('.arm-star').each(function(i) {
                        if (i < rating) {
                            $(this).css('color', '#ffc107');
                        } else {
                            $(this).css('color', '#ddd');
                        }
                    });
                });
                
                $rating.on('mouseleave', function() {
                    var selectedRating = $input.val();
                    $rating.find('.arm-star').each(function(i) {
                        if (i < selectedRating) {
                            $(this).css('color', '#ffc107');
                        } else {
                            $(this).css('color', '#ddd');
                        }
                    });
                });
                
                $rating.find('.arm-star').on('click', function() {
                    var rating = $(this).data('rating');
                    $input.val(rating);
                    $rating.find('.arm-star').removeClass('active');
                    $rating.find('.arm-star').each(function(i) {
                        if (i < rating) {
                            $(this).addClass('active').css('color', '#ffc107');
                        }
                    });
                });
            });

            // Media upload handling
            $('.arm-upload-trigger').on('click', function() {
                $(this).siblings('.arm-media-upload').click();
            });
            
            var uploadedFiles = [];
            var pendingCommentId = null;
            
            $('.arm-media-upload').on('change', function(e) {
                var files = Array.from(e.target.files);
                var $preview = $(this).siblings('.arm-media-preview');
                var $status = $(this).siblings('.arm-upload-status');
                var $form = $(this).closest('.arm-inline-review-form');
                var maxFiles = <?php echo isset($max_files) ? $max_files : 3; ?>;
                var maxSize = <?php echo isset($max_size) ? $max_size : 5; ?> * 1024 * 1024;
                
                if (uploadedFiles.length + files.length > maxFiles) {
                    $status.html('<span style="color: #f44336;">❌ Max ' + maxFiles + ' photos allowed</span>');
                    return;
                }
                
                files.forEach(function(file, index) {
                    // Validate size
                    if (file.size > maxSize) {
                        $status.html('<span style="color: #f44336;">❌ ' + file.name + ' is too large</span>');
                        return;
                    }
                    
                    // Preview
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var $img = $('<div style="position: relative; width: 80px; height: 80px;"><img src="' + e.target.result + '" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;"><span class="remove-img" data-index="' + uploadedFiles.length + '" style="position: absolute; top: -8px; right: -8px; background: #f44336; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; font-weight: bold;">×</span></div>');
                        $preview.append($img);
                    };
                    reader.readAsDataURL(file);
                    
                    uploadedFiles.push(file);
                });
                
                $status.html('<span style="color: #4caf50;">✓ ' + uploadedFiles.length + ' photo(s) ready to upload</span>');
                e.target.value = ''; // Reset input
            });
            
            // Remove preview
            $(document).on('click', '.remove-img', function() {
                var index = $(this).data('index');
                uploadedFiles.splice(index, 1);
                $(this).parent().remove();
                
                // Update indices
                $('.remove-img').each(function(i) {
                    $(this).data('index', i);
                });
                
                var $form = $(this).closest('.arm-inline-review-form');
                $form.find('.arm-upload-status').html('<span style="color: #4caf50;">✓ ' + uploadedFiles.length + ' photo(s) ready</span>');
            });

            // Review form submission (with media upload)
            $('.arm-inline-review-form').on('submit', function(e) {
                e.preventDefault();
                
                var $form = $(this);
                var $btn = $form.find('.arm-submit-review-btn');
                var $loading = $form.find('.arm-review-loading');
                var $message = $form.find('.arm-review-message');
                
                var rating = $form.find('.arm-rating-input').val();
                if (!rating) {
                    $message.html('<div style="background: #fee; padding: 12px; border-radius: 6px; color: #c33;">Please select a star rating</div>');
                    return;
                }
                
                $btn.prop('disabled', true);
                $loading.show();
                $message.html('');
                
                $.ajax({
                    url: '<?php echo esc_url(admin_url("admin-ajax.php")); ?>',
                    type: 'POST',
                    data: {
                        action: 'arm_submit_inline_review',
                        product_id: $form.data('product-id'),
                        order_id: $form.data('order-id'),
                        customer_email: $form.data('customer-email'),
                        rating: rating,
                        review_title: $form.find('.arm-review-title').val(),
                        review_text: $form.find('.arm-review-text').val(),
                        nonce: $form.find('input[name="arm_review_nonce"]').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            // Upload media files if any
                            if (uploadedFiles.length > 0 && response.data.comment_id) {
                                uploadMediaFiles($form, response.data.comment_id, function() {
                                    showSuccessAndReload($form, $message);
                                });
                            } else {
                                showSuccessAndReload($form, $message);
                            }
                        } else {
                            $message.html('<div style="background: #fee; padding: 12px; border-radius: 6px; color: #c33;">' + (response.data || 'Error submitting review') + '</div>');
                            $btn.prop('disabled', false);
                            $loading.hide();
                        }
                    },
                    error: function() {
                        $message.html('<div style="background: #fee; padding: 12px; border-radius: 6px; color: #c33;">Network error. Please try again.</div>');
                        $btn.prop('disabled', false);
                        $loading.hide();
                    }
                });
            });
            
            // Helper function to upload media files
            function uploadMediaFiles($form, commentId, callback) {
                var productId = $form.data('product-id');
                var nonce = $form.find('input[name="arm_media_nonce"]').val();
                var uploaded = 0;
                var total = uploadedFiles.length;
                
                console.log('ARM DEBUG - Starting upload for comment:', commentId);
                console.log('ARM DEBUG - Product ID:', productId);
                console.log('ARM DEBUG - Nonce:', nonce);
                console.log('ARM DEBUG - Total files:', total);
                
                $form.find('.arm-upload-status').html('<span style="color: #2196F3;">⏳ Uploading ' + uploaded + '/' + total + ' photos...</span>');
                
                uploadedFiles.forEach(function(file, index) {
                    var formData = new FormData();
                    formData.append('action', 'arm_upload_review_media');
                    formData.append('comment_id', commentId);
                    formData.append('product_id', productId);
                    formData.append('nonce', nonce);
                    formData.append('media_file', file);
                    
                    console.log('ARM DEBUG - Uploading file', index + 1, ':', file.name);
                    
                    $.ajax({
                        url: '<?php echo esc_url(admin_url("admin-ajax.php")); ?>',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            console.log('ARM DEBUG - Upload response:', response);
                            uploaded++;
                            $form.find('.arm-upload-status').html('<span style="color: #2196F3;">⏳ Uploading ' + uploaded + '/' + total + ' photos...</span>');
                            if (uploaded === total) {
                                callback();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('ARM DEBUG - Upload error:', error, xhr.responseText);
                            uploaded++;
                            if (uploaded === total) {
                                callback();
                            }
                        }
                    });
                });
            }
            
            // Helper function to show success and reload
            function showSuccessAndReload($form, $message) {
                $message.html('<div style="background: #e7f5e8; padding: 15px; border-radius: 8px; border-left: 4px solid #4caf50; color: #2e7d32;"><strong>✅ Review submitted successfully!</strong><br>Thank you for your feedback.</div>');
                $form.slideUp();
                uploadedFiles = [];
                setTimeout(function() {
                    location.reload();
                }, 2000);
            }
        });
        </script>

    <?php else: ?>
        <div class="arm-review-actions">
            <p>No products found in this order.</p>
        </div>
    <?php endif; ?>
    
    <div class="arm-review-actions" style="margin-top: 40px; text-align: center;">
        <p style="color: #999; font-size: 14px;">
            🔒 Your information is secure • Your feedback helps us improve
        </p>
    </div>
</div>
</div><!-- .arm-review-container -->
</div><!-- .arm-review-wrapper -->

<?php
get_footer();

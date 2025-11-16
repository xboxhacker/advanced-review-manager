<?php
/**
 * Email Template Editor
 * File: templates/email-template.php
 */
if (!defined('ABSPATH')) exit;

// Force fresh settings retrieval
$settings = get_option('arm_settings', array());

// Debug: Show current settings
if (isset($_GET['debug_settings'])) {
    echo '<pre style="background: #f0f0f0; padding: 10px; margin: 20px;">';
    echo 'Current Settings from Database:' . "\n";
    print_r($settings);
    echo '</pre>';
}

// Default values for email template
$defaults = array(
    'email_subject' => "We'd love your feedback on your recent order!",
    'email_heading' => 'How was your experience?',
    'email_message' => "Hi {customer_name},\n\nThank you for your recent purchase from {store_name}! We hope you're enjoying your new items.\n\nWe'd love to hear about your experience. Your feedback helps us improve and helps other customers make informed decisions.\n\nCould you take a moment to share your thoughts?",
    'button_text' => 'Leave a Review',
    'button_color' => '#667eea'
);

// Merge with defaults
$settings = wp_parse_args($settings, $defaults);
?>

<div class="arm-wrapper">
    <div class="arm-header">
        <h1>
            <span class="arm-icon">✉️</span>
            Email Template Editor
        </h1>
        <p class="arm-subtitle">Customize your review reminder emails with A/B testing variants</p>
        
        <!-- Settings Reload Notice -->
        <?php if (isset($_GET['settings_saved'])): ?>
            <div class="notice notice-success" style="margin: 20px 0; padding: 15px;">
                <strong>✅ Settings saved successfully!</strong> Page reloaded with fresh data.
                <?php if (empty($settings['email_subject'])): ?>
                    <br><span style="color: #d63638;">⚠️ Warning: Email subject is empty in database. Please set it below.</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tab Navigation -->
    <div class="arm-tabs">
        <button class="arm-tab-btn active" data-tab="template-a">Template A (Default)</button>
        <button class="arm-tab-btn" data-tab="template-b">Template B (Variant)</button>
        <button class="arm-tab-btn" data-tab="template-c">Template C (Variant)</button>
        <button class="arm-tab-btn" data-tab="followup">Follow-up Templates</button>
    </div>

    <!-- Template A -->
    <div class="arm-tab-content active" id="template-a">
        <div class="arm-grid arm-grid-2col">
            <div>
                <form id="arm-email-form-a" class="arm-form arm-email-form" data-variant="a">
                    <?php wp_nonce_field('arm_nonce', 'arm_nonce_field'); ?>
                    
                    <div class="arm-card">
                        <div class="arm-card-header">
                            <h2>📝 Email Content - Template A</h2>
                        </div>
                        <div class="arm-card-body">
                            <div class="arm-form-group">
                                <label for="email_subject_a">Email Subject</label>
                                <input type="text" id="email_subject_a" name="email_subject" value="<?php echo esc_attr($settings['email_subject']); ?>" class="arm-input-wide" required>
                            </div>

                            <div class="arm-form-group">
                                <label for="email_heading_a">Email Heading</label>
                                <input type="text" id="email_heading_a" name="email_heading" value="<?php echo esc_attr($settings['email_heading']); ?>" class="arm-input-wide" required>
                            </div>

                            <div class="arm-form-group">
                                <label for="email_message_a">Email Message</label>
                                <?php
                                $email_message_content = isset($settings['email_message']) ? $settings['email_message'] : '';
                                wp_editor(
                                    $email_message_content,
                                    'email_message_a',
                                    array(
                                        'textarea_name' => 'email_message',
                                        'textarea_rows' => 10,
                                        'media_buttons' => true,
                                        'teeny' => false,
                                        'tinymce' => array(
                                            'toolbar1' => 'formatselect,bold,italic,underline,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,forecolor,backcolor',
                                            'toolbar2' => 'undo,redo,removeformat,code'
                                        ),
                                        'quicktags' => true
                                    )
                                );
                                ?>
                                <p class="arm-field-description">Available variables: {customer_name}, {order_id}, {product_names}, {store_name}</p>
                            </div>

                            <div class="arm-form-group">
                                <label for="button_text_a">Button Text</label>
                                <input type="text" id="button_text_a" name="button_text" value="<?php echo esc_attr($settings['button_text']); ?>" required>
                            </div>

                            <div class="arm-form-group">
                                <label for="button_color_a">Button Color</label>
                                <input type="text" id="button_color_a" name="button_color" value="<?php echo esc_attr($settings['button_color']); ?>" class="arm-color-picker">
                            </div>

                            <div class="arm-form-group">
                                <label class="arm-toggle-label">
                                    <input type="checkbox" name="show_incentive_a" <?php checked(isset($settings['show_incentive_a']) && $settings['show_incentive_a'], true); ?>>
                                    <span class="arm-toggle-slider"></span>
                                    <span class="arm-toggle-text">Show Incentive Message in Email</span>
                                </label>
                            </div>

                            <div class="arm-form-group">
                                <label for="incentive_message_a">Incentive Message</label>
                                <textarea id="incentive_message_a" name="incentive_message" rows="2" class="arm-input-wide"><?php echo esc_textarea(isset($settings['incentive_message_a']) ? $settings['incentive_message_a'] : 'Leave a review and get 10% off your next purchase!'); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="arm-card">
                        <div class="arm-card-header">
                            <h2>🧪 Test Email</h2>
                        </div>
                        <div class="arm-card-body">
                            <div class="arm-form-group">
                                <label for="test_email_a">Send Test To</label>
                                <input type="email" id="test_email_a" name="test_email" placeholder="your@email.com">
                            </div>
                            <button type="button" class="arm-btn arm-btn-secondary arm-send-test" data-variant="a">
                                <span class="arm-btn-icon">🚀</span>
                                Send Test Email
                            </button>
                        </div>
                    </div>

                    <div class="arm-form-actions">
                        <button type="submit" class="arm-btn arm-btn-primary">
                            <span class="arm-btn-icon">💾</span>
                            Save Template A
                        </button>
                        <button type="button" class="arm-btn arm-btn-secondary arm-reset-template" data-variant="a" style="margin-left: 10px;">
                            <span class="arm-btn-icon">🔄</span>
                            Reset to Default
                        </button>
                    </div>
                </form>
            </div>

            <div>
                <div class="arm-card arm-sticky">
                    <div class="arm-card-header">
                        <h2>👁️ Live Preview</h2>
                    </div>
                    <div class="arm-card-body">
                        <div id="arm-email-preview-a" class="arm-email-preview">
                            <div class="arm-preview-header" data-field="email_heading_a">
                                <?php echo esc_html($settings['email_heading']); ?>
                            </div>
                            <div class="arm-preview-body">
                                <p data-field="email_message_a"><?php echo nl2br(esc_html($settings['email_message'])); ?></p>
                                <div class="arm-preview-incentive" data-field="incentive_message_a" style="display: <?php echo isset($settings['show_incentive_a']) && $settings['show_incentive_a'] ? 'block' : 'none'; ?>;">
                                    🎁 <?php echo esc_html(isset($settings['incentive_message_a']) ? $settings['incentive_message_a'] : 'Leave a review and get 10% off your next purchase!'); ?>
                                </div>
                                <button class="arm-preview-button" data-field="button_text_a" data-color="button_color_a" style="background-color: <?php echo esc_attr($settings['button_color']); ?>">
                                    <?php echo esc_html($settings['button_text']); ?>
                                </button>
                                <div class="arm-preview-stars">★★★★★</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template B (A/B Test Variant) -->
    <div class="arm-tab-content" id="template-b">
        <div class="arm-grid arm-grid-2col">
            <div>
                <form id="arm-email-form-b" class="arm-form arm-email-form" data-variant="b">
                    <?php wp_nonce_field('arm_nonce', 'arm_nonce_field'); ?>
                    
                    <div class="arm-card">
                        <div class="arm-card-header">
                            <h2>📝 Email Content - Template B (Variant)</h2>
                        </div>
                        <div class="arm-card-body">
                            <div class="arm-alert arm-alert-info">
                                <strong>💡 A/B Testing:</strong> This variant will be sent to 50% of customers when A/B testing is enabled.
                            </div>

                            <div class="arm-form-group">
                                <label for="email_subject_b">Email Subject</label>
                                <input type="text" id="email_subject_b" name="email_subject_b" value="<?php echo esc_attr(isset($settings['email_subject_b']) ? $settings['email_subject_b'] : 'Quick question about your order...'); ?>" class="arm-input-wide">
                            </div>

                            <div class="arm-form-group">
                                <label for="email_heading_b">Email Heading</label>
                                <input type="text" id="email_heading_b" name="email_heading_b" value="<?php echo esc_attr(isset($settings['email_heading_b']) ? $settings['email_heading_b'] : 'We Value Your Opinion!'); ?>" class="arm-input-wide">
                            </div>

                            <div class="arm-form-group">
                                <label for="email_message_b">Email Message</label>
                                <?php
                                $email_message_b_content = isset($settings['email_message_b']) ? $settings['email_message_b'] : "Hi {customer_name},\n\nHow's everything going with your recent order? We'd love to hear about your experience!\n\nYour feedback helps us serve you better.";
                                wp_editor(
                                    $email_message_b_content,
                                    'email_message_b',
                                    array(
                                        'textarea_name' => 'email_message_b',
                                        'textarea_rows' => 10,
                                        'media_buttons' => true,
                                        'teeny' => false,
                                        'tinymce' => array(
                                            'toolbar1' => 'formatselect,bold,italic,underline,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,forecolor,backcolor',
                                            'toolbar2' => 'undo,redo,removeformat,code'
                                        ),
                                        'quicktags' => true
                                    )
                                );
                                ?>
                                <p class="arm-field-description">Available variables: {customer_name}, {order_id}, {product_names}, {store_name}</p>
                            </div>

                            <div class="arm-form-group">
                                <label for="button_text_b">Button Text</label>
                                <input type="text" id="button_text_b" name="button_text_b" value="<?php echo esc_attr(isset($settings['button_text_b']) ? $settings['button_text_b'] : 'Share Your Thoughts'); ?>">
                            </div>

                            <div class="arm-form-group">
                                <label for="button_color_b">Button Color</label>
                                <input type="text" id="button_color_b" name="button_color_b" value="<?php echo esc_attr(isset($settings['button_color_b']) ? $settings['button_color_b'] : '#4CAF50'); ?>" class="arm-color-picker">
                            </div>
                        </div>
                    </div>

                    <div class="arm-form-actions">
                        <button type="submit" class="arm-btn arm-btn-primary">
                            <span class="arm-btn-icon">💾</span>
                            Save Template B
                        </button>
                        <button type="button" class="arm-btn arm-btn-secondary arm-reset-template" data-variant="b" style="margin-left: 10px;">
                            <span class="arm-btn-icon">🔄</span>
                            Reset to Default
                        </button>
                    </div>
                </form>
            </div>

            <div>
                <div class="arm-card arm-sticky">
                    <div class="arm-card-header">
                        <h2>👁️ Live Preview</h2>
                    </div>
                    <div class="arm-card-body">
                        <div id="arm-email-preview-b" class="arm-email-preview">
                            <div class="arm-preview-header" data-field="email_heading_b">
                                <?php echo esc_html(isset($settings['email_heading_b']) ? $settings['email_heading_b'] : 'We Value Your Opinion!'); ?>
                            </div>
                            <div class="arm-preview-body">
                                <p data-field="email_message_b"><?php echo nl2br(esc_html(isset($settings['email_message_b']) ? $settings['email_message_b'] : 'Hi {customer_name},\n\nHow\'s everything going with your recent order?')); ?></p>
                                <button class="arm-preview-button" data-field="button_text_b" data-color="button_color_b" style="background-color: <?php echo esc_attr(isset($settings['button_color_b']) ? $settings['button_color_b'] : '#4CAF50'); ?>">
                                    <?php echo esc_html(isset($settings['button_text_b']) ? $settings['button_text_b'] : 'Share Your Thoughts'); ?>
                                </button>
                                <div class="arm-preview-stars">★★★★★</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template C -->
    <div class="arm-tab-content" id="template-c">
        <div class="arm-grid arm-grid-2col">
            <div>
                <form id="arm-email-form-c" class="arm-form arm-email-form" data-variant="c">
                    <?php wp_nonce_field('arm_nonce', 'arm_nonce_field'); ?>
                    
                    <div class="arm-card">
                        <div class="arm-card-header">
                            <h2>📝 Email Content - Template C (Variant 2)</h2>
                        </div>
                        <div class="arm-card-body">
                            <div class="arm-form-group">
                                <label for="email_subject_c">Email Subject</label>
                                <input type="text" id="email_subject_c" name="email_subject_c" value="<?php echo esc_attr(isset($settings['email_subject_c']) ? $settings['email_subject_c'] : '🌟 Your opinion matters to us!'); ?>" class="arm-input-wide">
                            </div>

                            <div class="arm-form-group">
                                <label for="email_heading_c">Email Heading</label>
                                <input type="text" id="email_heading_c" name="email_heading_c" value="<?php echo esc_attr(isset($settings['email_heading_c']) ? $settings['email_heading_c'] : 'Tell Us What You Think!'); ?>" class="arm-input-wide">
                            </div>

                            <div class="arm-form-group">
                                <label for="email_message_c">Email Message</label>
                                <?php
                                $email_message_c_content = isset($settings['email_message_c']) ? $settings['email_message_c'] : "Hey {customer_name}! 👋\n\nWe hope you're loving your recent purchase. Got a minute to share your thoughts?\n\nYour review helps other shoppers make confident decisions!";
                                wp_editor(
                                    $email_message_c_content,
                                    'email_message_c',
                                    array(
                                        'textarea_name' => 'email_message_c',
                                        'textarea_rows' => 10,
                                        'media_buttons' => true,
                                        'teeny' => false,
                                        'tinymce' => array(
                                            'toolbar1' => 'formatselect,bold,italic,underline,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,forecolor,backcolor',
                                            'toolbar2' => 'undo,redo,removeformat,code'
                                        ),
                                        'quicktags' => true
                                    )
                                );
                                ?>
                                <p class="arm-field-description">Available variables: {customer_name}, {order_id}, {product_names}, {store_name}</p>
                            </div>

                            <div class="arm-form-group">
                                <label for="button_text_c">Button Text</label>
                                <input type="text" id="button_text_c" name="button_text_c" value="<?php echo esc_attr(isset($settings['button_text_c']) ? $settings['button_text_c'] : 'Write a Review'); ?>">
                            </div>

                            <div class="arm-form-group">
                                <label for="button_color_c">Button Color</label>
                                <input type="text" id="button_color_c" name="button_color_c" value="<?php echo esc_attr(isset($settings['button_color_c']) ? $settings['button_color_c'] : '#9C27B0'); ?>" class="arm-color-picker">
                            </div>
                        </div>
                    </div>

                    <div class="arm-form-actions">
                        <button type="submit" class="arm-btn arm-btn-primary">
                            <span class="arm-btn-icon">💾</span>
                            Save Template C
                        </button>
                        <button type="button" class="arm-btn arm-btn-secondary arm-reset-template" data-variant="c" style="margin-left: 10px;">
                            <span class="arm-btn-icon">🔄</span>
                            Reset to Default
                        </button>
                    </div>
                </form>
            </div>

            <div>
                <div class="arm-card arm-sticky">
                    <div class="arm-card-header">
                        <h2>👁️ Live Preview</h2>
                    </div>
                    <div class="arm-card-body">
                        <div id="arm-email-preview-c" class="arm-email-preview">
                            <div class="arm-preview-header" data-field="email_heading_c">
                                <?php echo esc_html(isset($settings['email_heading_c']) ? $settings['email_heading_c'] : 'Tell Us What You Think!'); ?>
                            </div>
                            <div class="arm-preview-body">
                                <p data-field="email_message_c"><?php echo nl2br(esc_html(isset($settings['email_message_c']) ? $settings['email_message_c'] : 'Hey {customer_name}! 👋')); ?></p>
                                <button class="arm-preview-button" data-field="button_text_c" data-color="button_color_c" style="background-color: <?php echo esc_attr(isset($settings['button_color_c']) ? $settings['button_color_c'] : '#9C27B0'); ?>">
                                    <?php echo esc_html(isset($settings['button_text_c']) ? $settings['button_text_c'] : 'Write a Review'); ?>
                                </button>
                                <div class="arm-preview-stars">★★★★★</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Follow-up Templates -->
    <div class="arm-tab-content" id="followup">
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>📬 Follow-up Email Templates</h2>
            </div>
            <div class="arm-card-body">
                <p class="arm-field-description">Create different messages for follow-up reminders to customers who haven't responded.</p>
                
                <form id="arm-followup-form" class="arm-form">
                    <?php wp_nonce_field('arm_nonce', 'arm_nonce_field'); ?>
                    
                    <h3>Follow-up #1 (First Reminder)</h3>
                    <div class="arm-form-group">
                        <label for="followup1_subject">Email Subject</label>
                        <input type="text" id="followup1_subject" name="followup1_subject" value="<?php echo esc_attr(isset($settings['followup1_subject']) ? $settings['followup1_subject'] : 'Still waiting to hear from you...'); ?>" class="arm-input-wide">
                    </div>

                    <div class="arm-form-group">
                        <label for="followup1_message">Email Message</label>
                        <?php
                        wp_editor(
                            isset($settings['followup1_message']) ? $settings['followup1_message'] : 'Hi {customer_name},\n\nWe noticed you haven\'t left a review yet. Your feedback is incredibly valuable to us!\n\nIt only takes a minute and helps us improve.',
                            'followup1_message',
                            array(
                                'textarea_name' => 'followup1_message',
                                'textarea_rows' => 8,
                                'media_buttons' => true,
                                'teeny' => false,
                                'tinymce' => array(
                                    'toolbar1' => 'formatselect,bold,italic,underline,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,forecolor,backcolor',
                                    'toolbar2' => 'undo,redo,removeformat,code'
                                ),
                                'quicktags' => true
                            )
                        );
                        ?>
                        <p class="arm-field-description">Available variables: {customer_name}, {order_id}, {product_names}, {store_name}</p>
                    </div>

                    <hr style="margin: 30px 0;">

                    <h3>Follow-up #2 (Second Reminder)</h3>
                    <div class="arm-form-group">
                        <label for="followup2_subject">Email Subject</label>
                        <input type="text" id="followup2_subject" name="followup2_subject" value="<?php echo esc_attr(isset($settings['followup2_subject']) ? $settings['followup2_subject'] : 'Last chance - Share your feedback!'); ?>" class="arm-input-wide">
                    </div>

                    <div class="arm-form-group">
                        <label for="followup2_message">Email Message</label>
                        <?php
                        wp_editor(
                            isset($settings['followup2_message']) ? $settings['followup2_message'] : 'Hi {customer_name},\n\nThis is our final request for your review. We really want to hear your thoughts!\n\nAs a thank you, we\'re offering a special discount on your next order.',
                            'followup2_message',
                            array(
                                'textarea_name' => 'followup2_message',
                                'textarea_rows' => 8,
                                'media_buttons' => true,
                                'teeny' => false,
                                'tinymce' => array(
                                    'toolbar1' => 'formatselect,bold,italic,underline,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,forecolor,backcolor',
                                    'toolbar2' => 'undo,redo,removeformat,code'
                                ),
                                'quicktags' => true
                            )
                        );
                        ?>
                        <p class="arm-field-description">Available variables: {customer_name}, {order_id}, {product_names}, {store_name}</p>
                    </div>

                    <div class="arm-form-actions">
                        <button type="submit" class="arm-btn arm-btn-primary">
                            <span class="arm-btn-icon">💾</span>
                            Save Follow-up Templates
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- A/B Test Results -->
    <div class="arm-card" style="margin-top: 30px;">
        <div class="arm-card-header">
            <h2>📊 A/B Test Performance</h2>
        </div>
        <div class="arm-card-body">
            <div id="arm-ab-test-results">
                <div class="arm-stats-grid">
                    <div class="arm-stat-card">
                        <div class="arm-stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <span>A</span>
                        </div>
                        <div class="arm-stat-content">
                            <h3 id="template-a-rate">--%</h3>
                            <p>Template A Conversion</p>
                        </div>
                    </div>

                    <div class="arm-stat-card">
                        <div class="arm-stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <span>B</span>
                        </div>
                        <div class="arm-stat-content">
                            <h3 id="template-b-rate">--%</h3>
                            <p>Template B Conversion</p>
                        </div>
                    </div>

                    <div class="arm-stat-card">
                        <div class="arm-stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <span>C</span>
                        </div>
                        <div class="arm-stat-content">
                            <h3 id="template-c-rate">--%</h3>
                            <p>Template C Conversion</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.arm-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    border-bottom: 2px solid #e8ecf1;
    flex-wrap: wrap;
}

.arm-tab-btn {
    padding: 12px 24px;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-weight: 600;
    color: #666;
    transition: all 0.3s ease;
}

.arm-tab-btn:hover {
    color: #667eea;
}

.arm-tab-btn.active {
    color: #667eea;
    border-bottom-color: #667eea;
}

.arm-tab-content {
    display: none;
}

.arm-tab-content.active {
    display: block;
}

.arm-alert-info {
    background: #d1ecf1;
    border-left: 4px solid #0c5460;
    color: #0c5460;
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.arm-preview-incentive {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
    text-align: center;
    font-weight: 600;
    color: #856404;
}
</style>

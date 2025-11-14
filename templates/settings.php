<?php
/**
 * Settings Template
 * File: templates/settings.php
 */
if (!defined('ABSPATH')) exit;

$settings = get_option('arm_settings');
?>

<div class="arm-wrapper">
    <div class="arm-header">
        <h1>
            <span class="arm-icon">⚙️</span>
            Review Manager Settings
        </h1>
        <p class="arm-subtitle">Configure your advanced review management system</p>
    </div>

    <form id="arm-settings-form" class="arm-form">
        <?php wp_nonce_field('arm_nonce', 'arm_nonce_field'); ?>
        
        <!-- Basic Reminder Settings -->
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>📅 Email Reminder Settings</h2>
            </div>
            <div class="arm-card-body">
                <div class="arm-form-group">
                    <label class="arm-toggle-label">
                        <input type="checkbox" name="enable_reminders" <?php checked($settings['enable_reminders'], true); ?>>
                        <span class="arm-toggle-slider"></span>
                        <span class="arm-toggle-text">Enable Automatic Email Review Reminders</span>
                    </label>
                    <p class="arm-field-description">Automatically send review requests to customers after order completion</p>
                </div>

                <div class="arm-form-group">
                    <label for="reminder_days">Days After Order Completion</label>
                    <div class="arm-input-group">
                        <input type="number" id="reminder_days" name="reminder_days" value="<?php echo esc_attr($settings['reminder_days']); ?>" min="1" max="90" required>
                        <span class="arm-input-suffix">days</span>
                    </div>
                    <p class="arm-field-description">Number of days to wait before sending the first review reminder</p>
                </div>
            </div>
        </div>

        <!-- SMS Settings -->
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>📱 SMS Integration</h2>
            </div>
            <div class="arm-card-body">
                <div class="arm-form-group">
                    <label class="arm-toggle-label">
                        <input type="checkbox" name="enable_sms" <?php checked(isset($settings['enable_sms']) && $settings['enable_sms'], true); ?>>
                        <span class="arm-toggle-slider"></span>
                        <span class="arm-toggle-text">Enable SMS Review Reminders</span>
                    </label>
                    <p class="arm-field-description">Send review requests via SMS (requires Twilio account)</p>
                </div>

                <div class="arm-form-group">
                    <label for="twilio_account_sid">Twilio Account SID</label>
                    <input type="text" id="twilio_account_sid" name="twilio_account_sid" value="<?php echo esc_attr(isset($settings['twilio_account_sid']) ? $settings['twilio_account_sid'] : ''); ?>" class="arm-input-wide">
                </div>

                <div class="arm-form-group">
                    <label for="twilio_auth_token">Twilio Auth Token</label>
                    <input type="password" id="twilio_auth_token" name="twilio_auth_token" value="<?php echo esc_attr(isset($settings['twilio_auth_token']) ? $settings['twilio_auth_token'] : ''); ?>" class="arm-input-wide">
                </div>

                <div class="arm-form-group">
                    <label for="twilio_phone_number">Twilio Phone Number</label>
                    <input type="text" id="twilio_phone_number" name="twilio_phone_number" value="<?php echo esc_attr(isset($settings['twilio_phone_number']) ? $settings['twilio_phone_number'] : ''); ?>" placeholder="+1234567890">
                </div>

                <div class="arm-form-group">
                    <label for="sms_message">SMS Message Template</label>
                    <textarea id="sms_message" name="sms_message" rows="3" class="arm-input-wide"><?php echo esc_textarea(isset($settings['sms_message']) ? $settings['sms_message'] : 'Hi {customer_name}! Thanks for your order. We\'d love your feedback: {review_url}'); ?></textarea>
                    <p class="arm-field-description">Available variables: {customer_name}, {review_url}, {store_name}</p>
                </div>
            </div>
        </div>

        <!-- Multi-Product Reviews -->
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>🛍️ Multi-Product Review Settings</h2>
            </div>
            <div class="arm-card-body">
                <div class="arm-form-group">
                    <label class="arm-toggle-label">
                        <input type="checkbox" name="enable_multi_product" <?php checked(isset($settings['enable_multi_product']) && $settings['enable_multi_product'], true); ?>>
                        <span class="arm-toggle-slider"></span>
                        <span class="arm-toggle-text">Enable Per-Product Review Requests</span>
                    </label>
                    <p class="arm-field-description">Ask customers to review each product in their order individually</p>
                </div>

                <div class="arm-form-group">
                    <label for="max_products_per_email">Maximum Products Per Email</label>
                    <input type="number" id="max_products_per_email" name="max_products_per_email" value="<?php echo esc_attr(isset($settings['max_products_per_email']) ? $settings['max_products_per_email'] : 5); ?>" min="1" max="20">
                    <p class="arm-field-description">For orders with many items, limit products shown in email</p>
                </div>
            </div>
        </div>

        <!-- Photo/Video Reviews -->
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>📸 Photo & Video Reviews</h2>
            </div>
            <div class="arm-card-body">
                <div class="arm-form-group">
                    <label class="arm-toggle-label">
                        <input type="checkbox" name="enable_photo_reviews" <?php checked(isset($settings['enable_photo_reviews']) && $settings['enable_photo_reviews'], true); ?>>
                        <span class="arm-toggle-slider"></span>
                        <span class="arm-toggle-text">Enable Photo Uploads</span>
                    </label>
                </div>

                <div class="arm-form-group">
                    <label class="arm-toggle-label">
                        <input type="checkbox" name="enable_video_reviews" <?php checked(isset($settings['enable_video_reviews']) && $settings['enable_video_reviews'], true); ?>>
                        <span class="arm-toggle-slider"></span>
                        <span class="arm-toggle-text">Enable Video Uploads</span>
                    </label>
                </div>

                <div class="arm-form-group">
                    <label for="max_media_files">Maximum Media Files Per Review</label>
                    <input type="number" id="max_media_files" name="max_media_files" value="<?php echo esc_attr(isset($settings['max_media_files']) ? $settings['max_media_files'] : 5); ?>" min="1" max="10">
                </div>

                <div class="arm-form-group">
                    <label for="max_file_size">Maximum File Size (MB)</label>
                    <input type="number" id="max_file_size" name="max_file_size" value="<?php echo esc_attr(isset($settings['max_file_size']) ? $settings['max_file_size'] : 10); ?>" min="1" max="50">
                </div>
            </div>
        </div>

        <!-- Incentives & Rewards -->
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>🎁 Review Incentives & Rewards</h2>
            </div>
            <div class="arm-card-body">
                <div class="arm-form-group">
                    <label class="arm-toggle-label">
                        <input type="checkbox" name="enable_incentives" <?php checked(isset($settings['enable_incentives']) && $settings['enable_incentives'], true); ?>>
                        <span class="arm-toggle-slider"></span>
                        <span class="arm-toggle-text">Enable Review Incentives</span>
                    </label>
                    <p class="arm-field-description">Reward customers for leaving reviews</p>
                </div>

                <div class="arm-form-group">
                    <label for="incentive_type">Incentive Type</label>
                    <select id="incentive_type" name="incentive_type">
                        <option value="coupon" <?php selected(isset($settings['incentive_type']) ? $settings['incentive_type'] : 'coupon', 'coupon'); ?>>Discount Coupon</option>
                        <option value="points" <?php selected(isset($settings['incentive_type']) ? $settings['incentive_type'] : 'coupon', 'points'); ?>>Loyalty Points</option>
                        <option value="free_shipping" <?php selected(isset($settings['incentive_type']) ? $settings['incentive_type'] : 'coupon', 'free_shipping'); ?>>Free Shipping</option>
                    </select>
                </div>

                <div class="arm-form-group">
                    <label for="coupon_amount">Coupon Discount Amount</label>
                    <div class="arm-input-group">
                        <input type="number" id="coupon_amount" name="coupon_amount" value="<?php echo esc_attr(isset($settings['coupon_amount']) ? $settings['coupon_amount'] : 10); ?>" min="1">
                        <select name="coupon_type" style="width: auto; margin-left: 10px;">
                            <option value="percent" <?php selected(isset($settings['coupon_type']) ? $settings['coupon_type'] : 'percent', 'percent'); ?>>%</option>
                            <option value="fixed" <?php selected(isset($settings['coupon_type']) ? $settings['coupon_type'] : 'percent', 'fixed'); ?>>$</option>
                        </select>
                    </div>
                </div>

                <div class="arm-form-group">
                    <label for="coupon_expiry_days">Coupon Expiry (Days)</label>
                    <input type="number" id="coupon_expiry_days" name="coupon_expiry_days" value="<?php echo esc_attr(isset($settings['coupon_expiry_days']) ? $settings['coupon_expiry_days'] : 30); ?>" min="1">
                </div>

                <div class="arm-form-group">
                    <label for="points_amount">Points to Award</label>
                    <input type="number" id="points_amount" name="points_amount" value="<?php echo esc_attr(isset($settings['points_amount']) ? $settings['points_amount'] : 100); ?>" min="1">
                    <p class="arm-field-description">Requires WooCommerce Points & Rewards plugin</p>
                </div>
            </div>
        </div>

        <!-- A/B Testing -->
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>🔬 A/B Testing</h2>
            </div>
            <div class="arm-card-body">
                <div class="arm-form-group">
                    <label class="arm-toggle-label">
                        <input type="checkbox" name="enable_ab_testing" <?php checked(isset($settings['enable_ab_testing']) && $settings['enable_ab_testing'], true); ?>>
                        <span class="arm-toggle-slider"></span>
                        <span class="arm-toggle-text">Enable A/B Testing for Email Templates</span>
                    </label>
                    <p class="arm-field-description">Test multiple email variants to optimize conversion rates</p>
                </div>
            </div>
        </div>

        <!-- Follow-up Sequences -->
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>📬 Automated Follow-up Sequences</h2>
            </div>
            <div class="arm-card-body">
                <div class="arm-form-group">
                    <label class="arm-toggle-label">
                        <input type="checkbox" name="enable_followup" <?php checked(isset($settings['enable_followup']) && $settings['enable_followup'], true); ?>>
                        <span class="arm-toggle-slider"></span>
                        <span class="arm-toggle-text">Enable Automated Follow-ups</span>
                    </label>
                    <p class="arm-field-description">Send multiple reminder emails if customer doesn't respond</p>
                </div>

                <div class="arm-form-group">
                    <label for="followup_count">Number of Follow-up Emails</label>
                    <input type="number" id="followup_count" name="followup_count" value="<?php echo esc_attr(isset($settings['followup_count']) ? $settings['followup_count'] : 2); ?>" min="1" max="5">
                </div>

                <div class="arm-form-group">
                    <label for="followup_interval">Days Between Follow-ups</label>
                    <input type="number" id="followup_interval" name="followup_interval" value="<?php echo esc_attr(isset($settings['followup_interval']) ? $settings['followup_interval'] : 7); ?>" min="1" max="30">
                </div>
            </div>
        </div>

        <!-- Review Moderation -->
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>🛡️ Review Moderation</h2>
            </div>
            <div class="arm-card-body">
                <div class="arm-form-group">
                    <label class="arm-toggle-label">
                        <input type="checkbox" name="enable_moderation" <?php checked(isset($settings['enable_moderation']) && $settings['enable_moderation'], true); ?>>
                        <span class="arm-toggle-slider"></span>
                        <span class="arm-toggle-text">Require Manual Approval for Reviews</span>
                    </label>
                </div>

                <div class="arm-form-group">
                    <label class="arm-toggle-label">
                        <input type="checkbox" name="enable_profanity_filter" <?php checked(isset($settings['enable_profanity_filter']) && $settings['enable_profanity_filter'], true); ?>>
                        <span class="arm-toggle-slider"></span>
                        <span class="arm-toggle-text">Enable Profanity Filter</span>
                    </label>
                </div>

                <div class="arm-form-group">
                    <label class="arm-toggle-label">
                        <input type="checkbox" name="enable_spam_detection" <?php checked(isset($settings['enable_spam_detection']) && $settings['enable_spam_detection'], true); ?>>
                        <span class="arm-toggle-slider"></span>
                        <span class="arm-toggle-text">Enable Spam Detection</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Review Gating -->
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>🚪 Review Gating (Use Ethically)</h2>
            </div>
            <div class="arm-card-body">
                <div class="arm-alert arm-alert-warning">
                    <strong>⚠️ Important:</strong> Review gating may violate platform policies. Use responsibly and in compliance with applicable laws.
                </div>
                
                <div class="arm-form-group">
                    <label class="arm-toggle-label">
                        <input type="checkbox" name="enable_review_gating" <?php checked(isset($settings['enable_review_gating']) && $settings['enable_review_gating'], true); ?>>
                        <span class="arm-toggle-slider"></span>
                        <span class="arm-toggle-text">Enable Review Gating</span>
                    </label>
                    <p class="arm-field-description">Route low-star reviews to support team before publication</p>
                </div>

                <div class="arm-form-group">
                    <label for="gating_threshold">Star Rating Threshold</label>
                    <select id="gating_threshold" name="gating_threshold">
                        <option value="3" <?php selected(isset($settings['gating_threshold']) ? $settings['gating_threshold'] : 3, 3); ?>>3 stars or below</option>
                        <option value="2" <?php selected(isset($settings['gating_threshold']) ? $settings['gating_threshold'] : 3, 2); ?>>2 stars or below</option>
                        <option value="1" <?php selected(isset($settings['gating_threshold']) ? $settings['gating_threshold'] : 3, 1); ?>>1 star only</option>
                    </select>
                </div>

                <div class="arm-form-group">
                    <label for="support_email">Support Team Email</label>
                    <input type="email" id="support_email" name="support_email" value="<?php echo esc_attr(isset($settings['support_email']) ? $settings['support_email'] : get_option('admin_email')); ?>" class="arm-input-wide">
                </div>
            </div>
        </div>

        <!-- Google Shopping -->
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>🛒 Google Shopping Integration</h2>
            </div>
            <div class="arm-card-body">
                <div class="arm-form-group">
                    <label class="arm-toggle-label">
                        <input type="checkbox" name="enable_google_shopping" <?php checked(isset($settings['enable_google_shopping']) && $settings['enable_google_shopping'], true); ?>>
                        <span class="arm-toggle-slider"></span>
                        <span class="arm-toggle-text">Submit Reviews to Google Merchant Center</span>
                    </label>
                </div>

                <div class="arm-form-group">
                    <label for="google_merchant_id">Google Merchant Center ID</label>
                    <input type="text" id="google_merchant_id" name="google_merchant_id" value="<?php echo esc_attr(isset($settings['google_merchant_id']) ? $settings['google_merchant_id'] : ''); ?>">
                </div>
            </div>
        </div>

        <!-- Social Proof Widgets -->
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>✨ Social Proof Widgets</h2>
            </div>
            <div class="arm-card-body">
                <div class="arm-form-group">
                    <label class="arm-toggle-label">
                        <input type="checkbox" name="enable_floating_widget" <?php checked(isset($settings['enable_floating_widget']) && $settings['enable_floating_widget'], true); ?>>
                        <span class="arm-toggle-slider"></span>
                        <span class="arm-toggle-text">Enable Floating Review Notification Widget</span>
                    </label>
                </div>

                <div class="arm-form-group">
                    <label class="arm-toggle-label">
                        <input type="checkbox" name="enable_review_badge" <?php checked(isset($settings['enable_review_badge']) && $settings['enable_review_badge'], true); ?>>
                        <span class="arm-toggle-slider"></span>
                        <span class="arm-toggle-text">Show Review Badges on Product Pages</span>
                    </label>
                </div>

                <div class="arm-form-group">
                    <label class="arm-toggle-label">
                        <input type="checkbox" name="enable_review_carousel" <?php checked(isset($settings['enable_review_carousel']) && $settings['enable_review_carousel'], true); ?>>
                        <span class="arm-toggle-slider"></span>
                        <span class="arm-toggle-text">Enable Review Carousel on Homepage</span>
                    </label>
                </div>

                <div class="arm-form-group">
                    <label for="widget_position">Floating Widget Position</label>
                    <select id="widget_position" name="widget_position">
                        <option value="bottom-left" <?php selected(isset($settings['widget_position']) ? $settings['widget_position'] : 'bottom-left', 'bottom-left'); ?>>Bottom Left</option>
                        <option value="bottom-right" <?php selected(isset($settings['widget_position']) ? $settings['widget_position'] : 'bottom-left', 'bottom-right'); ?>>Bottom Right</option>
                        <option value="top-left" <?php selected(isset($settings['widget_position']) ? $settings['widget_position'] : 'bottom-left', 'top-left'); ?>>Top Left</option>
                        <option value="top-right" <?php selected(isset($settings['widget_position']) ? $settings['widget_position'] : 'bottom-left', 'top-right'); ?>>Top Right</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Testing & Development -->
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>🎭 Testing & Development</h2>
            </div>
            <div class="arm-card-body">
                <div class="arm-alert arm-alert-warning">
                    <strong>⚠️ Important:</strong> Use these features ethically and in compliance with applicable laws and platform policies.
                </div>
                
                <div class="arm-form-group">
                    <label class="arm-toggle-label">
                        <input type="checkbox" name="fake_review_enabled" <?php checked($settings['fake_review_enabled'], true); ?>>
                        <span class="arm-toggle-slider"></span>
                        <span class="arm-toggle-text">Enable Fake Review Generation</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="arm-form-actions">
            <button type="submit" class="arm-btn arm-btn-primary">
                <span class="arm-btn-icon">💾</span>
                Save All Settings
            </button>
        </div>
    </form>
</div>

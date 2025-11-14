<?php
/**
 * Fake Reviews Template
 * File: templates/fake-reviews.php
 */
if (!defined('ABSPATH')) exit;
?>

<div class="arm-wrapper">
    <div class="arm-header">
        <h1>
            <span class="arm-icon">🎭</span>
            Review Generator
        </h1>
        <p class="arm-subtitle">Generate sample reviews for testing, seeding, or demonstrations</p>
    </div>

    <div class="arm-alert arm-alert-warning">
        <strong>⚠️ Ethical Use Only:</strong> This feature is intended for testing, demos, or initial seeding. Always comply with platform policies, FTC guidelines, and legal requirements when using generated reviews. Misleading consumers with fake reviews may violate laws and platform terms of service.
    </div>

    <div class="arm-grid">
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>✨ Single Review Generator</h2>
            </div>
            <div class="arm-card-body">
                <form id="arm-fake-review-form" class="arm-form">
                    <?php wp_nonce_field('arm_nonce', 'arm_nonce_field'); ?>
                    
                    <div class="arm-form-group">
                        <label for="product_id">Product</label>
                        <select id="product_id" name="product_id" required>
                            <option value="">Select a product...</option>
                            <?php
                            $products = wc_get_products(array('limit' => -1, 'status' => 'publish'));
                            foreach ($products as $product) {
                                echo '<option value="' . $product->get_id() . '">' . esc_html($product->get_name()) . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="arm-form-group">
                        <label for="rating">Rating</label>
                        <div class="arm-rating-selector">
                            <input type="radio" name="rating" value="5" id="rating-5" checked>
                            <label for="rating-5">⭐⭐⭐⭐⭐ (5 Stars)</label>
                            
                            <input type="radio" name="rating" value="4" id="rating-4">
                            <label for="rating-4">⭐⭐⭐⭐ (4 Stars)</label>
                            
                            <input type="radio" name="rating" value="3" id="rating-3">
                            <label for="rating-3">⭐⭐⭐ (3 Stars)</label>

                            <input type="radio" name="rating" value="2" id="rating-2">
                            <label for="rating-2">⭐⭐ (2 Stars)</label>

                            <input type="radio" name="rating" value="1" id="rating-1">
                            <label for="rating-1">⭐ (1 Star)</label>
                        </div>
                    </div>

                    <div class="arm-form-group">
                        <label for="author_name">Author Name (Optional)</label>
                        <input type="text" id="author_name" name="author_name" placeholder="Leave empty for random name">
                        <p class="arm-field-description">If empty, a random name will be generated</p>
                    </div>

                    <div class="arm-form-group">
                        <label for="review_text">Review Text (Optional)</label>
                        <textarea id="review_text" name="review_text" rows="4" placeholder="Leave empty for auto-generated review"></textarea>
                        <p class="arm-field-description">If empty, a review will be auto-generated based on rating</p>
                    </div>

                    <div class="arm-form-group">
                        <label class="arm-toggle-label">
                            <input type="checkbox" name="verified_purchase" id="verified_purchase">
                            <span class="arm-toggle-slider"></span>
                            <span class="arm-toggle-text">Mark as Verified Purchase</span>
                        </label>
                    </div>

                    <div class="arm-form-group">
                        <label class="arm-toggle-label">
                            <input type="checkbox" name="add_photos" id="add_photos">
                            <span class="arm-toggle-slider"></span>
                            <span class="arm-toggle-text">Add Random Product Photos</span>
                        </label>
                        <p class="arm-field-description">Attach random product images to make review more realistic</p>
                    </div>

                    <div class="arm-form-group">
                        <label for="review_date">Review Date (Optional)</label>
                        <input type="date" id="review_date" name="review_date" max="<?php echo date('Y-m-d'); ?>">
                        <p class="arm-field-description">Leave empty to use current date</p>
                    </div>

                    <div class="arm-form-actions">
                        <button type="submit" class="arm-btn arm-btn-primary">
                            <span class="arm-btn-icon">✨</span>
                            Generate Single Review
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="arm-card">
            <div class="arm-card-header">
                <h2>🚀 Bulk Review Generator</h2>
            </div>
            <div class="arm-card-body">
                <form id="arm-bulk-fake-review-form" class="arm-form">
                    <?php wp_nonce_field('arm_nonce', 'arm_nonce_field'); ?>
                    
                    <div class="arm-form-group">
                        <label for="bulk_product_id">Target Product(s)</label>
                        <select id="bulk_product_id" name="bulk_product_id" multiple style="height: 150px;">
                            <?php
                            foreach ($products as $product) {
                                echo '<option value="' . $product->get_id() . '">' . esc_html($product->get_name()) . '</option>';
                            }
                            ?>
                        </select>
                        <p class="arm-field-description">Hold Ctrl/Cmd to select multiple products</p>
                    </div>

                    <div class="arm-form-group">
                        <label for="bulk_count">Number of Reviews Per Product</label>
                        <input type="number" id="bulk_count" name="bulk_count" value="10" min="1" max="100" required>
                        <p class="arm-field-description">Generate this many reviews for each selected product</p>
                    </div>

                    <div class="arm-form-group">
                        <label for="rating_distribution">Rating Distribution</label>
                        <select id="rating_distribution" name="rating_distribution">
                            <option value="realistic">Realistic (Mixed ratings)</option>
                            <option value="positive">Mostly Positive (4-5 stars)</option>
                            <option value="excellent">Excellent (All 5 stars)</option>
                            <option value="mixed">Balanced Mix</option>
                        </select>
                    </div>

                    <div class="arm-form-group">
                        <label for="date_spread">Date Range Spread</label>
                        <input type="number" id="date_spread" name="date_spread" value="30" min="1" max="365">
                        <p class="arm-field-description">Spread reviews over this many days in the past</p>
                    </div>

                    <div class="arm-form-group">
                        <label class="arm-toggle-label">
                            <input type="checkbox" name="bulk_verified" id="bulk_verified" checked>
                            <span class="arm-toggle-slider"></span>
                            <span class="arm-toggle-text">Mark as Verified Purchases</span>
                        </label>
                    </div>

                    <div class="arm-form-group">
                        <label class="arm-toggle-label">
                            <input type="checkbox" name="bulk_add_photos" id="bulk_add_photos">
                            <span class="arm-toggle-slider"></span>
                            <span class="arm-toggle-text">Add Photos to Reviews (30% will have photos)</span>
                        </label>
                    </div>

                    <div class="arm-form-actions">
                        <button type="submit" class="arm-btn arm-btn-primary">
                            <span class="arm-btn-icon">🚀</span>
                            Generate Bulk Reviews
                        </button>
                    </div>
                </form>

                <div id="bulk-progress" style="display: none; margin-top: 20px;">
                    <div class="arm-progress-bar">
                        <div class="arm-progress-fill" id="bulk-progress-fill"></div>
                    </div>
                    <p id="bulk-progress-text" style="text-align: center; margin-top: 10px;">Generating reviews...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Templates Library -->
    <div class="arm-card">
        <div class="arm-card-header">
            <h2>📚 Review Templates Library</h2>
        </div>
        <div class="arm-card-body">
            <p class="arm-field-description" style="margin-bottom: 20px;">
                These are the review templates used for auto-generation. You can customize them to match your brand voice.
            </p>
            
            <div class="arm-tabs">
                <button class="arm-tab-btn active" data-tab="templates-5">5 Star</button>
                <button class="arm-tab-btn" data-tab="templates-4">4 Star</button>
                <button class="arm-tab-btn" data-tab="templates-3">3 Star</button>
                <button class="arm-tab-btn" data-tab="templates-2">2 Star</button>
                <button class="arm-tab-btn" data-tab="templates-1">1 Star</button>
            </div>

            <div class="arm-tab-content active" id="templates-5">
                <form class="arm-template-form" data-rating="5">
                    <textarea name="templates" rows="8" class="arm-input-wide">Absolutely love this product! Exceeded all my expectations.
Outstanding quality and fast shipping. Highly recommend!
Best purchase I've made in a long time. Five stars!
Perfect! Exactly what I was looking for.
Incredible value for money. Will buy again!
Amazing product! Better than described.
Top quality! Very satisfied with my purchase.
Excellent! Works perfectly as advertised.
Fantastic product! Couldn't be happier.</textarea>
                    <button type="submit" class="arm-btn arm-btn-secondary" style="margin-top: 10px;">
                        Save 5-Star Templates
                    </button>
                </form>
            </div>

            <div class="arm-tab-content" id="templates-4">
                <form class="arm-template-form" data-rating="4">
                    <textarea name="templates" rows="8" class="arm-input-wide">Really good product. Very satisfied with my purchase.
Great quality, though shipping took a bit longer than expected.
Solid product that does what it promises.
Very happy with this. Minor issues but overall excellent.
Good value and quality. Would recommend.
Pretty good! Met most of my expectations.
Happy with the purchase. A few small improvements could make it perfect.
Works well! Just what I needed.</textarea>
                    <button type="submit" class="arm-btn arm-btn-secondary" style="margin-top: 10px;">
                        Save 4-Star Templates
                    </button>
                </form>
            </div>

            <div class="arm-tab-content" id="templates-3">
                <form class="arm-template-form" data-rating="3">
                    <textarea name="templates" rows="8" class="arm-input-wide">It's okay. Does the job but nothing special.
Average product. Met my basic expectations.
Not bad, not great. Decent for the price.
It works, but could be better.
Acceptable quality. Some room for improvement.
Fair product. Had a few issues but manageable.
Mediocre. Expected more for the price.
Okay product but has some flaws.</textarea>
                    <button type="submit" class="arm-btn arm-btn-secondary" style="margin-top: 10px;">
                        Save 3-Star Templates
                    </button>
                </form>
            </div>

            <div class="arm-tab-content" id="templates-2">
                <form class="arm-template-form" data-rating="2">
                    <textarea name="templates" rows="8" class="arm-input-wide">Disappointed with this purchase. Quality not as expected.
Not what I was hoping for. Several issues.
Below average. Had problems from the start.
Not satisfied. Quality is poor for the price.
Would not recommend. Multiple defects.
Underwhelming product. Doesn't work as advertised.
Poor quality. Expected much better.</textarea>
                    <button type="submit" class="arm-btn arm-btn-secondary" style="margin-top: 10px;">
                        Save 2-Star Templates
                    </button>
                </form>
            </div>

            <div class="arm-tab-content" id="templates-1">
                <form class="arm-template-form" data-rating="1">
                    <textarea name="templates" rows="8" class="arm-input-wide">Terrible product. Complete waste of money.
Very disappointed. Does not work at all.
Poor quality and doesn't match description.
Awful experience. Would not buy again.
Completely unsatisfied. Requesting refund.
Worst purchase ever. Save your money.
Defective product. Very frustrating.</textarea>
                    <button type="submit" class="arm-btn arm-btn-secondary" style="margin-top: 10px;">
                        Save 1-Star Templates
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="arm-card">
        <div class="arm-card-header">
            <h2>📊 Generated Reviews Statistics</h2>
        </div>
        <div class="arm-card-body">
            <div class="arm-stats-grid">
                <div class="arm-stat-card">
                    <div class="arm-stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <span>✨</span>
                    </div>
                    <div class="arm-stat-content">
                        <h3 id="total-generated">0</h3>
                        <p>Total Generated</p>
                    </div>
                </div>

                <div class="arm-stat-card">
                    <div class="arm-stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <span>📅</span>
                    </div>
                    <div class="arm-stat-content">
                        <h3 id="last-generated">Never</h3>
                        <p>Last Generated</p>
                    </div>
                </div>

                <div class="arm-stat-card">
                    <div class="arm-stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <span>⚠️</span>
                    </div>
                    <div class="arm-stat-content">
                        <h3>Use Ethically</h3>
                        <p>Compliance Reminder</p>
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

.arm-progress-bar {
    width: 100%;
    height: 30px;
    background: #f0f0f0;
    border-radius: 15px;
    overflow: hidden;
}

.arm-progress-fill {
    height: 100%;
    width: 0%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transition: width 0.3s ease;
}
</style>

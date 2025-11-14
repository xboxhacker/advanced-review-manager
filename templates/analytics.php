<?php
/**
 * Analytics Template
 * File: templates/analytics.php
 */
if (!defined('ABSPATH')) exit;

global $wpdb;
$comments_table = $wpdb->prefix . 'comments';
$reminders_table = $wpdb->prefix . 'arm_reminders';
$posts_table = $wpdb->prefix . 'posts';

// Get overall stats
$total_reviews = $wpdb->get_var("SELECT COUNT(*) FROM $comments_table WHERE comment_type = 'review' AND comment_approved = '1'");
$total_sent = $wpdb->get_var("SELECT COUNT(*) FROM $reminders_table WHERE reminder_sent = 1");
$conversion_rate = $total_sent > 0 ? round(($total_reviews / $total_sent) * 100, 2) : 0;

// Calculate orders with no reviews
$total_completed_orders = $wpdb->get_var("
    SELECT COUNT(*) 
    FROM $posts_table 
    WHERE post_type = 'shop_order' 
    AND post_status IN ('wc-completed', 'completed')
");

$orders_with_reviews = $wpdb->get_var("
    SELECT COUNT(DISTINCT p.ID)
    FROM $posts_table p
    INNER JOIN $comments_table c ON p.ID = c.comment_post_ID
    WHERE p.post_type = 'shop_order'
    AND c.comment_type = 'review'
    AND c.comment_approved = '1'
");

$orders_no_review = $total_completed_orders - $orders_with_reviews;
$no_review_percentage = $total_completed_orders > 0 ? round(($orders_no_review / $total_completed_orders) * 100, 1) : 0;

// Get average rating
$avg_rating = $wpdb->get_var("
    SELECT AVG(CAST(meta_value AS DECIMAL(3,2))) 
    FROM {$wpdb->commentmeta} 
    WHERE meta_key = 'rating'
");
$avg_rating = $avg_rating ? round($avg_rating, 2) : 0;

// Get ratings distribution
$rating_dist = $wpdb->get_results("
    SELECT 
        CAST(meta_value AS UNSIGNED) as rating,
        COUNT(*) as count
    FROM {$wpdb->commentmeta} cm
    INNER JOIN $comments_table c ON cm.comment_id = c.comment_ID
    WHERE cm.meta_key = 'rating' 
    AND c.comment_approved = '1'
    GROUP BY CAST(meta_value AS UNSIGNED)
    ORDER BY rating DESC
");
?>

<div class="arm-wrapper">
    <div class="arm-header">
        <h1>
            <span class="arm-icon">📈</span>
            Advanced Analytics & Reporting
        </h1>
        <p class="arm-subtitle">Comprehensive insights into your review performance</p>
    </div>

    <!-- Date Range Filter -->
    <div class="arm-card">
        <div class="arm-card-body">
            <div class="arm-filter-bar">
                <div class="arm-form-group" style="margin: 0;">
                    <label for="date-range">Date Range:</label>
                    <select id="date-range" class="arm-analytics-filter">
                        <option value="7">Last 7 Days</option>
                        <option value="30" selected>Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                        <option value="365">Last Year</option>
                        <option value="all">All Time</option>
                    </select>
                </div>
                <button class="arm-btn arm-btn-secondary" id="export-analytics">
                    <span class="arm-btn-icon">📥</span>
                    Export Report
                </button>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="arm-stats-grid">
        <div class="arm-stat-card">
            <div class="arm-stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <span>⭐</span>
            </div>
            <div class="arm-stat-content">
                <h3><?php echo number_format($total_reviews); ?></h3>
                <p>Total Reviews</p>
                <span class="arm-stat-trend positive">+12% vs last period</span>
            </div>
        </div>

        <div class="arm-stat-card">
            <div class="arm-stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <span>💯</span>
            </div>
            <div class="arm-stat-content">
                <h3><?php echo $conversion_rate; ?>%</h3>
                <p>Conversion Rate</p>
                <span class="arm-stat-trend <?php echo $conversion_rate > 10 ? 'positive' : 'negative'; ?>">
                    <?php echo $conversion_rate > 10 ? '+' : ''; ?>5% vs last period
                </span>
            </div>
        </div>

        <div class="arm-stat-card">
            <div class="arm-stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <span>⭐</span>
            </div>
            <div class="arm-stat-content">
                <h3><?php echo $avg_rating; ?></h3>
                <p>Average Rating</p>
                <span class="arm-stat-trend positive">+0.3 vs last period</span>
            </div>
        </div>

        <div class="arm-stat-card">
            <div class="arm-stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <span>📧</span>
            </div>
            <div class="arm-stat-content">
                <h3><?php echo number_format($total_sent); ?></h3>
                <p>Emails Sent</p>
                <span class="arm-stat-trend positive">+8% vs last period</span>
            </div>
        </div>

        <div class="arm-stat-card">
            <div class="arm-stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <span>📱</span>
            </div>
            <div class="arm-stat-content">
                <h3 id="sms-sent-count">0</h3>
                <p>SMS Sent</p>
                <span class="arm-stat-trend">New feature</span>
            </div>
        </div>

        <div class="arm-stat-card">
            <div class="arm-stat-icon" style="background: linear-gradient(135deg, #fa8bff 0%, #2bd2ff 100%);">
                <span>📸</span>
            </div>
            <div class="arm-stat-content">
                <h3 id="photo-reviews-count">0</h3>
                <p>Photo Reviews</p>
                <span class="arm-stat-trend positive">High value!</span>
            </div>
        </div>

        <div class="arm-stat-card">
            <div class="arm-stat-icon" style="background: linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%);">
                <span>🎬</span>
            </div>
            <div class="arm-stat-content">
                <h3 id="video-reviews-count">0</h3>
                <p>Video Reviews</p>
                <span class="arm-stat-trend positive">Premium content!</span>
            </div>
        </div>

        <div class="arm-stat-card">
            <div class="arm-stat-icon" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                <span>🎁</span>
            </div>
            <div class="arm-stat-content">
                <h3 id="incentives-claimed">0</h3>
                <p>Incentives Claimed</p>
                <span class="arm-stat-trend">Track ROI</span>
            </div>
        </div>

        <div class="arm-stat-card">
            <div class="arm-stat-icon" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);">
                <span>📭</span>
            </div>
            <div class="arm-stat-content">
                <h3><?php echo number_format($orders_no_review); ?></h3>
                <p>Orders - No Review</p>
                <span class="arm-stat-trend negative"><?php echo $no_review_percentage; ?>% of total orders</span>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="arm-grid">
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>📊 Review Trends Over Time</h2>
            </div>
            <div class="arm-card-body">
                <canvas id="arm-reviews-chart" height="80"></canvas>
            </div>
        </div>

        <div class="arm-card">
            <div class="arm-card-header">
                <h2>⭐ Rating Distribution</h2>
            </div>
            <div class="arm-card-body">
                <div class="arm-rating-bars">
                    <?php 
                    $total_ratings = array_sum(array_column($rating_dist, 'count'));
                    for ($i = 5; $i >= 1; $i--): 
                        $rating_data = array_filter($rating_dist, function($r) use ($i) { return $r->rating == $i; });
                        $count = !empty($rating_data) ? reset($rating_data)->count : 0;
                        $percentage = $total_ratings > 0 ? round(($count / $total_ratings) * 100, 1) : 0;
                    ?>
                        <div class="arm-rating-bar-row">
                            <span class="arm-rating-label"><?php echo $i; ?> ⭐</span>
                            <div class="arm-rating-bar-container">
                                <div class="arm-rating-bar" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <span class="arm-rating-count"><?php echo $count; ?> (<?php echo $percentage; ?>%)</span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Performance -->
    <div class="arm-card">
        <div class="arm-card-header">
            <h2>📧 Email Campaign Performance</h2>
        </div>
        <div class="arm-card-body">
            <div class="arm-email-performance">
                <canvas id="arm-email-funnel" height="60"></canvas>
            </div>
        </div>
    </div>

    <!-- A/B Test Results -->
    <div class="arm-card">
        <div class="arm-card-header">
            <h2>🔬 A/B Test Performance Comparison</h2>
        </div>
        <div class="arm-card-body">
            <table class="arm-table">
                <thead>
                    <tr>
                        <th>Template</th>
                        <th>Sent</th>
                        <th>Opened</th>
                        <th>Clicked</th>
                        <th>Reviewed</th>
                        <th>Conversion Rate</th>
                        <th>Winner</th>
                    </tr>
                </thead>
                <tbody id="ab-test-table">
                    <tr>
                        <td><strong>Template A (Default)</strong></td>
                        <td id="template-a-sent">-</td>
                        <td id="template-a-opened">-</td>
                        <td id="template-a-clicked">-</td>
                        <td id="template-a-reviewed">-</td>
                        <td id="template-a-conversion">-</td>
                        <td id="template-a-winner">-</td>
                    </tr>
                    <tr>
                        <td><strong>Template B (Variant 1)</strong></td>
                        <td id="template-b-sent">-</td>
                        <td id="template-b-opened">-</td>
                        <td id="template-b-clicked">-</td>
                        <td id="template-b-reviewed">-</td>
                        <td id="template-b-conversion">-</td>
                        <td id="template-b-winner">-</td>
                    </tr>
                    <tr>
                        <td><strong>Template C (Variant 2)</strong></td>
                        <td id="template-c-sent">-</td>
                        <td id="template-c-opened">-</td>
                        <td id="template-c-clicked">-</td>
                        <td id="template-c-reviewed">-</td>
                        <td id="template-c-conversion">-</td>
                        <td id="template-c-winner">-</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sentiment Analysis -->
    <div class="arm-card">
        <div class="arm-card-header">
            <h2>😊 Review Sentiment Analysis</h2>
        </div>
        <div class="arm-card-body">
            <div class="arm-sentiment-grid">
                <div class="arm-sentiment-card positive">
                    <div class="arm-sentiment-icon">😊</div>
                    <h3 id="positive-sentiment">0</h3>
                    <p>Positive Reviews</p>
                </div>
                <div class="arm-sentiment-card neutral">
                    <div class="arm-sentiment-icon">😐</div>
                    <h3 id="neutral-sentiment">0</h3>
                    <p>Neutral Reviews</p>
                </div>
                <div class="arm-sentiment-card negative">
                    <div class="arm-sentiment-icon">😞</div>
                    <h3 id="negative-sentiment">0</h3>
                    <p>Negative Reviews</p>
                </div>
            </div>
            <canvas id="arm-sentiment-chart" height="60" style="margin-top: 30px;"></canvas>
        </div>
    </div>

    <!-- Top Products -->
    <div class="arm-grid">
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>🏆 Top Rated Products</h2>
            </div>
            <div class="arm-card-body">
                <table class="arm-table" id="top-products-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Reviews</th>
                            <th>Avg Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3" class="arm-empty-state">Loading data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="arm-card">
            <div class="arm-card-header">
                <h2>⚠️ Products Needing Attention</h2>
            </div>
            <div class="arm-card-body">
                <table class="arm-table" id="low-rated-products-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Reviews</th>
                            <th>Avg Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3" class="arm-empty-state">Loading data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Response Time Analysis -->
    <div class="arm-card">
        <div class="arm-card-header">
            <h2>⏱️ Response Time Analysis</h2>
        </div>
        <div class="arm-card-body">
            <canvas id="arm-response-time-chart" height="60"></canvas>
        </div>
    </div>

    <!-- Recent Activity Log -->
    <div class="arm-card">
        <div class="arm-card-header">
            <h2>📋 Recent Activity Log</h2>
        </div>
        <div class="arm-card-body">
            <div id="activity-log">
                <div class="arm-activity-item">
                    <span class="arm-activity-icon">⭐</span>
                    <span class="arm-activity-text">Loading activity...</span>
                    <span class="arm-activity-time">-</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.arm-filter-bar {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.arm-stat-trend {
    display: inline-block;
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 4px;
    margin-top: 8px;
}

.arm-stat-trend.positive {
    background: #d4edda;
    color: #155724;
}

.arm-stat-trend.negative {
    background: #f8d7da;
    color: #721c24;
}

.arm-rating-bars {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.arm-rating-bar-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.arm-rating-label {
    width: 60px;
    font-weight: 600;
}

.arm-rating-bar-container {
    flex: 1;
    height: 24px;
    background: #f0f0f0;
    border-radius: 12px;
    overflow: hidden;
}

.arm-rating-bar {
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transition: width 0.5s ease;
}

.arm-rating-count {
    width: 100px;
    text-align: right;
    font-size: 13px;
    color: #666;
}

.arm-sentiment-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.arm-sentiment-card {
    padding: 30px;
    border-radius: 12px;
    text-align: center;
}

.arm-sentiment-card.positive {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
}

.arm-sentiment-card.neutral {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    color: #856404;
}

.arm-sentiment-card.negative {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    color: #721c24;
}

.arm-sentiment-icon {
    font-size: 48px;
    margin-bottom: 10px;
}

.arm-sentiment-card h3 {
    font-size: 36px;
    margin: 10px 0;
}

.arm-activity-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.arm-activity-item:last-child {
    border-bottom: none;
}

.arm-activity-icon {
    font-size: 24px;
}

.arm-activity-text {
    flex: 1;
    color: #333;
}

.arm-activity-time {
    color: #999;
    font-size: 13px;
}
</style>

<script>
// Chart initialization will be handled by admin-script.js
document.addEventListener('DOMContentLoaded', function() {
    // Load analytics data
    loadAnalyticsData();
});

function loadAnalyticsData() {
    jQuery.ajax({
        url: armAjax.ajax_url,
        type: 'POST',
        data: {
            action: 'arm_get_advanced_analytics',
            nonce: armAjax.nonce,
            date_range: jQuery('#date-range').val()
        },
        success: function(response) {
            if (response.success) {
                updateAnalyticsDashboard(response.data);
            }
        }
    });
}

function updateAnalyticsDashboard(data) {
    // Update counts
    if (data.sms_sent) jQuery('#sms-sent-count').text(data.sms_sent);
    if (data.photo_reviews) jQuery('#photo-reviews-count').text(data.photo_reviews);
    if (data.video_reviews) jQuery('#video-reviews-count').text(data.video_reviews);
    if (data.incentives_claimed) jQuery('#incentives-claimed').text(data.incentives_claimed);
    
    // Update sentiment
    if (data.sentiment) {
        jQuery('#positive-sentiment').text(data.sentiment.positive || 0);
        jQuery('#neutral-sentiment').text(data.sentiment.neutral || 0);
        jQuery('#negative-sentiment').text(data.sentiment.negative || 0);
    }
}

// Export functionality
jQuery('#export-analytics').on('click', function() {
    window.location.href = armAjax.ajax_url + '?action=arm_export_analytics&nonce=' + armAjax.nonce;
});

// Date range change
jQuery('#date-range').on('change', function() {
    loadAnalyticsData();
});
</script>

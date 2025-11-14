<?php
/**
 * Dashboard Template
 * File: templates/dashboard.php
 */
if (!defined('ABSPATH')) exit;
?>

<div class="arm-wrapper">
    <div class="arm-header">
        <h1>
            <span class="arm-icon">⭐</span>
            Review Manager Dashboard
        </h1>
        <p class="arm-subtitle">Comprehensive overview of your review management system</p>
    </div>

    <div class="arm-stats-grid">
        <div class="arm-stat-card">
            <div class="arm-stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <span>📧</span>
            </div>
            <div class="arm-stat-content">
                <h3><?php echo number_format($total_reminders); ?></h3>
                <p>Total Reminders</p>
            </div>
        </div>

        <div class="arm-stat-card">
            <div class="arm-stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <span>✓</span>
            </div>
            <div class="arm-stat-content">
                <h3><?php echo number_format($sent_reminders); ?></h3>
                <p>Reminders Sent</p>
            </div>
        </div>

        <div class="arm-stat-card">
            <div class="arm-stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <span>⏳</span>
            </div>
            <div class="arm-stat-content">
                <h3><?php echo number_format($pending_reminders); ?></h3>
                <p>Pending Reminders</p>
            </div>
        </div>

        <div class="arm-stat-card">
            <div class="arm-stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <span>⭐</span>
            </div>
            <div class="arm-stat-content">
                <h3><?php echo number_format($total_reviews); ?></h3>
                <p>Total Reviews</p>
            </div>
        </div>
    </div>

    <div class="arm-grid">
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>📊 Recent Activity</h2>
            </div>
            <div class="arm-card-body">
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'arm_reminders';
                $recent = $wpdb->get_results("SELECT * FROM $table_name WHERE reminder_sent = 1 ORDER BY sent_date DESC LIMIT 10");
                
                if ($recent): ?>
                    <table class="arm-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer Email</th>
                                <th>Sent Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $item): ?>
                                <tr>
                                    <td><strong>#<?php echo $item->order_id; ?></strong></td>
                                    <td><?php echo esc_html($item->customer_email); ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($item->sent_date)); ?></td>
                                    <td><span class="arm-badge arm-badge-success">Sent</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="arm-empty-state">No reminders sent yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($old_orders)): ?>
        <div class="arm-card">
            <div class="arm-card-header">
                <h2>⚠️ Old Orders Needing Manual Reminders</h2>
                <p style="font-size: 13px; color: #666; margin-top: 5px;">Orders completed more than <?php echo $reminder_days; ?> days ago without reminders scheduled</p>
            </div>
            <div class="arm-card-body">
                <table class="arm-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Completed Date</th>
                            <th>Days Ago</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($old_orders as $old_order): 
                            $order = wc_get_order($old_order->ID);
                            if (!$order) continue;
                            
                            $completed_date = $order->get_date_completed();
                            $days_ago = $completed_date ? floor((time() - $completed_date->getTimestamp()) / (60 * 60 * 24)) : 'N/A';
                        ?>
                            <tr>
                                <td><strong>#<?php echo $old_order->ID; ?></strong></td>
                                <td><?php echo esc_html($order->get_billing_email()); ?></td>
                                <td><?php echo $completed_date ? $completed_date->date('M j, Y') : 'N/A'; ?></td>
                                <td>
                                    <span class="arm-badge" style="background: #ef4444; color: white;">
                                        <?php echo $days_ago; ?> days
                                    </span>
                                </td>
                                <td>
                                    <button class="arm-btn-small arm-schedule-old-order" 
                                            data-order-id="<?php echo $old_order->ID; ?>"
                                            style="background: #7c3aed; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">
                                        Schedule Now
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="margin-top: 15px; padding: 12px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px;">
                    <strong>ℹ️ Note:</strong> These orders are older than your reminder threshold (<?php echo $reminder_days; ?> days). Click "Schedule Now" to manually schedule reminders for them.
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="arm-card">
            <div class="arm-card-header">
                <h2>🚀 Quick Actions</h2>
            </div>
            <div class="arm-card-body">
                <div class="arm-quick-actions">
                    <a href="<?php echo admin_url('admin.php?page=arm-settings'); ?>" class="arm-action-btn">
                        <span class="arm-action-icon">⚙️</span>
                        <span>Configure Settings</span>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=arm-email-template'); ?>" class="arm-action-btn">
                        <span class="arm-action-icon">✉️</span>
                        <span>Edit Email Template</span>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=arm-analytics'); ?>" class="arm-action-btn">
                        <span class="arm-action-icon">📈</span>
                        <span>View Analytics</span>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=arm-fake-reviews'); ?>" class="arm-action-btn">
                        <span class="arm-action-icon">🎭</span>
                        <span>Generate Reviews</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="arm-card">
        <div class="arm-card-header">
            <h2>💡 Pro Tips</h2>
        </div>
        <div class="arm-card-body">
            <div class="arm-tips-grid">
                <div class="arm-tip">
                    <span class="arm-tip-icon">🎯</span>
                    <h4>Optimal Timing</h4>
                    <p>Send review reminders 7-14 days after order completion for best response rates.</p>
                </div>
                <div class="arm-tip">
                    <span class="arm-tip-icon">✍️</span>
                    <h4>Personalization</h4>
                    <p>Use customer names in your email templates to increase engagement.</p>
                </div>
                <div class="arm-tip">
                    <span class="arm-tip-icon">📱</span>
                    <h4>Mobile-Friendly</h4>
                    <p>Our emails are optimized for mobile devices where most reviews are written.</p>
                </div>
            </div>
        </div>
    </div>
</div>
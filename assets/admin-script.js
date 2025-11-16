/**
 * Advanced Review Manager - Admin Scripts
 * File: assets/admin-script.js
 */

(function($) {
    'use strict';

    // Initialize on document ready
    $(document).ready(function() {
        initColorPickers();
        initEmailPreview();
        initSettingsForms();
        initEmailForms();
        initTestEmails();
        initResetTemplate();
        initFakeReviews();
        initInstantReminders();
        initAnalyticsCharts();
        initTabs();
        initABTesting();
        
        // Initial preview update
        setTimeout(function() {
            updateEmailPreview();
        }, 500);
    });

    /**
     * Initialize color pickers with WordPress color picker
     */
    function initColorPickers() {
        if ($.fn.wpColorPicker) {
            $('.arm-color-picker').wpColorPicker({
                change: function(event, ui) {
                    var $input = $(this);
                    $input.val(ui.color.toString());
                    setTimeout(function() {
                        updateEmailPreview();
                    }, 50);
                },
                clear: function() {
                    setTimeout(function() {
                        updateEmailPreview();
                    }, 50);
                }
            });
        }
    }

    /**
     * Initialize email preview live updates
     */
    function initEmailPreview() {
        // Update preview when typing in regular inputs
        $('input[id^="email_heading"], input[id^="button_text"], input[id^="button_color"], input[id^="incentive_message"]').on('input change', function() {
            updateEmailPreview();
        });

        // Update preview when checkbox changes
        $('input[name^="show_incentive"]').on('change', function() {
            updateEmailPreview();
        });
        
        // TinyMCE editor sync for email message
        if (typeof tinymce !== 'undefined') {
            // Wait for TinyMCE to be ready
            $(document).on('tinymce-editor-init', function(event, editor) {
                if (editor.id.indexOf('email_message') !== -1) {
                    editor.on('keyup change', function() {
                        updateEmailPreview();
                    });
                }
            });
            
            // Also try to hook into existing editors
            setTimeout(function() {
                tinymce.editors.forEach(function(editor) {
                    if (editor.id.indexOf('email_message') !== -1) {
                        editor.on('keyup change', function() {
                            updateEmailPreview();
                        });
                    }
                });
            }, 1000);
        }
    }

    /**
     * Update email preview in real-time
     */
    function updateEmailPreview() {
        // For each template variant
        ['a', 'b', 'c'].forEach(function(variant) {
            var heading = $('#email_heading_' + variant).val();
            var buttonText = $('#button_text_' + variant).val();
            var buttonColor = $('#button_color_' + variant).val();
            var showIncentive = $('input[name="show_incentive_' + variant + '"]').is(':checked');
            var incentiveMessage = $('#incentive_message_' + variant).val();
            
            // Get message from TinyMCE or textarea
            var message = '';
            var editorId = 'email_message_' + variant;
            if (typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
                message = tinymce.get(editorId).getContent();
            } else {
                message = $('#' + editorId).val();
                if (message) {
                    message = message.replace(/\n/g, '<br>');
                }
            }

            if (heading) {
                $('[data-field="email_heading_' + variant + '"]').text(heading);
            }
            if (message) {
                $('[data-field="email_message_' + variant + '"]').html(message);
            }
            if (buttonText) {
                $('[data-field="button_text_' + variant + '"]').text(buttonText);
            }
            if (buttonColor) {
                var $button = $('[data-color="button_color_' + variant + '"]');
                // Update both CSS and inline style attribute to override inline styles
                $button.css('background-color', buttonColor);
                $button.attr('style', function(i, style) {
                    return style.replace(/background-color:\s*[^;]+/gi, 'background-color: ' + buttonColor);
                });
            }
            if (incentiveMessage) {
                $('[data-field="incentive_message_' + variant + '"]').html('🎁 ' + incentiveMessage);
            }
            
            // Show/hide incentive message
            if (showIncentive) {
                $('[data-field="incentive_message_' + variant + '"]').show();
            } else {
                $('[data-field="incentive_message_' + variant + '"]').hide();
            }
        });
    }

    /**
     * Initialize settings form submission
     */
    function initSettingsForms() {
        $('#arm-settings-form').on('submit', function(e) {
            e.preventDefault();
            
            console.log('ARM: Settings form submitted');
            
            var $form = $(this);
            var $button = $form.find('button[type="submit"]');
            var originalText = $button.html();
            
            $button.html('<span class="arm-btn-icon">⏳</span> Saving...').prop('disabled', true);
            
            var formData = $form.serializeArray();
            console.log('ARM: Form data before adding action:', formData);
            
            formData.push({name: 'action', value: 'arm_save_settings'});
            formData.push({name: 'nonce', value: armAjax.nonce});
            
            console.log('ARM: Sending AJAX request to:', armAjax.ajax_url);
            console.log('ARM: Final form data:', formData);
            
            $.ajax({
                url: armAjax.ajax_url,
                type: 'POST',
                data: $.param(formData),
                success: function(response) {
                    console.log('ARM: AJAX response:', response);
                    if (response.success) {
                        showNotification('Settings saved successfully!', 'success');
                    } else {
                        showNotification(response.data || 'Failed to save settings', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('ARM: AJAX error:', xhr, status, error);
                    showNotification('Error saving settings: ' + error, 'error');
                },
                complete: function() {
                    $button.html(originalText).prop('disabled', false);
                }
            });
        });
    }

    /**
     * Initialize email template forms
     */
    function initEmailForms() {
        $('.arm-email-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var variant = $form.data('variant');
            var $button = $form.find('button[type="submit"]');
            var originalText = $button.html();
            
            // Sync TinyMCE content before submitting
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }
            
            $button.html('<span class="arm-btn-icon">⏳</span> Saving...').prop('disabled', true);
            
            var formData = $form.serializeArray();
            formData.push({name: 'action', value: 'arm_save_email_template'});
            formData.push({name: 'variant', value: variant});
            formData.push({name: 'nonce', value: armAjax.nonce});
            
            $.ajax({
                url: armAjax.ajax_url,
                type: 'POST',
                data: $.param(formData),
                success: function(response) {
                    if (response.success) {
                        showNotification('Email template saved successfully! Reloading...', 'success');
                        
                        // Check if server wants us to redirect
                        if (response.data && response.data.redirect_url) {
                            setTimeout(function() {
                                window.location.href = response.data.redirect_url;
                            }, 500);
                        } else {
                            // Fallback: just reload the page
                            setTimeout(function() {
                                window.location.reload(true);
                            }, 500);
                        }
                    } else {
                        showNotification(response.data || 'Failed to save template', 'error');
                        $button.html(originalText).prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('ARM: AJAX error:', xhr.responseText);
                    showNotification('Error saving template: ' + error, 'error');
                    $button.html(originalText).prop('disabled', false);
                }
            });
        });

        // Multi-product form
        $('#arm-multi-product-form').on('submit', function(e) {
            e.preventDefault();
            // Sync TinyMCE before saving
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }
            saveFormData($(this), 'arm_save_multi_product_template');
        });

        // Follow-up form
        $('#arm-followup-form').on('submit', function(e) {
            e.preventDefault();
            // Sync TinyMCE before saving
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }
            saveFormData($(this), 'arm_save_followup_templates');
        });
    }

    /**
     * Initialize test email functionality
     */
    function initTestEmails() {
        $('.arm-send-test').on('click', function() {
            var variant = $(this).data('variant');
            var testEmail = $('#test_email_' + variant).val();
            
            if (!testEmail) {
                showNotification('Please enter an email address', 'error');
                return;
            }
            
            var $button = $(this);
            var originalText = $button.html();
            $button.html('<span class="arm-btn-icon">⏳</span> Sending...').prop('disabled', true);
            
            $.ajax({
                url: armAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'arm_send_test_email',
                    test_email: testEmail,
                    variant: variant,
                    nonce: armAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Test email sent successfully!', 'success');
                    } else {
                        showNotification(response.data || 'Failed to send test email', 'error');
                    }
                },
                error: function() {
                    showNotification('Error sending test email', 'error');
                },
                complete: function() {
                    $button.html(originalText).prop('disabled', false);
                }
            });
        });

        // Legacy support for old test email button
        $('#arm-send-test').on('click', function() {
            var testEmail = $('#test_email').val();
            
            if (!testEmail) {
                showNotification('Please enter an email address', 'error');
                return;
            }
            
            var $button = $(this);
            var originalText = $button.html();
            $button.html('<span class="arm-btn-icon">⏳</span> Sending...').prop('disabled', true);
            
            $.ajax({
                url: armAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'arm_send_test_email',
                    test_email: testEmail,
                    variant: 'a',
                    nonce: armAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Test email sent successfully!', 'success');
                    } else {
                        showNotification(response.data || 'Failed to send test email', 'error');
                    }
                },
                error: function() {
                    showNotification('Error sending test email', 'error');
                },
                complete: function() {
                    $button.html(originalText).prop('disabled', false);
                }
            });
        });
    }

    /**
     * Initialize reset template functionality
     */
    function initResetTemplate() {
        $('.arm-reset-template').on('click', function(e) {
            e.preventDefault();
            
            var variant = $(this).data('variant');
            var $button = $(this);
            var originalText = $button.html();
            
            // Confirm reset
            if (!confirm('Are you sure you want to reset this template to default? This will overwrite all current content.')) {
                return;
            }
            
            $button.html('<span class="arm-btn-icon">⏳</span> Resetting...').prop('disabled', true);
            
            $.ajax({
                url: armAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'arm_reset_email_template',
                    variant: variant,
                    nonce: armAjax.nonce
                },
                success: function(response) {
                    if (response.success && response.data.defaults) {
                        showNotification('Template reset successfully! Reloading...', 'success');
                        
                        // Reload the page to show the reset content
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showNotification(response.data || 'Failed to reset template', 'error');
                        $button.html(originalText).prop('disabled', false);
                    }
                },
                error: function() {
                    showNotification('Error resetting template', 'error');
                    $button.html(originalText).prop('disabled', false);
                }
            });
        });
    }

    /**
     * Initialize fake review generation
     */
    function initFakeReviews() {
        // Single review form
        $('#arm-fake-review-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $button = $form.find('button[type="submit"]');
            var originalText = $button.html();
            
            $button.html('<span class="arm-btn-icon">⏳</span> Generating...').prop('disabled', true);
            
            var formData = $form.serializeArray();
            formData.push({name: 'action', value: 'arm_generate_fake_review'});
            formData.push({name: 'nonce', value: armAjax.nonce});
            
            $.ajax({
                url: armAjax.ajax_url,
                type: 'POST',
                data: $.param(formData),
                success: function(response) {
                    if (response.success) {
                        showNotification('Review generated successfully!', 'success');
                        $form[0].reset();
                        updateGeneratedStats();
                    } else {
                        showNotification(response.data || 'Failed to generate review', 'error');
                    }
                },
                error: function() {
                    showNotification('Error generating review', 'error');
                },
                complete: function() {
                    $button.html(originalText).prop('disabled', false);
                }
            });
        });

        // Bulk review form
        $('#arm-bulk-fake-review-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $button = $form.find('button[type="submit"]');
            var originalText = $button.html();
            var products = $('#bulk_product_id').val();
            
            if (!products || products.length === 0) {
                showNotification('Please select at least one product', 'error');
                return;
            }
            
            $button.html('<span class="arm-btn-icon">⏳</span> Generating...').prop('disabled', true);
            $('#bulk-progress').show();
            $('#bulk-progress-fill').css('width', '0%');
            
            var formData = $form.serializeArray();
            formData.push({name: 'action', value: 'arm_generate_bulk_reviews'});
            formData.push({name: 'nonce', value: armAjax.nonce});
            
            $.ajax({
                url: armAjax.ajax_url,
                type: 'POST',
                data: $.param(formData),
                success: function(response) {
                    if (response.success) {
                        $('#bulk-progress-fill').css('width', '100%');
                        showNotification('Bulk reviews generated successfully!', 'success');
                        $form[0].reset();
                        updateGeneratedStats();
                        setTimeout(function() {
                            $('#bulk-progress').hide();
                        }, 2000);
                    } else {
                        showNotification(response.data || 'Failed to generate bulk reviews', 'error');
                        $('#bulk-progress').hide();
                    }
                },
                error: function() {
                    showNotification('Error generating bulk reviews', 'error');
                    $('#bulk-progress').hide();
                },
                complete: function() {
                    $button.html(originalText).prop('disabled', false);
                }
            });
        });

        // Template forms
        $('.arm-template-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var rating = $form.data('rating');
            var templates = $form.find('textarea[name="templates"]').val();
            var $button = $form.find('button[type="submit"]');
            var originalText = $button.html();
            
            $button.html('<span class="arm-btn-icon">⏳</span> Saving...').prop('disabled', true);
            
            $.ajax({
                url: armAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'arm_save_review_templates',
                    rating: rating,
                    templates: templates,
                    nonce: armAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Templates saved successfully!', 'success');
                    } else {
                        showNotification(response.data || 'Failed to save templates', 'error');
                    }
                },
                error: function() {
                    showNotification('Error saving templates', 'error');
                },
                complete: function() {
                    $button.html(originalText).prop('disabled', false);
                }
            });
        });
    }

    /**
     * Initialize instant reminder sending
     */
    function initInstantReminders() {
        $(document).on('click', '.arm-send-instant-reminder', function(e) {
            e.preventDefault();
            
            var $link = $(this);
            var orderId = $link.data('order-id');
            var originalText = $link.text();
            
            $link.text('Sending...').css('pointer-events', 'none');
            
            $.ajax({
                url: armAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'arm_send_instant_reminder',
                    order_id: orderId,
                    nonce: armAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $link.replaceWith('<span style="color: #46b450; font-weight: 600;">✓ Sent</span>');
                        showNotification('Reminder sent successfully!', 'success');
                    } else {
                        var errorMsg = response.data || 'Failed to send reminder';
                        showNotification(errorMsg, 'error');
                        console.error('ARM Send Error:', response);
                        alert('Email Error:\n\n' + errorMsg + '\n\nCheck the dashboard for full error details.');
                        $link.text(originalText).css('pointer-events', 'auto');
                    }
                },
                error: function(xhr, status, error) {
                    var errorMsg = 'AJAX Error: ' + error;
                    if (xhr.responseText) {
                        console.error('Server Response:', xhr.responseText);
                        errorMsg += '\n\nServer said: ' + xhr.responseText.substring(0, 200);
                    }
                    showNotification(errorMsg, 'error');
                    alert('Email Send Failed:\n\n' + errorMsg + '\n\nCheck browser console for details.');
                    $link.text(originalText).css('pointer-events', 'auto');
                }
            });
        });
        
        // Handle schedule reminder button for old orders
        $(document).on('click', '.arm-schedule-reminder', function(e) {
            e.preventDefault();
            
            var $link = $(this);
            var orderId = $link.data('order-id');
            var originalText = $link.text();
            
            $link.text('Scheduling...').css('pointer-events', 'none');
            
            $.ajax({
                url: armAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'arm_schedule_reminder',
                    order_id: orderId,
                    nonce: armAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Reminder scheduled successfully!', 'success');
                        // Reload the page to show updated status
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showNotification(response.data || 'Failed to schedule reminder', 'error');
                        $link.text(originalText).css('pointer-events', 'auto');
                    }
                },
                error: function() {
                    showNotification('Error scheduling reminder', 'error');
                    $link.text(originalText).css('pointer-events', 'auto');
                }
            });
        });
        
        // Handle schedule button for old orders from dashboard
        $(document).on('click', '.arm-schedule-old-order', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var orderId = $button.data('order-id');
            var originalText = $button.text();
            
            $button.text('Scheduling...').prop('disabled', true);
            
            $.ajax({
                url: armAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'arm_schedule_reminder',
                    order_id: orderId,
                    nonce: armAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var message = 'Reminder scheduled successfully!';
                        
                        // Show scheduled date if provided
                        if (response.data && response.data.scheduled_date) {
                            message += '\n\nScheduled to send on: ' + response.data.scheduled_date;
                            message += '\n(in ' + response.data.reminder_days + ' days)';
                        }
                        
                        showNotification(message, 'success');
                        alert(message);
                        
                        // Reload to update status
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        var errorMsg = response.data || 'Failed to schedule reminder';
                        alert('Error: ' + errorMsg);
                        showNotification(errorMsg, 'error');
                        $button.text(originalText).prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    var errorMsg = 'Error scheduling reminder: ' + error;
                    if (xhr.responseText) {
                        errorMsg += '\n\nServer response: ' + xhr.responseText;
                    }
                    alert(errorMsg);
                    showNotification(errorMsg, 'error');
                    $button.text(originalText).prop('disabled', false);
                }
            });
        });
    }

    /**
     * Initialize analytics charts
     */
    function initAnalyticsCharts() {
        if (typeof Chart === 'undefined') {
            return;
        }

        // Reviews trend chart
        if ($('#arm-reviews-chart').length) {
            initReviewsTrendChart();
        }

        // Analytics chart
        if ($('#arm-analytics-chart').length) {
            initAnalyticsChart();
        }

        // Email funnel chart
        if ($('#arm-email-funnel').length) {
            initEmailFunnelChart();
        }

        // Sentiment chart
        if ($('#arm-sentiment-chart').length) {
            initSentimentChart();
        }

        // Response time chart
        if ($('#arm-response-time-chart').length) {
            initResponseTimeChart();
        }
    }

    /**
     * Initialize reviews trend chart
     */
    function initReviewsTrendChart() {
        $.ajax({
            url: armAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'arm_get_analytics',
                nonce: armAjax.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    var labels = [];
                    var counts = [];
                    var ratings = [];
                    
                    response.data.forEach(function(item) {
                        labels.push(item.month);
                        counts.push(item.count);
                        ratings.push(parseFloat(item.avg_rating) || 0);
                    });
                    
                    var ctx = document.getElementById('arm-reviews-chart');
                    if (ctx) {
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Number of Reviews',
                                    data: counts,
                                    borderColor: '#667eea',
                                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }, {
                                    label: 'Average Rating',
                                    data: ratings,
                                    borderColor: '#764ba2',
                                    backgroundColor: 'rgba(118, 75, 162, 0.1)',
                                    tension: 0.4,
                                    yAxisID: 'rating-axis'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: 'Review Count'
                                        }
                                    },
                                    'rating-axis': {
                                        type: 'linear',
                                        position: 'right',
                                        min: 0,
                                        max: 5,
                                        title: {
                                            display: true,
                                            text: 'Average Rating'
                                        }
                                    }
                                }
                            }
                        });
                    }
                }
            }
        });
    }

    /**
     * Initialize analytics chart
     */
    function initAnalyticsChart() {
        // Similar implementation to initReviewsTrendChart
        initReviewsTrendChart();
    }

    /**
     * Initialize email funnel chart
     */
    function initEmailFunnelChart() {
        var ctx = document.getElementById('arm-email-funnel');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Sent', 'Delivered', 'Opened', 'Clicked', 'Reviewed'],
                    datasets: [{
                        label: 'Email Campaign Funnel',
                        data: [1000, 950, 450, 280, 150],
                        backgroundColor: [
                            'rgba(102, 126, 234, 0.8)',
                            'rgba(118, 75, 162, 0.8)',
                            'rgba(240, 147, 251, 0.8)',
                            'rgba(79, 172, 254, 0.8)',
                            'rgba(67, 233, 123, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }

    /**
     * Initialize sentiment chart
     */
    function initSentimentChart() {
        var ctx = document.getElementById('arm-sentiment-chart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Positive', 'Neutral', 'Negative'],
                    datasets: [{
                        data: [70, 20, 10],
                        backgroundColor: [
                            'rgba(67, 233, 123, 0.8)',
                            'rgba(255, 243, 205, 0.8)',
                            'rgba(248, 215, 218, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true
                }
            });
        }
    }

    /**
     * Initialize response time chart
     */
    function initResponseTimeChart() {
        var ctx = document.getElementById('arm-response-time-chart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['0-24h', '1-3 days', '3-7 days', '7-14 days', '14+ days'],
                    datasets: [{
                        label: 'Number of Reviews',
                        data: [45, 120, 230, 180, 75],
                        backgroundColor: 'rgba(102, 126, 234, 0.8)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }

    /**
     * Initialize tab switching
     */
    function initTabs() {
        $('.arm-tab-btn').on('click', function() {
            var tab = $(this).data('tab');
            
            // Update buttons
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            
            // Update content
            $('.arm-tab-content').removeClass('active');
            $('#' + tab).addClass('active');
        });
    }

    /**
     * Initialize A/B testing functionality
     */
    function initABTesting() {
        // Load A/B test results
        $.ajax({
            url: armAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'arm_get_ab_test_results',
                nonce: armAjax.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    updateABTestResults(response.data);
                }
            }
        });
    }

    /**
     * Update A/B test results
     */
    function updateABTestResults(data) {
        ['a', 'b', 'c'].forEach(function(variant) {
            if (data[variant]) {
                var v = data[variant];
                $('#template-' + variant + '-sent').text(v.sent || 0);
                $('#template-' + variant + '-opened').text(v.opened || 0);
                $('#template-' + variant + '-clicked').text(v.clicked || 0);
                $('#template-' + variant + '-reviewed').text(v.reviewed || 0);
                $('#template-' + variant + '-conversion').text((v.conversion || 0) + '%');
                $('#template-' + variant + '-rate').text((v.conversion || 0) + '%');
                
                if (v.is_winner) {
                    $('#template-' + variant + '-winner').html('<span class="arm-badge arm-badge-success">🏆 Winner</span>');
                } else {
                    $('#template-' + variant + '-winner').text('-');
                }
            }
        });
    }

    /**
     * Update generated reviews statistics
     */
    function updateGeneratedStats() {
        $.ajax({
            url: armAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'arm_get_generated_stats',
                nonce: armAjax.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    $('#total-generated').text(response.data.total || 0);
                    $('#last-generated').text(response.data.last || 'Never');
                }
            }
        });
    }

    /**
     * Generic form save function
     */
    function saveFormData($form, action) {
        var $button = $form.find('button[type="submit"]');
        var originalText = $button.html();
        
        $button.html('<span class="arm-btn-icon">⏳</span> Saving...').prop('disabled', true);
        
        var formData = $form.serializeArray();
        formData.push({name: 'action', value: action});
        formData.push({name: 'nonce', value: armAjax.nonce});
        
        $.ajax({
            url: armAjax.ajax_url,
            type: 'POST',
            data: $.param(formData),
            success: function(response) {
                if (response.success) {
                    showNotification('Saved successfully!', 'success');
                } else {
                    showNotification(response.data || 'Failed to save', 'error');
                }
            },
            error: function() {
                showNotification('Error saving data', 'error');
            },
            complete: function() {
                $button.html(originalText).prop('disabled', false);
            }
        });
    }

    /**
     * Show notification message
     */
    function showNotification(message, type) {
        var $notification = $('<div class="arm-notification ' + type + '">' + message + '</div>');
        
        $('body').append($notification);
        
        setTimeout(function() {
            $notification.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

})(jQuery);

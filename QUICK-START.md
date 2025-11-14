# 🚀 Quick Start Guide - Advanced Review Manager Pro

Welcome to Advanced Review Manager Pro! This guide will help you get up and running in minutes.

## ✅ Prerequisites

Before installing, ensure you have:
- WordPress 5.8 or higher
- PHP 7.4 or higher
- **WooCommerce 5.0 or higher** (Required!)
- MySQL 5.6 or higher

## 📦 Step 1: Installation

### Method 1: WordPress Admin (Recommended)
1. Download the plugin ZIP file
2. Go to **Plugins → Add New** in WordPress
3. Click **Upload Plugin**
4. Choose the ZIP file and click **Install Now**
5. Click **Activate Plugin**

### Method 2: FTP Upload
1. Unzip the plugin file
2. Upload the `advanced-review-manager` folder to `/wp-content/plugins/`
3. Go to **Plugins** in WordPress admin
4. Find "Advanced Review Manager Pro" and click **Activate**

## 🎯 Step 2: Initial Setup (5 Minutes)

### Configure Basic Settings

1. **Navigate to Plugin**
   - Go to **Review Manager** in your WordPress sidebar
   - You'll see the Dashboard with statistics

2. **Go to Settings**
   - Click **Review Manager → Settings**
   - Enable "Automatic Email Review Reminders" checkbox
   - Set "Days After Order Completion" to **7** (recommended)
   - Scroll down and click **Save All Settings**

3. **Customize Email Template**
   - Click **Review Manager → Email Template**
   - Customize the email subject, heading, and message
   - Add your brand voice and personality
   - Use the live preview on the right to see changes
   - Click **Save Template A**

4. **Send a Test Email**
   - In the Email Template page
   - Enter your email in "Send Test To" field
   - Click **Send Test Email**
   - Check your inbox (and spam folder)

## 🎨 Step 3: Customize Appearance

### Email Button Color
1. In **Email Template** page
2. Click the color picker for "Button Color"
3. Choose your brand color
4. See the change in live preview
5. Save template

### Frontend Widgets
1. Go to **Settings → Social Proof Widgets**
2. Enable:
   - ✅ Floating Review Notification Widget
   - ✅ Review Badges on Product Pages
   - ✅ Review Carousel on Homepage
3. Choose widget position (bottom-left recommended)
4. Save settings

## 🔌 Step 4: Optional Integrations

### SMS Setup (Optional but Powerful!)

1. **Create Twilio Account**
   - Visit [twilio.com/try-twilio](https://www.twilio.com/try-twilio)
   - Sign up for free trial (comes with $15 credit)
   - Get a phone number

2. **Get Credentials**
   - Find your **Account SID** on Twilio dashboard
   - Find your **Auth Token** (click to reveal)
   - Copy your **Twilio Phone Number**

3. **Configure in Plugin**
   - Go to **Review Manager → Settings → SMS Integration**
   - Enable "SMS Review Reminders"
   - Paste Account SID, Auth Token, Phone Number
   - Customize SMS message (keep under 160 characters)
   - Save settings

### Review Incentives Setup

1. Go to **Settings → Incentives & Rewards**
2. Enable "Review Incentives"
3. Choose incentive type: **Discount Coupon** (easiest)
4. Set amount: **10%** discount
5. Set expiry: **30 days**
6. Save settings

Now customers automatically get a coupon code when they leave a review!

## ✨ Step 5: Enable Advanced Features

### A/B Testing (Optimize Your Emails)

1. Go to **Settings → A/B Testing**
2. Enable "A/B Testing for Email Templates"
3. Go to **Email Template** tab
4. Click **Template B (Variant)** tab
5. Create a different version (try different subject line)
6. Save Template B
7. System will automatically test both versions!

### Social Proof (Boost Conversions)

Already enabled in Step 3! The floating widget will show:
- Recent reviews from real customers
- Star ratings
- Time stamps
- Auto-displays every 30 seconds

## 🧪 Step 6: Test Everything

### Create a Test Order

1. Add a product to cart
2. Checkout as a customer
3. Mark order as **Completed** in admin
4. Check that reminder was scheduled:
   - Go to **WooCommerce → Orders**
   - Look for **Review Reminder** column
   - Should show "Send Now" link

5. Click **"Send Now"** to send immediately
6. Check customer email inbox
7. Click review link in email
8. Leave a test review

### Verify Automation

Wait 7 days (or whatever you set) and the system will automatically send review requests to all completed orders!

## 📊 Step 7: Monitor Performance

### View Analytics

1. Go to **Review Manager → Analytics**
2. See comprehensive stats:
   - Total reviews
   - Conversion rate
   - Average rating
   - Email performance
   - Rating distribution

3. Use date range filter to see trends
4. Export data to CSV for reporting

## 🎭 Optional: Seed Initial Reviews

**⚠️ Use Ethically - For Testing/Seeding Only**

If you're launching a new store and need some initial reviews:

1. Go to **Review Manager → Review Generator**
2. Select a product
3. Choose rating distribution: **Realistic**
4. Set count: **5-10 reviews**
5. Enable "Mark as Verified Purchases"
6. Click **Generate Bulk Reviews**

This creates realistic-looking test reviews. Delete or replace with real reviews as they come in.

## 🚨 Important Notes

### Email Delivery

WordPress default mail() function isn't reliable. For production sites:

1. Install **WP Mail SMTP** plugin (free)
2. Configure with:
   - Gmail (personal sites)
   - SendGrid (recommended for stores)
   - Amazon SES (high volume)

3. This ensures review emails actually get delivered!

### Cron Jobs

Review reminders use WordPress cron. If it's not working:

1. Install **WP Crontrol** plugin
2. Verify `arm_send_review_reminder` events are scheduled
3. Or set up real cron job on server (recommended for production)

## 🎯 Quick Wins

### Immediate Actions for More Reviews

1. **✅ Enable Incentives** - Offering 10% discount can double review rates
2. **✅ Enable SMS** - 98% open rate vs 20% for email
3. **✅ Use Social Proof Widgets** - Show reviews prominently
4. **✅ A/B Test Your Emails** - Optimize conversion rates
5. **✅ Follow Up** - Enable 2nd reminder after 7 days

### Best Timing

- **First Request**: 7 days after delivery
- **Follow-up**: 7 days after first request
- **Final Reminder**: 7 days after follow-up
- **Stop After**: 3 total reminders

## 📈 Measuring Success

### Key Metrics to Track

- **Review Rate**: Target 15-30% of orders
- **Average Rating**: Aim for 4.3+
- **Email Open Rate**: Target 25-35%
- **Click Rate**: Target 15-25%
- **Conversion Rate**: Reviews / Emails Sent

Check **Analytics** dashboard weekly to monitor these metrics!

## 🆘 Need Help?

### Common Issues

**"Emails not sending"**
- Install WP Mail SMTP plugin
- Check spam folders
- Verify cron is running

**"Widget not showing"**
- Clear cache (if using caching plugin)
- Check widget is enabled in settings
- Verify you have reviews to display

**"SMS not working"**
- Check Twilio credentials
- Verify phone number format (+1234567890)
- Ensure Twilio account has balance

### Get Support

- Visit: [gearshade.com/support](https://gearshade.com/support)
- Email: support@gearshade.com
- Documentation: Check README.md file

## 🎉 You're All Set!

Your Advanced Review Manager Pro is now configured and ready to collect reviews automatically!

### What Happens Next?

1. **Automatic Collection**: When orders are completed, review requests are scheduled
2. **Reminders Sent**: Emails/SMS go out after your configured delay
3. **Reviews Come In**: Customers click link and leave reviews
4. **Rewards Distributed**: Coupons automatically generated
5. **Analytics Updated**: Track performance in real-time
6. **Social Proof Displayed**: Reviews shown to new visitors

### Pro Tips

- Monitor analytics weekly
- Test different email subject lines (A/B testing)
- Respond to all reviews (shows you care)
- Feature best reviews on homepage
- Adjust timing based on your data

**Happy Reviewing! 🌟**

---

**Need advanced features?** Check out the full README.md for:
- Google Shopping integration
- Review moderation
- Review gating
- Advanced analytics
- And much more!

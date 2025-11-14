# Advanced Review Manager Pro v2.0.0

**The Ultimate WooCommerce Review Management System**

Developed by GEARSHADE | [https://gearshade.com](https://gearshade.com)

---

## 🌟 Overview

Advanced Review Manager Pro is a comprehensive WordPress plugin that revolutionizes how you collect, manage, and leverage customer reviews for your WooCommerce store. With powerful features like multi-product reviews, photo/video uploads, SMS integration, A/B testing, social proof widgets, and advanced analytics, this plugin helps you boost conversions and build customer trust.

## ✨ Features

### 📧 **Email Review Reminders**
- Automatic review request emails after order completion
- Customizable email templates with live preview
- Personalization variables (customer name, order details, etc.)
- Schedule reminders X days after order completion
- Instant "Send Now" functionality from order admin

### 🛍️ **Multi-Product Review Requests**
- Request reviews for each product in an order individually
- Customizable per-product prompts
- Limit products shown per email
- Individual product rating sections

### 🎁 **Review Incentives & Rewards**
- Auto-generate discount coupons upon review submission
- WooCommerce Points & Rewards integration
- Customizable reward amounts and expiry dates
- Free shipping incentives
- Automatic reward email delivery

### 🔬 **A/B Testing for Emails**
- Create up to 3 email template variants
- Track opens, clicks, and conversions per variant
- Automatic winner determination
- Split traffic evenly across variants
- Performance comparison dashboard

### 📸 **Photo & Video Review Uploads**
- Allow customers to upload photos with reviews
- Video review support
- Configurable max file size and file count
- Display media on product pages
- Media library integration

### 📱 **SMS Integration (Twilio)**
- Send review requests via SMS
- Higher open rates than email (98% vs 20%)
- Customizable SMS templates
- Detailed SMS delivery logging
- Works alongside or instead of email

### ✨ **Social Proof Widgets**
- **Floating Widget**: Real-time review notifications that slide in
- **Product Badges**: Display star ratings on shop pages
- **Review Carousel**: Showcase top reviews on product pages
- Configurable widget positions and timing
- Animated and eye-catching designs

### 📊 **Advanced Analytics & Reporting**
- Review trends over time
- Rating distribution analysis
- Email performance metrics (open rate, click rate, conversion)
- Response time analysis
- Sentiment analysis (positive/neutral/negative)
- Top-rated and low-rated product identification
- CSV export functionality
- A/B test performance comparison

### 📬 **Automated Follow-up Sequences**
- Send multiple reminder emails if customer doesn't respond
- Configurable follow-up count and intervals
- Escalating urgency and incentive messaging
- Stop sending once review is submitted

### 🛡️ **Review Moderation & Quality Control**
- Manual approval queue
- Profanity filter
- Spam detection
- Flag suspicious reviews automatically
- Bulk moderation actions

### 🚪 **Review Gating (Use Ethically)**
- Route low-star reviews to support team before publication
- Configurable star rating threshold
- Automatic support team notification
- Opportunity to address concerns privately

### 📧 **Email Tracking**
- Track email opens with pixel tracking
- Click tracking for review links
- Conversion tracking
- User agent and IP logging
- Performance metrics per campaign

### 🛒 **Google Shopping Integration**
- Submit reviews to Google Merchant Center
- Product star ratings in Google Search results
- Increase click-through rates
- Configurable merchant ID

### 🌍 **Multi-Language Support**
- Create email templates in multiple languages
- Automatic language detection based on customer locale
- Translation-ready codebase

### 🎭 **Review Generator (Testing/Seeding)**
- Generate realistic fake reviews for testing
- Single or bulk generation
- Configurable rating distributions
- Date range spreading
- Custom review templates per rating
- Optional photo attachments
- **⚠️ Use ethically and in compliance with laws!**

### 📈 **Email Variants**
- Template A: Default template
- Template B: Variant 1 (different messaging)
- Template C: Variant 2 (emoji-based messaging)
- Multi-Product Template
- Follow-up Templates (1st and 2nd reminder)

### 🎨 **Design Features**
- Modern, gradient-based UI design
- Fully responsive admin interface
- Live email preview
- Color picker for button customization
- Tab-based navigation
- Animated statistics cards

---

## 📦 Installation

1. Upload the `advanced-review-manager` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure WooCommerce is installed and activated
4. Navigate to **Review Manager** in the WordPress admin menu
5. Configure your settings

---

## ⚙️ Configuration

### Basic Settings
1. Go to **Review Manager → Settings**
2. Enable automatic review reminders
3. Set days after order completion (recommended: 7-14 days)
4. Configure email content and styling

### SMS Setup (Optional)
1. Create a Twilio account at [twilio.com](https://twilio.com)
2. Get your Account SID, Auth Token, and Phone Number
3. Enter credentials in **Review Manager → Settings → SMS Integration**
4. Enable SMS reminders
5. Customize SMS message template

### Email Templates
1. Go to **Review Manager → Email Template**
2. Customize Template A (default)
3. If A/B testing is enabled, customize Templates B and C
4. Configure multi-product template
5. Set up follow-up email templates
6. Use live preview to see changes in real-time
7. Send test emails to verify

### Incentives Setup
1. Go to **Review Manager → Settings → Incentives & Rewards**
2. Enable review incentives
3. Choose incentive type (coupon, points, free shipping)
4. Set discount amount and expiry
5. Customers receive reward automatically upon review submission

### Social Proof Widgets
1. Go to **Review Manager → Settings → Social Proof Widgets**
2. Enable floating widget, badges, and/or carousel
3. Choose widget position
4. Widgets display automatically on frontend

### A/B Testing
1. Go to **Review Manager → Settings → A/B Testing**
2. Enable A/B testing
3. Create variant email templates
4. System automatically splits traffic
5. View results in **Review Manager → Analytics**

---

## 🎯 Usage

### Automatic Review Collection
- Reviews are automatically requested when orders are marked as "Completed"
- Emails are scheduled based on your configured delay
- SMS can be sent simultaneously or instead of email

### Manual Review Requests
- Go to **WooCommerce → Orders**
- Click **"Send Now"** in the Review Reminder column for any order
- Instant email delivery

### Viewing Analytics
- Go to **Review Manager → Analytics**
- View comprehensive metrics and charts
- Export data to CSV
- Track A/B test performance

### Moderating Reviews
- Reviews appear in **Comments** section
- Flagged reviews appear in moderation queue
- Approve, reject, or edit as needed

### Generating Test Reviews
1. Go to **Review Manager → Review Generator**
2. Select product(s)
3. Choose rating and quantity
4. Generate single or bulk reviews
5. **⚠️ Use responsibly for testing/seeding only**

---

## 🗄️ Database Tables

The plugin creates the following custom tables:

- `wp_arm_reminders` - Review reminder tracking
- `wp_arm_ab_tests` - A/B testing statistics
- `wp_arm_review_media` - Photo/video attachments
- `wp_arm_incentives` - Reward tracking
- `wp_arm_email_tracking` - Email performance data
- `wp_arm_sms_log` - SMS delivery logs
- `wp_arm_moderation` - Flagged reviews queue

---

## 🎨 Customization

### Email Template Variables
Use these variables in email templates:
- `{customer_name}` - Customer's first name
- `{order_id}` - Order number
- `{product_names}` - List of products
- `{store_name}` - Your store name
- `{review_url}` - Review submission link

### Shortcodes
- `[arm_review_form]` - Display review submission form anywhere

### Hooks & Filters
Developers can extend functionality using WordPress hooks:
- `arm_before_send_email` - Before sending review email
- `arm_after_review_submitted` - After customer submits review
- `arm_incentive_generated` - When reward is created

---

## 📊 Best Practices

### Timing
- Send first reminder 7-14 days after order completion
- Allow 7-10 days between follow-ups
- Don't send more than 3 total reminders

### Personalization
- Always use customer names
- Reference specific products
- Keep tone friendly and appreciative

### Incentives
- 10-15% discount is effective without hurting margins
- 30-day expiry creates urgency
- Clearly communicate reward in email

### A/B Testing
- Test one variable at a time (subject line, CTA, timing)
- Allow 100+ emails per variant for statistical significance
- Test for at least 2 weeks

### Review Gating Ethics
- Use gating to route negative reviews to support, NOT to hide them
- Always give customers option to publish after support interaction
- Comply with FTC guidelines and platform policies

### SMS Best Practices
- Keep messages under 160 characters
- Include shortened review link
- Send during business hours in customer's timezone
- Provide opt-out option

---

## ⚠️ Legal & Compliance

### Important Disclaimers

**Review Generation**: The review generation feature is intended for testing, development, and initial seeding purposes only. Posting fake reviews may violate:
- FTC guidelines in the United States
- Platform terms of service (WooCommerce, WordPress)
- Consumer protection laws in various jurisdictions

**Review Gating**: Selectively publishing only positive reviews may violate FTC guidelines. Use gating to route negative reviews to support for issue resolution, not to suppress negative feedback permanently.

**Always**:
- Comply with applicable laws and regulations
- Follow platform policies
- Be transparent with customers
- Act ethically and in good faith

---

## 🔧 Troubleshooting

### Emails Not Sending
- Check WordPress email settings
- Install and configure SMTP plugin (e.g., WP Mail SMTP)
- Verify cron jobs are running: `wp cron event list`
- Check spam folders

### SMS Not Sending
- Verify Twilio credentials are correct
- Ensure phone number includes country code (+1 for US)
- Check Twilio account balance
- Review SMS logs in database

### Photos Not Uploading
- Check PHP `upload_max_filesize` and `post_max_size`
- Verify WordPress uploads directory is writable
- Check file type restrictions

### A/B Testing Not Working
- Ensure A/B testing is enabled in settings
- Create variant templates
- Clear cache if using caching plugin

---

## 📞 Support

For support, questions, or feature requests:
- Visit: [https://gearshade.com](https://gearshade.com)
- Email: support@gearshade.com

---

## 📝 Changelog

### Version 2.0.0 (November 14, 2025)
- 🚀 **MAJOR UPDATE**: Complete plugin rebuild
- ✅ Added multi-product review requests
- ✅ Added photo and video review uploads
- ✅ Added SMS integration (Twilio)
- ✅ Added A/B testing for email templates
- ✅ Added review incentives and rewards system
- ✅ Added social proof widgets (floating, badges, carousel)
- ✅ Added advanced analytics with sentiment analysis
- ✅ Added automated follow-up email sequences
- ✅ Added review moderation with spam/profanity filters
- ✅ Added review gating functionality
- ✅ Added email tracking (opens, clicks, conversions)
- ✅ Added Google Shopping integration
- ✅ Added export functionality
- ✅ Enhanced review generator with bulk options
- ✅ Completely redesigned modern UI
- ✅ Added comprehensive database structure
- ✅ Added frontend widgets and animations
- 🔧 Improved performance and scalability
- 🎨 New gradient-based design system

### Version 1.0.0
- Initial release
- Basic email reminders
- Simple dashboard
- Fake review generation

---

## 📜 License

GPL v2 or later - [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

---

## 🙏 Credits

Developed with ❤️ by **GEARSHADE**

Special thanks to:
- WooCommerce for the amazing e-commerce platform
- Twilio for SMS infrastructure
- Chart.js for beautiful analytics visualizations
- WordPress community for inspiration and support

---

## 🚀 Roadmap

### Planned Features for v2.1:
- [ ] Integration with more SMS providers (Vonage, MessageBird)
- [ ] AI-powered review response suggestions
- [ ] Review translation to multiple languages
- [ ] Advanced sentiment analysis with NLP
- [ ] Integration with Zapier and Make (Integromat)
- [ ] Customer review history dashboard
- [ ] Product comparison reviews
- [ ] Review rewards marketplace

### Planned Features for v3.0:
- [ ] Mobile app for review management
- [ ] Live chat integration
- [ ] Video testimonial requests
- [ ] Influencer review campaigns
- [ ] Custom review forms per product category
- [ ] Review syndication to third-party sites

---

**Made with passion for e-commerce success! 🛒✨**

For more awesome WordPress plugins, visit [gearshade.com](https://gearshade.com)

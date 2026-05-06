# Brevo Email Integration Setup Guide

This guide explains how to set up email notifications for your StartSmart HR application using the Brevo API.

## What's Included

The email system automatically sends notifications for:
- **New Job Offers**: All job seekers receive an email when a startup posts a new job
- **New Applications**: Startups receive an email when a job seeker applies
- **Application Acceptance**: Job seekers receive congratulations email when accepted
- **Application Rejection**: Job seekers receive notification when rejected

## Step 1: Create a Brevo Account

1. Go to [https://app.brevo.com](https://app.brevo.com)
2. Sign up for a free account
3. Verify your email address

## Step 2: Get Your API Key

1. Log in to your Brevo account
2. Go to Settings → SMTP & API
3. Click on "API Keys" tab
4. Under v3 API Keys, click "Create a New API Key"
5. Copy the generated API key

## Step 3: Configure API Key in Your Application

1. Open `config/database.php`
2. Find the `EmailConfig` class
3. Replace `YOUR_BREVO_API_KEY_HERE` with your actual API key:

```php
class EmailConfig {
    public static $brevoApiKey = 'xkeysib-YOUR_ACTUAL_API_KEY_HERE';
    public static $emailsEnabled = true;
}
```

## Step 4: Configure Sender Email (Optional)

By default, emails are sent from `noreply@startsmart.com`. To customize:

1. Open `core/EmailService.php`
2. Find the constructor class properties
3. Modify `$senderEmail` and `$senderName`:

```php
private $senderEmail = 'your-email@yourdomain.com';
private $senderName = 'Your Company Name';
```

## Step 5: Test the Email System

### Test Job Offer Email
1. Log in as a startup account
2. Post a new job offer
3. Check your job seeker accounts - you should receive an email

### Test Application Email
1. Log in as a job seeker
2. Apply for a job
3. The startup should receive an email notification

### Test Acceptance/Rejection Email
1. Log in as a startup
2. View applications for your job postings
3. Accept or reject an application
4. The job seeker should receive a notification email

## Enabling/Disabling Email Notifications

To disable email notifications (useful for testing):

```php
class EmailConfig {
    public static $brevoApiKey = 'xkeysib-YOUR_API_KEY';
    public static $emailsEnabled = false;  // Set to false to disable
}
```

## Troubleshooting

### Emails Not Sending

1. **Check API Key**: Verify your API key is correct in `config/database.php`
2. **Check Error Logs**: PHP error logs are saved in the `error_debug.log` file
3. **Check Email Validity**: Ensure all email addresses in the database are valid
4. **Check Brevo Account**: Visit your Brevo dashboard to verify account status

### Common Issues

**Issue**: "Invalid parameter number" error
- Solution: This is fixed in the search functionality. Make sure your JobOfferController is up to date.

**Issue**: Emails going to spam
- Solution: Configure SPF/DKIM records for your domain in Brevo Settings

**Issue**: "Invalid API Key"
- Solution: Copy the API key again from Brevo dashboard, as it may have been truncated

## Email Templates Included

The system includes professional HTML email templates for:

1. **Job Offer Notification** - Highlights position, company, salary range, and location
2. **Application Confirmation** - Shows applicant details to startup
3. **Acceptance Email** - Congratulates the job seeker
4. **Rejection Email** - Professional rejection notification

All templates are responsive and work on mobile devices.

## Database Requirements

The following tables are required for email functionality:
- `users` - Contains email addresses and user details
- `job_offers` - Contains job posting information
- `applications` - Contains application records

## Security Notes

- Never commit your API key to public repositories
- Keep your Brevo API key confidential
- For production, consider storing the API key in environment variables

## Brevo Documentation

For more information about Brevo API:
- [Brevo API Documentation](https://developers.brevo.com/)
- [Email API Reference](https://developers.brevo.com/reference/send-transactional-email)
- [Getting Started Guide](https://help.brevo.com/)

## Support

For issues with Brevo, contact their support team at [support@brevo.com](mailto:support@brevo.com)

For application-specific issues, check the PHP error logs.

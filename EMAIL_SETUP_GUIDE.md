# Email Setup Guide for Candidate Password Delivery

## Quick Start (For Development)

By default, emails are logged to `storage/logs/laravel.log` - this is perfect for testing!

### View Emails in Development
```bash
# Windows PowerShell
Get-Content storage\logs\laravel.log -Tail 50 -Wait

# Or open the file directly
notepad storage\logs\laravel.log
```

## Production Setup

### Option 1: Gmail (Recommended for Testing)

1. **Enable 2-Step Verification** on your Google Account
2. **Generate App Password:**
   - Go to https://myaccount.google.com/security
   - Click "2-Step Verification"
   - Scroll down to "App passwords"
   - Generate a new app password for "Mail"
   - Copy the 16-character password

3. **Update `.env` file:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="ICSA Voting System"
```

4. **Clear config cache:**
```bash
php artisan config:clear
```

### Option 2: Mailtrap (Best for Development)

Perfect for testing without sending real emails!

1. **Sign up** at https://mailtrap.io (free)
2. **Get credentials** from your inbox
3. **Update `.env`:**
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="ICSA Voting System"
```

### Option 3: Other SMTP Providers

**Outlook/Hotmail:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
MAIL_USERNAME=your-email@outlook.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

**SendGrid:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
```

**Mailgun:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-mailgun-username
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
```

## Testing Email Functionality

### Test Email Sending

Create a test route to send an email (add to `routes/web.php`):

```php
Route::get('/test-email', function () {
    Mail::to('test@example.com')->send(new \App\Mail\CandidateCredentialsMail(
        'John Doe',
        'john@example.com',
        'johnA1B2',
        'Student Council Election 2024',
        'President'
    ));
    
    return 'Email sent! Check logs or your email inbox.';
});
```

Visit: `http://localhost:8000/test-email`

### Check If Email Was Sent

**Check logs:**
```bash
tail -f storage/logs/laravel.log
```

**Look for:**
```
Local: Email sent successfully
Mailtrap: Check your Mailtrap inbox
Gmail: Check recipient's email inbox
```

## Common Issues & Solutions

### 🚫 Issue: "Connection refused"
**Solution:** Check MAIL_HOST and MAIL_PORT are correct

### 🚫 Issue: "Authentication failed"
**Solution:** 
- Gmail: Make sure you're using App Password, not regular password
- Other: Verify credentials are correct

### 🚫 Issue: Email not received (Gmail)
**Solution:**
1. Check spam/junk folder
2. Verify "Less secure app access" is enabled (if not using App Password)
3. Check Gmail's blocked senders list

### 🚫 Issue: "Address in mailbox given does not conform to RFC"
**Solution:** Check MAIL_FROM_ADDRESS has proper email format

### 🚫 Issue: Emails go to spam
**Solution:**
- Use a proper domain email (not @gmail.com for MAIL_FROM_ADDRESS)
- Set up SPF and DKIM records (production only)
- Ask recipients to mark as "Not Spam"

## How It Works

When a candidate is created:

1. ✅ **Generate Password** - Unique password is created
2. ✅ **Create User Account** - Account is saved to database
3. ✅ **Send Email** - Email is sent automatically
4. ✅ **Show Admin Modal** - Success modal appears
5. ✅ **Candidate Receives Email** - Candidate gets credentials

### Email Flow Diagram
```
Admin Creates Candidate
        ↓
Password Generated: johnA5X9
        ↓
User Account Created
        ↓
Email Sent Automatically → john@example.com
        ↓
Success Modal Shows Admin (with password backup)
        ↓
Candidate Receives Email with Login Button
```

## Email Template Preview

The candidate receives a professional HTML email:

---

**Subject:** Your Candidate Account Credentials

**Body:**

> # Welcome to ICSA Voting System!
> 
> Hello **John Doe**,
> 
> Congratulations! You have been registered as a candidate for the **President** position in the **Student Council Election 2024** election.
> 
> ## Your Login Credentials
> 
> Please use the following credentials to access your candidate account:
> 
> | Field | Value |
> |-------|-------|
> | **Email:** | john@example.com |
> | **Password:** | `johnA5X9` |
> 
> [**Login to Your Account**]
> 
> ## Important Notes
> 
> - Please keep your password secure and do not share it with anyone.
> - You can change your password after logging in for the first time.
> - If you have any questions, please contact the election administrator.
> 
> We wish you the best of luck in the upcoming election!
> 
> Best regards,  
> ICSA Voting System Team

---

## Security Best Practices

✅ **Use App Passwords** (Gmail) - Never use your actual password  
✅ **Use TLS/SSL** - Always encrypt email connection  
✅ **Secure .env File** - Never commit to Git  
✅ **Test in Development** - Use Mailtrap or log driver first  
✅ **Monitor Logs** - Check for email failures  

## Production Checklist

Before going live:

- [ ] Update `MAIL_MAILER` to `smtp`
- [ ] Configure valid SMTP credentials
- [ ] Set proper `MAIL_FROM_ADDRESS` with your domain
- [ ] Set meaningful `MAIL_FROM_NAME`
- [ ] Test sending to real email addresses
- [ ] Clear config cache: `php artisan config:clear`
- [ ] Verify emails not going to spam
- [ ] Check email arrives within 30 seconds
- [ ] Test on multiple email providers (Gmail, Outlook, etc.)

## Support

If you need help:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Test with Mailtrap first
3. Verify SMTP credentials
4. Check firewall/antivirus blocking port 587
5. Try different SMTP provider

## Files Modified

- `app/Http/Controllers/CandidatesController.php` - Added email sending
- `app/Mail/CandidateCredentialsMail.php` - Email class
- `resources/views/emails/candidate-credentials.blade.php` - Email template
- `resources/js/pages/admin/candidates.vue` - UI updates
- `.env` - Email configuration

---

**🎉 Email setup complete! Candidates will now receive their passwords automatically.**

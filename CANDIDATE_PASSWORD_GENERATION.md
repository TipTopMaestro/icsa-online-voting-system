# Candidate Password Generation System

## Overview
The system now generates **unique passwords** for each newly created candidate and **automatically sends them via email** instead of using a static `password123` for all.

## Password Format
**Format:** `firstname + 4 random uppercase alphanumeric characters`

### Examples:
- Name: "John Doe" → Password: `johnA5X9`
- Name: "Maria Santos" → Password: `mariaK2P7`
- Name: "Jose Rizal" → Password: `joseT4M1`

## Features

### 1. Unique Password Generation
- Extracts the first name from the full name
- Converts it to lowercase
- Appends 4 random uppercase alphanumeric characters
- Ensures uniqueness by checking against existing passwords

### 2. Automatic Email Notification ✉️
- **Credentials are sent automatically** to the candidate's email
- Admin no longer needs to manually share passwords
- Professional email template with all necessary information
- Email includes:
  - Welcome message
  - Login credentials (email & password)
  - Direct login link button
  - Security reminders

### 3. Security
- Passwords are hashed using Laravel's `Hash::make()` before storage
- No two candidates will have the same password
- Random characters provide reasonable security while being memorable
- Password shown in admin panel for backup/reference only

### 4. User Experience
- After creating a candidate, a modal displays the generated password
- Email notification automatically sent to candidate
- The password can be copied to clipboard with one click
- Confirmation that email was sent successfully
- Password is available for admin reference if needed

## Implementation Details

### Backend Components

#### 1. Password Generation (`CandidatesController.php`)
```php
private function generateUniquePassword(string $name): string
{
    $firstName = strtolower(explode(' ', trim($name))[0]);
    $firstName = preg_replace('/[^a-z0-9]/', '', $firstName);
    
    do {
        $randomChars = Str::upper(Str::random(4));
        $password = $firstName . $randomChars;
        $exists = User::where('password', Hash::make($password))->exists();
    } while ($exists);
    
    return $password;
}
```

#### 2. Email Sending
```php
Mail::to($user->email)->send(new CandidateCredentialsMail(
    $user->name,
    $user->email,
    $generatedPassword,
    $candidate->election->title,
    $candidate->position->name
));
```

#### 3. Mail Class (`CandidateCredentialsMail.php`)
- Uses Laravel's Mailable system
- Markdown-based email template
- Includes all candidate information
- Professional formatting

### Frontend (`candidates.vue`)
- Success modal shows after candidate creation
- Displays confirmation that email was sent
- Shows the generated password for admin reference
- Copy-to-clipboard functionality
- Clear visual feedback with icons

## Email Template Preview

The candidate will receive a professional email containing:

```
Welcome to [App Name]!

Hello [Candidate Name],

Congratulations! You have been registered as a candidate 
for the [Position Name] position in the [Election Title] election.

Your Login Credentials
━━━━━━━━━━━━━━━━━━━━
Email: candidate@email.com
Password: johnA5X9

[Login to Your Account Button]

Important Notes
• Please keep your password secure
• You can change your password after first login
• Contact admin if you have questions

Best regards,
[App Name] Team
```

## Usage

### Creating a Candidate
1. Navigate to **Candidate Management**
2. Click **"+ Add Candidate"**
3. Fill in all required information including **email address**
4. Click **"Create Candidate"**
5. ✅ Email is **automatically sent** to the candidate
6. A modal appears confirming email was sent
7. Password is shown for admin reference (optional backup)

### What the Candidate Receives
The candidate will receive an email at their registered address with:
- **Subject:** "Your Candidate Account Credentials"
- **Email:** Their registered email
- **Password:** The auto-generated unique password
- **Login Link:** Direct link to the login page

## Email Configuration

### Development Environment
By default, emails are logged to `storage/logs/laravel.log` for testing.

**View emails in logs:**
```bash
tail -f storage/logs/laravel.log
```

### Production Environment
Update your `.env` file with SMTP settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Popular SMTP Providers:**
- Gmail: `smtp.gmail.com:587`
- Outlook: `smtp-mail.outlook.com:587`
- SendGrid: `smtp.sendgrid.net:587`
- Mailgun: `smtp.mailgun.org:587`

### Testing Email in Development

To test email sending locally without SMTP:
1. Use Mailtrap.io (free testing SMTP)
2. Update `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
```

## Benefits

✅ **No Manual Work** - Admin doesn't need to manually share passwords  
✅ **Instant Delivery** - Candidate receives credentials immediately  
✅ **Professional** - Branded email template  
✅ **Secure** - Password sent directly to candidate's email  
✅ **Backup Available** - Admin can still see password if needed  
✅ **Error Handling** - If email fails, candidate creation still succeeds  

## Best Practices

1. **Verify Email Addresses:** Ensure candidate email is correct before creating account
2. **Check Spam Folder:** Remind candidates to check spam/junk folders
3. **Email Configuration:** Test email sending before production use
4. **Password Backup:** Copy password from modal as backup if needed

## Technical Notes

- Passwords are generated server-side for security
- Email sending is wrapped in try-catch to prevent failure
- If email fails, candidate creation still succeeds (logged in logs)
- The password is returned in API response for admin reference
- Special characters in names are removed to ensure valid passwords
- Uses Laravel's built-in Mail system with Markdown templates

## Troubleshooting

### Email Not Received?
1. Check spam/junk folders
2. Verify email configuration in `.env`
3. Check `storage/logs/laravel.log` for errors
4. Ensure SMTP credentials are correct
5. Admin can manually share the password from the success modal

### Email Sending Fails?
- Candidate creation will still succeed
- Error is logged but doesn't block the process
- Admin can copy password from modal and share manually
- Check Laravel logs for specific error details

## Future Enhancements
- Add password strength requirements
- Implement password change functionality for candidates
- Add email templates for password reset
- Queue email sending for better performance
- Add email delivery status tracking
- Send reminder emails before election day

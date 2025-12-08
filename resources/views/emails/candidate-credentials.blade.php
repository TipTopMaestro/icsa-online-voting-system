<x-mail::message>
# Welcome to ISCA Voting System!

Hello **{{ $candidateName }}**,

Congratulations! You have been registered as a candidate for the **{{ $positionName }}** position in the **{{ $electionTitle }}** election.

## Your Login Credentials

Please use the following credentials to access your candidate account:

<x-mail::panel>
**Email:** {{ $email }}  
**Password:** `{{ $password }}`
</x-mail::panel>

<x-mail::button :url="$loginUrl">
Login to Your Account
</x-mail::button>

## Important Notes

- Please keep your password secure and do not share it with anyone.
- You can change your password after logging in for the first time.
- If you have any questions, please contact Master Froyd.

We wish you the best of luck in the upcoming election!

Best regards,<br>
ISCA Voting System Team
</x-mail::message>

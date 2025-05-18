<!DOCTYPE html>
<html>
<head>
    <title>Reset Your Password</title>
</head>
<body>
    <h2><strong>Hello!</strong></h2>
    <p>You requested a password reset.</p>
    <p>
        Click the button below to reset your password:
    </p>
    <a href="{{ $resetUrl }}" style="background-color: #4CAF50; padding: 10px 20px; color: white; text-decoration: none;">
        Reset Password
    </a>
    <p>This link will expire in 60 minutes.</p>
    <p>If you did not request this, no further action is required.</p>
    <hr>
    <p>Regards,<br><strong>Task Submission System Team.</strong></p>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <title>Update Password</title>
</head>
<body>
    <p>Hi {{ $name }},</p>
    <p>Your email has been updated. Please use the link below to set your new password:</p>
    <p><a href="{{ $setupLink }}">{{ $setupLink }}</a></p>
    <p>Thanks,<br>ATAI Team</p>
</body>
</html>

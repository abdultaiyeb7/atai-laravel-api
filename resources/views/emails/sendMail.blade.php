<!-- <!DOCTYPE html>
<html>
<head>
    <title>{{ $data['subject'] }}</title>
</head>
<body>
    <h3>Hello, {{ $data['name'] }}</h3>
    <p>{{ $data['message'] }}</p>
    <p>Thank you for using our service.</p>
</body>
</html> -->


<!DOCTYPE html>
<html>
<head>
    <title>{{ $data['subject'] }}</title>
</head>
<body>
    <h3>Dear {{ $data['name'] }},</h3>
    <p>You have been added as an agent on ATai Chatbot. Please verify your email to complete the registration.</p>
    <p>Click the link below to verify your email:</p>
    <p><a href="{{ $data['verification_link'] }}" style="padding: 10px 20px; background-color: #28a745; color: #fff; text-decoration: none; border-radius: 5px;">Verify Email</a></p>
    <br>
    <p>Best regards,</p>
    <p>[Admin Name]</p>
</body>
</html>
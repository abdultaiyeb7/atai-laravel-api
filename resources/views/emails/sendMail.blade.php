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


{{-- <!DOCTYPE html>
<html>
<head>
    <title>{{ $data['subject'] }}</title>
    <style>
        /* Prevent text selection and right-click */
        body {
            -webkit-user-select: none; /* Chrome/Safari */
            -moz-user-select: none;    /* Firefox */
            -ms-user-select: none;     /* IE10+ */
            user-select: none;         /* Standard */
        }
        a {
            pointer-events: auto;
            text-decoration: none;
        }

        .btn-link {
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff !important;
            border-radius: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <h3>Dear {{ $data['name'] }},</h3>
    <p>You have been added as an agent on ATai Chatbot. Please verify your email to complete the registration.</p>
    <p>Click the link below to verify your email:</p>
    <p>These link will be expire within 5 mins from the time it is sent to your email</p>
    <p><a href="{{ $data['verification_link'] }}" style="padding: 10px 20px; background-color: #28a745; color: #fff; text-decoration: none; border-radius: 5px;">Verify Email</a></p>
    <br>
    <p>Best regards,</p>
    <p>[Admin Name]</p>
</body>
</html> --}}
<!DOCTYPE html>
<html>
<head>
    <title>{{ $data['subject'] }}</title>
    <style>
        /* Disable text selection and context menu */
        body {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            margin: 20px;
            font-family: Arial, sans-serif;
        }

        a {
            pointer-events: auto;
            text-decoration: none;
        }

        .btn-link {
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff !important;
            border-radius: 5px;
            display: inline-block;
            cursor: pointer;
        }
    </style>
    <script>
        // Disable right-click
        document.addEventListener('contextmenu', e => e.preventDefault());

        // Disable common keyboard shortcuts (Ctrl+C, Ctrl+U, etc.)
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && ['c', 'u', 's', 'a'].includes(e.key.toLowerCase())) {
                e.preventDefault();
            }
        });
        document.addEventListener('contextmenu', event => event.preventDefault());
        document.addEventListener('copy', event => event.preventDefault());
    </script>
</head>
<body oncopy="return false" oncut="return false" onpaste="return false">
    <h3>Dear {{ $data['name'] }},</h3>
    <p>You have been added as an agent on ATai Chatbot. Please verify your email to complete the registration.</p>
    <p>Click the button below to verify your email:</p>
    <p>
        <a href="{{ $data['verification_link'] }}" class="btn-link">Verify Email</a>
    </p>
    <br>
    <p>Best regards,<br>[Admin Name]</p>
</body>
</html>

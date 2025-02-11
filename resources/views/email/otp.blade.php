<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>OTP Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        .container {
            max-width: 400px;
            background: #ffffff;
            padding: 20px;
            margin: 50px auto;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
        }
        .otp {
            font-size: 24px;
            font-weight: bold;
            color: #007BFF;
            background: #eaf4ff;
            padding: 10px;
            display: inline-block;
            border-radius: 6px;
            margin: 10px 0;
        }
        p {
            color: #555;
            font-size: 16px;
        }
        .footer {
            font-size: 14px;
            color: #999;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>OTP Verification</h2>
        <p>Your One-Time Password (OTP) for verification is:</p>
        <div class="otp">{{ $otp }}</div>
        <p>Please enter this OTP to verify your account. This code is valid for a limited time.</p>
        <div class="footer">If you did not request this, please ignore this email.</div>
    </div>
</body>
</html>

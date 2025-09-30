<?php
// INDEX::Prevent Direct Access to Private Files
if (!defined('APP_RUN')) {
    header("HTTP/1.0 403 Forbidden");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden - Access Denied</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background-color: #000000;
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1.6;
        }

        .container {
            text-align: center;
            max-width: 500px;
            padding: 40px;
        }

        .error-image {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: #1a1a1a;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #333;
        }

        .error-image img {
            max-width: 60px;
            opacity: 0.7;
        }

        .error-code {
            font-size: 5rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 10px;
            letter-spacing: 2px;
        }

        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #e0e0e0;
            margin-bottom: 20px;
        }

        .error-message {
            font-size: 1.1rem;
            color: #888;
            margin-bottom: 30px;
            font-weight: 400;
        }

        .quota-box {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }

        .quota-text {
            font-size: 0.95rem;
            color: #666;
            font-style: italic;
            line-height: 1.5;
        }

        .action-box {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #333;
        }

        .home-link {
            display: inline-block;
            padding: 10px 24px;
            background: #fff;
            color: #000;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .home-link:hover {
            background: #e0e0e0;
        }

        .status-info {
            font-size: 0.8rem;
            color: #555;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            .error-code {
                font-size: 4rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-image">
            <img src='../img/403-err-image.png' alt="403 Error" onerror="this.style.display='none'"/>
        </div>
        
        <div class="error-code">403</div>
        
        <h1 class="error-title">FORBIDDEN</h1>
        
        <p class="error-message">
            You don't have permission to access this resource.
        </p>

        <div class="quota-box">
            <p class="quota-text">
                Wrong door, champ. Stop poking where you don’t belong. Go get a life.
            </p>
        </div>

        <div class="action-box">
            <a href="/elocker" class="home-link">Return to Homepage</a>
            <p class="status-info">HTTP 403 • Forbidden</p>
        </div>
    </div>
</body>
</html>
<?php
    exit();
}
?>
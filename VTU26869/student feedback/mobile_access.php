<?php
require_once 'config/config.php';

// Detect local IP
$detectedIp = getHostByName(getHostName());
// Fallback if needed (127.0.0.1 is not useful for mobile)
$localIp = ($detectedIp !== '127.0.0.1') ? $detectedIp : "192.168.29.151";

// Construct the base URL for the project
$mobileUrl = "http://" . $localIp . "/student%20feedback/";

$qrApi = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($mobileUrl);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Access - Student Feedback System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4cc9f0;
            --bg: #f8f9fa;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .mobile-card {
            background: white;
            border-radius: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .card-header-gradient {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
        }

        .header-circles div {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .circle-1 {
            width: 100px;
            height: 100px;
            top: -30px;
            right: -30px;
        }

        .circle-2 {
            width: 60px;
            height: 60px;
            bottom: 20px;
            left: -20px;
        }

        .qr-section {
            padding: 40px;
            text-align: center;
        }

        .qr-container {
            background: white;
            padding: 15px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            display: inline-block;
            margin-bottom: 25px;
            transition: transform 0.3s ease;
        }

        .qr-container:hover {
            transform: scale(1.02);
        }

        .qr-image {
            width: 200px;
            height: 200px;
        }

        .instruction-box {
            background: #f0f3ff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .link-input-group {
            position: relative;
        }

        .form-control-custom {
            border-radius: 15px;
            padding: 12px 20px;
            background: #f8f9fa;
            border: 1px solid #eee;
            font-family: monospace;
            font-size: 0.9rem;
            color: #666;
        }

        .copy-btn {
            border-radius: 12px;
            padding: 8px 15px;
            background: var(--primary);
            color: white;
            border: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .copy-btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }

        .back-btn {
            text-decoration: none;
            color: #888;
            font-weight: 500;
            transition: color 0.3s;
            display: inline-block;
            margin-top: 10px;
        }

        .back-btn:hover {
            color: var(--primary);
        }

        .step-badge {
            width: 24px;
            height: 24px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            margin-right: 10px;
        }
    </style>
</head>

<body>

    <div class="mobile-card">
        <div class="card-header-gradient">
            <div class="header-circles">
                <div class="circle-1"></div>
                <div class="circle-2"></div>
            </div>
            <div class="mb-3">
                <i class="fas fa-mobile-alt fa-3x"></i>
            </div>
            <h2 class="fw-bold mb-1">Mobile Access</h2>
            <p class="opacity-75 mb-0">Open the system on your mobile device</p>
        </div>

        <div class="qr-section">
            <div class="instruction-box text-start">
                <div class="mb-2 d-flex align-items-center">
                    <span class="step-badge">1</span>
                    <span>Connect your phone to the <b>same Wi-Fi</b>.</span>
                </div>
                <div class="d-flex align-items-center">
                    <span class="step-badge">2</span>
                    <span>Scan the QR code below.</span>
                </div>
            </div>

            <div class="qr-container">
                <img src="<?php echo $qrApi; ?>" alt="QR Code" class="qr-image">
            </div>

            <div class="link-input-group mb-4">
                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Or enter this link
                    manually</label>
                <div class="input-group">
                    <input type="text" id="mobileUrl" class="form-control form-control-custom"
                        value="<?php echo $mobileUrl; ?>" readonly>
                    <button class="btn copy-btn" onclick="copyLink()">
                        <i class="fas fa-copy me-1"></i> Copy
                    </button>
                </div>
            </div>

            <div class="d-grid gap-2 mb-3">
                <a href="https://wa.me/?text=<?php echo urlencode("Access the Student Feedback System here: " . $mobileUrl); ?>"
                    target="_blank" class="btn btn-success rounded-pill py-2 fw-bold">
                    <i class="fab fa-whatsapp me-2"></i>Share via WhatsApp
                </a>
            </div>

            <a href="login.php" class="back-btn">
                <i class="fas fa-arrow-left me-1"></i> Back to Login
            </a>
        </div>
    </div>

    <script>
        function copyLink() {
            var copyText = document.getElementById("mobileUrl");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);

            const btn = event.currentTarget;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
            btn.style.background = '#28a745';

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = '';
            }, 2000);
        }
    </script>

</body>

</html>
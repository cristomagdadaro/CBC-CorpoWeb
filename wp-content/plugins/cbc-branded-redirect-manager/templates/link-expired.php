<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php esc_html_e( 'Link Expired | DA-Crop Biotechnology Center', 'cbc-golink' ); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: "League Spartan", sans-serif;
            background: linear-gradient(135deg, #444, #777);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            color: #fff;
        }
        .cbc-card {
            background: #fff;
            color: #2b2b2b;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 520px;
            padding: 32px 28px;
            animation: fadeIn 0.6s ease;
        }
        .cbc-redirect-loader {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 18px 0 14px;
        }
        .cbc-loader-spinner {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 4px solid rgba(26, 78, 19, 0.18);
            border-top-color: #1a4e13;
            animation: brmSpin 0.9s linear infinite;
        }
        .cbc-loader-text {
            font-size: 1rem;
            font-weight: 600;
            color: #1a4e13;
            margin: 0;
        }
        .cbc-logo {
            max-width: 120px;
            margin-bottom: 16px;
        }
        h2 {
            color: #a00;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 8px;
        }
        p {
            font-size: 1rem;
            color: #444;
            margin: 8px 0;
        }
        a {
            color: #1a4e13;
            font-weight: 600;
            text-decoration: none;
        }
        a:hover { text-decoration: underline; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes brmSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="cbc-card">
        <img src="<?php echo esc_url( $logo_image ); ?>" alt="<?php esc_attr_e( 'Logo', 'cbc-golink' ); ?>" class="cbc-logo">
        <h2><?php esc_html_e( 'Link Expired', 'cbc-golink' ); ?></h2>
        <p><?php esc_html_e( 'Sorry, this redirect link is no longer active.', 'cbc-golink' ); ?></p>
        <p><?php printf( __( 'You can return to the <a href="%s">main website</a>.', 'cbc-golink' ), esc_url( home_url() ) ); ?></p>
    </div>
</body>
</html>

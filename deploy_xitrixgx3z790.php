<?php
echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deployment Status</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #1e1e1e;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        h1 {
            border-bottom: 1px solid #444;
            padding-bottom: 10px;
            margin-top: 0;
            color: #ffffff;
        }
        pre {
            background-color: #252526;
            border: 1px solid #333;
            padding: 15px;
            border-radius: 5px;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
            color: #d4d4d4;
        }
        .timestamp {
            color: #888;
            font-size: 0.9em;
            text-align: right;
        }
        h2 {
            color: #bbbbbb;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Deployment Status</h1>
HTML;

    $timestamp = date('Y-m-d H:i:s');
    echo "<p class='timestamp'>Last updated: " . htmlspecialchars($timestamp) . "</p>";

    chdir('C:/nginx-1.24.0/vhost/cbccorpo');

    $command = 'git pull 2>&1';
    $output = shell_exec($command);

    file_put_contents('deploy_log.txt', $timestamp . "\n" . $output . "\n\n", FILE_APPEND);

    echo "<h2>Git Output:</h2>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";

    echo <<<HTML
    </div>
</body>
</html>
HTML;
?>
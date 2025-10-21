<?php
    // Optional: Add a security check here to ensure the request is from GitHub
    // by validating the secret key configured in the webhook (highly recommended!)

    // Go to the correct directory
    chdir('C:/nginx-1.24.0/vhost/cbccorpo');

    // Execute the git pull command and capture output
    $output = shell_exec('git pull 2>&1');
    
    // Log the pull result for debugging
    file_put_contents('deploy_log.txt', date('Y-m-d H:i:s') . "\n" . $output . "\n\n", FILE_APPEND);

    // Optional: Run any post-pull commands (e.g., clear cache, restart service)
    // shell_exec('/path/to/restart/service.sh'); 
?>
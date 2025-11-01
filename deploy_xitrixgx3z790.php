<?php
    chdir('C:/nginx-1.24.0/vhost/cbccorpo');
    $output = shell_exec('git pull 2>&1');
    file_put_contents('deploy_log.txt', date('Y-m-d H:i:s') . "\n" . $output . "\n\n", FILE_APPEND);
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
?>
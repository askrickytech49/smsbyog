<?php
$file = "C:\\Users\\ricky\\Downloads\\smsbyogc_app.sql";
if (file_exists($file)) {
    echo "File exists! Size: " . filesize($file) . " bytes\n";
    $content = file_get_contents($file);
    if (strpos($content, "junioranthony0011") !== false) {
        echo "Found junioranthony0011!\n";
    } else {
        echo "Did NOT find junioranthony0011 in this file.\n";
    }
} else {
    echo "File does not exist!\n";
}
?>

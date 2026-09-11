<?php
$file = file_get_contents("smsbyogc_main.sql");
preg_match("/INSERT INTO `user_data`.*?VALUES.*?;/s", $file, $matches);
if ($matches) {
    echo "Current SQL file has user_data:\n" . substr($matches[0], 0, 500) . "\n...\n";
} else {
    echo "No user_data in current SQL file.\n";
}
?>

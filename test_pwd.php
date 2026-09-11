<?php
$hash = '$2y$10$Suz7jo7zuhlfnoKt/d/OlOI7vF1.J15Abg7Ln5UCjtJv6En5i7ZRa';
$passwords = ['movet123', 'Movet123', 'movet123 ', 'movettech123', 'MovetTech', 'movet1234', 'movet@123', md5('movet123'), sha1('movet123')];
foreach($passwords as $p) {
    if(password_verify($p, $hash)) {
        echo "MATCH: " . $p . PHP_EOL;
        exit;
    }
}
echo "No matches found.";
?>

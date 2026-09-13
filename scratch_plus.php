<?php
include 'include/config.php';
$res = mysqli_query($conn, "SELECT id, number FROM active_number WHERE number LIKE '+%' LIMIT 10");
echo "Numbers stored with + prefix:\n";
while ($r = mysqli_fetch_assoc($res)) {
    echo "  id={$r['id']} number={$r['number']}\n";
}

$res2 = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM active_number WHERE number LIKE '+%'");
$r2 = mysqli_fetch_assoc($res2);
echo "\nTotal records with + prefix: {$r2['cnt']}\n";

$res3 = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM active_number WHERE number NOT LIKE '+%'");
$r3 = mysqli_fetch_assoc($res3);
echo "Total records without + prefix: {$r3['cnt']}\n";

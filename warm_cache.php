<?php
include 'include/config.php';
// We will skip auth checks for warming
$check_token = 1;
$_GET['service'] = 'whatsapp';
$_GET['server'] = '1';

echo "Warming up Server 1 cache...\n";
include 'api/service/getServices1.php';
echo "Done.\n\n";

echo "Warming up Server 2 cache...\n";
include 'api/service/getServices2All.php';
echo "Done.\n\n";

echo "Warming up USA Only cache...\n";
include 'api/service/getServiceUsa.php';
echo "Done.\n\n";

echo "Warming up USA + Canada cache...\n";
include 'api/service/getServicesUsaCaAll.php';
echo "Done.\n\n";

echo "All caches warmed up successfully!\n";

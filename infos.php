<?php
include  'include/config.php';

// Get today's date with the time set to 00:00:00
$today_start = date('Y-m-d 00:00:00');

// SQL query to fetch the required records
$sql = "
    SELECT user_id, SUM(service_price) as total_amount, COUNT(*) as service_count
    FROM active_number
    WHERE status = 1 AND buy_time >= '$today_start'
    GROUP BY user_id
    ORDER BY total_amount DESC
";

// Execute the query
$result = $conn->query($sql);

if ($result === false) {
    die("Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Service Purchases</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>User Service Purchases</h1>
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Total Amount</th>
                <th>Service Count</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_amount']); ?></td>
                        <td><?php echo htmlspecialchars($row['service_count']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No results found for today.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

<?php
$conn->close();
?>

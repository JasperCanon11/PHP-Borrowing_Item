<?php
session_start();
if(!isset($_SESSION['admin_logged_in'])){
    header("Location: login.php");
    exit;
}

include 'config.php';

// Process borrow request
if(isset($_POST['borrow'])){
    $item_id = intval($_POST['item_id']);
    $borrower_name = $conn->real_escape_string($_POST['borrower_name']);
    $quantity = intval($_POST['quantity']);

    // Fetch item
    $itemQuery = $conn->query("SELECT * FROM items WHERE id = $item_id");
    if($itemQuery->num_rows == 0){
        die("Item not found.");
    }
    $item = $itemQuery->fetch_assoc();
    $item_name = $item['item_name'];

    // Validate quantity
    if($quantity <= 0 || $quantity > $item['stock']){
        die("Invalid quantity. Available stock: ".$item['stock']);
    }

    // Insert into borrowings table
    $stmt = $conn->prepare("INSERT INTO borrowings (item_id, item_name, quantity, borrower_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $item_id, $item_name, $quantity, $borrower_name);
    $stmt->execute();
    $stmt->close();

    // Update items stock
    $conn->query("UPDATE items SET stock = stock - $quantity WHERE id = $item_id");
}

// Fetch all borrowings
$borrowings = $conn->query("SELECT * FROM borrowings ORDER BY borrowed_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Borrowings</title>
<style>
body {
    background-color: black;
    font-family: Arial, sans-serif;
}
.container {
    width: 850px;
    margin: 50px auto;
    background-color: gold; /* container color */
    padding: 20px;
    border-radius: 10px;
}
h2 {
    text-align: center;
    color: black; /* header text */
}
table {
    width: 100%;
    border-collapse: collapse;
    text-align: center;
}
th, td {
    padding: 10px;
    border: 1px solid black;
    color: black; /* table text */
}
th {
    background-color: #ffd700; /* slightly darker gold for header */
    color: black;
}
a {
    color: gold;
    background-color: black;
    padding: 5px 10px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
}
a:hover {
    background-color: #333;
    color: gold;
}
</style>
</head>
<body>
<div class="container">
<h2>All Borrowings</h2>
<table>
<tr>
    <th>ID</th>
    <th>Item Name</th>
    <th>Quantity</th>
    <th>Borrower Name</th>
    <th>Borrowed At</th>
    <th>Returned At</th>
</tr>
<?php
if($borrowings->num_rows > 0){
    while($row = $borrowings->fetch_assoc()){
        echo "<tr>";
        echo "<td>".$row['id']."</td>";
        echo "<td>".$row['item_name']."</td>";
        echo "<td>".$row['quantity']."</td>";
        echo "<td>".$row['borrower_name']."</td>";
        echo "<td>".$row['borrowed_at']."</td>";
        echo "<td>".($row['returned_at'] ?? '-')."</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No borrowings yet.</td></tr>";
}
?>
</table>
<p style="text-align:center; margin-top:20px;">
    <a href="items.php">Back to Items</a>
</p>
</div>
</body>
</html>

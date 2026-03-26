<?php
session_start();
if(!isset($_SESSION['admin_logged_in'])){
    header("Location: login.php");
    exit;
}

include 'config.php';

// Partial return
if(isset($_POST['partial_return'])){
    $borrow_id = intval($_POST['borrow_id']);
    $return_quantity = intval($_POST['return_quantity']);

    $borrowQuery = $conn->query("SELECT * FROM borrowings WHERE id = $borrow_id");
    if($borrowQuery->num_rows > 0){
        $borrow = $borrowQuery->fetch_assoc();
        $remaining = $borrow['quantity'] - ($borrow['quantity_returned'] ?? 0);

        if($return_quantity > 0 && $return_quantity <= $remaining){
            $conn->query("UPDATE items SET stock = stock + $return_quantity WHERE id = ".$borrow['item_id']);

            $new_quantity_returned = ($borrow['quantity_returned'] ?? 0) + $return_quantity;

            if($new_quantity_returned == $borrow['quantity']){
                $conn->query("UPDATE borrowings SET quantity_returned = $new_quantity_returned, returned_at = NOW() WHERE id = $borrow_id");
            } else {
                $conn->query("UPDATE borrowings SET quantity_returned = $new_quantity_returned WHERE id = $borrow_id");
            }
        }
    }
    header("Location: control_panel.php");
    exit;
}

// Add stock to existing item
if(isset($_POST['add_stock'])){
    $item_id = intval($_POST['item_id']);
    $additional_stock = intval($_POST['additional_stock']);

    if($additional_stock > 0){
        $conn->query("UPDATE items SET stock = stock + $additional_stock WHERE id = $item_id");
    }
    header("Location: control_panel.php");
    exit;
}

// Reduce stock
if(isset($_POST['reduce_item_stock'])){
    $item_id = intval($_POST['item_id']);
    $reduce = intval($_POST['reduce_stock']);

    $itemQuery = $conn->query("SELECT stock FROM items WHERE id = $item_id");
    if($itemQuery->num_rows > 0){
        $item = $itemQuery->fetch_assoc();
        $new_stock = max(0, $item['stock'] - $reduce);
        $conn->query("UPDATE items SET stock = $new_stock WHERE id = $item_id");
    }
    header("Location: control_panel.php");
    exit;
}

// Delete item
if(isset($_POST['delete_item'])){
    $item_id = intval($_POST['item_id']);
    $conn->query("DELETE FROM items WHERE id = $item_id");
    // Optional: delete borrowings of this item
    // $conn->query("DELETE FROM borrowings WHERE item_id = $item_id");
    header("Location: control_panel.php");
    exit;
}

// Add new item
if(isset($_POST['add_new_item'])){
    $new_item_name = $conn->real_escape_string($_POST['new_item_name']);
    $new_item_stock = intval($_POST['new_item_stock']);

    $check = $conn->query("SELECT * FROM items WHERE item_name = '$new_item_name'");
    if($check->num_rows > 0){
        $conn->query("UPDATE items SET stock = stock + $new_item_stock WHERE item_name = '$new_item_name'");
    } else {
        $conn->query("INSERT INTO items (item_name, stock) VALUES ('$new_item_name', $new_item_stock')");
    }
    header("Location: control_panel.php");
    exit;
}

// Fetch borrowings and items
$borrowings = $conn->query("SELECT * FROM borrowings ORDER BY borrowed_at DESC");
$items = $conn->query("SELECT * FROM items ORDER BY item_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Control Panel</title>
<style>
body { background-color: black; 
font-family: Arial, sans-serif; 
}
.container { 
    width: 1000px; 
    margin: 50px auto; 
    background-color: gold; 
    padding: 20px; border-radius: 10px; 
}
h2, h3 { 
    text-align: center; 
    color: black; 
}
table { 
    width: 100%; 
    border-collapse: collapse; 
    text-align: center; 
    margin-bottom: 30px; 
}
th, td { 
    padding: 10px; 
    border: 1px solid black; 
    color: black; 
}
th { 
    background-color: #ffd700; 
    color: black; 
}
a.button { 
    color: gold; 
    background-color: black; 
    padding: 5px 10px; 
    border-radius: 5px; 
    text-decoration: none; 
    font-weight: bold; 
}
a.button:hover { 
    background-color: #333; 
    color: gold; 
}
form { 
    display: inline-block; 
}
input[type=number], input[type=text] { 
    padding: 5px; 
    border-radius: 5px; 
    border: 1px solid black; 
    text-align: center; 
}
input[type=submit] { 
    padding: 5px 10px; 
    border-radius: 5px; 
    border: none; 
    background-color: black; 
    color: gold; 
    font-weight: bold; 
    cursor: pointer; 
}
input[type=submit]:hover { 
    background-color: #333; 
    color: gold; }
</style>
</head>
<body>
<div class="container">
<h2>Control Panel - Manage Borrowings & Stock</h2>

<!-- Borrowings Table -->
<h3>Borrowings</h3>
<table>
<tr>
    <th>ID</th>
    <th>Item Name</th>
    <th>Quantity</th>
    <th>Returned</th>
    <th>Borrower Name</th>
    <th>Borrowed At</th>
    <th>Returned At</th>
    <th>Action</th>
</tr>
<?php
if($borrowings->num_rows > 0){
    while($row = $borrowings->fetch_assoc()){
        $quantity_returned = $row['quantity_returned'] ?? 0;
        $remaining = $row['quantity'] - $quantity_returned;

        echo "<tr>";
        echo "<td>".$row['id']."</td>";
        echo "<td>".$row['item_name']."</td>";
        echo "<td>".$row['quantity']."</td>";
        echo "<td>$quantity_returned / ".$row['quantity']."</td>";
        echo "<td>".$row['borrower_name']."</td>";
        echo "<td>".$row['borrowed_at']."</td>";
        echo "<td>".($row['returned_at'] ?? '-')."</td>";

        if($remaining > 0){
            echo "<td>
            <form method='post'>
                <input type='hidden' name='borrow_id' value='".$row['id']."'>
                <input type='number' name='return_quantity' min='1' max='$remaining' value='$remaining' required>
                <input type='submit' name='partial_return' value='Return'>
            </form>
            </td>";
        } else {
            echo "<td>Returned</td>";
        }
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='8'>No borrowings yet.</td></tr>";
}
?>
</table>

<!-- Add / Reduce / Delete Stock Section -->
<h3>Manage Items</h3>
<table>
<tr>
    <th>Item Name</th>
    <th>Current Stock</th>
    <th>Add Stock</th>
    <th>Reduce / Delete</th>
</tr>
<?php
if($items->num_rows > 0){
    while($item = $items->fetch_assoc()){
        echo "<tr>";
        echo "<td>".$item['item_name']."</td>";
        echo "<td>".$item['stock']."</td>";
        echo "<td>
            <form method='post'>
                <input type='hidden' name='item_id' value='".$item['id']."'>
                <input type='number' name='additional_stock' min='1' required>
                <input type='submit' name='add_stock' value='Add'>
            </form>
        </td>";
        echo "<td>
            <form method='post' style='display:inline-block;'>
                <input type='hidden' name='item_id' value='".$item['id']."'>
                <input type='number' name='reduce_stock' min='1' max='".$item['stock']."' placeholder='Reduce' required>
                <input type='submit' name='reduce_item_stock' value='Reduce'>
            </form>
            <form method='post' style='display:inline-block;'>
                <input type='hidden' name='item_id' value='".$item['id']."'>
                <input type='submit' name='delete_item' value='Delete' onclick='return confirm(\"Are you sure you want to delete this item?\");'>
            </form>
        </td>";
        echo "</tr>";
    }
}
?>
</table>

<!-- Add New Item Section -->
<h3>Add New Item</h3>
<form method="post" style="text-align:center; margin-bottom:30px;">
    <input type="text" name="new_item_name" placeholder="Item Name" required>
    <input type="number" name="new_item_stock" min="1" placeholder="Initial Stock" required>
    <input type="submit" name="add_new_item" value="Add Item">
</form>

<p style="text-align:center;">
    <a href="items.php" class="button">Back to Items</a>
</p>
</div>
</body>
</html>

<?php
session_start();
if(!isset($_SESSION['admin_logged_in'])){
    header("Location: login.php");
    exit;
}

include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lab Items</title>
    <style>
        body {
            background-color: black;
            font-family: Arial, sans-serif;
        }

        .items-container {
            background-color: gold;
            padding: 30px;
            border-radius: 10px;
            width: 750px;
            margin: 50px auto;
            box-shadow: 0 0 20px #000;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #000;
            vertical-align: middle;
        }

        th {
            background-color: black;
            color: gold;
        }

        a.button, input[type="submit"] {
            background-color: black;
            color: gold;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        a.button:hover, input[type="submit"]:hover {
            background-color: #333;
        }

        input[type="text"], input[type="number"] {
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #000;
            text-align: center;
        }

        form {
            display: inline-block;
        }

        .top-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        input[type="submit"]:disabled {
            background-color: #555;
            color: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<div class="items-container">

    <!-- Top Buttons -->
    <div class="top-buttons">
        <a href="logout.php" class="button">Logout</a>
        <a href="control_panel.php" class="button">Control Panel</a>
    </div>

    <h2>Lab Items List</h2>
    <table>
        <tr>
            <th>Item Name</th>
            <th>Stock</th>
            <th>Borrower Name</th>
            <th>Quantity</th>
            <th>Action</th>
        </tr>

        <?php
        $result = $conn->query("SELECT * FROM items ORDER BY item_name ASC");

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                echo "<tr>";
                echo "<td>".$row['item_name']."</td>";
                echo "<td>".$row['stock']."</td>";

                if($row['stock'] > 0){
                    echo "<form action='borrow.php' method='post'>";
                    echo "<td><input type='text' name='borrower_name' placeholder='Enter name' required></td>";
                    echo "<td><input type='number' name='quantity' min='1' max='".$row['stock']."' value='1' required></td>";
                    echo "<td>";
                    echo "<input type='hidden' name='item_id' value='".$row['id']."'>";
                    echo "<input type='submit' name='borrow' value='Borrow'>";
                    echo "</td>";
                    echo "</form>";
                } else {
                    echo "<td>-</td><td>-</td><td><input type='submit' value='Out of Stock' disabled></td>";
                }

                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No items found.</td></tr>";
        }
        ?>
    </table>

</div>

</body>
</html>

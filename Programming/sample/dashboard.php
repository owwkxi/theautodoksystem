<?php
session_start();

include 'verify.php';
include 'db_connection.php';

    $sql = "SELECT id,username  FROM  users";
    $result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DASHBOARD</title>
</head>
<body>

test
    <?php

   echo $_SESSION['username'];
    ?>


<table>
    <tr>
    <th>
        id
    </th>
    <th>
        username
    </th>
    </tr>
    <?php if($result && $result->num_rows > 0):?>
             <?php while($row = $result->fetch_assoc()):?>
        <tr>
            <td>
                <?php echo $row['id']?>
            </td>
            <td>
                <?php echo $row['username']?>
            </td>
        </tr>
        <?php endwhile;?>
        <?php else:?>
            <tr>
                <td colspan = "2"> no record found</td>
            </tr>
        <?php endif;?>

        

    
</table>


    <form action="logout.php" method="post">
        <button type="submit">
            Logout
        </button>
    </form>
    
</body>
</html>
<?php
    session_start();
    require_once "utils.php";
    function printTable()
    {
        require_once "pdo.php";
        $sql = $pdo->prepare("SELECT profile.first_name, profile.last_name, profile.headline, profile.profile_id 
                            FROM profile JOIN users ON profile.user_id = users.user_id WHERE users.user_id = :u_id");
        $sql->execute(array('u_id'=>$_SESSION['user_id']));
        if($sql->rowCount()!==0)
        {
            echo('<table border="2">'."\n");
            echo('<thread><tr><th>Name</th><th>Headline</th><th>Action</th></thread>'."\n");
            echo('<tbody>');
            while ( $row = $sql->fetch(PDO::FETCH_ASSOC) ) 
            {
                echo "<tr><td>";
                echo('<a href="view.php?profile_id='.$row['profile_id'].'">'.$row['first_name'].' '.$row['last_name'].'</a>');
                echo("</td><td>");
                echo($row['headline']);
                echo("</td><td>");
                echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
                echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
                echo("</td></tr>\n");
            }
            echo('</tbody>');
            echo('</table>');
            echo('<br>');
        }
        // Print "No rows found"
        else
        {
            echo('<h5>No rows found</h5>'."\n");
        }
    }
?>
<!-- ============================================================================================== -->
<!DOCTYPE html>
<html>
    <head>
        <?php require_once "bootstrap.php"; ?>
        <title>Raymond Youssef's Home Page </title>
    </head>
    <body>
        <div class="container">
        <h1>Raymond Youssef's Resume Registry</h1>
        <?php flashMessages(); ?>
        <?php
            if(!isset($_SESSION['name']) || !isset($_SESSION['user_id']))
            {
                echo('<a href="login.php">Please log in</a>');
            }
            else
            {
                printTable();
                echo('<a href="logout.php">Logout</a><br>'); 
                echo('<a href="add.php">Add New Entry</a>');  
            }
        ?>
        </div>
    </body>

</html>
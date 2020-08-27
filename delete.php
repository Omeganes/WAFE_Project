<?php
    session_start();
    require_once "utils.php";
    require_once "pdo.php";
    checkAccess();
    if(isset($_POST['delete']) && isset($_POST['profile_id']))
    {    
        $stmt = $pdo->prepare("DELETE from profile WHERE profile_id= :p_id AND user_id = :u_id");
        $stmt->execute(array(':p_id'=> htmlentities($_POST['profile_id']),
                            ':u_id'=>htmlentities($_SESSION['user_id'])));
        $_SESSION['success'] = "Record deleted";
        header("Location: index.php");
        return;
    }
    if(isset($_POST['cancel']))
    {
        header("Location: index.php");
        return;
    }
    
    $row = checkProfileID($pdo);
?>
<html>
    <head>
        <title>Raymond Youssef 's Delete Page</title>
    </head>
    <body>
        <h1>Confirm: Deleting <?= $row['first_name'].' '.$row['last_name']; ?></h1>
        <form method="POST">
            <input type="submit" name="delete" value="Delete">
            <input type="hidden" name="profile_id" value="<?= $row['profile_id'] ?>">
            <input type="submit" name="cancel" value="Cancel">
        </form>
    </body>
</html>
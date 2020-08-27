<?php
    session_start();
    require_once "utils.php";
    if(isset($_POST['cancel']))
    {
        header("Location: index.php");
        return;
    }
    if(isset($_POST['email']) && isset($_POST['pass']))
    {
        require_once "pdo.php";
        $salt = 'XyZzy12*_';
        $check = hash('md5', $salt.htmlentities($_POST['pass']));
        $stmt = $pdo->prepare('SELECT user_id, name FROM users
            WHERE email = :em AND password = :pw');
        $stmt->execute(array(':em' => htmlentities($_POST['email']),
                             ':pw' => $check));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ( $row !== false ) 
        {
            $_SESSION['name'] = $row['name'];
            $_SESSION['user_id'] = $row['user_id'];
            header("Location: index.php");
            return;
        }
        else
        {
            $_SESSION['error'] = "Wrong email or password";
            header("Location: login.php");
            return;
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <?php require_once "bootstrap.php"; ?>
        <title>Raymond Youssef 's Login Page</title>
    </head>
    <body>
        <div class="container">
        <h1>Please Log In</h1>
        <?php flashMessages(); ?>
        <form method="POST">
            <label for="nam">Email</label>
            <input type="text" name="email" id="nam"><br/>
            <label for="id_1723">Password</label>
            <input type="text" name="pass" id="id_1723"><br/>
            <input type="submit" onclick="return doValidate();" value="Log In">
            <input type="submit" name="cancel" value="Cancel">
        </form>
        </div>
    </body>
    <script>
        function doValidate() 
        {
            console.log('Validating...');
            try {
                eml = document.getElementById('nam').value;
                pw = document.getElementById('id_1723').value;
                var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
                console.log("Validating pw="+pw);
                if (eml == "" || pw == "") 
                {
                    alert("Both fields must be filled out");
                    return false;
                }
                else if(!mailformat.test(eml))
                {
                    alert("Invalid email format");
                    return false;                    
                }
                return true;
            } catch(e) {
                return false;
            }
            return false;
        }
    </script>
</html>
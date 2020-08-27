<?php
    session_start();
    require_once "pdo.php";
    require_once "utils.php";
    checkAccess();
    $row = checkProfileID($pdo);

    function printPositions(PDO $pdo){
        $sql = $pdo->prepare('SELECT * from position WHERE profile_id = :p_id');
        $sql->execute(array(
            ':p_id'=>htmlentities($_REQUEST['profile_id'])
        ));
        if($sql->rowCount()!=0)
        {
            echo('<ul>');
            while ( $row1 = $sql->fetch(PDO::FETCH_ASSOC) )
            {
                echo('<li>'.$row1['year'].':'.$row1['description'].'</li>');
            }
            echo('</ul>');
        }
    }
    function printEducation(PDO $pdo)
    {
        $sql = $pdo->prepare('SELECT * 
                    FROM profile JOIN education JOIN institution 
                    WHERE profile.profile_id=education.profile_id AND education.institution_id=institution.institution_id 
                    AND profile.profile_id= :p_id');
        $sql->execute(array(
            ':p_id'=>htmlentities($_REQUEST['profile_id'])
        ));
        if($sql->rowCount()!=0)
        {
            echo('<ul>');
            while ( $row2 = $sql->fetch(PDO::FETCH_ASSOC) )
            {
                echo('<li>'.$row2['year'].':'.$row2['name'].'</li>');
            }
            echo('</ul>');
        }
    }
?>

<html>
    <head>
        <?php require_once "bootstrap.php"; ?>
        <title>Raymond Youssef's Profile View</title>
    </head>
    <body>
        <div class="container">
            <h1>Profile information</h1>
            <p>First Name: <?= $row['first_name'] ?></p>
            <p>Last Name: <?= $row['last_name'] ?> </p>
            <p>Email: <?= $row['email'] ?></p>
            <p>Headline:<br> <?= $row['headline'] ?></p>
            <p>Summary:<br> <?= $row['summary'] ?></p>
            <p>Education:<br>
                <?php printEducation($pdo); ?>
            </p>
            <p>Position:<br>
                <?php printPositions($pdo) ?>
            </p>
            <a href="index.php">Done</a>
        </div>
    </body>
</html>
<?php
    session_start();
    require_once "utils.php";
    require_once "pdo.php";
    checkAccess();
    if(isset($_POST['cancel']))
    {
        header("Location: index.php");
        return;
    }
    if(postInputsSet())
    {
        if(validateInputs())
        {
            $stmt = $pdo->prepare("UPDATE profile 
                SET first_name= :fn, last_name= :ln, email= :em, headline=:he, summary=:su
                WHERE profile_id = :p_id AND user_id = :u_id");
            $stmt->execute(array(
            ':fn' => htmlentities($_POST['first_name']),
            ':ln' => htmlentities($_POST['last_name']),
            ':em' => htmlentities($_POST['email']),
            ':he' => htmlentities($_POST['headline']),
            ':su' => htmlentities($_POST['summary']),
            ':p_id'=> htmlentities($_POST['profile_id']),
            ':u_id'=> htmlentities($_SESSION['user_id']))
            );

            $sql = $pdo->prepare('DELETE FROM position WHERE profile_id= :pid');
            $sql->execute(array(
                ':pid'=>htmlentities($_REQUEST['profile_id'])
            ));

            $sql = $pdo->prepare('DELETE FROM education WHERE profile_id=:pid');
            $sql->execute(array(
                ':pid'=>htmlentities($_REQUEST['profile_id'])
            ));
            $profile_id=htmlentities($_REQUEST['profile_id']);
            updatePositionAndEducation($pdo, $profile_id);
            if(isset($_SESSION['error']))
            {
                header("Location: edit.php".$_GET['profile_id']);
                return;
            }
            else
            {
                $_SESSION['success'] = "Record edited";
                header("Location: index.php");
                return;         
            }
        }
        else
        {
            header("Location: edit.php?profile_id=".$_GET['profile_id']);
            return;
        }
    }
    
    $row = checkProfileID($pdo);

    $posCount=0;
    $eduCount=0;

    function printPositions(PDO $pdo, &$posCount){
        $sql2 = $pdo->prepare('SELECT * from position WHERE profile_id = :p_id');
        $sql2->execute(array(
            ':p_id'=>htmlentities($_REQUEST['profile_id'])
        ));
        if($sql2->rowCount()!=0)
        {
            while ( $row2 = $sql2->fetch(PDO::FETCH_ASSOC) )
            {
                $posCount++;
                echo ('<div id="position'.$posCount.'">');
                echo ('<p>Year: <input type="text" name="year'.$posCount.'" value="'.$row2['year'].'">');
                echo (" <input type=\"button\" value=\"-\" onclick=\"$('#position".$posCount."').remove();return false\";>");
                echo ('</p>');
                echo ('<textarea name="desc1" rows="8" cols="80">'.$row2['description'].'</textarea>');
                echo ('</div><br>');
            }
        }
    }

    function printEducation(PDO $pdo, &$eduCount)
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
            while($row = $sql->fetch(PDO::FETCH_ASSOC))
            {
                $eduCount++;
                echo ('<div id="edu'.$eduCount.'">');       
                echo ('<p>');
                echo ('Year: <input type="text" name="edu_year'.$eduCount.'" value="'.$row['year'].'">');         
                echo ('<input type="button" value="-" onclick="$(\'#edu'.$eduCount.'\').remove();return false;"><br>');        
                echo ('</p>');
                echo ('<p>');
                echo ('School: <input type="text" size="80" name="edu_school'.$eduCount.'" class="school ui-autocomplete-input" value="'.$row['name'].'" autocomplete="off">');      
                echo ('</p>');
                echo ('</div>');
            }
        }
    }
    
?>

<html>
    <head>
        <?php require_once "bootstrap.php"; ?>
        <title>Raymond Youssef's Edit page</title>
    </head>
    <body>
        <div class="container">
            <h1>Editing Profile for <?= $_SESSION['name']; ?></h1>
            <?php flashMessages(); ?>
            <form method="post">
                <p>First Name:
                <input type="text" name="first_name" size="60" value="<?= $row['first_name'];?>"></p>
                <p>Last Name:
                <input type="text" name="last_name" size="60" value="<?= $row['last_name'];?>"></p>
                <p>Email:
                <input type="text" name="email" size="30" value="<?= $row['email'];?>"></p>
                <p>Headline:<br>
                <input type="text" name="headline" size="80" value="<?= $row['headline'];?>"></p>
                <p>Summary:<br>
                <textarea name="summary" rows="8" cols="80"><?= $row['summary'];?></textarea></p>
                <input type="hidden" name="profile_id" value="<?= $row['profile_id'] ?>" >
                <p>Education:
                    <input type="submit" value="+" id="addEdu">
                </p>
                <div id="education_fields"><?php printEducation($pdo, $eduCount); ?></div>
                <p>Position:
                <input type="submit" value="+" id="addPos">
                </p>
                <div id="position_fields"><?php printPositions($pdo, $posCount); ?>
                </div>
                <input type="submit" value="Save">
                <input type="submit" name="cancel" value="Cancel">
                </p>
            </form>
        </div>
    </body>
    <script>
            posCount = <?= $posCount ?>;
            eduCount = <?= $eduCount ?>;
            $(document).ready(function()
            {
                window.console && console.log('Document is loaded');
                $("#addPos").click(function(event)
                {
                    event.preventDefault();
                    if(posCount>=9)
                    {
                        alert("Maximum of nine position entries exceeded");
                        return;
                    }
                    posCount++;
                    window.console && console.log('Adding position'+posCount);
                    var posSource = $("#pos_template").html();
                    $('#position_fields').append(posSource.replace(/@COUNT@/g,posCount));
                });
                $('#addEdu').click(function()
                {
                    event.preventDefault();
                    if(eduCount>=9)
                    {
                        alert("Maximum of nine education entries exceeded");
                        return;
                    }
                    eduCount++;
                    window.console && console.log('Adding education'+eduCount);
                    var eduSource = $("#edu_template").html();
                    $('#education_fields').append(eduSource.replace(/@COUNT@/g,eduCount));
                    // Add the event handler to the new ones
                    $('.school').autocomplete({
                        source: "school.php"
                    });
                });

            })
    </script>
    <script id="edu_template" type="text">
        <div id="edu@COUNT@">             
            <p>
                Year: <input type="text" name="edu_year@COUNT@">             
                <input type="button" value="-" onclick="$('#edu@COUNT@').remove();return false;"><br>            
            </p>
            <p>
                School: <input type="text" size="80" name="edu_school@COUNT@" class="school ui-autocomplete-input" value="" autocomplete="off">            
            </p>
        </div>
    </script>
    <script id="pos_template" type="text">
        <div id="position@COUNT@">
            <p>Year: <input type="text" name="year@COUNT@">
                    <input type="button" value="-" onclick="$('#position@COUNT@').remove(); return false;">
            </p>
            <textarea name="desc@COUNT@" rows="8" cols="80"></textarea>
        </div><br>
    </script>
</html>
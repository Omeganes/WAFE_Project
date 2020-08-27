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
            $stmt = $pdo->prepare('INSERT INTO profile
            (user_id, first_name, last_name, email, headline, summary)
            VALUES ( :uid, :fn, :ln, :em, :he, :su)');
            $stmt->execute(array(
            ':uid' => htmlentities($_SESSION['user_id']),
            ':fn' => htmlentities($_POST['first_name']),
            ':ln' => htmlentities($_POST['last_name']),
            ':em' => htmlentities($_POST['email']),
            ':he' => htmlentities($_POST['headline']),
            ':su' => htmlentities($_POST['summary'])
            ));
            $profile_id = $pdo->lastInsertId();
            updatePositionAndEducation($pdo, $profile_id);
            if(isset($_SESSION['error']))
            {
                header("Location: add.php");
                return;
            }
            else
            {
                $_SESSION['success'] = "Record added";
                header("Location: index.php");
                return;
            }
        }
        else
        {
            header("Location: add.php");
            return;
        }
    }
?>

<!-- ================================================================================================ -->
<html>
    <head>
        <?php require_once "bootstrap.php"; ?>
        <title>Raymond Youssef's Add page</title>
    </head>
    <body>
        <div class="container">
            <h1>Adding Profile for <?= $_SESSION['name']; ?></h1>
            <?php flashMessages();?>
            <form method="post">
                <p>First Name:
                <input type="text" name="first_name" size="60"></p>
                <p>Last Name:
                <input type="text" name="last_name" size="60"></p>
                <p>Email:
                <input type="text" name="email" size="30"></p>
                <p>Headline:<br>
                <input type="text" name="headline" size="80"></p>
                <p>Summary:<br>
                <textarea name="summary" rows="8" cols="80"></textarea>
                </p>
                <p>Education:
                    <input type="submit" value="+" id="addEdu">
                </p>
                <div id="education_fields"></div>
                <p>Position:
                    <input type="submit" value="+" id="addPos">
                </p>
                <div id="position_fields"></div>
                <input type="submit" value="Add">
                <input type="submit" name="cancel" value="Cancel">
            </form>
        </div>
        </form>
        </div>
    </body>
    <script>
            posCount=0;
            eduCount=0;
            $(document).ready(function()
            {
                window.console && console.log('Document is loaded');
                $('#addPos').click(function()
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
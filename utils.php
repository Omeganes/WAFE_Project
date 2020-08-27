<?php

    /**
     * Displays flash messeges
     */
    function flashMessages()
    {
        if(isset($_SESSION['error']))
        {
            echo('<p style="color: red">'.$_SESSION['error'].'</p>');
            unset($_SESSION['error']);
        }
        if(isset($_SESSION['success']))
        {
            echo('<p style="color: green">'.$_SESSION['success'].'</p>');
            unset($_SESSION['success']);
        }
    }

    /**
     * checks if post inputs are set
     */
    function postInputsSet()
    {
        if(isset($_POST['first_name']) && 
            isset($_POST['last_name']) && 
            isset($_POST['email']) && 
            isset($_POST['headline']) && 
            isset($_POST['summary']))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Validates Inputs
     */
    function validateInputs()
    {
        if(strlen($_POST['first_name'])<1 ||
            strlen($_POST['last_name'])<1||
            strlen($_POST['email'])<1 || 
            strlen($_POST['headline'])<1 || 
            strlen($_POST['summary'])<1)
        {
            $_SESSION['error'] = "All fields are required";
            return false;
        }
        else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
        {
            $_SESSION['error'] = "Email address must contain @";
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * Checks if the user is logged-in
     */
    function checkAccess()
    {
        if(!isset($_SESSION['user_id']))
        die("Access Denied");
    }

    /**
     * Checks if profile_id is present in the GET request
     * OR if the profile_id doesn't exist
     */
    function checkProfileID(PDO $pdo)
    {
        if(!isset($_GET['profile_id']))
        {
            $_SESSION['error'] = "Missing profile_id";
            header("Location: index.php");
            return;
        }
        $sql = $pdo->prepare('SELECT * from profile WHERE profile_id = :p_id AND user_id = :u_id');
        $sql->execute(array(':p_id'=>htmlentities($_GET['profile_id']),
                            'u_id'=>htmlentities($_SESSION['user_id'])));
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        if($row ==false)
        {
            $_SESSION['error'] = "Bad value for profile_id";
            header("Location: index.php");
            return;
        }
        else
        {
            return $row;
        }
    }


    /**
     * Inserts positions and education
     */
    function updatePositionAndEducation(PDO $pdo, $profile_id)
    {
        for($i=1; $i<=9; $i++) 
        {
            if ( ! isset($_POST['year'.$i]) ) continue;
            if ( ! isset($_POST['desc'.$i]) ) continue;
            $year = htmlentities($_POST['year'.$i]);
            if(!is_numeric($year))
            {
                $_SESSION['error']= "Year must be numeric";
                return;
            }
            $desc = htmlentities($_POST['desc'.$i]);
            $sql = $pdo->prepare('INSERT INTO Position
                (profile_id, rank, year, description) 
                VALUES ( :pid, :rank, :year, :desc)');
            $sql->execute(array(
                ':pid'=>$profile_id,
                ':rank'=>$i,
                ':year'=> $year,
                ':desc'=> $desc
            ));
        }
        $rank=1;
        for($i=1;$i<=9;$i++)
        {
            if(!isset($_POST['edu_year'.$i])) continue;
            if(!isset($_POST['edu_school'.$i])) continue;
            $edu_year = htmlentities($_POST['edu_year'.$i]);
            $edu_school = htmlentities($_POST['edu_school'.$i]);
            if(!is_numeric($edu_year))
            {
                $_SESSION['error']= "Year must be numeric";
                return;
            }
            $sql = $pdo->prepare('SELECT * from institution WHERE name= :nm');
            $sql->execute(array(
                ':nm'=>$edu_school
            ));
            $row = $sql->fetch(PDO::FETCH_ASSOC);
            if($row!=false)
            {
                $institution_id = $row['institution_id'];
            }
            else
            {
                $sql = $pdo->prepare('INSERT INTO institution(name) VALUES(:nm)');
                $sql->execute(array(
                    ':nm'=>$edu_school
                ));
                $institution_id=$pdo->lastInsertId();
            }
            $sql = $pdo->prepare('INSERT INTO education(profile_id,institution_id,rank,year)
                                    VALUES(:p_id, :ins_id, :rank, :year)');
            $sql->execute(array(
                ':p_id'=>$profile_id,
                ':ins_id'=>$institution_id,
                ':rank'=>$rank,
                ':year'=> $edu_year
            ));
            $rank++;
        }
    }

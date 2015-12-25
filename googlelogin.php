<?php

require 'db_connect.inc.php';

$connect = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if (mysqli_connect_errno()) {
    showJson(10, "Cannot connect with MySql :" . mysqli_connect_errno());
    mysqli_close($connect);
} else {
    //Successfully connected
    if (isset($_POST['name']) && isset($_POST['email'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        if (!empty($email)) {
            googleLogin($connect, $name, $email);
        } else {
            showJson(4, "Fields cannot be empty");
        }
    }
    mysqli_close($connect);
}

function googleLogin($connect, $name, $email)
{
    $id = getUserId($connect, $email);
    if ($id > 0) {
        updateLastLogin($connect, $id);
        $response = array();
        $response['status'] = 1;
        $response['message'] = "Successfully logged in";
        $response['user_id'] = $id;
        echo json_encode($response);
    } else {
        $code = createAccount($connect, $name, $email);
        if ($code == 1) {
            $response = array();
            $response['status'] = 5;
            $response['message'] = "Account Created Successfully";
            $response['user_id'] = getUserId($connect, $email);
            echo json_encode($response);
        } elseif ($code == 3) {
            showJson(3, "Email already registered\nLogin using email and password");
        } else {
            showJson(0, "Oops!...Details cannot be added into Database.Try again later.");
        }
    }
}

function updateLastLogin($connect, $id)
{
    $query = "UPDATE `users` SET `last_login`=CURRENT_TIMESTAMP WHERE `id`='$id'";
    mysqli_query($connect, $query);
}

function getUserId($connect, $email)
{
    $query = "SELECT * FROM `users` WHERE `email`='$email' AND `is_google`='1' LIMIT 1";
    $query_run = mysqli_query($connect, $query);
    if ($result = mysqli_fetch_assoc($query_run)) {
        $user_id = $result['id'];
        return $user_id;
    } else {
        return 0;
    }
}

function createAccount($connect, $name = "", $email)
{
    if (getUserId($connect, $email) > 0) {
        return 3;
    } else {
        $query = "INSERT INTO `users` (`id`, `name`, `password`, `email`,`is_google`,`last_login`) VALUES (NULL,'$name',NULL,'$email',1,CURRENT_TIMESTAMP)";
        $query_run = mysqli_query($connect, $query);
        if ($query_run) {
            return 1;
        } else {
            return 0;
        }
    }
}

function showJson($status, $message)
{
    $response = array();
    $response['status'] = $status;
    $response['message'] = $message;
    echo json_encode($response);
}

?>

<form action="googlelogin.php" method="post">
    <fieldset>
        <legend>Google Login</legend>
        <label for="name">Name</label><br><input type="text" name="name" maxlength="80"><br><br>
        <label for="email">Email</label><br><input type="email" name="email" maxlength="40"><br><br>
        <input type="Submit" name="submit" value="Login">
    </fieldset>
</form>

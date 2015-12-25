<?php

require 'db_connect.inc.php';

$connect = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if (mysqli_connect_errno()) {
    showJson(10, "Cannot connect with MySql :" . mysqli_connect_errno());
    mysqli_close($connect);
} else {
    //Successfully connected
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        if (!empty($email) && !empty($password)) {
            normalLogin($connect, $email, $password);
        } else {
            showJson(4, "Fields cannot be empty");
        }
    }
    mysqli_close($connect);
}

function normalLogin($connect, $email, $password)
{
    $id = checkCredentials($connect, $email, $password);
    if ($id > 0) {
        updateLastLogin($connect, $id);
        $response = array();
        $response['status'] = 1;
        $response['message'] = "Successfully logged in";
        $response['user_id'] = $id;
        echo json_encode($response);
    } else {
        showJson(2, "Cannot login\nCheck your Credentials");
    }
}

function checkCredentials($connect, $email, $password)
{
    $password = hash("sha256", $password);
    $query = "SELECT `id` FROM `users` WHERE `email`='$email' AND `password`='$password' AND `is_google`=0 LIMIT 1";
    $query_run = mysqli_query($connect, $query);
    if ($result = mysqli_fetch_assoc($query_run)) {
        $user_id = $result['id'];
        return $user_id;
    } else {
        return 0;
    }
}

function updateLastLogin($connect, $id)
{
    $query = "UPDATE `users` SET `last_login`=CURRENT_TIMESTAMP WHERE `id`='$id'";
    mysqli_query($connect, $query);
}

function showJson($status, $message)
{
    $response = array();
    $response['status'] = $status;
    $response['message'] = $message;
    echo json_encode($response);
}

?>

<form action="login.php" method="post">
    <fieldset>
        <legend>Login</legend>
        <label for="email">Email</label><br><input type="email" name="email" maxlength="40"><br><br>
        <label for="password">Password</label><br><input type="password" name="password" maxlength="40"><br><br>
        <input type="Submit" name="submit" value="Login">
    </fieldset>
</form>

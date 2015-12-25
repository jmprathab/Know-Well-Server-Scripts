<?php

require 'db_connect.inc.php';

$connect = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if (mysqli_connect_errno()) {
    showJson(10, "Cannot connect with MySql :" . mysqli_connect_errno());
    mysqli_close($connect);
} else {
    //Successfully connected
    if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        if (!empty($name) && !empty($email) && !empty($password)) {
            if (getUserId($connect, $email) === 0) {
                if (createAccount($connect, $name, $email, $password) == 1) {
                    sendEmailConfirmation($email);
                    $id = getUserId($connect, $email);
                    $response = array();
                    $response['status'] = 1;
                    $response['message'] = "Account Created";
                    $response['user_id'] = $id;
                    echo json_encode($response);
                } else {
                    showJson(0, "Oops!...Details cannot be added into Database.Try again later.");
                    die();
                }
            } else {
                showJson(3, "Email has already been registered.");
            }
        } else {
            showJson(4, "Fields cannot be empty.");
        }
    }
    mysqli_close($connect);
}

function getUserId($connect, $email)
{
    $query = "SELECT `id` FROM `users` WHERE `email`='$email' LIMIT 1";
    $query_run = mysqli_query($connect, $query);
    if ($result = mysqli_fetch_assoc($query_run)) {
        $user_id = $result['id'];
        return $user_id;
    } else {
        return 0;
    }
}

function createAccount($connect, $name, $email, $password, $is_google = 0)
{
    $password = hash("sha256", $password);
    if (getUserId($connect, $email) > 0) {
        return 3;
    } else {
        $query = "INSERT INTO `users` (`id`, `name`, `password`, `email`,`is_google`,`last_login`) VALUES (NULL,'$name','$password','$email','$is_google',CURRENT_TIMESTAMP)";
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

function sendEmailConfirmation($email)
{
    $subject = "Know Well Application Account Created";
    $message = "Registration Successful !" . "\r\n" . "Your account has been created." . "\r\n" . "You can log in to your account using your email and your password.";
    $headers = 'From: Know Well Application' . "\r\n" . 'Reply-To: jm.prathab@gmail.com' . "\r\n";
    $message = wordwrap($message, 70, "\r\n");
    mail($email, $subject, $message, $headers);
}

?>
<form action="register.php" method="post">
    <fieldset>
        <legend>Register</legend>
        <label for="name">Name</label><br><input type="text" name="name" maxlength="40"><br><br>
        <label for="email">Email</label><br><input type="email" name="email" maxlength="40"><br><br>
        <label for="password">Password</label><br><input type="password" name="password" maxlength="40"><br><br>
        <input type="Submit" name="submit" value="Create Account">
    </fieldset>
</form>

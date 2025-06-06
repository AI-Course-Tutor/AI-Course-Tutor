<?php

/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

# must be called at top of each php file accessible from the internet (i.e., in folder 'public')
require_once '../src/init.php';


# start of content specific to this file

require_once '../src/Auth.php';

// user is logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])) {

    /* if one needs to check consent, this would be a good place to check for that, e.g.
    $auth = new Auth();
    $_SESSION['consent'] = $auth->get_consent();

    if ($_SESSION['consent'] == 'tutor_only' || $_SESSION['consent'] == 'study_participation') { // change to your needs in case of study_participation something else should be shown

        // redirect user to tutor mode selection
        header('Location: select.php');
        exit();

    } else {

        // none of the above valid values for consent -> redirect user to consent form
        header('Location: consent.php');
        exit();

    }
    */

    // currently, no consent is implemented -> always redirect to select.php
    header('Location: select.php');
    exit();
}

// normal log-in procedure
if (isset($_GET['user_name']) || isset($_POST['user_name'])) {

    if (isset($_GET['user_name'])) {
        $user_name = $_GET['user_name'];
        $_SESSION['show_logout_button'] = false;

        if (isset($_GET['predefined_tutor_mode'])) { // can be used for studies to skip select dialog and choose default tutor mode via GET parameters
            $_SESSION['predefined_tutor_mode'] = $_GET['predefined_tutor_mode'];
            $_SESSION['$continue_last_conversation_once'] = true;
        }

        // For GET requests (used when called from external platform), we don't require a password as it would anyway be potentially insecure via GET
        $auth = new Auth();
        $user_id_db = $auth->login($user_name, 'DEFAULT_DUMMY_PASSWORD_FOR_GET_REQUESTS_12345');
        if (is_numeric($user_id_db)) {
            $_SESSION['user_id'] = $user_id_db;
            $_SESSION['user_name'] = $user_name;
            unset($_SESSION['conversation_id']);

            // user_id set -> now re-load this page, such that it can perform consent checks
            header('Location: index.php');
            exit();
        } else {
            // Handle specific error codes
            $error_message = "Authentication error. Please check your username and password.";

            if ($user_id_db === 'INVALID_PASSWORD_FORMAT') {
                $error_message = "Password must be at least 8 characters long and contain both letters and numbers.";
            } elseif ($user_id_db === 'EMPTY_PASSWORD') {
                $error_message = "Password cannot be empty.";
            }

            echo $error_message;
            echo "<br><br>";
            echo "<a href='index.php'>Back to Login</a>";
            exit();
        }
    }
    else {
        // For POST requests (form submission), we require a password
        $user_name = $_POST['user_name'];
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $_SESSION['show_logout_button'] = true;

        if (strlen($user_name) > 0) {
            $auth = new Auth();
            $user_id_db = $auth->login($user_name, $password);

            if (is_numeric($user_id_db)) {
                $_SESSION['user_id'] = $user_id_db;
                $_SESSION['user_name'] = $user_name;
                unset($_SESSION['conversation_id']);

                // user_id set -> now re-load this page, such that it can perform consent checks
                header('Location: index.php');
                exit();
            } else {
                // Authentication failed - handle specific error codes
                $error_message = "Authentication error. Please check your username and password.";

                if ($user_id_db === 'INVALID_PASSWORD_FORMAT') {
                    $error_message = "Password must be at least 8 characters long and contain both letters and numbers.";
                } elseif ($user_id_db === 'EMPTY_PASSWORD') {
                    $error_message = "Password cannot be empty.";
                }

                include '../templates/login.php';
                exit();
            }
        } else { // show login page if empty username was entered
            include '../templates/login.php';
        }
    }

} else {
    include '../templates/login.php';
}
?>

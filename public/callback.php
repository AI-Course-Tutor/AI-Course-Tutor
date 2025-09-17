<?php

/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

# must be called at top of each php file accessible from the internet (i.e., in folder 'public')
require_once '../src/init.php';


# start of content specific to this file

require_once '../src/Auth.php';

$error_message_text = "There was an error processing your request. Please contact the administrator with information about how you reached this page. (Important: please also copy the URL of this webpage in your email to the administrator).";

// both parameters are passed back from SoSci Survey. If they are missing, this is a invalid call to this website
if ( isset($_GET['id']) && isset($_GET['test']) ) {

    // currently only test = pre (for pretest exists) -> if other parameter is passed, then there is something wrong here
    if ($_GET['test'] != "pre") {
        echo $error_message_text;
        exit();
    }

    // id should be formatted as USERID-USERNAME
    $id_split = explode('-', $_GET['id']);

    // id string must always consist of 2 elements
    if (count($id_split) != 2) {
        echo $error_message_text;
        exit();
    }

    $auth = new Auth();

    $user_id = $id_split[0];
    $user_name = $id_split[1];

    // if there is no matching user for the id that was passed to callback, then stop with error message
    if (! $auth->user_exists($user_id, $user_name)) {
        echo $error_message_text;
        exit();
    }

    if ($auth->set_pretest_completed($user_id)) {
        // pretest successfully set to "completed" in DB
        // -> redirect back to index.php from where user will be forwarded to tutor (if still logged in)
        header('Location: index.php');
        exit();

    } else {
        // error when setting pretest to "completed" in DB
        echo $error_message_text;
        exit();
    }

} else {

    echo $error_message_text;

}


?>

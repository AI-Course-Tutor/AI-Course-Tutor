<?php

/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

class AccessToken {

    public static function get_access_token_from_url(): void
    {
        if (isset($_GET['access_token'])) {
            $_SESSION['access_token'] = $_GET['access_token'];
        }
    }

    public static function check_access_token(): void
    {
        // do as follows if multiple access_tokens exist:
        // if (!isset($_SESSION['access_token']) || ($_SESSION['access_token'] != "token_1" && $_SESSION['access_token'] != "token_2")) {
        if (!isset($_SESSION['access_token']) || ($_SESSION['access_token'] != "set_your_access_token_here")) {
            echo "No access - Please contact [your-email] if you want to access the [your-tutor-name].";
            exit();
        }
    }
}
?>

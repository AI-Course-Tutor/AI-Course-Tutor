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
        $config = Configuration::getInstance();
        $valid_tokens = $config->getValidAccessTokens();
        
        // If no valid access tokens are configured, deny access
        if ($valid_tokens === null) {
            $message = "No access - Access token system is enabled but no access token was configured. Please contact {email} if you want to access the {tutor_name}.";
            $message = str_replace('{email}', $config->placeholderRaw('contact.email'), $message);
            $message = str_replace('{tutor_name}', $config->placeholderRaw('tutor.name'), $message);
            echo $message;
            exit();
        }
        
        // Check if user's session token matches any of the valid tokens
        if (!isset($_SESSION['access_token']) || !in_array($_SESSION['access_token'], $valid_tokens)) {
            $message = "No access - Please contact {email} if you want to access the {tutor_name}.";
            $message = str_replace('{email}', $config->placeholderRaw('contact.email'), $message);
            $message = str_replace('{tutor_name}', $config->placeholderRaw('tutor.name'), $message);
            echo $message;
            exit();
        }
    }
}
?>

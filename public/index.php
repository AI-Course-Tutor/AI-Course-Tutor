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

    // Check consent system configuration
    $config = Configuration::getInstance();
    
    if ($config->isConsentEnabled()) {
        $auth = new Auth();
        $_SESSION['consent'] = $auth->get_consent();
        
        $consent_options = $config->getConsentOptions();
        
        // Check if user has given consent
        // Note: when editing something, cross-check whether src/init.php enforceConsent() would also require respective changes (there, it ensures that user does not manually enter a tutor page by entering it in the URL field despite consent requirements not fulfilled.
        if ($_SESSION['consent'] && isset($consent_options[$_SESSION['consent']])) {
            $consent_option = $consent_options[$_SESSION['consent']];
            
            // Check if this consent option allows tutor access
            if (isset($consent_option['tutor_access']) && $consent_option['tutor_access']) {
                
                // Check if pretest is required for this consent option
                if (isset($consent_option['pretest_required_before_tutor_access']) && 
                    $consent_option['pretest_required_before_tutor_access']) {
                    
                    // Check if pretest is completed
                    if (!$auth->is_pretest_completed()) {
                        // Redirect to pretest URL
                        if (isset($consent_option['pretest_url']) && !empty($consent_option['pretest_url'])) {
                            $pretest_url = $consent_option['pretest_url'];
                            // Add user identification to pretest URL for callback
                            $separator = strpos($pretest_url, '?') !== false ? '&' : '?';
                            $pretest_url_with_id = $pretest_url . $separator . 'id=' . $_SESSION['user_id'] . '-' . $_SESSION['user_name'];
                            header('Location: ' . $pretest_url_with_id);
                            exit();
                        } else {
                            echo "Pretest is required but no pretest URL is configured. Please contact the administrator.";
                            exit();
                        }
                    }
                }
                
                // User has valid consent with tutor access and pretest completed (if required)
                header('Location: select.php');
                exit();
            } else {
                // User's consent option doesn't allow tutor access
                echo "Your consent selection does not allow access to the tutor. Please contact the study team if you wish to change your consent.";
                echo "<br><a href='logout.php'>Logout</a>";
                exit();
            }
        } else {
            // No valid consent -> redirect to consent form
            header('Location: consent.php');
            exit();
        }
    } else {
        // consent system is disabled -> always redirect to select.php
        header('Location: select.php');
        exit();
    }
}

// normal log-in procedure
if (isset($_GET['user_name']) || isset($_POST['user_name'])) {

    $config = Configuration::getInstance();

    if (isset($_GET['user_name']) && $config->isGetAuthEnabled()) {
        $user_name = $_GET['user_name'];
        $token = isset($_GET['token']) ? $_GET['token'] : '';

        if (isset($_GET['predefined_tutor_mode'])) { // can be used for studies to skip select dialog and choose default tutor mode via GET parameters
            $_SESSION['predefined_tutor_mode'] = $_GET['predefined_tutor_mode'];
            $_SESSION['continue_last_conversation_once'] = true;
        }

        // Validate username for GET authentication
        $auth = new Auth();
        if (!$auth->validateUsername($user_name, true)) { // true indicates token-based authentication
            $error_message = "Username does not match the required format pattern. Please check your username format.";

            echo $error_message;
            echo "<br><br>";
            echo "<a href='index.php'>Back to Login</a>";
            exit();
        }

        // For GET requests, use token-based authentication
        $user_id_db = $auth->login($user_name, $token, true); // true indicates token-based authentication
        
        if (is_numeric($user_id_db)) {
            $_SESSION['user_id'] = $user_id_db;
            $_SESSION['user_name'] = $user_name;
            unset($_SESSION['conversation_id']);

            // user_id set -> now re-load this page, such that it can perform consent checks
            header('Location: index.php');
            exit();
        } else {
            // Handle specific error codes for token authentication
            $error_message = "Authentication error. Please check your username and token.";

            if ($user_id_db === 'INVALID_TOKEN_FORMAT') {
                $tokenConfig = $config->getTokenValidationConfig();
                $error_message = "Token must be at least {$tokenConfig['min_length']} characters long.";
            } elseif ($user_id_db === 'EMPTY_TOKEN') {
                $error_message = "Token cannot be empty.";
            } elseif ($user_id_db === 'INVALID_TOKEN') {
                $error_message = "Invalid token. Please check your token.";
            } elseif ($user_id_db === 'INVALID_TOKEN_FOR_PASSWORD_USER') {
                $error_message = "This user account requires password authentication, not token authentication.";
            }

            echo $error_message;
            echo "<br><br>";
            echo "<a href='index.php'>Back to Login</a>";
            exit();
        }
    }
    elseif (isset($_POST['user_name']) && $config->isPostAuthEnabled()) {
        // For POST requests (form submission), use password authentication
        $user_name = $_POST['user_name'];
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if (strlen($user_name) > 0) {
            // Validate username for POST authentication
            $auth = new Auth();
            if (!$auth->validateUsername($user_name, false)) { // false indicates password-based authentication
                $error_message = "Username does not match the required format pattern. Please check your username format.";

                Configuration::getInstance()->renderPage('login.php', [
                    'error_message' => $error_message
                ]);
                exit();
            }
            
            $user_id_db = $auth->login($user_name, $password, false); // false indicates password-based authentication

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
                    $passwordConfig = $config->getPasswordValidationConfig();
                    $error_message = "Password must be at least {$passwordConfig['min_length']} characters long";
                    if ($passwordConfig['require_letters'] && $passwordConfig['require_numbers']) {
                        $error_message .= " and contain both letters and numbers";
                    }
                    $error_message .= ".";
                } elseif ($user_id_db === 'EMPTY_PASSWORD') {
                    $error_message = "Password cannot be empty.";
                } elseif ($user_id_db === 'INVALID_PASSWORD') {
                    $error_message = "Invalid password. Please check your password.";
                } elseif ($user_id_db === 'INVALID_PASSWORD_FOR_TOKEN_USER') {
                    $error_message = "This user account requires token authentication, not password authentication.";
                }

                Configuration::getInstance()->renderPage('login.php', [
                    'error_message' => $error_message
                ]);
                exit();
            }
        } else { // show login page if empty username was entered
            Configuration::getInstance()->renderPage('login.php');
        }
    }
    else {
        // Authentication method not allowed or invalid request
        $error_message = "Invalid authentication method.";
        if (!$config->isPostAuthEnabled() && isset($_POST['user_name'])) {
            $error_message = "Only GET-based token authentication is allowed.";
        } elseif (!$config->isGetAuthEnabled() && isset($_GET['user_name'])) {
            $error_message = "Only POST-based password authentication is allowed.";
        }
        
        echo $error_message;
        echo "<br><br>";
        echo "<a href='index.php'>Back to Login</a>";
        exit();
    }

} else {
    Configuration::getInstance()->renderPage('login.php');
}
?>

<?php

/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

# must be called at top of each php file accessible from the internet (i.e., in folder 'public')
require_once '../src/init.php';


# start of content specific to this file

require_once '../src/Auth.php';
require_once '../src/Configuration.php';

if (isset($_POST['consent'])) {
    $config = Configuration::getInstance();
    $consent_options = $config->getConsentOptions();
    
    $selected_consent = $_POST['consent'];
    
    // Check if the selected consent option exists in configuration
    if (!isset($consent_options[$selected_consent])) {
        echo "Invalid consent option selected. Please try again.";
        exit();
    }
    
    $consent_option = $consent_options[$selected_consent];
    
    // Check if this consent option allows tutor access
    if (isset($consent_option['tutor_access']) && $consent_option['tutor_access']) {
        // Store consent in database for options that allow tutor access
        $auth = new Auth();

        if ($auth->set_consent($selected_consent)) {
            // setting consent in DB successful -> redirect to index.php, where user gets redirected depending on consent he/she gave
            header('Location: index.php');
            exit();
        } else {
            echo "There was an error processing the consent. Please try again. If the error persists, please contact " . Configuration::getInstance()->placeholder('contact.name') . " at " . Configuration::getInstance()->placeholder('contact.email');
        }
    } else {
        // For options that don't allow tutor access, show message but don't store in DB
        $consent_text = str_replace('{tutor.name}', $config->placeholderRaw('tutor.name'), $consent_option['consent_text']);
        echo "You have selected: " . htmlspecialchars($consent_text);
        echo "<br><a href='logout.php'>Logout</a>";
    }

} else {



    Configuration::getInstance()->renderPage('consent.php');
}


?>

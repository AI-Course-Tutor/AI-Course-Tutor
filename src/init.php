<?php

/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

# init code -> called at top of each php file accessible from the internet (i.e., in folder 'public')

# 1.) Start Session
require_once 'SessionCookie.php';
SessionCookie::set_params_and_start_session();

# 2.) Load Configuration class
require_once 'Configuration.php';

# 3.) Check for access token (configurable)
$config = Configuration::getInstance();
if ($config->isAccessTokenEnabled()) {
    require_once 'AccessToken.php';
    AccessToken::get_access_token_from_url();
    AccessToken::check_access_token();
}

# 4.) Make classes installed via composer available
require_once __DIR__ . '/../vendor/autoload.php';

# 5.) Load values from .env file into environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();
$dotenv->required(['DATABASE_HOST','DATABASE_DB_NAME','DATABASE_USERNAME','DATABASE_PASSWORD','OPENAI_API_KEY'])->notEmpty();

# 6.) Consent enforcement for protected pages
function enforceConsent() {
    $config = Configuration::getInstance();
    
    // Only enforce consent if consent system is enabled
    if (!$config->isConsentEnabled()) {
        return;
    }
    
    // Get current script name
    $current_script = basename($_SERVER['PHP_SELF']);
    
    // List of pages that don't require consent enforcement
    $exempt_pages = ['index.php', 'consent.php', 'logout.php', 'callback.php', 'legal-notice.php', 'privacy-policy.php'];
    
    // Skip consent enforcement for exempt pages
    if (in_array($current_script, $exempt_pages)) {
        return;
    }
    
    // If user_id missing or not logged in, redirect to logout.php from where it will be redirected to index.php
    if (!isset($_SESSION['user_id'])) {
        header('Location: logout.php');
        exit();
    }
    
    // Check user consent
    require_once __DIR__ . '/Auth.php';
    $auth = new Auth();
    $user_consent = $auth->get_consent();
    
    $consent_options = $config->getConsentOptions();
    
    // If user has no consent or invalid consent, redirect to index.php where consent is handled
    if (!$user_consent || !isset($consent_options[$user_consent])) {
        header('Location: index.php');
        exit();
    }
    
    $consent_option = $consent_options[$user_consent];
    
    // If consent option doesn't allow tutor access, redirect to index.php
    if (!isset($consent_option['tutor_access']) || !$consent_option['tutor_access']) {
        header('Location: index.php');
        exit();
    }
    
    // Check pretest requirement
    if (isset($consent_option['pretest_required_before_tutor_access']) && 
        $consent_option['pretest_required_before_tutor_access']) {
        
        if (!$auth->is_pretest_completed()) {
            // Redirect back to index.php which will handle pretest redirection
            header('Location: index.php');
            exit();
        }
    }
}

// Call consent enforcement
enforceConsent();

?>

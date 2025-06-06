<?php

/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

# init code -> called at top of each php file accessible from the internet (i.e., in folder 'public')

# 1.) Start Session
require_once 'SessionCookie.php';
SessionCookie::set_params_and_start_session();

# 2.) Check for access token
/*
 * uncomment the following code block if you want to require an access_token in the URL for access to the tutor
 * -> e.g. if you want to change a global token from semester to semester
 * -> set token in AccessToken.php by replacing "set_your_access_token_here"
 * -> once activated, students need to call the tutor with [your_URL]/index.php?access_token=yourtoken
 */
/*
require_once '../src/AccessToken.php';
AccessToken::get_access_token_from_url();
AccessToken::check_access_token();
*/

# 3.) Make classes installed via composer available
require_once __DIR__ . '/../vendor/autoload.php';

# 4.) Load values from .env file into environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();
$dotenv->required(['DATABASE_HOST','DATABASE_DB_NAME','DATABASE_USERNAME','DATABASE_PASSWORD','OPENAI_API_KEY'])->notEmpty();

?>

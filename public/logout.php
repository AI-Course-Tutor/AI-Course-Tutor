<?php

/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

# must be called at top of each php file accessible from the internet (i.e., in folder 'public')
require_once '../src/init.php';


# start of content specific to this file

$access_token = $_SESSION['access_token'];

unset($_SESSION['user_id']);
unset($_SESSION['conversation_id']);
unset($_SESSION['access_token']);

session_destroy();

header('Location: index.php?access_token=' . $access_token);
exit();

?>

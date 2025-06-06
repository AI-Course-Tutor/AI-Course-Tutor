<?php

/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

# must be called at top of each php file accessible from the internet (i.e., in folder 'public')
require_once '../src/init.php';


# start of content specific to this file

require_once '../src/Auth.php';

if (isset($_POST['consent'])) {

    if ($_POST['consent'] == 'study_participation' || $_POST['consent'] == 'tutor_only') {

        $auth = new Auth();

        if ($auth->set_consent($_POST['consent'])) {
            // setting consent in DB successful -> redirect to index.php, where user gets redirected depending on consent he/she gave
            header('Location: index.php');
            exit();
        } else {
            echo "Es gab einen Fehler bei der Verarbeitung der Einwilligung. Bitte versuchen Sie es erneut. Sollte der Fehler weiterhin auftreten, dann kontaktieren Sie bitte [your-name] unter [your-email]";
        }

    } else {
        echo "Sie haben angegeben, dass Sie weder an der Studie teilnehmen noch den [your-tutor-name] nutzen möchten.";
        echo "<br><a href='logout.php'>Logout</a>";
    }

} else {



    include '../templates/consent.php';
}


?>

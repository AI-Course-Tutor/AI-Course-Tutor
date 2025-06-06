<?php
/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */
?>
<?php include 'header.php'; ?>
<div>
    <h2>Allgemeine Informationen für Teilnehmende und Einwilligungserklärung</h2>
    <h3>Ansprechpartner für eventuelle Rückfragen (Versuchsleitung)</h3>
    <p>[your-name]: <a href="mailto:[your-email]">[your-email]</a></p>
    <p>[additional-name]: <a href="mailto:[additional-email]">[additional-email]</a></p>


    <h2 style="padding-top: 40px;">Allgemeine Informationen für Teilnehmende</h2>
    <h3>[your-department]</h3>

    <p><b>Titel der Studie:</b> [your-study-title]</p>

    <p>[your-study-description]</p>


    <p></p>
    <p><button onClick="window.print()">Klicken Sie hier, um sich eine Ausfertigung der Informationen für Teilnehmende und der Einwilligungserklärung für Ihre Unterlagen auszudrucken bzw. abzulegen</button></p>


    <h2 style="padding-top: 40px;">Einwilligungserklärung</h2>
    <h3>[your-department]</h3>

    <p><b>Titel der Studie:</b> [your-study-title]</p>

    <p>[your-consent-text]</p>
    <h3>Bitte wählen Sie eine der folgenden Optionen:</h3>

<?php if (! isset($_SESSION['user_id'])): ?>
    <p><i>Bitte <a href="index.php">einloggen</a>, um eine Option auswählen zu können.</i></p>

<?php elseif ($_SESSION['consent'] == 'study_participation'): ?>
    <p><i>Sie haben die folgende Option gewählt:</i></p>
    <p><b>Ich bestätige, dass ich die Teilnehmendeninformation gelesen habe und willige in die Teilnahme an der Studie ein. Ich stimme zu, dass meine Anfragen und die Antworten des Tutors im Rahmen der Bereitstellung des [your-tutor-name] inklusive Chatverläufe gespeichert und für die Studie verwendet werden.</b></p>
    <p>Falls Sie Ihre Teilnahme widerrufen möchten, dann kontaktieren Sie bitte die Versuchsleitung.</p>

<?php elseif ($_SESSION['consent'] == 'tutor_only'): ?>
    <p><i>Sie haben die folgende Option gewählt:</i></p>
    <p><b>Ich nehme an der Studie nicht teil. Ich möchte den [your-tutor-name] ohne Studienteilnahme nutzen. Ich stimme zu, dass meine Anfragen und die Antworten des Tutors im Rahmen der Bereitstellung des [your-tutor-name] inklusive Chatverläufe gespeichert werden. Eine Verwendung der Daten im Rahmen der Studie erfolgt nicht.</b></p>
    <p>Falls Sie doch noch an der Studie teilnehmen möchten, dann kontaktieren Sie bitte die Versuchsleitung, um Möglichkeiten zur Studienteilnahme zu besprechen.</p>

<?php else: ?>
    <form action="consent.php" method="POST">
        <label>
            <input type="radio" name="consent" value="study_participation" required>
            Ich bestätige, dass ich die Teilnehmendeninformation gelesen habe und willige in die Teilnahme an der Studie ein. Ich stimme zu, dass meine Anfragen und die Antworten des Tutors im Rahmen der Bereitstellung des [your-tutor-name] inklusive Chatverläufe gespeichert und für die Studie verwendet werden.
        </label>
        <br><br>

        <label>
            <input type="radio" name="consent" value="tutor_only" required>
            Ich nehme an der Studie nicht teil. Ich möchte den [your-tutor-name] ohne Studienteilnahme nutzen. Ich stimme zu, dass meine Anfragen und die Antworten des Tutors im Rahmen der Bereitstellung des [your-tutor-name] inklusive Chatverläufe gespeichert werden. Eine Verwendung der Daten im Rahmen der Studie erfolgt nicht.
        </label>
        <br><br>

        <label>
            <input type="radio" name="consent" value="no_consent" required>
            Ich möchte weder an der Studie teilnehmen noch den [your-tutor-name] nutzen.
        </label>

        <p><button type="submit">Weiter</button></p>

    </form>

<?php endif; ?>

</div>
<?php include 'footer.php'; ?>

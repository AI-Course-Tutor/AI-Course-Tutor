<?php
/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

$config = Configuration::getInstance();

// Get consent options from configuration
$consent_options = $config->getConsentOptions();


?>
<div>
    <?php
    // show participant information
    $config->includeTemplate('components/consent_participant_information.php');
    ?>
    
    <h3>Please choose one of the following options:</h3>

<?php if (! isset($_SESSION['user_id'])): ?>
    <p><i>Please <a href="index.php">log in</a> to select an option.</i></p>

<?php elseif (isset($_SESSION['consent']) && isset($consent_options[$_SESSION['consent']])): ?>
    <p><i>You have selected the following option:</i></p>
    <p><b><?php echo htmlspecialchars($config->replacePlaceholdersRaw($consent_options[$_SESSION['consent']]['consent_text'])); ?></b></p>
    <p>If you wish to withdraw your consent, please contact the study team.</p>

<?php else: ?>
    <form action="consent.php" method="POST">
        <?php foreach ($consent_options as $option_key => $option_data): ?>
            <label>
                <input type="radio" name="consent" value="<?php echo htmlspecialchars($option_key); ?>" required>
                <?php echo htmlspecialchars($config->replacePlaceholdersRaw($option_data['consent_text'])); ?>
            </label>
            <br><br>
        <?php endforeach; ?>

        <p><button type="submit">Continue</button></p>

    </form>

<?php endif; ?>

</div>

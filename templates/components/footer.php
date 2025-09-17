<?php
/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

// Capture the buffered content (page content that may have used placeholders)
$pageContent = ob_get_clean();

// Now generate and display the warning header with complete information
$config = Configuration::getInstance();
if ($config->hasMissingConfigurationItems()): 
    $missingFiles = $config->getMissingConfigFiles();
    $missingKeys = $config->getMissingPlaceholderKeys();
    $missingTemplates = $config->getMissingTemplateFiles();
?>
<div class="config-warning-header">
    <div class="config-warning-content">
        <strong>⚠️ Configuration Warning:</strong>
        <br>
        <?php if (!empty($missingFiles)): ?>
            The following configuration files are missing and example files are being used instead:
            <strong><?php echo implode(', ', $missingFiles); ?></strong>
            <br>
            <small>Please create these files from their .example templates to customize your installation.</small>
        <?php endif; ?>
        <?php if (!empty($missingKeys)): ?>
            <?php if (!empty($missingFiles)): ?><br><?php endif; ?>
            The following placeholder keys are missing and generated defaults are being used:
            <strong><?php echo implode(', ', $missingKeys); ?></strong>
            <br>
            <small>Please add these keys to your placeholders.json file to provide proper values.</small>
        <?php endif; ?>
        <?php if (!empty($missingTemplates)): ?>
            <?php if (!empty($missingFiles) || !empty($missingKeys)): ?><br><?php endif; ?>
            The following template files are missing and example files are being used instead:
            <strong><?php echo implode(', ', $missingTemplates); ?></strong>
            <br>
            <small>Please create these files from their .example templates to customize your installation.</small>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php
// Output the main content that was captured in the buffer
echo $pageContent;
?>
<footer class="border-tb-primary">
    <a href="legal-notice.php">Legal Notice</a>
    <span>•</span>
    <a href="privacy-policy.php">Privacy Policy</a>
    <span>•</span>
    <a href="mailto:<?php echo Configuration::getInstance()->placeholder('contact.email'); ?>">Contact</a>
</footer>
</div><?php // end of page-container, see header.php ?>
</body>
</html>

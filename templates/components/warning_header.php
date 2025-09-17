<?php
/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 * Warning header component for missing configuration files
 */

$config = Configuration::getInstance();
if ($config->hasMissingConfigurationItems()): 
    $missingFiles = $config->getMissingConfigFiles();
    $missingKeys = $config->getMissingPlaceholderKeys();
    $missingTemplates = $config->getMissingTemplateFiles();
    $missingImages = $config->getMissingImageFiles();
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
        <?php if (!empty($missingImages)): ?>
            <?php if (!empty($missingFiles) || !empty($missingKeys) || !empty($missingTemplates)): ?><br><?php endif; ?>
            The following image files are missing and example files are being used instead:
            <strong><?php echo implode(', ', $missingImages); ?></strong>
            <br>
            <small>Please create these image files from their .example templates and customize them for your installation.</small>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
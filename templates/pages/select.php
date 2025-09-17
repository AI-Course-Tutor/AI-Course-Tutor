<?php
/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */
?>
<?php if (isset($_SESSION['tutor_mode_setup_error'])): ?>
<div class="tutor_mode_setup_error">
    <?php echo htmlspecialchars($_SESSION['tutor_mode_setup_error']); ?>
    <?php unset($_SESSION['tutor_mode_setup_error']); ?>
</div>
<?php endif; ?>

<?php

/**
 * Check if an item (mode or section) is available based on available_from and available_to dates
 * @param array $item The item (mode or section) to check
 * @return bool True if the item should be shown, false otherwise
 */
function is_item_available($item) {
    // Admin users can see everything
    require_once __DIR__ . '/../../src/Auth.php';
    $auth = new Auth();
    if ($auth->is_admin()) {
        return true;
    }
    
    $now = new DateTime();
    
    // Check available_from
    if (isset($item['available_from'])) {
        try {
            $availableFrom = new DateTime($item['available_from']);
            if ($now < $availableFrom) {
                return false; // Not yet available
            }
        } catch (Exception $e) {
            // Invalid date format, assume available
        }
    }
    
    // Check available_to
    if (isset($item['available_to'])) {
        try {
            $availableTo = new DateTime($item['available_to']);
            if ($now > $availableTo) {
                return false; // No longer available
            }
        } catch (Exception $e) {
            // Invalid date format, assume available
        }
    }
    
    return true; // Available if no restrictions or all checks pass
}

/**
 * Generate CSS spacing styles based on mode configuration
 * @param array $mode The mode configuration
 * @return string CSS style attribute content or empty string
 */
function generate_spacing_style($mode) {
    // Check for bottom spacing
    if (isset($mode['add_spacing_below']) && $mode['add_spacing_below']) {
        if ($mode['add_spacing_below'] === true) {
            return 'margin-bottom: 2em';  // Default fallback for boolean true
        } else {
            return 'margin-bottom: ' . htmlspecialchars($mode['add_spacing_below']);
        }
    }
    
    return '';
}
?>

<div class="container">

    <?php 
    $config = Configuration::getInstance();
    if ($config->isSidebarEnabled()) {
        $config->includeTemplate('components/sidebar.php', [
            'conversations' => $conversations ?? []
        ]); 
    }
    ?>

    <?php $config = Configuration::getInstance(); $tutorModes = $config->getTutorModes(); ?>
    <div class="selection-box">
        <h2>What can the <?php echo $config->placeholder('tutor.name'); ?> support you with today?</h2>
        <form action="select.php" method="POST">
            <br>
            
            <?php if (isset($tutorModes['modes'])): ?>
                <?php foreach ($tutorModes['modes'] as $mode): ?>
                    <?php if ($mode['enabled'] && is_item_available($mode)): ?>
                        <?php $spacingStyle = generate_spacing_style($mode); ?>
                        <div<?php if ($spacingStyle): ?> style="<?php echo $spacingStyle; ?>"<?php endif; ?>>
                            <?php if ($mode['type'] === 'simple_button'): ?>
                                <!-- Simple Button Mode -->
                                <p><?php echo $config->replacePlaceholders($mode['title']); ?></p>
                                <p><button type="submit" name="tutor_mode" value="<?php echo htmlspecialchars($mode['tutor_mode_value']); ?>"><?php echo $config->replacePlaceholders($mode['button_text']); ?></button></p>
                                
                            <?php elseif ($mode['type'] === 'homework_sections'): ?>
                                <!-- Homework Sections Mode -->
                                <p><?php echo $config->replacePlaceholders($mode['title']); ?></p>
                                
                                <?php if (isset($mode['sections'])): ?>
                                    <?php foreach ($mode['sections'] as $section): ?>
                                        <?php if ($section['enabled'] && is_item_available($section)): ?>
                                            <div class="<?php echo htmlspecialchars($mode['css_class'] ?? 'selection-box-homework'); ?>">
                                                <div><?php echo $config->replacePlaceholders($section['title']); ?></div>
                                                <?php if (isset($section['tasks'])): ?>
                                                    <?php foreach ($section['tasks'] as $task): ?>
                                                        <button type="submit" name="tutor_mode" value="<?php echo htmlspecialchars($task['tutor_mode_value']); ?>"><?php echo $config->replacePlaceholders($task['button_text']); ?></button>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

        </form>
    </div>
</div>

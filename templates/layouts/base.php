<?php
/**
 * Base layout template
 * This layout automatically includes header and footer for all pages
 * Preserves the output buffering system for configuration warnings
 * 
 * Variables expected:
 * - $pageName: Name of the page template to render
 * 
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

// Include header (starts output buffering)
Configuration::getInstance()->includeTemplate('components/header.php', $templateVariables ?? []);

// Include the specific page content
Configuration::getInstance()->includeTemplate('pages/' . $pageName, $templateVariables ?? []);

// Include footer (stops buffer, shows: optional warnings + page content + footer)
Configuration::getInstance()->includeTemplate('components/footer.php', $templateVariables ?? []);
?>
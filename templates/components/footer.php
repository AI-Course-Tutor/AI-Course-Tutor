<?php
/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

// Capture the buffered content (page content that may have used placeholders)
$pageContent = ob_get_clean();

// Include the warning header component
include __DIR__ . '/warning_header.php';
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

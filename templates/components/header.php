<?php
/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Configuration::getInstance()->placeholder('tutor.name') ?></title>
    <link rel="stylesheet" href="assets/colors.css">
    <?php if (file_exists(__DIR__ . '/../../public/assets/colors.custom.css')): ?>
    <link rel="stylesheet" href="assets/colors.custom.css">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/default.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>

    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js"></script>
</head>
<body>
<div class="page-container">
<header class="vertical-middle" style="display: flex;">
    <a href="index.php" style="display: flex; align-items: center; height: 100%;"><img src="<?= Configuration::getInstance()->resolveImagePath('logo.png') ?>" style="height: 80%;" alt="Logo"></a>
    <a href="index.php" style="display: flex; height: 100%; flex-direction: column; padding-left:1em; align-items: center; text-decoration: none;" class="">
        <div style="font-size: 0.9em;"><?= Configuration::getInstance()->placeholder('contact.department') ?></div>
        <div style="font-weight: bold; font-size: 1.2em;"><?= Configuration::getInstance()->placeholder('tutor.name') ?></div>
    </a>
</header>
<?php
/*
 * Start output buffering for main content.
 *
 * This is needed such that the main content is processed before the warning header is displayed (because the warning header will also include warnings for missing placeholders in the main content)
 * --> footer displays: optional warning header + main content + footer
 */
ob_start();
?>


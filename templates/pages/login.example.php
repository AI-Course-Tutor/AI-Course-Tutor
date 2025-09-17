<?php
/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

$config = Configuration::getInstance();
$passwordValidationConfig = $config->getPasswordValidationConfig();
?>
<div>
    <h2><?php echo $config->placeholder('tutor.name'); ?> Login</h2>

    <?php if (isset($error_message)): ?>
        <div style="color: red; margin-bottom: 15px;">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <?php if ($config->isGetAuthEnabled() && !$config->isPostAuthEnabled()): ?>
    <div style="background-color: #f0f8ff; border: 2px solid #4682b4; padding: 20px; margin: 20px 0; border-radius: 8px;">
        <h3 style="color: #2c5aa0; margin-top: 0;">Direct Access Not Available</h3>
        <p style="margin-bottom: 0;">
            Direct access to the tutor is not possible through this page. 
            Please use the specific link provided by your lecturer to access the system.
        </p>
    </div>
    <?php elseif ($config->isPostAuthEnabled()): ?>
    <form action="index.php" method="POST" style="padding-top: 20px;">
        <div style="margin-bottom: 10px;">
            <label for="user_name">Username:</label>
            <input type="text" id="user_name" name="user_name" required>
        </div>
        <?php if ($config->isPasswordRequiredForPost()): ?>
        <div style="margin-bottom: 10px;">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <?php endif; ?>
        <button type="submit">Login</button>
    </form>

    <h2 style="padding-top: 40px;">How to Login</h2>
    <h3>User Login</h3>
    <?php if ($config->isPasswordRequiredForPost()): ?>
    <p>Use the form above to log in with your username and password.</p>
    <ul>
        <li>If you are logging in for the first time, an account will be created with your username and chosen password.</li>
        <li>For future logins, please use the same username and password.</li>
        <?php if ($passwordValidationConfig['enabled']): ?>
        <li>Choose a secure password that is at least <?php echo $passwordValidationConfig['min_length']; ?> characters long<?php
            if ($passwordValidationConfig['require_letters'] && $passwordValidationConfig['require_numbers']) echo ' and contains both letters and numbers';
            elseif ($passwordValidationConfig['require_letters']) echo ' and contains letters';
            elseif ($passwordValidationConfig['require_numbers']) echo ' and contains numbers';
        ?>.</li>
        <?php endif; ?>
        <li>Remember your username and password well, as they are required to access your data.</li>
    </ul>
    <?php else: ?>
    <p>Use the form above to log in with your username.</p>
    <ul>
        <li>If you are logging in for the first time, an account will be created with your username.</li>
        <li>For future logins, please use the same username.</li>
        <li>Remember your username well, as it is required to access your data.</li>
    </ul>
    <?php endif; ?>
    <?php endif; ?>

    <h2 style="padding-top: 40px;">Contact for Questions</h2>
    <p><?php echo $config->placeholder('contact.name'); ?>: <a href="mailto:<?php echo $config->placeholder('contact.email'); ?>"><?php echo $config->placeholder('contact.email'); ?></a></p>
</div>

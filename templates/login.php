<?php
/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */
?>
<?php include 'header.php'; ?>
<div>
    <h2>[your-tutor-name] Login</h2>

    <?php if (isset($error_message)): ?>
        <div style="color: red; margin-bottom: 15px;">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form action="index.php" method="POST" style="padding-top: 20px;">
        <div style="margin-bottom: 10px;">
            <label for="user_name">Username:</label>
            <input type="text" id="user_name" name="user_name" required>
        </div>
        <div style="margin-bottom: 10px;">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>


    <h2 style="padding-top: 40px;">Login with Username and Password</h2>
    <p>To access the system, you need a username and password.</p>

    <h3>Login Instructions:</h3>
    <ul>
        <li>If you are logging in for the first time, an account will be created with your username and chosen password.</li>
        <li>For future logins, please use the same username and password.</li>
        <li>Choose a secure password that is at least 8 characters long and contains both letters and numbers.</li>
        <li>Remember your username and password well, as they are required to access your data.</li>
    </ul>

    <h2 style="padding-top: 40px;">Contact for Questions</h2>
    <p>[your-name]: <a href="mailto:[your-email]">[your-email]</a></p>
    <p>[additional-name]: <a href="mailto:[additional-email]">[additional-email]</a></p>
</div>
<?php include 'footer.php'; ?>

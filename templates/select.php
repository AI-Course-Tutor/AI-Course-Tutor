<?php
/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */
?>
<?php include 'header.php'; ?>

<?php if (isset($_SESSION['tutor_mode_setup_error'])): ?>
<div class="tutor_mode_setup_error">
    <?php echo htmlspecialchars($_SESSION['tutor_mode_setup_error']); ?>
    <?php unset($_SESSION['tutor_mode_setup_error']); ?>
</div>
<?php endif; ?>

<?php
function current_user_sees_all_boxes() {

    // can be used to implement some kind of admin user or moderator user that can already see all tasks before the
    // given date approached to check whether everything works as expected
    // example:
    // - return preg_match('/^ADAD\d{2}[A-ZÄÖÜẞ]{2}$/u', $_SESSION['user_name']);
    // - or add some flag to database to indicate admin / moderator users

    return false; // right now, nothing is implemented here, so always return false.
}
?>

<div class="container">

    <?php include 'conversation_history.php'; ?>

    <div class="selection-box">
        <h2>What can the [your-tutor-name] support you with today?</h2>
        <form action="select.php" method="POST">
            <br>
            <p>I have a question regarding R</p>
            <p><button type="submit" name="tutor_mode" value="general$question#Question">Start a new conversation with the [your-tutor-name]</button></p>
            <br><br>
            <p>I completed my homework and want to compare my solutions</p>

            <?php if ((new DateTime()) > (new DateTime('2024-11-19')) || current_user_sees_all_boxes()): # date when this should appear on the website ?>
                <div class="selection-box-homework">
                    <div>Plotting part 2</div>
                    <button type="submit" name="tutor_mode" value="plotting-2$task-1#Plotting 2: Task 1">Task 1</button>
                    <button type="submit" name="tutor_mode" value="plotting-2$task-2#Plotting 2: Task 2">Task 2</button>
                </div>
            <?php endif; ?>

        </form>
    </div>
</div>
<?php include 'footer.php'; ?>

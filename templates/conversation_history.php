<?php
/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */
?>
<div class="history-box">
    <?php if ($_SESSION['show_logout_button']): ?>
    <a href="logout.php" class="logout">Logout</a>
    <?php endif; ?>
    <form action="chat.php" method="POST">
        <button type="submit" name="finish" style="margin-top:0;">Start a new conversation</button>
    </form>
    <h3>Conversation History</h3>
    <div id="conversation-history">
        <?php foreach ($conversations as $conversation): ?>
            <a class="conversation-item<?php if (isset($_SESSION['conversation_id']) && $_SESSION['conversation_id'] == $conversation['id']): ?> conversation-item-current<?php endif; ?>" href="chat.php?conversation_id=<?php echo $conversation['id']; ?>">
                <?php
                    if (strlen($conversation['title']) > 0) {
                        echo "<span class='conversation-item-title'>";
                        echo htmlspecialchars($conversation['title']);
                        echo "</span>";
                        echo "<br>";
                        echo "<span class='conversation-item-date'>";
                        echo "(" . $conversation['created_at'] . ")";
                        echo "</span>";
                    } else {
                        echo "<span class='conversation-item-title'>";
                        echo $conversation['created_at'];
                        echo "</span>";
                    }
                ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
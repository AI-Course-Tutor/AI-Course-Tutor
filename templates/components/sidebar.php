<?php
/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */
$config = Configuration::getInstance();
?>
<div class="history-box">
    <?php if ($config->shouldShowLogoutButton()): ?>
    <a href="logout.php" class="logout">Logout</a>
    <?php endif; ?>
    <?php if ($config->shouldShowStartNewConversationButton()): ?>
    <form action="chat.php" method="POST">
        <button type="submit" name="finish" style="margin-top:0;">Start a new conversation</button>
    </form>
    <?php endif; ?>
    
    <?php 
    if ($config->isConversationHistoryEnabled()) {
        $config->includeTemplate('components/conversation_history_content.php', [
            'conversations' => $conversations ?? []
        ]);
    }
    ?>
</div>
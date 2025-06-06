<?php

/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

/*
 * needs to be passed to this template:
 * - $chat_messages: Messages to be displayed in chat box
 * - $parsedown: MarkdownToHTML converter
*/
?>


<?php include 'header.php'; ?>
<div class="container">

    <?php include 'conversation_history.php'; ?>

    <div class="chat-box">
        <div id="chat-messages">
<?php

foreach ($chat_messages as $chat_message) {
    $chat_message_content = $chat_message['content'];

    if ($chat_message['role'] == 'user') {
        $chat_message_css_class = 'user-message';
    } else if ($chat_message['role'] == 'assistant') {
        $chat_message_css_class = 'bot-response';
        $chat_message_content = convert_markdown_to_html($chat_message_content); // only convert openai markdown to html, not user markdown to prevent potential security issue caused by user-generated content
    } else {
        continue; // do not show system messages to user
    }

    echo '<div class="' . $chat_message_css_class . '">' . $chat_message_content . '</div>';

}
?>
        </div>
        <form id="chat-form">
            <textarea id="message" placeholder="Type your message..." autocomplete="off" autofocus></textarea>
            <button type="submit">Send</button>
        </form>

<?php if ($cur_conversation['tutor_mode'] !== 'general$question'): ?>
        <!-- Toggle for Tutor Provide Solutions -->
        <div class="tutor-provide-solutions-container">
            <label class="tutor-provide-solutions-label">Tutor provides solutions on request:</label>
            <div id="tutor-provide-solutions-toggle">
                <span class="tutor-provide-solutions-toggle-dot"></span>
            </div>
            <span id="tutor-provide-solutions-status">Disabled</span>
        </div>
<?php endif; ?>

        <form id="finish-form" action="chat.php" method="POST">
            <button type="submit" name="finish">Done with this question - Start a new conversation</button>
        </form>
    </div>
</div>
<script src="assets/chat.js"></script>
<script>
    document.querySelectorAll('div.bot-response').forEach((botResponseDiv) => {
        // Format the bot response
        formatBotResponseDiv(botResponseDiv);
    });
</script>
<?php include 'footer.php'; ?>

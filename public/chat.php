<?php

/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

# must be called at top of each php file accessible from the internet (i.e., in folder 'public')
require_once '../src/init.php';


# start of content specific to this file

if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

require_once '../src/Chat.php';
require_once '../src/GPT.php';

$chat = new Chat();
$gpt = new GPT();

use xenocrat\markdown\Markdown;

// escapeCharacters fix, because otherwise GPT response like this
// you can escape it using a backslash like this: "gr\_y".
// would appear as (so backslash stripped)
// you can escape it using a backslash like this: "gr_y".
// also not even escape backslash as such, because this could also cause issues such as "a.\\\\f" showing as "a.\\f"
class RTutorMarkdown extends Markdown
{
    protected $escapeCharacters = [
    ];
}

$markdownParser = new RTutorMarkdown();
$markdownParser->html5 = true;

// call this function such that we always use same conversion no matter whether called for JavaScript post or new loading of conversation
function convert_markdown_to_html($markdown) {
    global $markdownParser;
    return $markdownParser->parse($markdown);
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['finish'])) {

        unset($_SESSION['conversation_id']);
        header('Location: select.php');
        exit();

    } elseif (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json') { // user question for tutor

        $data = json_decode(file_get_contents('php://input'), true);
        $message = $data['message'];

        $system_prompt_provide_solutions = "From this point forward, you provide the R code for the solution to a specific task if the use requests the solution to a specific task. Avoid giving solutions otherwise.";
        $last_system_message = $chat->getLastSystemMessage();

        // process magic strings added to message
        if (str_starts_with($message, '#+TPS1+#')) {  // tutor provides solutions toggle is enabled
            $message = substr($message, strlen('#+TPS1+#'));

            // if last system message was not $system_prompt_provide_solutions, then add it now
            if (is_null($last_system_message) || strcmp($last_system_message, $system_prompt_provide_solutions) !== 0) {
                $chat->addMessage('system', $system_prompt_provide_solutions);
            }
        } else { // tutor provides solutions toggle is disabled
            // if last system message was  $system_prompt_provide_solutions, then revoke it
            if (! is_null($last_system_message) && strcmp($last_system_message, $system_prompt_provide_solutions) === 0) {
                $chat->addMessage('system', "From this point forward, you must not provide the R code for the solution to a specific task if the use requests the solution to a specific task. Always focus on offering guidance or clarifications instead of direct solutions.");
            }
        }

        $chat->addMessage('user', $message);
        $gpt_result = $gpt->getResponse($chat->getMessages());
        $gpt_response = $gpt_result['response'];

        // do *not* log errors to chat database and thus chat history or AI context
        if ($gpt_result['status'] != 'error') {
            $chat->addMessage('assistant', $gpt_response, $gpt_result['tokens_prompt'], $gpt_result['tokens_completion']);
        }

        echo json_encode(['response' => convert_markdown_to_html($gpt_response)]); // Markdown to HTML
        exit();

    }
}  else {

    // load chat history for this conversation id
    $chat_messages_html = "";
    if (isset($_GET['conversation_id'])) {

        // does conversation_id passed by GET belong to current user id (session variable)?
        $conversation_id = (int)$_GET['conversation_id'];
        $user_id_for_conversation_id = $chat->getUseridForConversationId($conversation_id);
        if ($user_id_for_conversation_id == $_SESSION['user_id']) {
            // yes, conversation_id belongs to current user id
            // --> update SESSION conversation id to the one passed via GET
            // --> ensures that user always sees the conversation he/she wants to interact with,
            //     such as after clicking on conversation in history
            $_SESSION['conversation_id'] = $conversation_id;
        } else {
            // no, conversation_id does not belong to current user id
            // --> send user back to starting new conversation
            unset($_SESSION['conversation_id']);
            header('Location: select.php');
            exit();
        }
    }

    // no conversation_id in session? --> send user back to starting new conversation
    if (! isset($_SESSION['conversation_id'])) {
        header('Location: select.php');
        exit();
    }

    // read messages belonging to this chat from DB
    // --> template adds them to output
    $chat_messages = $chat->getMessages();

    // read info about current conversation from DB
    // --> template requires to decide which tutor mode and whether to add 'Tutor Provide Solutions'-Toggle
    $cur_conversation = $chat->getConversation();

    // get all conversations belonging to this user from DB
    // --> template adds them to output
    $conversations = $chat->getConversations();


    include '../templates/chat.php';
}
?>

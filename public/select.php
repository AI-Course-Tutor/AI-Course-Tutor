<?php

/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

# must be called at top of each php file accessible from the internet (i.e., in folder 'public')
require_once '../src/init.php';


# start of content specific to this file

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) { #user_id: make sure user is logged in; user_name: needed in template
    header('Location: logout.php');
    exit();
}

require_once '../src/Chat.php';
require_once '../src/TutorModes.php';

$chat = new Chat();


// If select.php form was submitted with tutor_mode or default tutor_mode was defined with call of website (e.g. for a study)
// --> create conversation and continue to chat

if (isset($_POST['tutor_mode']) || isset($_SESSION['predefined_tutor_mode'])) {

    unset($_SESSION['conversation_id']);

    if (isset($_SESSION['predefined_tutor_mode'])) {
        $tutor_mode = $_SESSION['predefined_tutor_mode'];
    } else {
        $tutor_mode = $_POST["tutor_mode"];
    }

    if (isset($_SESSION['$continue_last_conversation_once'])) { // try to continue last conversation if a conversation with same tutor mode exists (only when using predefined_tutor_mode)
        unset($_SESSION['$continue_last_conversation_once']);

        $conversations = $chat->getConversations();

        foreach ($conversations as $conversation) {
            if ($conversation["tutor_mode"] == $tutor_mode) {
                $_SESSION['conversation_id'] = $conversation["id"];
                break;
            }
        }

    }

    // create new conversation if not old conversation is followed up upon
    if (! isset($_SESSION['conversation_id'])) {

        // extract conversation title from $tutor_mode (format "mode#title")
        $tutor_mode_split = explode('#', $tutor_mode);

        if (count($tutor_mode_split) == 2) {
            $tutor_mode = $tutor_mode_split[0];
            $conversation_title = $tutor_mode_split[1];
        } else {
            $conversation_title = "";
        }

        $_SESSION['conversation_id'] = $chat->createConversation($tutor_mode, $conversation_title);

        // add start messages to chat according to current tutor mode
        try {
            TutorModes::add_start_messages_to_chat($chat, $tutor_mode);
        } catch (Exception $e) {
            // Log the detailed error for server administrators including file, line, and stack trace
            error_log("PHP error: '" . get_class($e) . "' with message '" . $e->getMessage() . "' in " . $e->getFile() . ":" . $e->getLine() . "\nStack trace:\n" . $e->getTraceAsString());

            // The conversation is incomplete - just unset the conversation_id
            // It will be cleaned up later by getConversations method
            unset($_SESSION['conversation_id']);

            // Unset predefined_tutor_mode to prevent endless loop if it contains an invalid value
            if (isset($_SESSION['predefined_tutor_mode'])) {
                unset($_SESSION['predefined_tutor_mode']);
            }

            // Set error message to display to the user
            $_SESSION['tutor_mode_setup_error'] = "An error occurred while setting up the conversation. Please try again or contact support. Check server logs for more details.";

            // Redirect back to select.php to show the error
            header('Location: select.php');
            exit();
        }
    }

    header('Location: chat.php?conversation_id='.$_SESSION['conversation_id']);
    exit();
}

// No tutor mode selected yet -> let user choose a tutor mode

// get all conversations belonging to this user from DB
// --> template adds them to output
$conversations = $chat->getConversations();

include '../templates/select.php';

?>

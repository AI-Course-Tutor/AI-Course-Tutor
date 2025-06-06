<?php

/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

class TutorModes {
    public static function add_start_messages_to_chat($chat, $tutor_mode): void
    {
        /*
         * First, read default start messages from tutor-modes/!default folder (if it exists)
         *
         * Second, read start messages matching to current tutor_mode from files located in respective "tutor-modes" subfolders
         *
         * tutor_mode should be formatted as:
         * session_name$task_name
         *
         * Note: session_name and task_name may only contain lower letter a-z, numbers, and hyphens
         *
         * Start messages are extracted grom folder: tutor_modes/[session_name]/[task_name]/
         * Files are processed in numerical order and should be labeled:
         * X_[assistant or system].txt
         */

        // First, add default messages from !default folder
        self::add_messages_from_folder($chat, '../tutor-modes/!default');

        // Then add mode-specific messages
        $tutor_mode_split = explode('$', $tutor_mode);

        // tutor_mode string must always consist of 2 elements
        if (count($tutor_mode_split) != 2) {
            throw new Exception('Invalid tutor_mode format: Expected format "session_name$task_name" but got "' . $tutor_mode . '". The tutor_mode must contain exactly one "$" character.');
        }

        // folders (parts 1 and 2) in tutor_mode string must not contain any characters that might allow to walk file tree for hacking -> thus only lower letter a-z, numbers, and hyphens are allowed
        if (preg_match('/[^a-z0-9-]/', $tutor_mode_split[0]) ||  preg_match('/[^a-z0-9-]/', $tutor_mode_split[1])) {
            throw new Exception('Invalid tutor_mode format: The tutor_mode contains invalid characters. Only lowercase letters, numbers, and hyphens are allowed.');
        }

        $tutor_messages_folder = '../tutor-modes/' . $tutor_mode_split[0] . '/' . $tutor_mode_split[1];

        // if folder does not exist
        if (! is_dir($tutor_messages_folder)) {
            throw new Exception('Tutor mode folder not found: The directory "' . $tutor_messages_folder . '" does not exist.');
        }

        // Add messages from the mode-specific folder
        self::add_messages_from_folder($chat, $tutor_messages_folder);
    }

    /**
     * Helper function to add messages from a specific folder to the chat
     */
    private static function add_messages_from_folder($chat, $folder_path): void
    {
        // get all files in the folder
        $tutor_messages = scandir($folder_path);

        // if scandir failed (returns false if directory doesn't exist or can't be accessed)
        if ($tutor_messages === false) {
            if (strpos($folder_path, '!default') !== false) {
                return; // Skip if directory doesn't exist for !default folder
            } else {
                throw new Exception('Failed to access tutor mode folder: Could not read directory "' . $folder_path . '". Please check that the directory exists and has correct permissions.');
            }
        }

        // remove '.' and '..' from scan result
        $tutor_messages = array_diff($tutor_messages, array('..', '.'));

        // ensure that 11_assistant.txt is sorted after 2_system.txt
        natcasesort($tutor_messages);

        // check all file names that they indeed have the expected format, thus number_[assistant or system]
        foreach ($tutor_messages as $tutor_message) {
            $filename_split = explode('_', $tutor_message);

            // $tutor_message filename string must always consist of 2 elements
            if (count($filename_split) != 2) {
                throw new Exception('Invalid message file name format in "' . $folder_path . '": File "' . $tutor_message . '" does not follow the required format "number_role.txt". The filename must contain exactly one underscore character.');
            }

            // first part of file name must always be a number
            if (! is_numeric($filename_split[0])) {
                throw new Exception('Invalid message file name format in "' . $folder_path . '": In file "' . $tutor_message . '", the first part before the underscore ("' . $filename_split[0] . '") must be a number.');
            }

            // second part of file name must always be one of the accepted GPT roles, namely 'assistant' or 'system'
            // remove '.txt' from second part (to get tutor role)
            $tutor_role = preg_replace('/\.txt$/', '', $filename_split[1]);

            if ($tutor_role != 'assistant' && $tutor_role != 'system') {
                throw new Exception('Invalid message file name format in "' . $folder_path . '": In file "' . $tutor_message . '", the second part after the underscore must be either "assistant.txt" or "system.txt", but got "' . $filename_split[1] . '".');
            }
        }

        // add the actual messages to the chat
        foreach ($tutor_messages as $tutor_message) {
            $filename_split = explode('_', $tutor_message);

            // remove '.txt' from second part (to get tutor role)
            $tutor_role = preg_replace('/\.txt$/', '', $filename_split[1]);

            // just check again to ensure nothing goes wrong here
            if ($tutor_role != 'assistant' && $tutor_role != 'system') {
                throw new Exception('Invalid message file name format in "' . $folder_path . '": In file "' . $tutor_message . '", the second part after the underscore must be either "assistant.txt" or "system.txt", but got "' . $filename_split[1] . '".');
            }

            $tutor_message_content = file_get_contents($folder_path . '/' . $tutor_message);

            // add content of file with role provided in filename to chat
            $chat->addMessage($tutor_role, $tutor_message_content);
        }
    }
}
?>

<?php

/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

require_once 'Database.php';

class Chat {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createConversation($tutor_mode, $conversation_title) {

        if (! isset($_SESSION['user_id'])) throw new Exception('user_id missing');
        if (! isset($tutor_mode) || strlen($tutor_mode) == 0) throw new Exception('tutor_mode error');

        $query = "INSERT INTO conversations (user_id, tutor_mode, title) VALUES (:user_id, :tutor_mode, :title)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':tutor_mode', $tutor_mode);
        $stmt->bindParam(':title', $conversation_title);
        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    public function addMessage($role, $content, $tokens_prompt = 0, $tokens_completion = 0) {

        if (! isset($_SESSION['conversation_id'])) throw new Exception('conversation_id missing');
        if (! isset($_SESSION['user_id'])) throw new Exception('user_id missing');

        $query = "INSERT INTO messages (conversation_id, user_id, role, content, tokens_prompt, tokens_completion) VALUES (:conversation_id, :user_id, :role, :content, :tokens_prompt, :tokens_completion)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':conversation_id', $_SESSION['conversation_id']);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':tokens_prompt', $tokens_prompt);
        $stmt->bindParam(':tokens_completion', $tokens_completion);
        $stmt->execute();
    }

    public function getMessages() {

        if (! isset($_SESSION['conversation_id'])) throw new Exception('conversation_id missing');
        if (! isset($_SESSION['user_id'])) throw new Exception('user_id missing');

        $query = "SELECT role, content FROM messages WHERE conversation_id = :conversation_id AND user_id = :user_id ORDER BY created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':conversation_id', $_SESSION['conversation_id']);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLastSystemMessage() {

        if (! isset($_SESSION['conversation_id'])) throw new Exception('conversation_id missing');
        if (! isset($_SESSION['user_id'])) throw new Exception('user_id missing');

        $query = "SELECT content FROM messages WHERE conversation_id = :conversation_id AND user_id = :user_id AND role = 'system' ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':conversation_id', $_SESSION['conversation_id']);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);;

        if (! empty($result)) {
            return $result[0]['content'];
        } else {
            return null;
        }
    }

    public function getConversation() {

        if (! isset($_SESSION['conversation_id'])) throw new Exception('conversation_id missing');
        if (! isset($_SESSION['user_id'])) throw new Exception('user_id missing');

        $query = "SELECT * FROM conversations WHERE id = :conversation_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':conversation_id', $_SESSION['conversation_id']);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result[0];
    }

    public function getUseridForConversationId($conversation_id) {

        $query = "SELECT user_id FROM conversations WHERE id = :conversation_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':conversation_id', $conversation_id);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);;

        if (! empty($result)) {
            return $result[0]['user_id'];
        } else {
            return -1;
        }
    }

    public function getConversations() {

        if (! isset($_SESSION['user_id'])) throw new Exception('user_id missing');

        // 1. Delete conversations where the user never entered a single prompt
        $query = "DELETE FROM conversations
WHERE user_id = :user_id
AND id NOT IN (
    SELECT DISTINCT conversation_id 
    FROM messages 
    WHERE user_id = :user_id 
    AND role = 'user'
)";
        # (except for the conversation the user is currently working on, if any)
        if (isset($_SESSION['conversation_id'])) {
            $query .= " AND id != :conversation_id";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        if (isset($_SESSION['conversation_id'])) {
            $stmt->bindParam(':conversation_id', $_SESSION['conversation_id']);
        }
        $stmt->execute();


        // 2. Get all conversations belonging to this user
        $query = "SELECT * FROM conversations WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

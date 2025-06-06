<?php

/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

require_once 'Database.php';

class Auth {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Login or register a user
     * @param string $user_name The username
     * @param string $password The password
     * @return mixed User ID on success, error code string on failure
     */
    public function login($user_name, $password) {
        // Check if user exists
        $query = "SELECT id, password FROM users WHERE user_name = :user_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_name', $user_name);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // User exists
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password
            if (!$this->verifyPassword($password, $row['password'])) {
                return 'INVALID_PASSWORD'; // Password doesn't match
            }

            return $row['id'];
        } else {
            // User does not exist, create new user with password
            if (empty($password)) {
                return 'EMPTY_PASSWORD'; // Password is required for new users
            }

            // Validate password strength
            if (!$this->validatePassword($password)) {
                return 'INVALID_PASSWORD_FORMAT'; // Password doesn't meet requirements
            }

            $query = "INSERT INTO users (user_name, password) VALUES (:user_name, :password)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_name', $user_name);
            $hashed_password = $this->hashPassword($password);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();
            return $this->conn->lastInsertId();
        }
    }

    private function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    private function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Validates password strength according to requirements
     * @param string $password The password to validate
     * @return bool True if password meets requirements, false otherwise
     */
    private function validatePassword($password) {
        // Check if password is at least 8 characters long
        if (strlen($password) < 8) {
            return false;
        }

        // Check if password contains both letters and numbers
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return false;
        }

        return true;
    }

    public function set_consent($consent) {

        if (! isset($_SESSION['user_id'])) throw new Exception('user_id missing');

        $query = "UPDATE users SET consent = :consent WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':consent', $consent);
        return $stmt->execute();
    }

    public function get_consent() {

        if (! isset($_SESSION['user_id'])) throw new Exception('user_id missing');

        $query = "SELECT consent FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // User exists
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['consent'];
        } else {
            return '';
        }
    }

    public function user_exists($user_id, $user_name): bool
    {

        $query = "SELECT id FROM users WHERE id = :user_id AND user_name = :user_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':user_name', $user_name);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // User exists
            return true;
        } else {
            return false;
        }
    }
}
?>

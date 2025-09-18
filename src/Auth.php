<?php

/**
 * @author Frank Papenmeier <frank.papenmeier@uni-tuebingen.de>
 */

require_once 'Database.php';
require_once 'Configuration.php';

class Auth {
    private $conn;
    private $config;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->config = Configuration::getInstance();
    }

    /**
     * Login or register a user with configurable authentication
     * @param string $user_name The username
     * @param string $credential The password or token
     * @param bool $is_token Whether the credential is a token (true) or password (false)
     * @return mixed User ID on success, error code string on failure
     */
    public function login($user_name, $credential, $is_token = false) {
        // Check if user exists
        $query = "SELECT id, credential FROM users WHERE user_name = :user_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_name', $user_name);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // User exists - verify credential
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stored_credential = $row['credential'];

            if ($is_token) {
                // Token-based authentication
                if (!$this->config->isTokenRequiredForGet()) {
                    // Token not required, allow access
                    return $row['id'];
                }

                if ($this->isHashedPassword($stored_credential)) {
                    // User has a hashed password but trying to use token - not allowed
                    return 'INVALID_TOKEN_FOR_PASSWORD_USER';
                }

                // Only support hashed tokens
                if ($this->isHashedToken($stored_credential)) {
                    // Stored credential is a hashed token - verify using hash
                    if (!$this->verifyToken($credential, $stored_credential)) {
                        return 'INVALID_TOKEN';
                    }
                } else {
                    // Stored credential is not a hashed token - invalid
                    return 'INVALID_TOKEN';
                }

                return $row['id'];
            } else {
                // Password-based authentication
                if (!$this->config->isPasswordRequiredForPost()) {
                    // Password not required, allow access
                    return $row['id'];
                }

                if ($this->isHashedPassword($stored_credential)) {
                    // Stored credential is a hashed password
                    if (!$this->verifyPassword($credential, $stored_credential)) {
                        return 'INVALID_PASSWORD';
                    }
                } else {
                    // Stored credential is a plain token, but user trying to use password
                    return 'INVALID_PASSWORD_FOR_TOKEN_USER';
                }

                return $row['id'];
            }
        } else {
            // User does not exist - create new user
            if ($is_token) {
                // Create user with token
                if ($this->config->isTokenRequiredForGet()) {
                    // Token is required
                    if (empty($credential)) {
                        return 'EMPTY_TOKEN';
                    }
                    if (!$this->validateToken($credential)) {
                        return 'INVALID_TOKEN_FORMAT';
                    }
                    // Hash token for secure storage
                    $stored_credential = $this->hashToken($credential);
                } else {
                    // Token is not required
                    if (empty($credential)) {
                        // No token provided and none required - create user without credential
                        $stored_credential = '';
                    } else {
                        // Token provided but not required - still validate and store it
                        if (!$this->validateToken($credential)) {
                            return 'INVALID_TOKEN_FORMAT';
                        }
                        $stored_credential = $this->hashToken($credential);
                    }
                }
            } else {
                // Create user with password
                if ($this->config->isPasswordRequiredForPost()) {
                    // Password is required
                    if (empty($credential)) {
                        return 'EMPTY_PASSWORD';
                    }
                    if (!$this->validatePassword($credential)) {
                        return 'INVALID_PASSWORD_FORMAT';
                    }
                    // Hash password for storage
                    $stored_credential = $this->hashPassword($credential);
                } else {
                    // Password is not required
                    if (empty($credential)) {
                        // No password provided and none required - create user without credential
                        $stored_credential = '';
                    } else {
                        // Password provided but not required - still validate and store it
                        if (!$this->validatePassword($credential)) {
                            return 'INVALID_PASSWORD_FORMAT';
                        }
                        $stored_credential = $this->hashPassword($credential);
                    }
                }
            }

            $query = "INSERT INTO users (user_name, credential) VALUES (:user_name, :credential)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_name', $user_name);
            $stmt->bindParam(':credential', $stored_credential);
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

    private function hashToken($token) {
        // Use a different prefix to distinguish token hashes from password hashes
        return 'TOKEN:' . password_hash($token, PASSWORD_DEFAULT);
    }

    private function verifyToken($token, $hash) {
        // Remove the TOKEN: prefix and verify
        if (strpos($hash, 'TOKEN:') === 0) {
            $actualHash = substr($hash, 6); // Remove 'TOKEN:' prefix
            return password_verify($token, $actualHash);
        }
        return false;
    }

    /**
     * Validates password strength according to configuration requirements
     * @param string $password The password to validate
     * @return bool True if password meets requirements, false otherwise
     */
    private function validatePassword($password) {
        $config = $this->config->getPasswordValidationConfig();
        
        if (!$config['enabled']) {
            return true;
        }

        // Check minimum length
        if (strlen($password) < $config['min_length']) {
            return false;
        }

        // Check if password contains letters (if required)
        if ($config['require_letters'] && !preg_match('/[A-Za-z]/', $password)) {
            return false;
        }

        // Check if password contains numbers (if required)
        if ($config['require_numbers'] && !preg_match('/[0-9]/', $password)) {
            return false;
        }

        return true;
    }

    /**
     * Validates token according to configuration requirements
     * @param string $token The token to validate
     * @return bool True if token meets requirements, false otherwise
     */
    private function validateToken($token) {
        $config = $this->config->getTokenValidationConfig();
        
        if (!$config['enabled']) {
            return true;
        }

        // Check minimum length
        if (strlen($token) < $config['min_length']) {
            return false;
        }

        // Check if token contains only alphanumeric characters (if required)
        if ($config['allow_alphanumeric_only'] && !preg_match('/^[A-Za-z0-9]+$/', $token)) {
            return false;
        }

        return true;
    }

    /**
     * Validates username according to configuration requirements
     * @param string $username The username to validate
     * @param bool $isTokenAuth True for GET/token auth, false for POST/password auth
     * @return bool True if username meets requirements, false otherwise
     */
    public function validateUsername($username, $isTokenAuth) {
        $config = $isTokenAuth 
            ? $this->config->getUsernameValidationConfigForGet()
            : $this->config->getUsernameValidationConfigForPost();
        
        if (!$config['enabled']) {
            return true;
        }

        // Check if username matches required pattern
        if (!empty($config['require_pattern'])) {
            if (!preg_match($config['require_pattern'], $username)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determines if a stored credential is a hashed password
     * @param string $credential The stored credential
     * @return bool True if it's a hashed password, false otherwise
     */
    private function isHashedPassword($credential) {
        // Password hashes typically start with $ (e.g., $2y$, $argon2i$, etc.)
        return strpos($credential, '$') === 0;
    }

    /**
     * Determines if a stored credential is a hashed token
     * @param string $credential The stored credential
     * @return bool True if it's a hashed token, false otherwise
     */
    private function isHashedToken($credential) {
        // Token hashes start with TOKEN: prefix
        return strpos($credential, 'TOKEN:') === 0;
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

    public function is_pretest_completed(): bool
    {
        if (! isset($_SESSION['user_id'])) throw new Exception('user_id missing');

        $query = "SELECT pretest_completed FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (bool)$row['pretest_completed'];
        } else {
            return false;
        }
    }

    public function set_pretest_completed($user_id): bool
    {
        $query = "UPDATE users SET pretest_completed = 1 WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }

    public function is_admin(): bool
    {
        if (! isset($_SESSION['user_id'])) throw new Exception('user_id missing');

        $query = "SELECT is_admin FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (bool)$row['is_admin'];
        } else {
            return false;
        }
    }
}
?>

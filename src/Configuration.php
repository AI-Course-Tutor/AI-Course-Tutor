<?php

/**
 * Configuration management class for AI Course Tutor
 * Loads configuration from JSON files and replaces placeholders
 */

class Configuration {
    private static $instance = null;
    private $placeholders = [];
    private $tutorModes = [];
    private $system = [];
    private $missingConfigFiles = [];
    private $missingPlaceholderKeys = [];
    private $missingTemplateFiles = [];
    private $missingImageFiles = [];

    private function __construct() {
        $this->loadConfigurations();
    }

    public static function getInstance(): Configuration {
        if (self::$instance === null) {
            self::$instance = new Configuration();
        }
        return self::$instance;
    }

    private function loadConfigurations(): void {
        $configDir = dirname(__DIR__) . '/config/';
        
        // Load placeholder configuration - try system-specific first, then fall back to default
        $placeholdersFile = $configDir . 'placeholders.json';
        $placeholdersDefaultFile = $configDir . 'placeholders.example.json';
        if (file_exists($placeholdersFile)) {
            $this->placeholders = json_decode(file_get_contents($placeholdersFile), true);
        } elseif (file_exists($placeholdersDefaultFile)) {
            $this->placeholders = json_decode(file_get_contents($placeholdersDefaultFile), true);
            $this->missingConfigFiles[] = 'placeholders.json';
        }
        
        // Load tutor modes configuration - try system-specific first, then fall back to default
        $tutorModesFile = $configDir . 'tutor-modes.json';
        $tutorModesDefaultFile = $configDir . 'tutor-modes.example.json';
        if (file_exists($tutorModesFile)) {
            $this->tutorModes = json_decode(file_get_contents($tutorModesFile), true);
        } elseif (file_exists($tutorModesDefaultFile)) {
            $this->tutorModes = json_decode(file_get_contents($tutorModesDefaultFile), true);
            $this->missingConfigFiles[] = 'tutor-modes.json';
        }
        
        // Load system configuration - try system-specific first, then fall back to default
        $systemFile = $configDir . 'system.json';
        $systemDefaultFile = $configDir . 'system.example.json';
        if (file_exists($systemFile)) {
            $this->system = json_decode(file_get_contents($systemFile), true);
        } elseif (file_exists($systemDefaultFile)) {
            $this->system = json_decode(file_get_contents($systemDefaultFile), true);
            $this->missingConfigFiles[] = 'system.json';
        }
        
    }


    /**
     * Get placeholder value by dot notation path
     * If key is missing, uses the path as default and tracks the missing key
     * Automatically applies htmlspecialchars for safe HTML output
     */
    public function placeholder(string $path) {
        return htmlspecialchars($this->placeholderRaw($path), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get placeholder value by dot notation path without HTML escaping
     * Use this method for backend processing where HTML escaping is not needed
     * If key is missing, uses the path as default and tracks the missing key
     */
    public function placeholderRaw(string $path) {
        $keys = explode('.', $path);
        $value = $this->placeholders;
        
        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                // Key is missing - use path as default and track as missing
                if (!in_array($path, $this->missingPlaceholderKeys)) {
                    $this->missingPlaceholderKeys[] = $path;
                }
                return '{'.$path.'}';
            }
            $value = $value[$key];
        }
        
        return $value;
    }

    /**
     * Replace all placeholders in a string with their corresponding values
     * Searches for patterns like {placeholder.path} and replaces them with actual values
     * Uses the existing placeholder() method which includes HTML escaping
     */
    public function replacePlaceholders(string $text): string {
        return preg_replace_callback('/\{([^}]+)\}/', function($matches) {
            return $this->placeholder($matches[1]);
        }, $text);
    }

    /**
     * Replace all placeholders in a string with their corresponding raw values
     * Searches for patterns like {placeholder.path} and replaces them with actual values
     * Uses the existing placeholderRaw() method without HTML escaping
     */
    public function replacePlaceholdersRaw(string $text): string {
        return preg_replace_callback('/\{([^}]+)\}/', function($matches) {
            return $this->placeholderRaw($matches[1]);
        }, $text);
    }


    /**
     * Get tutor modes configuration
     */
    public function getTutorModes(): array {
        return $this->tutorModes;
    }

    /**
     * Get valid access tokens from configuration
     * Returns array of valid tokens or null if no tokens are configured
     */
    public function getValidAccessTokens(): ?array {
        $tokens = $this->system['access_token']['tokens'] ?? null;
        
        if ($tokens === null || !is_array($tokens)) {
            return null;
        }
        
        $validTokens = [];
        $currentDate = date('Y-m-d');
        
        foreach ($tokens as $tokenConfig) {
            $tokenValue = $tokenConfig['value'] ?? null;
            
            // Skip tokens with placeholder values or null values
            if ($tokenValue === null || preg_match('/^set_your_\w+_access_token_here$/', $tokenValue)) {
                continue;
            }
            
            // Check validity dates
            $validFrom = $tokenConfig['valid_from'] ?? null;
            $validTo = $tokenConfig['valid_to'] ?? null;
            
            // If valid_from is set and current date is before it, skip token
            if ($validFrom !== null && $currentDate < $validFrom) {
                continue;
            }
            
            // If valid_to is set and current date is after it, skip token
            if ($validTo !== null && $currentDate > $validTo) {
                continue;
            }
            
            $validTokens[] = $tokenValue;
        }
        
        return empty($validTokens) ? null : $validTokens;
    }

    /**
     * Check if access token system is enabled
     */
    public function isAccessTokenEnabled(): bool {
        return $this->system['access_token']['enabled'] ?? false;
    }

    /**
     * Check if consent system is enabled
     */
    public function isConsentEnabled(): bool {
        return $this->system['consent']['enabled'] ?? false;
    }

    /**
     * Get consent options configuration
     */
    public function getConsentOptions(): array {
        return $this->system['consent']['options'] ?? [];
    }

    /**
     * Get GPT model configuration
     */
    public function getGptModel(): string {
        return $this->system['gpt']['model'] ?? 'gpt-4o-mini-2024-07-18';
    }

    /**
     * Get GPT max tokens
     */
    public function getGptMaxTokens(): int {
        return $this->system['gpt']['max_tokens'] ?? 4096;
    }

    /**
     * Check if conversation history is enabled
     */
    public function isConversationHistoryEnabled(): bool {
        return $this->system['sidebar']['show_conversation_history'] ?? true;
    }

    /**
     * Check if sidebar should be shown
     */
    public function isSidebarEnabled(): bool {
        return $this->system['sidebar']['enabled'] ?? true;
    }

    /**
     * Check if logout button should be shown
     */
    public function shouldShowLogoutButton(): bool {
        return $this->system['sidebar']['show_logout_button'] ?? true;
    }

    /**
     * Check if start new conversation button should be shown
     */
    public function shouldShowStartNewConversationButton(): bool {
        return $this->system['sidebar']['show_start_new_conversation_button'] ?? true;
    }

    /**
     * Check if POST authentication is enabled
     */
    public function isPostAuthEnabled(): bool {
        return $this->system['authentication']['post']['enabled'] ?? true;
    }

    /**
     * Check if GET authentication is enabled
     */
    public function isGetAuthEnabled(): bool {
        return $this->system['authentication']['get']['enabled'] ?? true;
    }


    /**
     * Check if password is required for POST authentication
     */
    public function isPasswordRequiredForPost(): bool {
        return $this->system['authentication']['post']['require_password'] ?? true;
    }

    /**
     * Check if token is required for GET authentication
     */
    public function isTokenRequiredForGet(): bool {
        return $this->system['authentication']['get']['require_token'] ?? true;
    }

    /**
     * Get password validation configuration
     */
    public function getPasswordValidationConfig(): array {
        return $this->system['authentication']['post']['password_validation'] ?? [
            'enabled' => true,
            'min_length' => 8,
            'require_letters' => true,
            'require_numbers' => true
        ];
    }

    /**
     * Get token validation configuration
     */
    public function getTokenValidationConfig(): array {
        return $this->system['authentication']['get']['token_validation'] ?? [
            'enabled' => true,
            'min_length' => 16,
            'allow_alphanumeric_only' => true
        ];
    }

    /**
     * Get username validation configuration for POST authentication
     */
    public function getUsernameValidationConfigForPost(): array {
        return $this->system['authentication']['post']['username_validation'] ?? [
            'enabled' => false,
            'require_pattern' => ''
        ];
    }

    /**
     * Get username validation configuration for GET authentication
     */
    public function getUsernameValidationConfigForGet(): array {
        return $this->system['authentication']['get']['username_validation'] ?? [
            'enabled' => false,
            'require_pattern' => ''
        ];
    }

    /**
     * Get list of missing configuration files that are using example fallbacks
     */
    public function getMissingConfigFiles(): array {
        return $this->missingConfigFiles;
    }

    /**
     * Check if there are any missing configuration files
     */
    public function hasMissingConfigFiles(): bool {
        return !empty($this->missingConfigFiles);
    }

    /**
     * Get list of missing placeholder keys that are using generated defaults
     */
    public function getMissingPlaceholderKeys(): array {
        return $this->missingPlaceholderKeys;
    }

    /**
     * Check if there are any missing placeholder keys
     */
    public function hasMissingPlaceholderKeys(): bool {
        return !empty($this->missingPlaceholderKeys);
    }

    /**
     * Check if there are any missing configuration items (files or keys)
     */
    public function hasMissingConfigurationItems(): bool {
        return $this->hasMissingConfigFiles() || $this->hasMissingPlaceholderKeys() || $this->hasMissingTemplateFiles() || $this->hasMissingImageFiles();
    }

    /**
     * Get solution button configuration for a specific tutor mode
     */
    private function getSolutionButtonConfigForTutorMode(string $tutorModeValue): ?array {
        $tutorModesConfig = $this->getTutorModes();
        
        if (!isset($tutorModesConfig['solution_button']) || !is_array($tutorModesConfig['solution_button'])) {
            return null;
        }
        
        // Look through the top-level solution_button array for matching configuration
        foreach ($tutorModesConfig['solution_button'] as $solutionConfig) {
            if (isset($solutionConfig['tutor_mode_value_starts_with'])) {
                $startsWithValue = $solutionConfig['tutor_mode_value_starts_with'];
                if (str_starts_with($tutorModeValue, $startsWithValue)) {
                    return $solutionConfig;
                }
            }
        }
        
        return null;
    }

    /**
     * Check if the solution button is enabled for a specific tutor mode
     */
    public function isSolutionButtonEnabled(string $tutorModeValue): bool {
        $config = $this->getSolutionButtonConfigForTutorMode($tutorModeValue);
        return $config['enabled'] ?? false;
    }

    /**
     * Get the system prompt for enabling solution provision for a specific tutor mode
     */
    public function getSolutionToggleEnablePrompt(string $tutorModeValue): string {
        $config = $this->getSolutionButtonConfigForTutorMode($tutorModeValue);
        return $config['prompt_enable_show_solutions'] ?? '';
    }

    /**
     * Get the system prompt for disabling solution provision for a specific tutor mode
     */
    public function getSolutionToggleDisablePrompt(string $tutorModeValue): string {
        $config = $this->getSolutionButtonConfigForTutorMode($tutorModeValue);
        return $config['prompt_disable_show_solutions'] ?? '';
    }

    /**
     * Resolve image path with fallback to .example files
     * Returns the relative path to use and tracks missing images
     */
    public function resolveImagePath(string $imageName): string {
        $publicDir = dirname(__DIR__) . '/public/assets/imgs/';
        
        $customImage = $publicDir . $imageName;
        // Insert .example before the file extension
        $pathInfo = pathinfo($imageName);
        $exampleImage = $publicDir . $pathInfo['filename'] . '.example.' . $pathInfo['extension'];
        
        if (file_exists($customImage)) {
            return 'assets/imgs/' . $imageName;
        } elseif (file_exists($exampleImage)) {
            // Track that we're using example fallback
            if (!in_array($imageName, $this->missingImageFiles)) {
                $this->missingImageFiles[] = $imageName;
            }
            return 'assets/imgs/' . $pathInfo['filename'] . '.example.' . $pathInfo['extension'];
        } else {
            // Neither custom nor example exists - return custom path (will cause error)
            return 'assets/imgs/' . $imageName;
        }
    }

    /**
     * Get list of missing image files that are using example fallbacks
     */
    public function getMissingImageFiles(): array {
        return $this->missingImageFiles;
    }

    /**
     * Check if there are any missing image files
     */
    public function hasMissingImageFiles(): bool {
        return !empty($this->missingImageFiles);
    }

    /**
     * Resolve template path with fallback to .example files
     * Returns the path to use and tracks missing templates
     */
    public function resolveTemplatePath(string $templateName): string {
        $templatesDir = dirname(__DIR__) . '/templates/';
        
        $customTemplate = $templatesDir . $templateName;
        // Insert .example before the file extension
        $pathInfo = pathinfo($templateName);
        $exampleTemplate = $templatesDir . $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.example.' . $pathInfo['extension'];
        
        if (file_exists($customTemplate)) {
            return $customTemplate;
        } elseif (file_exists($exampleTemplate)) {
            // Track that we're using example fallback
            if (!in_array($templateName, $this->missingTemplateFiles)) {
                $this->missingTemplateFiles[] = $templateName;
            }
            return $exampleTemplate;
        } else {
            // Neither custom nor example exists - return custom path (will cause error)
            return $customTemplate;
        }
    }

    /**
     * Get list of missing template files that are using example fallbacks
     */
    public function getMissingTemplateFiles(): array {
        return $this->missingTemplateFiles;
    }

    /**
     * Check if there are any missing template files
     */
    public function hasMissingTemplateFiles(): bool {
        return !empty($this->missingTemplateFiles);
    }

    /**
     * Include a template file with fallback to .example files
     * This should be used instead of direct include statements for customizable templates
     */
    public function includeTemplate(string $templateName, array $variables = []): void {
        // Extract variables to make them available in template scope
        extract($variables);
        
        $templatePath = $this->resolveTemplatePath($templateName);
        include $templatePath;
    }

    /**
     * Render a page using the new template system
     * Automatically includes header and footer via base layout
     * Uses resolveTemplatePath for fallback to .example files
     */
    public function renderPage(string $pageName, array $variables = []): void {
        // Store variables for template access (passed from base layout to includeTemplate)
        $templateVariables = $variables;

        $layoutPath = $this->resolveTemplatePath('layouts/base.php');

        include $layoutPath;
    }

}
?>
<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * AiCommandValidator - Validates and sanitizes AI-generated commands before execution.
 *
 * Provides strict allowlist-based validation, parameter sanitization, and command structure
 * verification to prevent malicious or malformed commands from being executed.
 */
class AiCommandValidator
{
    /**
     * Allowed parameter types and their validation rules.
     */
    protected const PARAM_RULES = [
        'integer' => 'required|integer',
        'number' => 'required|numeric',
        'string' => 'required|string|max:1000',
        'boolean' => 'required|boolean',
        'email' => 'required|email',
        'date' => 'required|date',
        'array' => 'required|array',
        'uuid' => 'required|uuid',
    ];

    /**
     * Dangerous patterns that should be blocked in string parameters.
     */
    protected const DANGEROUS_PATTERNS = [
        '/<script/i',                          // Script injection
        '/javascript:/i',                      // JavaScript protocol
        '/data:/i',                            // Data protocol
        '/vbscript:/i',                        // VBScript protocol
        '/on\w+\s*=/i',                        // Event handlers (onclick=, etc.)
        '/union\s+select/i',                   // SQL injection
        '/drop\s+table/i',                     // SQL injection
        '/delete\s+from/i',                    // SQL injection
        '/insert\s+into/i',                    // SQL injection
        '/exec\s*\(/i',                        // Command execution
        '/system\s*\(/i',                      // System calls
        '/passthru\s*\(/i',                    // Passthru calls
        '/shell_exec/i',                       // Shell execution
        '/\.\./',                              // Directory traversal
        '/%2e%2e/i',                           // Encoded directory traversal
    ];

    /**
     * Maximum depth for nested arrays.
     */
    protected const MAX_ARRAY_DEPTH = 3;

    /**
     * Validate a command's arguments against its schema.
     *
     * @param  string  $commandName  The name of the command
     * @param  array  $arguments  The arguments to validate
     * @return array ['valid' => bool, 'errors' => array, 'sanitized' => array]
     */
    public function validate(string $commandName, array $arguments): array
    {
        $errors = [];
        $sanitized = [];

        // Get the tool definition to validate against
        $toolDef = $this->getToolDefinition($commandName);

        if (! $toolDef) {
            return [
                'valid' => false,
                'errors' => ["Unknown command: {$commandName}"],
                'sanitized' => [],
            ];
        }

        // Validate required parameters — required is nested inside parameters
        $requiredParams = $toolDef['parameters']['required'] ?? $toolDef['required'] ?? [];
        foreach ($requiredParams as $param) {
            if (! array_key_exists($param, $arguments)) {
                $errors[] = "Missing required parameter: {$param}";
            }
        }

        // Validate and sanitize each parameter
        $properties = $toolDef['parameters']['properties'] ?? [];
        foreach ($arguments as $paramName => $paramValue) {
            if (! isset($properties[$paramName])) {
                // Skip unknown parameters silently instead of blocking execution
                Log::debug("AiCommandValidator: Skipping unknown parameter '{$paramName}' for tool '{$commandName}'");

                continue;
            }

            $paramSchema = $properties[$paramName];
            $validationResult = $this->validateParameter(
                $paramName,
                $paramValue,
                $paramSchema,
                in_array($paramName, $requiredParams)
            );

            if ($validationResult['valid'] === false) {
                $errors = array_merge($errors, $validationResult['errors']);
            } else {
                $sanitized[$paramName] = $validationResult['value'];
            }
        }

        // Check for additional dangerous patterns
        $this->checkDangerousPatterns($sanitized, $errors);

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'sanitized' => $sanitized,
        ];
    }

    /**
     * Validate a single parameter against its schema.
     */
    protected function validateParameter(
        string $name,
        mixed $value,
        array $schema,
        bool $isRequired
    ): array {
        $type = $schema['type'] ?? 'string';
        $errors = [];

        // Handle nullable parameters
        if ($value === null) {
            if ($isRequired && empty($schema['nullable'])) {
                return ['valid' => false, 'errors' => ["Parameter {$name} is required"]];
            }

            return ['valid' => true, 'value' => null];
        }

        // Type validation
        $typeValidation = $this->validateType($name, $value, $type);
        if ($typeValidation['valid'] === false) {
            return $typeValidation;
        }

        // Apply type-specific sanitization
        $sanitizedValue = $this->sanitizeByType($value, $type);

        // Additional constraints
        if (isset($schema['minLength']) && is_string($sanitizedValue)) {
            if (strlen($sanitizedValue) < $schema['minLength']) {
                $errors[] = "Parameter {$name} must be at least {$schema['minLength']} characters";
            }
        }

        if (isset($schema['maxLength']) && is_string($sanitizedValue)) {
            if (strlen($sanitizedValue) > $schema['maxLength']) {
                $errors[] = "Parameter {$name} must not exceed {$schema['maxLength']} characters";
            }
        }

        if (isset($schema['minimum']) && is_numeric($sanitizedValue)) {
            if ($sanitizedValue < $schema['minimum']) {
                $errors[] = "Parameter {$name} must be at least {$schema['minimum']}";
            }
        }

        if (isset($schema['maximum']) && is_numeric($sanitizedValue)) {
            if ($sanitizedValue > $schema['maximum']) {
                $errors[] = "Parameter {$name} must not exceed {$schema['maximum']}";
            }
        }

        if (isset($schema['enum']) && ! in_array($sanitizedValue, $schema['enum'], true)) {
            $errors[] = "Parameter {$name} must be one of: ".implode(', ', $schema['enum']);
        }

        if (empty($errors)) {
            return ['valid' => true, 'value' => $sanitizedValue];
        }

        return ['valid' => false, 'errors' => $errors];
    }

    /**
     * Validate parameter type.
     */
    protected function validateType(string $name, mixed $value, string $type): array
    {
        $typeChecks = [
            'string' => 'is_string',
            'integer' => fn ($v) => is_int($v) || (is_numeric($v) && floor((float) $v) == (float) $v),
            'number' => 'is_numeric',
            'boolean' => 'is_bool',
            'array' => 'is_array',
            'object' => fn ($v) => is_array($v) || is_object($v),
        ];

        if (! isset($typeChecks[$type])) {
            return ['valid' => false, 'errors' => ["Unknown type: {$type}"]];
        }

        if (! $typeChecks[$type]($value)) {
            return [
                'valid' => false,
                'errors' => ["Parameter {$name} must be of type {$type}"],
            ];
        }

        // Special validation for specific types
        if ($type === 'string') {
            if (mb_strlen($value) > 10000) {
                return ['valid' => false, 'errors' => ["Parameter {$name} is too long"]];
            }
        }

        if ($type === 'array') {
            $depth = $this->getArrayDepth($value);
            if ($depth > self::MAX_ARRAY_DEPTH) {
                return [
                    'valid' => false,
                    'errors' => ["Parameter {$name} exceeds maximum nesting depth"],
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * Sanitize value based on type.
     */
    protected function sanitizeByType(mixed $value, string $type): mixed
    {
        switch ($type) {
            case 'string':
                // Remove null bytes and trim whitespace
                $value = str_replace(chr(0), '', $value);
                $value = trim($value);
                // Strip tags but preserve allowed HTML
                $value = strip_tags($value, '<b><i><u><strong><em><br><p>');
                break;

            case 'integer':
                $value = (int) round((float) $value);
                break;

            case 'number':
                $value = (float) $value;
                break;

            case 'boolean':
                $value = (bool) $value;
                break;

            case 'array':
                // Recursively sanitize array values
                $value = array_map(function ($item) {
                    if (is_string($item)) {
                        return $this->sanitizeByType($item, 'string');
                    }

                    return $item;
                }, $value);
                break;
        }

        return $value;
    }

    /**
     * Check for dangerous patterns in sanitized values.
     */
    protected function checkDangerousPatterns(array $values, array &$errors): void
    {
        foreach ($values as $key => $value) {
            if (is_string($value)) {
                foreach (self::DANGEROUS_PATTERNS as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $errors[] = "Parameter {$key} contains potentially dangerous content";
                        Log::warning('AiCommandValidator: Blocked dangerous pattern', [
                            'pattern' => $pattern,
                            'parameter' => $key,
                            'value' => substr($value, 0, 100),
                        ]);
                        break;
                    }
                }
            }

            if (is_array($value)) {
                $this->checkDangerousPatternsInArray($value, $key, $errors);
            }
        }
    }

    /**
     * Recursively check dangerous patterns in nested arrays.
     */
    protected function checkDangerousPatternsInArray(
        array $array,
        string $parentKey,
        array &$errors,
        int $depth = 1
    ): void {
        if ($depth > self::MAX_ARRAY_DEPTH) {
            return;
        }

        foreach ($array as $key => $value) {
            $fullKey = "{$parentKey}[{$key}]";

            if (is_string($value)) {
                foreach (self::DANGEROUS_PATTERNS as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $errors[] = "Parameter {$fullKey} contains potentially dangerous content";
                        Log::warning('AiCommandValidator: Blocked dangerous pattern in array', [
                            'pattern' => $pattern,
                            'parameter' => $fullKey,
                            'value' => substr($value, 0, 100),
                        ]);
                        break;
                    }
                }
            }

            if (is_array($value)) {
                $this->checkDangerousPatternsInArray($value, $fullKey, $errors, $depth + 1);
            }
        }
    }

    /**
     * Get array nesting depth.
     */
    protected function getArrayDepth(array $array): int
    {
        $maxDepth = 0;
        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = 1 + $this->getArrayDepth($value);
                $maxDepth = max($maxDepth, $depth);
            }
        }

        return $maxDepth;
    }

    /**
     * Tool definitions cache.
     */
    protected static ?array $toolDefinitions = null;

    /**
     * Get tool definition by name.
     */
    protected function getToolDefinition(string $commandName): ?array
    {
        if (self::$toolDefinitions === null) {
            try {
                // Lazy load - will be populated by ToolRegistry
                self::$toolDefinitions = [];
            } catch (\Throwable $e) {
                Log::error('AiCommandValidator: Failed to load tool definitions', [
                    'error' => $e->getMessage(),
                ]);

                return null;
            }
        }

        return self::$toolDefinitions[$commandName] ?? null;
    }

    /**
     * Set tool definitions from ToolRegistry.
     */
    public function setToolDefinitions(array $definitions): void
    {
        self::$toolDefinitions = $definitions;
    }
}

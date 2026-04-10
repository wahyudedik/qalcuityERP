<?php

namespace App\Rules;

use App\Models\PasswordHistory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class StrongPassword implements ValidationRule
{
    protected $user;
    protected $attribute;

    public function __construct($user = null)
    {
        $this->user = $user;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $this->attribute = $attribute;
        $errors = [];

        // Check minimum length
        $minLength = config('password.min_length', 12);
        if (strlen($value) < $minLength) {
            $errors[] = "Password must be at least {$minLength} characters long.";
        }

        // Check uppercase
        if (config('password.require_uppercase', true) && !preg_match('/[A-Z]/', $value)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }

        // Check lowercase
        if (config('password.require_lowercase', true) && !preg_match('/[a-z]/', $value)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }

        // Check numbers
        if (config('password.require_numbers', true) && !preg_match('/[0-9]/', $value)) {
            $errors[] = 'Password must contain at least one number.';
        }

        // Check special characters
        if (config('password.require_special_chars', true) && !preg_match('/[' . preg_quote(config('password.special_chars', '!@#$%^&*()_+-=[]{}|;:,.<>?'), '/') . ']/', $value)) {
            $errors[] = 'Password must contain at least one special character.';
        }

        // Check for common passwords
        if (config('password.prevent_common_passwords', true) && $this->isCommonPassword($value)) {
            $errors[] = 'This password is too common and easily guessable.';
        }

        // Check if username is in password
        if ($this->user && config('password.prevent_username_in_password', true)) {
            $username = strtolower($this->user->name ?? '');
            if (strlen($username) >= 3 && str_contains(strtolower($value), $username)) {
                $errors[] = 'Password cannot contain your username.';
            }
        }

        // Check if email is in password
        if ($this->user && config('password.prevent_email_in_password', true)) {
            $email = strtolower($this->user->email ?? '');
            $emailPart = explode('@', $email)[0];
            if (strlen($emailPart) >= 3 && str_contains(strtolower($value), $emailPart)) {
                $errors[] = 'Password cannot contain your email address.';
            }
        }

        // Check password history (prevent reuse)
        if ($this->user) {
            $historyCount = config('password.prevent_reuse_count', 5);
            if ($historyCount > 0) {
                $recentPasswords = PasswordHistory::where('user_id', $this->user->id)
                    ->orderBy('created_at', 'desc')
                    ->limit($historyCount)
                    ->get();

                foreach ($recentPasswords as $history) {
                    if (Hash::check($value, $history->password_hash)) {
                        $errors[] = "You cannot reuse a password from the last {$historyCount} passwords.";
                        break;
                    }
                }
            }
        }

        // Check password strength score
        $score = $this->calculateStrengthScore($value);
        $minScore = config('password.min_complexity_score', 3);
        if ($score < $minScore) {
            $errors[] = "Password is too weak. Please use a more complex password (score: {$score}/{$minScore}).";
        }

        // Fail validation if there are errors
        if (!empty($errors)) {
            $fail(implode(' ', $errors));
        }
    }

    /**
     * Check if password is in common passwords list
     */
    protected function isCommonPassword(string $password): bool
    {
        $commonPasswordsFile = config('password.common_passwords_file');

        if (!file_exists($commonPasswordsFile)) {
            // Fallback: check against a small list of very common passwords
            $commonPasswords = [
                'password',
                '123456',
                '12345678',
                'qwerty',
                'abc123',
                'monkey',
                '1234567',
                'letmein',
                'trustno1',
                'dragon',
                'baseball',
                'iloveyou',
                'master',
                'sunshine',
                'ashley',
                'bailey',
                'passw0rd',
                'shadow',
                '123123',
                '654321',
                'superman',
                'qazwsx',
                'michael',
                'football',
                'password1',
                'password123',
                'admin',
                'admin123',
                'root',
                'toor',
                'welcome',
                'welcome1',
                'hello',
                'charlie',
                'donald',
            ];
            return in_array(strtolower($password), $commonPasswords);
        }

        $commonPasswords = file($commonPasswordsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return in_array(strtolower($password), $commonPasswords);
    }

    /**
     * Calculate password strength score
     */
    protected function calculateStrengthScore(string $password): int
    {
        $score = 0;
        $weights = config('password.complexity_weights', [
            'length' => 1,
            'uppercase' => 1,
            'lowercase' => 1,
            'numbers' => 1,
            'special_chars' => 2,
            'mixed_case' => 1,
        ]);

        // Length score (1 point per 4 characters)
        $score += floor(strlen($password) / 4) * ($weights['length'] ?? 1);

        // Character variety
        if (preg_match('/[A-Z]/', $password)) {
            $score += $weights['uppercase'] ?? 1;
        }
        if (preg_match('/[a-z]/', $password)) {
            $score += $weights['lowercase'] ?? 1;
        }
        if (preg_match('/[0-9]/', $password)) {
            $score += $weights['numbers'] ?? 1;
        }
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $score += $weights['special_chars'] ?? 2;
        }

        // Mixed case bonus
        if (preg_match('/[A-Z]/', $password) && preg_match('/[a-z]/', $password)) {
            $score += $weights['mixed_case'] ?? 1;
        }

        return $score;
    }

    /**
     * Get password strength level
     */
    public static function getStrengthLevel(string $password): string
    {
        $score = (new static())->calculateStrengthScore($password);

        if ($score >= 8)
            return 'Very Strong';
        if ($score >= 6)
            return 'Strong';
        if ($score >= 4)
            return 'Medium';
        if ($score >= 2)
            return 'Weak';
        return 'Very Weak';
    }

    /**
     * Get password strength score
     */
    public static function getStrengthScore(string $password): int
    {
        return (new static())->calculateStrengthScore($password);
    }
}

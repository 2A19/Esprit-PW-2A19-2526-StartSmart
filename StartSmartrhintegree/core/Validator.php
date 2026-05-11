<?php
/**
 * Custom Form Validator Class
 * Handles all form validation without HTML5 validation
 */

class Validator {
    private $errors = [];

    /**
     * Validate email format
     * @param string $email
     * @param string $fieldName
     * @return void
     */
    public function validateEmail($email, $fieldName = 'Email') {
        if (empty($email)) {
            $this->addError("$fieldName is required");
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addError("$fieldName must be a valid email address");
        }
    }

    /**
     * Validate required field
     * @param string $value
     * @param string $fieldName
     * @return void
     */
    public function validateRequired($value, $fieldName = 'Field') {
        if (empty(trim($value))) {
            $this->addError("$fieldName is required");
        }
    }

    /**
     * Validate string length
     * @param string $value
     * @param int $minLength
     * @param int $maxLength
     * @param string $fieldName
     * @return void
     */
    public function validateLength($value, $minLength, $maxLength, $fieldName = 'Field') {
        $length = strlen($value);
        if ($length < $minLength || $length > $maxLength) {
            $this->addError("$fieldName must be between $minLength and $maxLength characters");
        }
    }

    /**
     * Validate password strength
     * @param string $password
     * @param string $fieldName
     * @return void
     */
    public function validatePassword($password, $fieldName = 'Password') {
        if (empty($password)) {
            $this->addError("$fieldName is required");
            return;
        }
        if (strlen($password) < 8) {
            $this->addError("$fieldName must be at least 8 characters");
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $this->addError("$fieldName must contain at least one uppercase letter");
        }
        if (!preg_match('/[a-z]/', $password)) {
            $this->addError("$fieldName must contain at least one lowercase letter");
        }
        if (!preg_match('/[0-9]/', $password)) {
            $this->addError("$fieldName must contain at least one number");
        }
    }

    /**
     * Validate phone number
     * @param string $phone
     * @param string $fieldName
     * @return void
     */
    public function validatePhone($phone, $fieldName = 'Phone') {
        if (empty($phone)) {
            $this->addError("$fieldName is required");
            return;
        }
        // Accept various phone formats
        if (!preg_match('/^[0-9\s\-\+\(\)]{10,20}$/', $phone)) {
            $this->addError("$fieldName format is invalid");
        }
    }

    /**
     * Validate numeric field
     * @param mixed $value
     * @param string $fieldName
     * @return void
     */
    public function validateNumeric($value, $fieldName = 'Field') {
        if (!is_numeric($value)) {
            $this->addError("$fieldName must be a number");
        }
    }

    /**
     * Validate date format
     * @param string $date
     * @param string $format
     * @param string $fieldName
     * @return void
     */
    public function validateDate($date, $format = 'Y-m-d', $fieldName = 'Date') {
        if (empty($date)) {
            $this->addError("$fieldName is required");
            return;
        }
        $d = DateTime::createFromFormat($format, $date);
        if (!$d || $d->format($format) !== $date) {
            $this->addError("$fieldName must be in format $format");
        }
    }

    /**
     * Validate URL
     * @param string $url
     * @param string $fieldName
     * @return void
     */
    public function validateUrl($url, $fieldName = 'URL') {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->addError("$fieldName must be a valid URL");
        }
    }

    /**
     * Validate salary range
     * @param float $salary
     * @param float $min
     * @param float $max
     * @param string $fieldName
     * @return void
     */
    public function validateSalaryRange($salary, $min = 0, $max = 1000000, $fieldName = 'Salary') {
        if (!is_numeric($salary)) {
            $this->addError("$fieldName must be a number");
            return;
        }
        if ($salary < $min || $salary > $max) {
            $this->addError("$fieldName must be between $min and $max");
        }
    }

    /**
     * Add error message
     * @param string $message
     * @return void
     */
    private function addError($message) {
        $this->errors[] = $message;
    }

    /**
     * Get all errors
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Check if validation passed
     * @return bool
     */
    public function isValid() {
        return empty($this->errors);
    }

    /**
     * Clear errors
     * @return void
     */
    public function clearErrors() {
        $this->errors = [];
    }
}
?>

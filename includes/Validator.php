<?php
/**
 * includes/Validator.php
 * Input validation utilities
 */

class Validator {
    private $errors = [];
    private $data = [];

    /**
     * Constructor
     */
    public function __construct($data = []) {
        $this->data = $data;
    }

    /**
     * Validate required field
     */
    public function required($field, $label = null) {
        $label = $label ?? ucfirst(str_replace('_', ' ', $field));
        if (empty($this->data[$field])) {
            $this->errors[$field] = "$label is required";
        }
        return $this;
    }

    /**
     * Validate email
     */
    public function email($field, $label = null) {
        $label = $label ?? ucfirst(str_replace('_', ' ', $field));
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "$label must be a valid email address";
        }
        return $this;
    }

    /**
     * Validate minimum length
     */
    public function minLength($field, $length, $label = null) {
        $label = $label ?? ucfirst(str_replace('_', ' ', $field));
        if (!empty($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field] = "$label must be at least $length characters";
        }
        return $this;
    }

    /**
     * Validate maximum length
     */
    public function maxLength($field, $length, $label = null) {
        $label = $label ?? ucfirst(str_replace('_', ' ', $field));
        if (!empty($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field] = "$label must not exceed $length characters";
        }
        return $this;
    }

    /**
     * Validate numeric
     */
    public function numeric($field, $label = null) {
        $label = $label ?? ucfirst(str_replace('_', ' ', $field));
        if (!empty($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = "$label must be a number";
        }
        return $this;
    }

    /**
     * Validate match fields
     */
    public function match($field, $matchField, $label = null) {
        $label = $label ?? ucfirst(str_replace('_', ' ', $field));
        if (!empty($this->data[$field]) && $this->data[$field] !== ($this->data[$matchField] ?? null)) {
            $this->errors[$field] = "$label does not match";
        }
        return $this;
    }

    /**
     * Validate URL
     */
    public function url($field, $label = null) {
        $label = $label ?? ucfirst(str_replace('_', ' ', $field));
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_URL)) {
            $this->errors[$field] = "$label must be a valid URL";
        }
        return $this;
    }

    /**
     * Validate regex pattern
     */
    public function regex($field, $pattern, $label = null) {
        $label = $label ?? ucfirst(str_replace('_', ' ', $field));
        if (!empty($this->data[$field]) && !preg_match($pattern, $this->data[$field])) {
            $this->errors[$field] = "$label format is invalid";
        }
        return $this;
    }

    /**
     * Check if validation passes
     */
    public function passes() {
        return empty($this->errors);
    }

    /**
     * Check if validation fails
     */
    public function fails() {
        return !empty($this->errors);
    }

    /**
     * Get validation errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get error message for field
     */
    public function getError($field) {
        return $this->errors[$field] ?? null;
    }
}

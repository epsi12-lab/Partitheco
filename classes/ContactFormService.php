<?php
// classes/ContactFormService.php

namespace App;

class ContactFormService {
    public function validate(array $input, array $translations): array {
        $data = [
            'name' => trim($input['name'] ?? ''),
            'email' => trim($input['email'] ?? ''),
            'message' => trim($input['message'] ?? ''),
        ];

        $errors = [];

        if ($data['name'] === '') {
            $errors[] = $translations['error_name'];
        } elseif (strlen($data['name']) > 100) {
            $errors[] = $translations['error_name_length'];
        }

        if ($data['email'] === '') {
            $errors[] = $translations['error_email'];
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = $translations['error_email_invalid'];
        }

        if ($data['message'] === '') {
            $errors[] = $translations['error_message'];
        } elseif (strlen($data['message']) > 1000) {
            $errors[] = $translations['error_message_length'];
        }

        return [
            'data' => $data,
            'errors' => $errors,
            'success' => $errors === [],
        ];
    }
}

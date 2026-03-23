<?php
// classes/RegistrationService.php

namespace App;

class RegistrationService {
    private User $user;
    private AuthService $authService;

    public function __construct(User $user, AuthService $authService) {
        $this->user = $user;
        $this->authService = $authService;
    }

    public function registerFromInput(array $input): array {
        $data = [
            'first_name' => trim($input['first_name'] ?? ''),
            'last_name' => trim($input['last_name'] ?? ''),
            'username' => trim($input['username'] ?? ''),
            'email' => trim($input['email'] ?? ''),
            'password' => $input['password'] ?? '',
            'password_confirm' => $input['password_confirm'] ?? '',
            'paroisse' => trim($input['paroisse'] ?? ''),
            'role_choral' => trim($input['role_choral'] ?? ''),
        ];

        $errors = [];

        if ($data['first_name'] === '') {
            $errors[] = 'Le prénom est requis.';
        }
        if ($data['last_name'] === '') {
            $errors[] = 'Le nom est requis.';
        }
        if ($data['username'] === '') {
            $errors[] = "Le nom d'utilisateur est requis.";
        }
        if ($data['email'] === '') {
            $errors[] = "L'adresse e-mail est requise.";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse e-mail n'est pas valide.";
        }
        if ($data['password'] === '') {
            $errors[] = 'Le mot de passe est requis.';
        }
        if ($data['password'] !== $data['password_confirm']) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors, 'data' => $data, 'user' => null];
        }

        try {
            $this->user->register(
                $data['username'],
                $data['email'],
                $data['password'],
                $data['first_name'],
                $data['last_name'],
                $data['paroisse'] !== '' ? $data['paroisse'] : null,
                $data['role_choral'] !== '' ? $data['role_choral'] : null
            );

            $user = $this->authService->login($data['username'], $data['password']);
            if ($user) {
                $this->authService->startAuthenticatedSession($user);
            }

            return ['success' => $user !== false, 'errors' => [], 'data' => $data, 'user' => $user ?: null];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'errors' => [$e->getMessage()],
                'data' => $data,
                'user' => null,
            ];
        }
    }
}

<?php
// config/csrf.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['csrf_tokens'])) {
    $_SESSION['csrf_tokens'] = [];
}

/**
 * Generate/return a CSRF token for a given scope.
 * @param string $scope e.g. 'contact_form', 'users_actions'
 * @param int $ttl Token time-to-live in seconds (default 1800 = 30 min)
 */
function csrf_token(string $scope, int $ttl = 1800): string {
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_tokens'][$scope] = [
        'value' => $token,
        'exp'   => time() + $ttl
    ];
    return $token;
}

/**
 * Verify and optionally consume a CSRF token for a scope.
 */
function csrf_verify(?string $token, string $scope): bool {
    if (!isset($_SESSION['csrf_tokens'][$scope])) return false;
    $stored = $_SESSION['csrf_tokens'][$scope];
    $valid  = is_array($stored)
           && isset($stored['value'], $stored['exp'])
           && hash_equals($stored['value'], (string)$token)
           && time() <= (int)$stored['exp'];

    // one-time token: consume after successful check
    if ($valid) {
        unset($_SESSION['csrf_tokens'][$scope]);
    }
    return $valid;
}

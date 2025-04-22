<?php
/**
 * Security related functions
 */

/**
 * Generate CSRF token
 * @return string The generated token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token The token to verify
 * @return bool Whether the token is valid
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set security headers
 */
function set_security_headers() {
    // Content Security Policy (CSP)
    $csp = "default-src 'self'; " .
           "script-src 'self' https://cdn.jsdelivr.net; " .
           "style-src 'self' https://cdn.jsdelivr.net; " .
           "img-src 'self' data: https:; " .
           "font-src 'self' https://cdn.jsdelivr.net; " .
           "connect-src 'self'; " .
           "frame-ancestors 'none'; " .
           "form-action 'self';";
    
    header("Content-Security-Policy: " . $csp);
    
    // Anti-Clickjacking
    header("X-Frame-Options: DENY");
    
    // Prevent MIME type sniffing
    header("X-Content-Type-Options: nosniff");
    
    // Enable XSS filtering
    header("X-XSS-Protection: 1; mode=block");
    
    // Control referrer information
    header("Referrer-Policy: strict-origin-when-cross-origin");
    
    // Prevent browsers from trying to guess the content type
    header("X-Download-Options: noopen");
    
    // Control browser features
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
    
    // Enable HSTS (HTTP Strict Transport Security)
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    
    // Prevent caching of sensitive pages
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");
} 

<?php
/**
 * Helper functions for output escaping and security
 */

/**
 * Safely escape and output content
 * @param mixed $content Content to be escaped and output
 * @param bool $nl2br Whether to convert newlines to <br> tags
 * @return string Escaped content
 */
function h($content) {
    return htmlspecialchars($content ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Safely escape and output content with newlines converted to <br>
 * @param mixed $content Content to be escaped and output
 * @return string Escaped content with newlines as <br>
 */
function h_br($content) {
    return nl2br(h($content));
}

/**
 * Display session flash message with proper escaping
 * @param string $type 'error', 'success', 'info', or 'warning'
 * @return void
 */
function display_flash_message($type) {
    if (isset($_SESSION[$type])) {
        $class = match($type) {
            'error' => 'danger',
            'success' => 'success',
            'info' => 'info',
            'warning' => 'warning',
            default => 'info'
        };
        echo '<div class="alert alert-' . $class . '">';
        echo h($_SESSION[$type]);
        echo '</div>';
        unset($_SESSION[$type]);
    }
} 
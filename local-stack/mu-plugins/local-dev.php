<?php
/**
 * Local dev only: allow WooCommerce webhooks to reach the n8n container
 * (WordPress normally blocks private/internal hosts).
 */
add_filter('http_request_host_is_external', function ($external, $host) {
    return in_array($host, ['n8n', 'localhost'], true) ? true : $external;
}, 10, 2);

// Deliver webhooks immediately instead of via Action Scheduler (local dev convenience).
add_filter('woocommerce_webhook_deliver_async', '__return_false');

// n8n listens on port 5678, which WordPress does not allow by default.
add_filter('http_allowed_safe_ports', function ($ports) {
    $ports[] = 5678;
    return $ports;
});

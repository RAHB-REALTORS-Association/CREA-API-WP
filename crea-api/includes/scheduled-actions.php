<?php

// scheduled-actions.php

add_action('crea_refresh_token', 'refresh_crea_token');
add_action('crea_refresh_office_data', 'refresh_office_data_cache');

function refresh_crea_token() {
    // Retrieve credentials from WordPress options
    $client_id = get_option('crea_client_id');
    $client_secret = get_option('crea_client_secret');

    if ($client_id && $client_secret) {
        $tokenManager = new TokenManager($client_id, $client_secret);
        $tokenManager->refreshTokenIfNeeded();
    }
}

function refresh_office_data_cache() {
    // Fetch and cache the office data
    $offices = fetch_office_data();
    $refresh_interval = get_option('crea_refresh_interval', 86400); // Default to 86400 seconds (1 day) if not set
    set_transient('crea_office_data', $offices, $refresh_interval);
}

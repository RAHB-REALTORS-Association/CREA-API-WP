// scheduled-actions.php

function refresh_crea_token() {
    // Retrieve credentials from WordPress options
    $client_id = get_option('crea_client_id');
    $client_secret = get_option('crea_client_secret');

    if ($client_id && $client_secret) {
        $tokenManager = new TokenManager($client_id, $client_secret);
        $tokenManager->fetchAccessToken();
    }
}

<?php

// office-functions.php

// Register the shortcode
add_shortcode('crea_office_roster', 'render_crea_office_roster');

function render_crea_office_roster() {
    // Fetch the office data
    $offices = fetch_office_data();

    ob_start(); // Start output buffering
    ?>
    <div class="crea-office-container">
        <table>
            <tr>
                <?php
                $col_count = 0;
                foreach ($offices as $office):
                    if ($col_count % 3 == 0 && $col_count > 0) {
                        echo '</tr><tr>';
                    }
                    $col_count++;
                ?>
                    <td>
                        <div class="crea-office-card">
                            <h3><?php echo esc_html($office['OfficeName']); ?></h3>
                            <p>Type: <?php echo esc_html($office['OfficeType']); ?></p>
                            <p>Status: <?php echo esc_html($office['OfficeStatus']); ?></p>
                            <p>Address: <?php echo esc_html($office['OfficeAddress1'] . ', ' . $office['OfficeCity'] . ', ' . $office['OfficeStateOrProvince']); ?></p>
                            <p>Phone: <?php echo esc_html($office['OfficePhone']); ?></p>
                            <p>Fax: <?php echo esc_html($office['OfficeFax']); ?></p>
                            <p>
                                <?php
                                if (!empty($office['OfficeSocialMedia'])) {
                                    echo 'Social Media: ';
                                    foreach ($office['OfficeSocialMedia'] as $socialMedia) {
                                        echo '<a href="' . esc_url($socialMedia['SocialMediaUrlOrId']) . '">' . esc_html($socialMedia['SocialMediaType']) . '</a> ';
                                    }
                                }
                                ?>
                            </p>
                        </div>
                    </td>
                <?php endforeach; ?>
            </tr>
        </table>
    </div>
    <?php
    return ob_get_clean(); // Return the buffered output
}

function fetch_office_data() {
    // Initialize TokenManager and get the current access token
    $tokenManager = new TokenManager(get_option('crea_client_id'), get_option('crea_client_secret'));
    $access_token = $tokenManager->refreshTokenIfNeeded();

    // Initialize API endpoint and arguments
    $api_endpoint = "https://boardapi.realtor.ca/Office?OfficeStatus=Active&top=500";
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
        )
    );

    // Make the API request
    $response = wp_remote_get($api_endpoint, $args);

    // Check if WP_Error
    if (is_wp_error($response)) {
        // Log this error to the PHP error log
        error_log('WP_Error encountered while fetching office data: ' . $response->get_error_message());
        return array();
    }

    // Check HTTP status code
    $status_code = wp_remote_retrieve_response_code($response);

    if ($status_code != 200) {
        switch ($status_code) {
            case 401:
                // Handle unauthorized access
                // Attempt to refresh the access token and retry
                $access_token = $tokenManager->fetchAccessToken();
                $args['headers']['Authorization'] = 'Bearer ' . $access_token;
                $response = wp_remote_get($api_endpoint, $args);
                
                if (is_wp_error($response)) {
                    // Log this error to the PHP error log
                    error_log('WP_Error encountered while fetching office data: ' . $response->get_error_message());
                    return array();
                } else if (wp_remote_retrieve_response_code($response) != 200) {
                    // Log this error to the PHP error log
                    error_log('Unexpected status code while fetching office data: ' . wp_remote_retrieve_response_code($response));
                    return array();
                }

                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);

                if (isset($data['data'])) {
                    return $data['data'];
                }

                break;
        }

        return array();
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Check if the 'data' key exists in the API response
    if (isset($data['data'])) {
        return $data['data'];
    }

    return array();
}

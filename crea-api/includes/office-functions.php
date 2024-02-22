<?php

// office-functions.php

// Register the shortcode
add_shortcode('crea_office_roster', 'render_crea_office_roster');

function render_crea_office_roster() {
    // Fetch the office data from the cache or API
    $offices = get_transient('crea_office_data');
    if (!$offices) {
        $offices = fetch_office_data();
        $refresh_interval = get_option('crea_refresh_interval', 86400); // Default to 86400 seconds (1 day) if not set
        set_transient('crea_office_data', $offices, $refresh_interval);
    }

    ob_start(); // Start output buffering
    ?>
    <div id="crea-filter-container">
        <input type="text" id="crea-filter-text" placeholder="Search">
        <div id="crea-alpha-buttons">
            <!-- Button for entries starting with a number -->
            <button class="crea-alpha-button" data-letter="#">#</button>
            <!-- Buttons for entries starting with a letter -->
            <?php foreach (range('A', 'Z') as $letter): ?>
                <button class="crea-alpha-button" data-letter="<?php echo $letter; ?>"><?php echo $letter; ?></button>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="crea-office-container">
        <?php foreach ($offices as $office): ?>
            <div class="crea-office-card">
                <h4><?php echo esc_html($office['OfficeName']); ?></h4>
                <p>Address: <?php echo esc_html($office['OfficeAddress1'] . ', ' . $office['OfficeCity'] . ', ' . $office['OfficeStateOrProvince']); ?></p>
                <p>Phone: <?php echo esc_html($office['OfficePhone']); ?></p>
                <?php
                if (!empty($office['OfficeFax'])) {
                    echo '<p>Fax: ' . esc_html($office['OfficeFax']) . '</p>';
                }
                if (!empty($office['OfficeEmail'])) {
                    echo '<p>Email: <a href="mailto:' . esc_attr($office['OfficeEmail']) . '">' . esc_html($office['OfficeEmail']) . '</a></p>';
                }
                ?>
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
        <?php endforeach; ?>
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
        // Sort by OfficeName
        usort($data['data'], function($a, $b) {
            return strcmp($a['OfficeName'], $b['OfficeName']);
        });
    
        // Filter by OfficeType "Firm"
        $filteredData = array_filter($data['data'], function($office) {
            return $office['OfficeType'] === "Firm";
        });
    
        return array_values($filteredData);
    }
}

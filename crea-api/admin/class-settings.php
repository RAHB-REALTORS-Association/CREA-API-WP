<?php

// class-settings.php

class CREA_Settings_Page {
    public function __construct() {
        add_action('admin_menu', array($this, 'create_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function create_menu() {
        add_menu_page('CREA Settings', 'CREA API', 'manage_options', 'crea_settings', array($this, 'settings_page'));
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h2>CREA API Settings</h2>
            <form method="post" action="options.php">
                <?php settings_fields('crea-settings-group'); ?>
                <?php do_settings_sections('crea_settings'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Client ID</th>
                        <td><input type="text" name="crea_client_id" value="<?php echo esc_attr(get_option('crea_client_id')); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Client Secret</th>
                        <td><input type="password" name="crea_client_secret" value="<?php echo esc_attr(get_option('crea_client_secret')); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Refresh Interval (seconds)</th>
                        <td><input type="number" name="crea_refresh_interval" value="<?php echo esc_attr(get_option('crea_refresh_interval', 86400)); ?>" min="300" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        register_setting('crea-settings-group', 'crea_client_id');
        register_setting('crea-settings-group', 'crea_client_secret');
        register_setting('crea-settings-group', 'crea_refresh_interval', 'validate_refresh_interval');
    }
}

function validate_refresh_interval($input) {
    $validated = intval($input);
    if ($validated < 300) {
        $validated = 300;
    }
    return $validated;
}

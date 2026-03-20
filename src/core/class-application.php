<?php

namespace Swift_Login\Core;

defined('ABSPATH') || exit;

class Application
{
    public function boot(): void
    {
        register_activation_hook(SWIFT_LOGIN_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(SWIFT_LOGIN_PLUGIN_FILE, [$this, 'deactivate']);

        add_filter('plugin_action_links', [$this, 'add_settings_link'], 10, 2);
        add_action('activated_plugin', [$this, 'redirect_to_settings']);
        add_action('admin_init', [$this, 'maybe_redirect_after_activation']);
        add_action('init', [$this, 'load_textdomain']);

        $this->load_includes();
        $this->load_hooks();
        $this->load_ajax();
    }

    public function activate(): void
    {
        $this->run_migrations();
        $this->set_default_options();
    }

    public function deactivate(): void
    {
        // Clean up scheduled events if any
    }

    public function load_textdomain(): void
    {
        load_plugin_textdomain('swift-login', false, SWIFT_LOGIN_LANGUAGES_DIR);
    }

    public function load_includes(): void
    {
        $files = glob(SWIFT_LOGIN_INCLUDES_DIR . '/class-*.php');
        if ($files) {
            foreach ($files as $file) {
                require_once $file;
            }
        }
    }

    public function load_hooks(): void
    {
        $files = glob(SWIFT_LOGIN_HOOKS_DIR . '/class-*.php');
        if ($files) {
            foreach ($files as $file) {
                require_once $file;
            }
        }
    }

    public function load_ajax(): void
    {
        $files = glob(SWIFT_LOGIN_AJAX_DIR . '/class-*.php');
        if ($files) {
            foreach ($files as $file) {
                require_once $file;
            }
        }
    }

    public function add_settings_link(array $links, string $plugin): array
    {
        if ($plugin === SWIFT_LOGIN_PLUGIN_BASENAME) {
            $url     = admin_url('options-general.php?page=swift-login');
            $links[] = sprintf('<a href="%s">%s</a>', esc_url($url), esc_html__('Settings', 'swift-login'));
        }
        return $links;
    }

    public function redirect_to_settings(string $plugin): void
    {
        if ($plugin === SWIFT_LOGIN_PLUGIN_BASENAME) {
            set_transient('swift_login_activation_redirect', true, 30);
        }
    }

    public function maybe_redirect_after_activation(): void
    {
        if (!is_admin() || wp_doing_ajax()) {
            return;
        }
        if (get_transient('swift_login_activation_redirect')) {
            delete_transient('swift_login_activation_redirect');
            wp_safe_redirect(admin_url('options-general.php?page=swift-login'));
            exit;
        }
    }

    private function run_migrations(): void
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( "CREATE TABLE {$wpdb->prefix}swift_login_passkeys (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  credential_id VARCHAR(512) NOT NULL,
  public_key TEXT NOT NULL,
  sign_counter BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(255) DEFAULT '',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_used_at DATETIME DEFAULT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY credential_id (credential_id(191)),
  KEY user_id (user_id)
) $charset_collate;" );

        dbDelta( "CREATE TABLE {$wpdb->prefix}swift_login_challenges (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  challenge VARCHAR(512) NOT NULL,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  type VARCHAR(10) NOT NULL DEFAULT 'login',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  KEY challenge (challenge(191))
) $charset_collate;" );

        dbDelta( "CREATE TABLE {$wpdb->prefix}swift_login_social (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  social_type VARCHAR(50) NOT NULL,
  social_uid VARCHAR(255) NOT NULL,
  access_token VARCHAR(512) DEFAULT '',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  UNIQUE KEY social_unique (social_type, social_uid(191)),
  KEY user_id (user_id)
) $charset_collate;" );
    }

    private function set_default_options(): void
    {
        $defaults = [
            // Passkey
            'passkey_enabled'              => true,
            'passkey_user_verification'    => 'preferred',
            'passkey_timeout'              => 60,
            // Login page customization
            'custom_login_enabled'         => true,
            'login_logo_url'               => '',
            'login_background_color'       => '#f0f0f1',
            'login_card_color'             => '#ffffff',
            'login_button_color'           => '#2271b1',
            'login_button_text_color'      => '#ffffff',
            'login_custom_css'             => '',
            // Login security
            'disable_password_login'       => false,
            // Social login
            'social_login_enabled'         => false,
            'social_appid'                 => '',
            'social_appkey'                => '',
            'social_platforms'             => ['qq', 'wx', 'google', 'github'],
            'social_auto_register'         => true,
            'social_redirect_uri'          => '',
            'social_api_base'              => '',
            'social_button_style'          => 'icon-text',
        ];

        $existing = get_option('swift_login_options', []);
        if (empty($existing)) {
            update_option('swift_login_options', $defaults);
        } else {
            // Only add missing keys
            $updated = false;
            foreach ($defaults as $key => $value) {
                if (!array_key_exists($key, $existing)) {
                    $existing[$key] = $value;
                    $updated = true;
                }
            }
            if ($updated) {
                update_option('swift_login_options', $existing);
            }
        }
    }
}

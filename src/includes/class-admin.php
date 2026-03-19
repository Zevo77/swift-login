<?php

namespace Swift_Login\Includes;

use Swift_Login\Core\Helper;

defined('ABSPATH') || exit;

class Admin
{
    public function __construct()
    {
        if (!is_admin()) {
            return;
        }

        add_action('admin_menu',            [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_swift_login_save_settings', [$this, 'save_settings']);
        add_action('wp_ajax_swift_login_get_settings',  [$this, 'get_settings']);
    }

    public function register_menu(): void
    {
        add_options_page(
            __('Swift Login 设置', 'swift-login'),
            __('Swift Login', 'swift-login'),
            'manage_options',
            'swift-login',
            [$this, 'render_page']
        );
    }

    public function enqueue_assets(string $hook): void
    {
        if ($hook !== 'settings_page_swift-login') {
            return;
        }

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style(
            'swift-login-admin',
            SWIFT_LOGIN_ASSETS_ADMIN_URL . '/css/admin.css',
            [],
            SWIFT_LOGIN_VERSION
        );
        wp_enqueue_script(
            'swift-login-admin',
            SWIFT_LOGIN_ASSETS_ADMIN_URL . '/js/admin.js',
            ['jquery', 'wp-color-picker'],
            SWIFT_LOGIN_VERSION,
            true
        );
        wp_localize_script('swift-login-admin', 'SwiftLoginAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce(SWIFT_LOGIN_NONCE),
            'strings' => [
                'saved'      => __('设置已保存', 'swift-login'),
                'saveError'  => __('保存失败，请重试', 'swift-login'),
                'saving'     => __('保存中…', 'swift-login'),
            ],
        ]);
    }

    public function render_page(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('权限不足', 'swift-login'));
        }
        require_once SWIFT_LOGIN_VIEWS_ADMIN_DIR . '/settings.php';
    }

    public function save_settings(): void
    {
        check_ajax_referer(SWIFT_LOGIN_NONCE, 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('权限不足', 'swift-login')], 403);
        }

        $raw = isset($_POST['settings']) ? json_decode(stripslashes($_POST['settings']), true) : null;
        if (!is_array($raw)) {
            wp_send_json_error(['message' => __('数据格式错误', 'swift-login')], 400);
        }

        $allowed = [
            'passkey_enabled'           => 'bool',
            'passkey_user_verification' => 'string',
            'passkey_timeout'           => 'int',
            'custom_login_enabled'      => 'bool',
            'login_logo_url'            => 'url',
            'login_background_color'    => 'color',
            'login_card_color'          => 'color',
            'login_button_color'        => 'color',
            'login_button_text_color'   => 'color',
            'login_custom_css'          => 'textarea',
            'social_login_enabled'      => 'bool',
            'social_appid'              => 'string',
            'social_appkey'             => 'string',
            'social_platforms'          => 'array',
            'social_auto_register'      => 'bool',
            'social_redirect_uri'       => 'url',
            'social_api_base'           => 'url',
            'disable_password_login'    => 'bool',
        ];

        $sanitized = [];
        foreach ($allowed as $key => $type) {
            if (!array_key_exists($key, $raw)) {
                continue;
            }
            $val = $raw[$key];
            switch ($type) {
                case 'bool':
                    $sanitized[$key] = (bool) $val;
                    break;
                case 'int':
                    $sanitized[$key] = (int) $val;
                    break;
                case 'url':
                    $sanitized[$key] = esc_url_raw($val);
                    break;
                case 'color':
                    $sanitized[$key] = sanitize_hex_color($val) ?: '';
                    break;
                case 'textarea':
                    $sanitized[$key] = wp_strip_all_tags($val);
                    break;
                case 'array':
                    $sanitized[$key] = is_array($val) ? array_map('sanitize_key', $val) : [];
                    break;
                default:
                    $sanitized[$key] = sanitize_text_field($val);
            }
        }

        Helper::update_options($sanitized);
        wp_send_json_success(['message' => __('设置已保存', 'swift-login')]);
    }

    public function get_settings(): void
    {
        check_ajax_referer(SWIFT_LOGIN_NONCE, 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('权限不足', 'swift-login')], 403);
        }

        wp_send_json_success(Helper::get_options());
    }
}

new Admin();

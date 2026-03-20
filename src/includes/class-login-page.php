<?php

namespace Swift_Login\Includes;

use Swift_Login\Core\Helper;

defined('ABSPATH') || exit;

class Login_Page
{
    public function __construct()
    {
        add_action('login_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_enqueue_scripts',    [$this, 'enqueue_assets']);
        add_action('login_head',            [$this, 'inject_custom_styles']);
        add_action('login_headerurl',       [$this, 'login_logo_url']);
        add_action('login_headertext',      [$this, 'login_logo_title']);

        // Show errors from social callback
        add_action('login_messages', [$this, 'show_error_message']);

        // Remove language switcher
        add_filter('login_display_language_dropdown', '__return_false');

        // Shortcodes for theme integration
        add_shortcode('swift_passkey_button', [$this, 'shortcode_passkey_button']);
        add_shortcode('swift_social_buttons', [$this, 'shortcode_social_buttons']);

        // Social login buttons (priority 20, after passkey at 10)
        if (Helper::is_social_login_enabled()) {
            add_action('login_form', [$this, 'render_social_buttons'], 20);
        }

        // Disable password login
        if (Helper::is_password_login_disabled()) {
            add_filter('authenticate', [$this, 'block_password_login'], 30, 3);
            add_action('login_head', [$this, 'hide_password_login_form']);
        }
    }

    public function hide_password_login_form(): void
    {
        echo '<style>
#loginform > p,
#loginform .forgetmenot,
#loginform .submit,
#login .login-username,
#login .login-password,
#login .login-remember,
#login input[type="text"],
#login input[type="password"],
#login label[for="user_login"],
#login label[for="user_pass"],
#login .login-submit,
#login #nav,
#login .swift-login-divider,
.password-input-wrapper,
.wp-pwd,
#wp-submit { display: none !important; }
</style>';
    }

    public function block_password_login($user, string $username, string $password)
    {
        if (empty($password)) {
            return $user;
        }
        return new \WP_Error(
            'password_login_disabled',
            __('Username and password login is disabled. Please use Passkey or social login.', 'swift-login')
        );
    }

    public function enqueue_assets(): void
    {
        wp_enqueue_style(
            'swift-login-login',
            SWIFT_LOGIN_ASSETS_FRONTEND_URL . '/css/login.css',
            [],
            SWIFT_LOGIN_VERSION
        );

        if (Helper::is_social_login_enabled()) {
            wp_enqueue_script(
                'swift-login-social',
                SWIFT_LOGIN_ASSETS_FRONTEND_URL . '/js/social.js',
                [],
                SWIFT_LOGIN_VERSION,
                true
            );
        }
    }

    public function inject_custom_styles(): void
    {
        if (!Helper::is_custom_login_enabled()) {
            return;
        }

        $bg_color     = sanitize_hex_color(Helper::get_option('login_background_color', '#f0f0f1')) ?: '#f0f0f1';
        $card_color   = sanitize_hex_color(Helper::get_option('login_card_color', '#ffffff')) ?: '#ffffff';
        $btn_color    = sanitize_hex_color(Helper::get_option('login_button_color', '#2271b1')) ?: '#2271b1';
        $btn_txt      = sanitize_hex_color(Helper::get_option('login_button_text_color', '#ffffff')) ?: '#ffffff';
        $logo_url     = esc_url(Helper::get_option('login_logo_url', ''));
        $custom_css   = wp_strip_all_tags(Helper::get_option('login_custom_css', ''));

        echo '<style id="swift-login-custom-styles">';
        echo 'body.login { background-color: ' . esc_attr($bg_color) . '; }';
        echo '#login { background: ' . esc_attr($card_color) . '; border-radius: 8px; padding: 26px 28px; box-shadow: 0 2px 16px rgba(0,0,0,.1); }';
        echo '.login form { box-shadow: none; border: none; background: transparent; padding: 0; margin: 0; }';
        echo '.wp-core-ui .button-primary { background: ' . esc_attr($btn_color) . '; border-color: ' . esc_attr($btn_color) . '; color: ' . esc_attr($btn_txt) . '; border-radius: 6px; }';
        echo '.wp-core-ui .button-primary:hover { opacity:.88; }';
        if ($logo_url) {
            echo 'h1 a { background-image: url(' . esc_url($logo_url) . ') !important; background-size: contain !important; width: 100% !important; height: 80px !important; }';
        }
        if ($custom_css) {
            // Custom CSS is user-entered; we already stripped tags above
            echo $custom_css; // phpcs:ignore WordPress.Security.EscapeOutput
        }
        echo '</style>';
    }

    public function login_logo_url(): string
    {
        return home_url('/');
    }

    public function login_logo_title(): string
    {
        return get_bloginfo('name');
    }

    public function show_error_message(string $messages): string
    {
        if (!empty($_GET['swift_login_error'])) {
            $error = sanitize_text_field(urldecode($_GET['swift_login_error']));
            $messages .= '<p class="message" style="border-left-color:#d63638;">' . esc_html($error) . '</p>';
        }
        return $messages;
    }

    public function render_social_buttons(): void
    {
        $platforms = Helper::get_social_platforms();
        if (empty($platforms)) {
            return;
        }

        $all_platforms = Helper::all_social_platforms();
        ?>
        <div class="swift-login-social-wrap">
            <div class="swift-login-divider"><span><?php esc_html_e('Social Login', 'swift-login'); ?></span></div>
            <div class="swift-login-social-buttons swift-social-style-<?php echo esc_attr(Helper::get_social_button_style()); ?>">
                <?php foreach ($platforms as $type) :
                    $label = $all_platforms[$type] ?? $type;
                ?>
                <button type="button"
                        class="swift-btn swift-social-btn swift-social-<?php echo esc_attr($type); ?>"
                        data-type="<?php echo esc_attr($type); ?>"
                        data-nonce="<?php echo esc_attr(wp_create_nonce(SWIFT_LOGIN_NONCE)); ?>">
                    <img src="<?php $icon_map = ['twitter' => 'x']; echo esc_url(SWIFT_LOGIN_ASSETS_FRONTEND_URL . '/img/social/' . ($icon_map[$type] ?? $type) . '.png'); ?>"
                         alt="<?php echo esc_attr($label); ?>"
                         onerror="this.style.display='none'">
                    <span><?php echo esc_html($label); ?></span>
                </button>
                <?php endforeach; ?>
            </div>
            <div id="swift-social-loading" style="display:none;text-align:center;padding:8px;">
                <span><?php esc_html_e('Redirecting, please wait…', 'swift-login'); ?></span>
            </div>
        </div>
        <?php
    }
}

new Login_Page();

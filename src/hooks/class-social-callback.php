<?php

namespace Swift_Login\Hooks;

use Swift_Login\Core\Helper;
use Swift_Login\Models\Social_Model;

defined('ABSPATH') || exit;

class Social_Callback
{
    public function __construct()
    {
        add_action('init', [$this, 'handle_callback']);
    }

    public function handle_callback(): void
    {
        if (!isset($_GET['swift_login_social_callback'])) {
            return;
        }

        if (!Helper::is_social_login_enabled()) {
            return;
        }

        $code = sanitize_text_field($_GET['code'] ?? '');
        $type = sanitize_key($_GET['type'] ?? '');

        if (empty($code) || empty($type)) {
            $this->redirect_with_error(__('Missing callback parameters.', 'swift-login'));
            return;
        }

        $appid  = Helper::get_option('social_appid', '');
        $appkey = Helper::get_option('social_appkey', '');

        if (empty($appid) || empty($appkey)) {
            $this->redirect_with_error(__('Social login configuration error.', 'swift-login'));
            return;
        }

        // Exchange code for user info
        $api_base = Helper::get_social_api_base();
        $api_url  = "{$api_base}?act=callback&appid={$appid}&appkey={$appkey}&type={$type}&code={$code}";
        $response = wp_remote_get($api_url, ['timeout' => 15, 'sslverify' => true]);

        if (is_wp_error($response)) {
            $this->redirect_with_error(__('Could not connect to the login service.', 'swift-login'));
            return;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!isset($body['code']) || $body['code'] !== 0) {
            $msg = $body['msg'] ?? __('Failed to retrieve user information.', 'swift-login');
            $this->redirect_with_error($msg);
            return;
        }

        $social_uid   = sanitize_text_field($body['social_uid'] ?? '');
        $access_token = sanitize_text_field($body['access_token'] ?? '');
        $nickname     = sanitize_text_field($body['nickname'] ?? '');
        $avatar       = esc_url_raw($body['faceimg'] ?? '');

        if (empty($social_uid)) {
            $this->redirect_with_error(__('Could not retrieve the social account identifier.', 'swift-login'));
            return;
        }

        // If this is a bind request from a logged-in user
        if (is_user_logged_in() && !empty($_GET['swift_bind_social'])) {
            $current_user_id = get_current_user_id();
            Social_Model::bind($current_user_id, $type, $social_uid, $access_token);
            wp_safe_redirect(add_query_arg('swift_bind_success', '1', get_edit_profile_url($current_user_id)));
            exit;
        }

        // Find existing bound user
        $user_id = Social_Model::find_user_by_social($type, $social_uid);

        if ($user_id) {
            // Already bound — log in
            $user = get_user_by('id', $user_id);
            if (!$user) {
                $this->redirect_with_error(__('User not found.', 'swift-login'));
                return;
            }
            Social_Model::bind($user_id, $type, $social_uid, $access_token);
            $this->login_user($user);
            return;
        }

        // If auto-register is enabled, create a new user
        if (Helper::get_option('social_auto_register', true)) {
            $user_id = $this->create_user($nickname, $type, $social_uid, $avatar);
            if (is_wp_error($user_id)) {
                $this->redirect_with_error($user_id->get_error_message());
                return;
            }
            Social_Model::bind($user_id, $type, $social_uid, $access_token);
            $user = get_user_by('id', $user_id);
            $this->login_user($user);
            return;
        }

        // Not registered and auto-register off — prompt user to link account
        // Store social info in transient and redirect to link page
        $transient_key = 'swift_social_pending_' . wp_generate_password(12, false);
        set_transient($transient_key, [
            'type'         => $type,
            'social_uid'   => $social_uid,
            'access_token' => $access_token,
            'nickname'     => $nickname,
            'avatar'       => $avatar,
        ], 600);

        wp_safe_redirect(add_query_arg([
            'swift_link_social' => $transient_key,
        ], wp_login_url()));
        exit;
    }

    private function create_user(string $nickname, string $type, string $social_uid, string $avatar)
    {
        $username = $type . '_' . substr($social_uid, 0, 12);
        $username = sanitize_user($username, true);

        // Ensure unique username
        $base = $username;
        $i    = 1;
        while (username_exists($username)) {
            $username = $base . '_' . $i++;
        }

        $email    = $username . '@swift.social.placeholder';
        $password = wp_generate_password(24);

        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        // Set display name
        wp_update_user([
            'ID'           => $user_id,
            'display_name' => $nickname ?: $username,
            'nickname'     => $nickname ?: $username,
        ]);

        // Save avatar URL as meta
        if ($avatar) {
            update_user_meta($user_id, 'swift_login_social_avatar', $avatar);
        }

        return $user_id;
    }

    private function login_user(\WP_User $user): void
    {
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        do_action('wp_login', $user->user_login, $user);

        $redirect = apply_filters('login_redirect', admin_url(), '', $user);
        wp_safe_redirect($redirect);
        exit;
    }

    private function redirect_with_error(string $message): void
    {
        wp_safe_redirect(add_query_arg(
            ['swift_login_error' => urlencode($message)],
            wp_login_url()
        ));
        exit;
    }
}

new Social_Callback();

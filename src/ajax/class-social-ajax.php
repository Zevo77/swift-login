<?php

namespace Swift_Login\Ajax;

use Swift_Login\Core\Helper;
use Swift_Login\Models\Social_Model;

defined('ABSPATH') || exit;

class Social_Ajax
{
    public function __construct()
    {
        // Initiate social login redirect
        add_action('wp_ajax_nopriv_swift_login_social_init', [$this, 'init_social_login']);
        add_action('wp_ajax_swift_login_social_init',        [$this, 'init_social_login']);

        // Unbind social account
        add_action('wp_ajax_swift_login_social_unbind', [$this, 'unbind_social']);

        // Bind social account (logged-in user)
        add_action('wp_ajax_swift_login_social_bind_init', [$this, 'init_bind_social']);
    }

    public function init_bind_social(): void
    {
        check_ajax_referer(SWIFT_LOGIN_NONCE, 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('请先登录', 'swift-login')], 401);
        }

        if (!Helper::is_social_login_enabled()) {
            wp_send_json_error(['message' => __('社会化登录未启用', 'swift-login')], 403);
        }

        $type = sanitize_key($_POST['type'] ?? '');
        if (empty($type)) {
            wp_send_json_error(['message' => __('参数错误', 'swift-login')], 400);
        }

        $appid    = Helper::get_option('social_appid', '');
        $appkey   = Helper::get_option('social_appkey', '');
        if (empty($appid) || empty($appkey)) {
            wp_send_json_error(['message' => __('社会化登录尚未配置，请联系管理员', 'swift-login')], 500);
        }

        $callback     = add_query_arg('swift_bind_social', '1', Helper::get_social_callback_url());
        $redirect_uri = urlencode($callback);
        $api_base     = Helper::get_social_api_base();
        $api_url      = "{$api_base}?act=login&appid={$appid}&appkey={$appkey}&type={$type}&redirect_uri={$redirect_uri}";
        $response     = wp_remote_get($api_url, ['timeout' => 15, 'sslverify' => true]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => __('无法连接到登录服务', 'swift-login')], 502);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($body['code']) || $body['code'] !== 0) {
            $msg = $body['msg'] ?? __('获取绑定地址失败', 'swift-login');
            wp_send_json_error(['message' => $msg], 500);
        }

        wp_send_json_success(['url' => $body['url'], 'type' => $type]);
    }

    public function unbind_social(): void
    {
        check_ajax_referer(SWIFT_LOGIN_NONCE, 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('请先登录', 'swift-login')], 401);
        }

        $type = sanitize_key($_POST['type'] ?? '');
        if (empty($type)) {
            wp_send_json_error(['message' => __('参数错误', 'swift-login')], 400);
        }

        $user_id = get_current_user_id();
        Social_Model::unbind($user_id, $type);
        wp_send_json_success(['message' => __('解绑成功', 'swift-login')]);
    }

    public function init_social_login(): void
    {
        if (!Helper::is_social_login_enabled()) {
            wp_send_json_error(['message' => __('社会化登录未启用', 'swift-login')], 403);
        }

        check_ajax_referer(SWIFT_LOGIN_NONCE, 'nonce');

        $type = sanitize_key($_POST['type'] ?? '');
        if (empty($type)) {
            wp_send_json_error(['message' => __('登录平台参数缺失', 'swift-login')], 400);
        }

        $allowed = Helper::get_social_platforms();
        if (!in_array($type, $allowed, true)) {
            wp_send_json_error(['message' => __('不支持的登录平台', 'swift-login')], 400);
        }

        $appid        = Helper::get_option('social_appid', '');
        $appkey       = Helper::get_option('social_appkey', '');
        $redirect_uri = urlencode(Helper::get_social_callback_url());

        if (empty($appid) || empty($appkey)) {
            wp_send_json_error(['message' => __('社会化登录尚未配置，请联系管理员', 'swift-login')], 500);
        }

        $api_base = Helper::get_social_api_base();
        $api_url  = "{$api_base}?act=login&appid={$appid}&appkey={$appkey}&type={$type}&redirect_uri={$redirect_uri}";
        $response = wp_remote_get($api_url, ['timeout' => 15, 'sslverify' => true]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => __('无法连接到登录服务', 'swift-login')], 502);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!isset($body['code']) || $body['code'] !== 0) {
            $msg = $body['msg'] ?? __('获取登录地址失败', 'swift-login');
            wp_send_json_error(['message' => $msg], 500);
        }

        wp_send_json_success([
            'url'    => $body['url'],
            'qrcode' => $body['qrcode'] ?? null,
            'type'   => $type,
        ]);
    }
}

new Social_Ajax();

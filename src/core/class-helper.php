<?php

namespace Swift_Login\Core;

defined('ABSPATH') || exit;

class Helper
{
    public static function get_options(): array
    {
        return (array) get_option('swift_login_options', []);
    }

    public static function get_option(string $key, $default = null)
    {
        $options = self::get_options();
        return isset($options[$key]) ? $options[$key] : $default;
    }

    public static function update_options(array $data): bool
    {
        $options = self::get_options();
        $options = array_merge($options, $data);
        return update_option('swift_login_options', $options);
    }

    public static function is_passkey_enabled(): bool
    {
        return (bool) self::get_option('passkey_enabled', true);
    }

    public static function is_password_login_disabled(): bool
    {
        return (bool) self::get_option('disable_password_login', false);
    }

    public static function is_custom_login_enabled(): bool
    {
        return (bool) self::get_option('custom_login_enabled', true);
    }

    public static function is_social_login_enabled(): bool
    {
        return (bool) self::get_option('social_login_enabled', false);
    }

    public static function get_social_platforms(): array
    {
        $platforms = self::get_option('social_platforms', ['qq', 'wx', 'google', 'github']);
        return is_array($platforms) ? $platforms : [];
    }

    public static function get_rp_id(): string
    {
        return parse_url(home_url(), PHP_URL_HOST);
    }

    public static function get_rp_name(): string
    {
        return get_bloginfo('name');
    }

    public static function get_social_callback_url(): string
    {
        $custom = self::get_option('social_redirect_uri', '');
        if (!empty($custom)) {
            return $custom;
        }
        return add_query_arg('swift_login_social_callback', '1', wp_login_url());
    }

    public static function get_social_api_base(): string
    {
        $custom = self::get_option('social_api_base', '');
        if (!empty($custom)) {
            return rtrim($custom, '/');
        }
        return 'https://u.zevost.com/connect.php';
    }

    public static function all_social_platforms(): array
    {
        return [
            'qq'        => 'QQ',
            'wx'        => '微信',
            'alipay'    => '支付宝',
            'sina'      => '微博',
            'baidu'     => '百度',
            'douyin'    => '抖音',
            'huawei'    => '华为',
            'xiaomi'    => '小米',
            'google'    => 'Google',
            'microsoft' => 'Microsoft',
            'twitter'   => 'Twitter',
            'dingtalk'  => '钉钉',
            'gitee'     => 'Gitee',
            'github'    => 'GitHub',
        ];
    }
}

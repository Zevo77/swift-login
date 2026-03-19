<?php

namespace Swift_Login\Models;

defined('ABSPATH') || exit;

class Social_Model
{
    private static function table(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'swift_login_social';
    }

    public static function find_user_by_social(string $type, string $social_uid): ?int
    {
        global $wpdb;
        $table = self::table();
        $user_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT user_id FROM $table WHERE social_type = %s AND social_uid = %s LIMIT 1",
                $type,
                $social_uid
            )
        );
        return $user_id ? (int) $user_id : null;
    }

    public static function bind(int $user_id, string $type, string $social_uid, string $access_token = ''): void
    {
        global $wpdb;
        $existing = self::find_user_by_social($type, $social_uid);
        if ($existing) {
            $wpdb->update(
                self::table(),
                ['access_token' => $access_token, 'user_id' => $user_id],
                ['social_type' => $type, 'social_uid' => $social_uid],
                ['%s', '%d'],
                ['%s', '%s']
            );
        } else {
            $wpdb->insert(self::table(), [
                'user_id'      => $user_id,
                'social_type'  => $type,
                'social_uid'   => $social_uid,
                'access_token' => $access_token,
                'created_at'   => current_time('mysql'),
            ], ['%d', '%s', '%s', '%s', '%s']);
        }
    }

    public static function get_bindings_by_user(int $user_id): array
    {
        global $wpdb;
        $table = self::table();
        return (array) $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE user_id = %d", $user_id),
            ARRAY_A
        );
    }

    public static function unbind(int $user_id, string $type): void
    {
        global $wpdb;
        $wpdb->delete(
            self::table(),
            ['user_id' => $user_id, 'social_type' => $type],
            ['%d', '%s']
        );
    }
}

<?php

namespace Swift_Login\Models;

defined('ABSPATH') || exit;

class Challenge_Model
{
    private static function table(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'swift_login_challenges';
    }

    /**
     * @param string   $challenge  Raw binary challenge bytes
     * @param string   $type       'register' or 'login'
     * @param int|null $user_id
     */
    public static function create(string $challenge, string $type, ?int $user_id = null): int
    {
        global $wpdb;
        $wpdb->insert(
            self::table(),
            [
                'challenge'  => base64_encode($challenge),
                'type'       => $type,
                'user_id'    => $user_id ? $user_id : null,
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', $user_id ? '%d' : '%s', '%s']
        );
        return (int) $wpdb->insert_id;
    }

    /**
     * @param string $challenge  Raw binary challenge bytes
     */
    public static function find(string $challenge, string $type): ?array
    {
        global $wpdb;
        $table = self::table();
        $row   = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE challenge = %s AND type = %s ORDER BY created_at DESC LIMIT 1",
                base64_encode($challenge),
                $type
            ),
            ARRAY_A
        );
        return $row ?: null;
    }

    public static function delete(int $id): void
    {
        global $wpdb;
        $wpdb->delete(self::table(), ['id' => $id], ['%d']);
    }

    public static function cleanup(int $seconds = 300): void
    {
        global $wpdb;
        $table = self::table();
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table WHERE created_at < %s",
                date('Y-m-d H:i:s', time() - $seconds)
            )
        );
    }
}

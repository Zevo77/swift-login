<?php

namespace Swift_Login\Models;

defined('ABSPATH') || exit;

class Passkey_Model
{
    private static function table(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'swift_login_passkeys';
    }

    public static function create(array $data): int
    {
        global $wpdb;
        $wpdb->insert(self::table(), [
            'user_id'       => (int) $data['user_id'],
            'credential_id' => $data['credential_id'],
            'public_key'    => $data['public_key'],
            'sign_counter'  => (int) ($data['sign_counter'] ?? 0),
            'name'          => sanitize_text_field($data['name'] ?? ''),
            'created_at'    => current_time('mysql'),
        ], ['%d', '%s', '%s', '%d', '%s', '%s']);
        return (int) $wpdb->insert_id;
    }

    public static function find_by_credential_id(string $credential_id): ?array
    {
        global $wpdb;
        $table = self::table();
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE credential_id = %s LIMIT 1", $credential_id),
            ARRAY_A
        );
        return $row ?: null;
    }

    public static function get_by_user(int $user_id): array
    {
        global $wpdb;
        $table = self::table();
        return (array) $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC", $user_id),
            ARRAY_A
        );
    }

    public static function update_counter(int $id, int $counter): void
    {
        global $wpdb;
        $wpdb->update(
            self::table(),
            ['sign_counter' => $counter, 'last_used_at' => current_time('mysql')],
            ['id' => $id],
            ['%d', '%s'],
            ['%d']
        );
    }

    public static function delete(int $id, int $user_id): bool
    {
        global $wpdb;
        return (bool) $wpdb->delete(
            self::table(),
            ['id' => $id, 'user_id' => $user_id],
            ['%d', '%d']
        );
    }

    public static function count_by_user(int $user_id): int
    {
        global $wpdb;
        $table = self::table();
        return (int) $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE user_id = %d", $user_id)
        );
    }
}

<?php

namespace Swift_Login\Ajax;

use Swift_Login\Core\Helper;
use Swift_Login\Models\Passkey_Model;
use Swift_Login\Models\Challenge_Model;
use Swift_Login\Packages\Web_Authn\Web_Authn;
use Swift_Login\Packages\Web_Authn\Binary\Byte_Buffer;
use Swift_Login\Packages\Web_Authn\Web_Authn_Exception;

defined('ABSPATH') || exit;

// Load the WebAuthn package
require_once SWIFT_LOGIN_SRC_DIR . '/packages/web-authn/web-authn-exception.php';
require_once SWIFT_LOGIN_SRC_DIR . '/packages/web-authn/binary/byte-buffer.php';
require_once SWIFT_LOGIN_SRC_DIR . '/packages/web-authn/cbor/cbor-decoder.php';
require_once SWIFT_LOGIN_SRC_DIR . '/packages/web-authn/attestation/format/format-base.php';
require_once SWIFT_LOGIN_SRC_DIR . '/packages/web-authn/attestation/format/none.php';
require_once SWIFT_LOGIN_SRC_DIR . '/packages/web-authn/attestation/format/packed.php';
require_once SWIFT_LOGIN_SRC_DIR . '/packages/web-authn/attestation/format/u2f.php';
require_once SWIFT_LOGIN_SRC_DIR . '/packages/web-authn/attestation/format/android-key.php';
require_once SWIFT_LOGIN_SRC_DIR . '/packages/web-authn/attestation/format/android-safety-net.php';
require_once SWIFT_LOGIN_SRC_DIR . '/packages/web-authn/attestation/format/apple.php';
require_once SWIFT_LOGIN_SRC_DIR . '/packages/web-authn/attestation/format/tpm.php';
require_once SWIFT_LOGIN_SRC_DIR . '/packages/web-authn/attestation/authenticator-data.php';
require_once SWIFT_LOGIN_SRC_DIR . '/packages/web-authn/attestation/attestation-object.php';
require_once SWIFT_LOGIN_SRC_DIR . '/packages/web-authn/web-authn.php';

class Passkey_Ajax
{
    public function __construct()
    {
        add_action('wp_ajax_swift_login_passkey_register_options',        [$this, 'register_options']);
        add_action('wp_ajax_swift_login_passkey_register_verify',         [$this, 'register_verify']);

        add_action('wp_ajax_nopriv_swift_login_passkey_login_options',    [$this, 'login_options']);
        add_action('wp_ajax_swift_login_passkey_login_options',           [$this, 'login_options']);
        add_action('wp_ajax_nopriv_swift_login_passkey_login_verify',     [$this, 'login_verify']);
        add_action('wp_ajax_swift_login_passkey_login_verify',            [$this, 'login_verify']);

        add_action('wp_ajax_swift_login_passkey_delete',                  [$this, 'delete_passkey']);
    }

    private function verify_nonce(): void
    {
        if (!check_ajax_referer(SWIFT_LOGIN_NONCE, 'nonce', false)) {
            wp_send_json_error(['message' => __('安全验证失败', 'swift-login')], 403);
        }
    }

    private function clean_output(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    private function get_post_data(): array
    {
        $raw  = isset($_POST['data']) ? stripslashes($_POST['data']) : file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function base64url_decode(string $data): string
    {
        $padded = str_pad(
            str_replace(['-', '_'], ['+', '/'], $data),
            strlen($data) + (4 - strlen($data) % 4) % 4,
            '='
        );
        return base64_decode($padded);
    }

    private function make_webauthn(): Web_Authn
    {
        return new Web_Authn(
            Helper::get_rp_name(),
            Helper::get_rp_id()
        );
    }

    // -------------------------------------------------------------------------
    // Registration: generate options (challenge)
    // -------------------------------------------------------------------------
    public function register_options(): void
    {
        $this->clean_output();
        $this->verify_nonce();

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('请先登录后再注册 Passkey', 'swift-login')], 401);
        }

        $user    = wp_get_current_user();
        $timeout = (int) Helper::get_option('passkey_timeout', 60);
        $uv      = Helper::get_option('passkey_user_verification', 'preferred');

        try {
            $webAuthn = $this->make_webauthn();
            $args     = $webAuthn->getCreateArgs(
                $user->ID,
                $user->user_login,
                $user->display_name ?: $user->user_login,
                $timeout,
                false,   // requireResidentKey
                $uv
            );
        } catch (\Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }

        // Extract the binary challenge and store it
        $challenge_binary = $args->publicKey->challenge->getBinaryString();
        Challenge_Model::cleanup(300);
        Challenge_Model::create($challenge_binary, 'register', $user->ID);

        // Encode challenge as base64url for the browser
        $args->publicKey->challenge = $this->binary_to_base64url($challenge_binary);

        // Encode user.id as base64url for the browser
        if (isset($args->publicKey->user->id) && $args->publicKey->user->id instanceof Byte_Buffer) {
            $args->publicKey->user->id = $this->binary_to_base64url($args->publicKey->user->id->getBinaryString());
        }

        // Encode excludeCredentials ids
        if (!empty($args->publicKey->excludeCredentials)) {
            foreach ($args->publicKey->excludeCredentials as &$cred) {
                if ($cred->id instanceof Byte_Buffer) {
                    $cred->id = $this->binary_to_base64url($cred->id->getBinaryString());
                }
            }
            unset($cred);
        }

        // Add existing user credentials to exclude list
        $existing = Passkey_Model::get_by_user($user->ID);
        if (!empty($existing)) {
            if (!isset($args->publicKey->excludeCredentials)) {
                $args->publicKey->excludeCredentials = [];
            }
            foreach ($existing as $pk) {
                $ex = new \stdClass();
                $ex->id   = $this->binary_to_base64url($pk['credential_id']);
                $ex->type = 'public-key';
                $args->publicKey->excludeCredentials[] = $ex;
            }
        }

        wp_send_json_success($args->publicKey);
    }

    // -------------------------------------------------------------------------
    // Registration: verify & store credential
    // -------------------------------------------------------------------------
    public function register_verify(): void
    {
        $this->clean_output();
        $this->verify_nonce();

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('请先登录', 'swift-login')], 401);
        }

        $data = $this->get_post_data();
        if (empty($data['id']) || empty($data['response']['clientDataJSON']) || empty($data['response']['attestationObject'])) {
            wp_send_json_error(['message' => __('数据不完整', 'swift-login')], 400);
        }

        $user = wp_get_current_user();

        // Decode clientDataJSON to find the challenge
        $client_data_raw = $this->base64url_decode($data['response']['clientDataJSON']);
        $client_data     = json_decode($client_data_raw);

        if (empty($client_data->challenge)) {
            wp_send_json_error(['message' => __('challenge 无效', 'swift-login')], 400);
        }

        $challenge_binary = $this->base64url_decode($client_data->challenge);
        $challenge_rec    = Challenge_Model::find($challenge_binary, 'register');

        if (!$challenge_rec || (int) $challenge_rec['user_id'] !== $user->ID) {
            wp_send_json_error(['message' => __('challenge 不匹配或已过期', 'swift-login')], 400);
        }

        Challenge_Model::delete((int) $challenge_rec['id']);

        try {
            $webAuthn          = $this->make_webauthn();
            $clientDataJSON    = $this->base64url_decode($data['response']['clientDataJSON']);
            $attestationObject = new Byte_Buffer($this->base64url_decode($data['response']['attestationObject']));
            $uv                = Helper::get_option('passkey_user_verification', 'preferred') === 'required';

            $attestation = $webAuthn->processCreate(
                $clientDataJSON,
                $attestationObject,
                $challenge_binary,
                $uv
            );
        } catch (\Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()], 400);
        }

        $credential_id = $data['id']; // base64url string from browser
        $name          = sanitize_text_field($data['name'] ?? '');

        if (Passkey_Model::find_by_credential_id($credential_id)) {
            wp_send_json_error(['message' => __('该 Passkey 已注册', 'swift-login')], 409);
        }

        $pk_id = Passkey_Model::create([
            'user_id'       => $user->ID,
            'credential_id' => $credential_id,
            'public_key'    => $attestation->credentialPublicKey,
            'sign_counter'  => $webAuthn->getSignatureCounter() ?? 0,
            'name'          => $name ?: sprintf(__('Passkey %s', 'swift-login'), date('Y-m-d')),
        ]);

        if (!$pk_id) {
            wp_send_json_error(['message' => __('保存失败', 'swift-login')], 500);
        }

        wp_send_json_success(['message' => __('Passkey 注册成功', 'swift-login'), 'id' => $pk_id]);
    }

    // -------------------------------------------------------------------------
    // Login: generate options
    // -------------------------------------------------------------------------
    public function login_options(): void
    {
        $this->clean_output();
        $this->verify_nonce();

        $timeout = (int) Helper::get_option('passkey_timeout', 60);
        $uv      = Helper::get_option('passkey_user_verification', 'preferred');

        try {
            $webAuthn = $this->make_webauthn();
            $args     = $webAuthn->getGetArgs([], $timeout, true, true, true, true, true, $uv);
        } catch (\Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }

        $challenge_binary = $args->publicKey->challenge->getBinaryString();
        Challenge_Model::cleanup(300);
        Challenge_Model::create($challenge_binary, 'login');

        $args->publicKey->challenge = $this->binary_to_base64url($challenge_binary);

        wp_send_json_success($args->publicKey);
    }

    // -------------------------------------------------------------------------
    // Login: verify assertion
    // -------------------------------------------------------------------------
    public function login_verify(): void
    {
        $this->clean_output();
        $this->verify_nonce();

        $data = $this->get_post_data();
        if (empty($data['id']) || empty($data['response']['clientDataJSON'])) {
            wp_send_json_error(['message' => __('数据不完整', 'swift-login')], 400);
        }

        $credential_id = $data['id'];
        $passkey       = Passkey_Model::find_by_credential_id($credential_id);

        if (!$passkey) {
            wp_send_json_error(['message' => __('未找到对应的 Passkey', 'swift-login')], 404);
        }

        $client_data_raw = $this->base64url_decode($data['response']['clientDataJSON']);
        $client_data     = json_decode($client_data_raw);

        if (empty($client_data->challenge)) {
            wp_send_json_error(['message' => __('challenge 无效', 'swift-login')], 400);
        }

        $challenge_binary = $this->base64url_decode($client_data->challenge);
        $challenge_rec    = Challenge_Model::find($challenge_binary, 'login');

        if (!$challenge_rec) {
            wp_send_json_error(['message' => __('challenge 不匹配或已过期', 'swift-login')], 400);
        }

        Challenge_Model::delete((int) $challenge_rec['id']);

        try {
            $webAuthn = $this->make_webauthn();
            $webAuthn->processGet(
                $this->base64url_decode($data['response']['clientDataJSON']),
                $this->base64url_decode($data['response']['authenticatorData']),
                $this->base64url_decode($data['response']['signature']),
                $passkey['public_key'],
                $challenge_binary,
                (int) $passkey['sign_counter'],
                Helper::get_option('passkey_user_verification', 'preferred') === 'required'
            );
        } catch (\Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()], 400);
        }

        // Update counter
        $new_counter = $webAuthn->getSignatureCounter() ?? (int) $passkey['sign_counter'];
        Passkey_Model::update_counter((int) $passkey['id'], $new_counter);

        // Log user in
        $user_id = (int) $passkey['user_id'];
        $user    = get_user_by('id', $user_id);

        if (!$user) {
            wp_send_json_error(['message' => __('用户不存在', 'swift-login')], 404);
        }

        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);
        do_action('wp_login', $user->user_login, $user);

        $redirect = apply_filters('login_redirect', admin_url(), '', $user);
        wp_send_json_success([
            'message'  => __('登录成功', 'swift-login'),
            'redirect' => $redirect,
        ]);
    }

    // -------------------------------------------------------------------------
    // Delete passkey
    // -------------------------------------------------------------------------
    public function delete_passkey(): void
    {
        $this->clean_output();
        $this->verify_nonce();

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('请先登录', 'swift-login')], 401);
        }

        $id   = (int) ($_POST['passkey_id'] ?? 0);
        $user = wp_get_current_user();

        if (!$id) {
            wp_send_json_error(['message' => __('参数错误', 'swift-login')], 400);
        }

        if (Passkey_Model::delete($id, $user->ID)) {
            wp_send_json_success(['message' => __('已删除', 'swift-login')]);
        } else {
            wp_send_json_error(['message' => __('删除失败', 'swift-login')], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
    private function binary_to_base64url(string $binary): string
    {
        return rtrim(strtr(base64_encode($binary), '+/', '-_'), '=');
    }
}

new Passkey_Ajax();

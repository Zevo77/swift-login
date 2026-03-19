<?php

namespace Swift_Login\Includes;

use Swift_Login\Core\Helper;
use Swift_Login\Models\Passkey_Model;
use Swift_Login\Models\Challenge_Model;
use Swift_Login\Models\Social_Model;

defined('ABSPATH') || exit;

class Passkey
{
    public function __construct()
    {
        if (!Helper::is_passkey_enabled()) {
            return;
        }

        // Inject passkey button on login page
        add_action('login_form', [$this, 'render_login_button']);

        // Enqueue scripts
        add_action('login_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_enqueue_scripts',    [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Profile page: manage passkeys
        add_action('show_user_profile', [$this, 'render_profile_section']);
        add_action('edit_user_profile', [$this, 'render_profile_section']);

        // Profile page: manage social bindings
        if (Helper::is_social_login_enabled()) {
            add_action('show_user_profile', [$this, 'render_social_binding_section']);
            add_action('edit_user_profile', [$this, 'render_social_binding_section']);
        }
    }

    public function enqueue_scripts(): void
    {
        wp_enqueue_script(
            'swift-login-passkey',
            SWIFT_LOGIN_ASSETS_FRONTEND_URL . '/js/passkey.js',
            [],
            SWIFT_LOGIN_VERSION,
            true
        );
        wp_localize_script('swift-login-passkey', 'SwiftLoginPasskey', [
            'ajaxUrl'  => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce(SWIFT_LOGIN_NONCE),
            'strings'  => [
                'loginWithPasskey'   => __('使用 Passkey 登录', 'swift-login'),
                'registerPasskey'    => __('注册 Passkey', 'swift-login'),
                'passkeyNotSupported'=> __('您的浏览器不支持 Passkey', 'swift-login'),
                'error'              => __('登录失败，请重试', 'swift-login'),
                'success'            => __('登录成功', 'swift-login'),
            ],
        ]);
    }

    public function render_login_button(): void
    {
        ?>
        <div class="swift-login-passkey-wrap">
            <div class="swift-login-divider"><span><?php esc_html_e('或者', 'swift-login'); ?></span></div>
            <button type="button" id="swift-passkey-login-btn" class="swift-btn swift-btn-passkey">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 1 0-16 0"/><line x1="19" y1="11" x2="23" y2="11"/><line x1="21" y1="9" x2="21" y2="13"/></svg>
                <?php esc_html_e('使用 Passkey 登录', 'swift-login'); ?>
            </button>
            <div id="swift-passkey-message" class="swift-passkey-message" style="display:none;"></div>
        </div>
        <?php
    }

    public function render_profile_section(\WP_User $user): void
    {
        $passkeys = Passkey_Model::get_by_user($user->ID);
        ?>
        <div class="swift-login-profile-section">
            <h2><?php esc_html_e('Passkey 管理', 'swift-login'); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('我的 Passkey', 'swift-login'); ?></th>
                    <td>
                        <div id="swift-passkey-list">
                        <?php if (empty($passkeys)) : ?>
                            <p class="description"><?php esc_html_e('您还没有注册任何 Passkey。', 'swift-login'); ?></p>
                        <?php else : ?>
                            <ul class="swift-passkey-items">
                            <?php foreach ($passkeys as $pk) : ?>
                                <li data-id="<?php echo esc_attr($pk['id']); ?>">
                                    <div class="swift-passkey-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    </div>
                                    <div class="swift-passkey-info">
                                        <span class="passkey-name"><?php echo esc_html($pk['name'] ?: __('未命名设备', 'swift-login')); ?></span>
                                        <span class="passkey-date"><?php printf(esc_html__('添加于 %s', 'swift-login'), date_i18n(get_option('date_format'), strtotime($pk['created_at']))); ?></span>
                                    </div>
                                    <button type="button" class="button swift-delete-passkey" data-id="<?php echo esc_attr($pk['id']); ?>">
                                        <?php esc_html_e('删除', 'swift-login'); ?>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        </div>
                        <p>
                            <button type="button" id="swift-register-passkey-btn" class="button button-primary">
                                <?php esc_html_e('添加新 Passkey', 'swift-login'); ?>
                            </button>
                        </p>
                        <input type="hidden" id="swift-profile-user-id" value="<?php echo esc_attr($user->ID); ?>">
                        <div id="swift-passkey-profile-message" class="swift-passkey-message"></div>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
    public function render_social_binding_section(\WP_User $user): void
    {
        $bindings      = Social_Model::get_bindings_by_user($user->ID);
        $bound_types   = array_column($bindings, null, 'social_type');
        $all_platforms = Helper::all_social_platforms();
        $enabled       = Helper::get_social_platforms();
        $assets_url    = SWIFT_LOGIN_ASSETS_FRONTEND_URL . '/images/social';
        $bind_success  = !empty($_GET['swift_bind_success']);
        ?>
        <div class="swift-login-profile-section">
            <h2><?php esc_html_e('社会化登录绑定', 'swift-login'); ?></h2>
            <?php if ($bind_success) : ?>
            <div class="swift-passkey-message success" style="display:block;margin-bottom:12px;"><?php esc_html_e('绑定成功！', 'swift-login'); ?></div>
            <?php endif; ?>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('绑定平台', 'swift-login'); ?></th>
                    <td>
                        <ul class="swift-social-bindings">
                        <?php foreach ($enabled as $type) :
                            $label    = $all_platforms[$type] ?? $type;
                            $is_bound = isset($bound_types[$type]);
                        ?>
                            <li>
                                <div class="swift-social-binding-icon">
                                    <img src="<?php echo esc_url($assets_url . '/' . esc_attr($type) . '.svg'); ?>" alt="<?php echo esc_attr($label); ?>" onerror="this.style.display='none'">
                                </div>
                                <div class="swift-social-binding-info">
                                    <span class="binding-name"><?php echo esc_html($label); ?></span>
                                    <span class="binding-status <?php echo $is_bound ? 'bound' : 'unbound'; ?>">
                                        <?php echo $is_bound ? esc_html__('已绑定', 'swift-login') : esc_html__('未绑定', 'swift-login'); ?>
                                    </span>
                                </div>
                                <?php if ($is_bound) : ?>
                                <button type="button" class="button swift-unbind-social" data-type="<?php echo esc_attr($type); ?>">
                                    <?php esc_html_e('解绑', 'swift-login'); ?>
                                </button>
                                <?php else : ?>
                                <button type="button" class="button button-primary swift-bind-social" data-type="<?php echo esc_attr($type); ?>">
                                    <?php esc_html_e('绑定', 'swift-login'); ?>
                                </button>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                        <div id="swift-social-binding-message" class="swift-passkey-message"></div>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
}

new Passkey();

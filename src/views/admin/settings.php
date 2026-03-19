<?php defined('ABSPATH') || exit; ?>
<div class="wrap swift-login-admin">
    <h1><?php esc_html_e('Swift Login 设置', 'swift-login'); ?></h1>
    <div id="swift-login-notice" class="notice" style="display:none;"></div>

    <form id="swift-login-settings-form">
        <!-- Passkey -->
        <div class="swift-card">
            <h2 class="swift-card-title">
                <span class="dashicons dashicons-lock"></span>
                <?php esc_html_e('Passkey 设置', 'swift-login'); ?>
            </h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('启用 Passkey 登录', 'swift-login'); ?></th>
                    <td><label class="swift-toggle"><input type="checkbox" name="passkey_enabled" id="passkey_enabled"><span class="slider"></span></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('用户验证级别', 'swift-login'); ?></th>
                    <td>
                        <select name="passkey_user_verification" id="passkey_user_verification">
                            <option value="preferred"><?php esc_html_e('推荐 (preferred)', 'swift-login'); ?></option>
                            <option value="required"><?php esc_html_e('必须 (required)', 'swift-login'); ?></option>
                            <option value="discouraged"><?php esc_html_e('不要求 (discouraged)', 'swift-login'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('超时时间（秒）', 'swift-login'); ?></th>
                    <td><input type="number" name="passkey_timeout" id="passkey_timeout" min="10" max="300" class="small-text"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('禁用用户名密码登录', 'swift-login'); ?></th>
                    <td>
                        <label class="swift-toggle"><input type="checkbox" name="disable_password_login" id="disable_password_login"><span class="slider"></span></label>
                        <p class="description" style="color:#b32d2e;"><?php esc_html_e('⚠️ 危险：开启后所有用户（包括管理员）将无法使用用户名和密码登录，请确保您已设置 Passkey 或社会化登录后再开启。', 'swift-login'); ?></p>
                    </td>
                </tr>
            </table>
            <p class="description" style="margin-top:8px;"><?php esc_html_e('提示：Passkey 可在 WordPress 用户资料页面底部进行添加和管理。', 'swift-login'); ?></p>
        </div>

        <!-- Login Page -->
        <div class="swift-card">
            <h2 class="swift-card-title">
                <span class="dashicons dashicons-art"></span>
                <?php esc_html_e('登录页面美化', 'swift-login'); ?>
            </h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('启用登录页美化', 'swift-login'); ?></th>
                    <td><label class="swift-toggle"><input type="checkbox" name="custom_login_enabled" id="custom_login_enabled"><span class="slider"></span></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('自定义 Logo URL', 'swift-login'); ?></th>
                    <td><input type="url" name="login_logo_url" id="login_logo_url" class="regular-text" placeholder="https://"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('页面背景颜色', 'swift-login'); ?></th>
                    <td><input type="text" name="login_background_color" id="login_background_color" class="swift-color-picker" value="#f0f0f1"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('卡片背景颜色', 'swift-login'); ?></th>
                    <td><input type="text" name="login_card_color" id="login_card_color" class="swift-color-picker" value="#ffffff"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('按钮颜色', 'swift-login'); ?></th>
                    <td><input type="text" name="login_button_color" id="login_button_color" class="swift-color-picker" value="#2271b1"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('按钮文字颜色', 'swift-login'); ?></th>
                    <td><input type="text" name="login_button_text_color" id="login_button_text_color" class="swift-color-picker" value="#ffffff"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('自定义 CSS', 'swift-login'); ?></th>
                    <td><textarea name="login_custom_css" id="login_custom_css" rows="6" class="large-text code" placeholder="/* 自定义 CSS */"></textarea></td>
                </tr>
            </table>
        </div>

        <!-- Social Login -->
        <div class="swift-card">
            <h2 class="swift-card-title">
                <span class="dashicons dashicons-share"></span>
                <?php esc_html_e('社会化登录', 'swift-login'); ?>
            </h2>
            <p style="margin:0 0 12px;color:#646970;">
                <?php echo wp_kses(
                    sprintf(
                        __('本功能对接 <a href="%s" target="_blank" rel="noopener">知我云聚合登录</a>，请前往该平台注册并创建应用，获取 App ID 和 App Key 后填入下方。', 'swift-login'),
                        'https://u.zevost.com/'
                    ),
                    ['a' => ['href' => [], 'target' => [], 'rel' => []]]
                ); ?>
            </p>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('启用社会化登录', 'swift-login'); ?></th>
                    <td><label class="swift-toggle"><input type="checkbox" name="social_login_enabled" id="social_login_enabled"><span class="slider"></span></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('聚合登录接口地址', 'swift-login'); ?></th>
                    <td>
                        <input type="url" name="social_api_base" id="social_api_base" class="regular-text" value="https://u.zevost.com/connect.php" placeholder="https://u.zevost.com/connect.php">
                        <p class="description"><?php echo wp_kses(
                            sprintf(
                                __('默认使用 <a href="%1$s" target="_blank" rel="noopener">%1$s</a>，如果您自建了聚合登录程序可在此填入自定义地址。', 'swift-login'),
                                'https://u.zevost.com/connect.php'
                            ),
                            ['a' => ['href' => [], 'target' => [], 'rel' => []]]
                        ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('App ID', 'swift-login'); ?></th>
                    <td><input type="text" name="social_appid" id="social_appid" class="regular-text" placeholder="<?php esc_attr_e('请输入知我云 App ID', 'swift-login'); ?>"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('App Key', 'swift-login'); ?></th>
                    <td><input type="text" name="social_appkey" id="social_appkey" class="regular-text" placeholder="<?php esc_attr_e('请输入知我云 App Key', 'swift-login'); ?>"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('启用的平台', 'swift-login'); ?></th>
                    <td>
                        <div class="swift-platforms-grid" id="swift-platforms-grid">
                            <?php
                            $all = \Swift_Login\Core\Helper::all_social_platforms();
                            foreach ($all as $type => $label) :
                            ?>
                            <label class="swift-platform-item">
                                <input type="checkbox" name="social_platforms[]" value="<?php echo esc_attr($type); ?>" class="social-platform-cb">
                                <img src="<?php echo esc_url(SWIFT_LOGIN_ASSETS_FRONTEND_URL . '/img/social/' . $type . '.png'); ?>" alt="" onerror="this.style.display='none'" width="20">
                                <span><?php echo esc_html($label); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('未绑定用户自动注册', 'swift-login'); ?></th>
                    <td>
                        <label class="swift-toggle"><input type="checkbox" name="social_auto_register" id="social_auto_register"><span class="slider"></span></label>
                        <p class="description"><?php esc_html_e('开启后，通过社会化登录的新用户将自动创建 WordPress 账号', 'swift-login'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('自定义回调地址', 'swift-login'); ?></th>
                    <td>
                        <input type="url" name="social_redirect_uri" id="social_redirect_uri" class="regular-text" placeholder="留空则使用默认">
                        <p class="description"><?php printf(esc_html__('默认回调地址：%s', 'swift-login'), '<code>' . esc_html(\Swift_Login\Core\Helper::get_social_callback_url()) . '</code>'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <p class="submit">
            <button type="submit" id="swift-save-btn" class="button button-primary"><?php esc_html_e('保存设置', 'swift-login'); ?></button>
        </p>
    </form>
    <div class="swift-card" style="margin-top:24px;">
        <h2 class="swift-card-title"><span class="dashicons dashicons-shortcode"></span> <?php esc_html_e('主题集成短代码', 'swift-login'); ?></h2>
        <p><?php esc_html_e('如果您的主题有自定义登录页面，可使用以下短代码将 Swift Login 的功能嵌入到主题模板中：', 'swift-login'); ?></p>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Passkey 登录按钮', 'swift-login'); ?></th>
                <td><code>[swift_passkey_button]</code><p class="description"><?php esc_html_e('在主题登录页面中输出 Passkey 登录按钮', 'swift-login'); ?></p></td>
            </tr>
            <tr>
                <th><?php esc_html_e('社会化登录按钮组', 'swift-login'); ?></th>
                <td><code>[swift_social_buttons]</code><p class="description"><?php esc_html_e('在主题登录页面中输出社会化登录按钮组', 'swift-login'); ?></p></td>
            </tr>
            <tr>
                <th><?php esc_html_e('PHP 模板调用', 'swift-login'); ?></th>
                <td><code>do_action('login_form');</code><p class="description"><?php esc_html_e('在主题模板的登录表单内调用此函数，可自动输出所有已启用的登录方式', 'swift-login'); ?></p></td>
            </tr>
        </table>
    </div>

    <p style="margin-top:24px;color:#8c8f94;font-size:13px;text-align:center;"><?php echo wp_kses(sprintf(__('感谢您使用 <a href="%1$s" target="_blank" rel="noopener">ZevoST</a> 开发的 <a href="%2$s" target="_blank" rel="noopener">WordPress</a> 插件 <a href="%3$s" target="_blank" rel="noopener">Swift Login</a>', 'swift-login'), 'https://www.zevost.com/', 'https://wordpress.org/', 'https://www.zevost.com/product/swift-login'), ['a' => ['href' => [], 'target' => [], 'rel' => []]]); ?></p>
</div>

<?php defined('ABSPATH') || exit; ?>
<div class="wrap swift-login-admin">
    <h1><?php esc_html_e('Swift Login Settings', 'swift-login'); ?></h1>
    <div id="swift-login-notice" class="notice" style="display:none;"></div>

    <form id="swift-login-settings-form">
        <!-- Passkey -->
        <div class="swift-card">
            <h2 class="swift-card-title">
                <span class="dashicons dashicons-lock"></span>
                <?php esc_html_e('Passkey Settings', 'swift-login'); ?>
            </h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('Enable Passkey Login', 'swift-login'); ?></th>
                    <td><label class="swift-toggle"><input type="checkbox" name="passkey_enabled" id="passkey_enabled"><span class="slider"></span></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('User Verification Level', 'swift-login'); ?></th>
                    <td>
                        <select name="passkey_user_verification" id="passkey_user_verification">
                            <option value="preferred"><?php esc_html_e('Preferred', 'swift-login'); ?></option>
                            <option value="required"><?php esc_html_e('Required', 'swift-login'); ?></option>
                            <option value="discouraged"><?php esc_html_e('Discouraged', 'swift-login'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Timeout (seconds)', 'swift-login'); ?></th>
                    <td><input type="number" name="passkey_timeout" id="passkey_timeout" min="10" max="300" class="small-text"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Disable Password Login', 'swift-login'); ?></th>
                    <td>
                        <label class="swift-toggle"><input type="checkbox" name="disable_password_login" id="disable_password_login"><span class="slider"></span></label>
                        <p class="description" style="color:#b32d2e;"><?php esc_html_e('Warning: Once enabled, all users (including administrators) will not be able to log in with username and password. Make sure you have set up a Passkey or social login first.', 'swift-login'); ?></p>
                    </td>
                </tr>
            </table>
            <p class="description" style="margin-top:8px;"><?php esc_html_e('Tip: Passkeys can be added and managed at the bottom of the WordPress user profile page.', 'swift-login'); ?></p>
        </div>

        <!-- Login Page -->
        <div class="swift-card">
            <h2 class="swift-card-title">
                <span class="dashicons dashicons-art"></span>
                <?php esc_html_e('Login Page Customization', 'swift-login'); ?>
            </h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('Enable Login Page Customization', 'swift-login'); ?></th>
                    <td><label class="swift-toggle"><input type="checkbox" name="custom_login_enabled" id="custom_login_enabled"><span class="slider"></span></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Custom Logo URL', 'swift-login'); ?></th>
                    <td><input type="url" name="login_logo_url" id="login_logo_url" class="regular-text" placeholder="https://"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Background Color', 'swift-login'); ?></th>
                    <td><input type="text" name="login_background_color" id="login_background_color" class="swift-color-picker" value="#f0f0f1"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Card Background Color', 'swift-login'); ?></th>
                    <td><input type="text" name="login_card_color" id="login_card_color" class="swift-color-picker" value="#ffffff"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Button Color', 'swift-login'); ?></th>
                    <td><input type="text" name="login_button_color" id="login_button_color" class="swift-color-picker" value="#2271b1"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Button Text Color', 'swift-login'); ?></th>
                    <td><input type="text" name="login_button_text_color" id="login_button_text_color" class="swift-color-picker" value="#ffffff"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Custom CSS', 'swift-login'); ?></th>
                    <td><textarea name="login_custom_css" id="login_custom_css" rows="6" class="large-text code" placeholder="/* Custom CSS */"></textarea></td>
                </tr>
            </table>
        </div>

        <!-- Social Login -->
        <div class="swift-card">
            <h2 class="swift-card-title">
                <span class="dashicons dashicons-share"></span>
                <?php esc_html_e('Social Login', 'swift-login'); ?>
            </h2>
            <p style="margin:0 0 12px;color:#646970;">
                <?php echo wp_kses(
                    sprintf(
                        __('This feature integrates with <a href="%s" target="_blank" rel="noopener">Zhiwo Cloud Social Login</a>. Please register on that platform, create an application, and enter the App ID and App Key below.', 'swift-login'),
                        'https://u.zevost.com/'
                    ),
                    ['a' => ['href' => [], 'target' => [], 'rel' => []]]
                ); ?>
            </p>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('Enable Social Login', 'swift-login'); ?></th>
                    <td><label class="swift-toggle"><input type="checkbox" name="social_login_enabled" id="social_login_enabled"><span class="slider"></span></label></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('API Endpoint URL', 'swift-login'); ?></th>
                    <td>
                        <input type="url" name="social_api_base" id="social_api_base" class="regular-text" value="https://u.zevost.com/connect.php" placeholder="https://u.zevost.com/connect.php">
                        <p class="description"><?php echo wp_kses(
                            sprintf(
                                __('Defaults to <a href="%1$s" target="_blank" rel="noopener">%1$s</a>. You can enter a custom URL if you self-host the aggregated login service.', 'swift-login'),
                                'https://u.zevost.com/connect.php'
                            ),
                            ['a' => ['href' => [], 'target' => [], 'rel' => []]]
                        ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('App ID', 'swift-login'); ?></th>
                    <td><input type="text" name="social_appid" id="social_appid" class="regular-text" placeholder="<?php esc_attr_e('Enter App ID', 'swift-login'); ?>"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('App Key', 'swift-login'); ?></th>
                    <td><input type="text" name="social_appkey" id="social_appkey" class="regular-text" placeholder="<?php esc_attr_e('Enter App Key', 'swift-login'); ?>"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Button Style', 'swift-login'); ?></th>
                    <td>
                        <?php $x_icon = esc_url(SWIFT_LOGIN_ASSETS_FRONTEND_URL . '/img/social/x.png'); ?>
                        <div class="swift-btn-style-picker" id="swift-btn-style-picker">
                            <label class="swift-btn-style-option">
                                <input type="radio" name="social_button_style" value="icon-text">
                                <div class="swift-btn-style-preview">
                                    <div class="swift-login-social-buttons" style="grid-template-columns:repeat(3,1fr);">
                                        <button type="button" class="swift-btn swift-social-btn"><img src="<?php echo $x_icon; ?>" width="24" height="24" alt="X"><span>X</span></button>
                                        <button type="button" class="swift-btn swift-social-btn"><img src="<?php echo $x_icon; ?>" width="24" height="24" alt="X"><span>X</span></button>
                                        <button type="button" class="swift-btn swift-social-btn"><img src="<?php echo $x_icon; ?>" width="24" height="24" alt="X"><span>X</span></button>
                                    </div>
                                </div>
                                <span class="swift-btn-style-name"><?php esc_html_e('Icon + Text', 'swift-login'); ?></span>
                            </label>
                            <label class="swift-btn-style-option">
                                <input type="radio" name="social_button_style" value="icon-only">
                                <div class="swift-btn-style-preview">
                                    <div class="swift-social-style-icon-only">
                                        <button type="button" class="swift-btn swift-social-btn"><img src="<?php echo $x_icon; ?>" width="22" height="22" alt="X"></button>
                                        <button type="button" class="swift-btn swift-social-btn"><img src="<?php echo $x_icon; ?>" width="22" height="22" alt="X"></button>
                                        <button type="button" class="swift-btn swift-social-btn"><img src="<?php echo $x_icon; ?>" width="22" height="22" alt="X"></button>
                                    </div>
                                </div>
                                <span class="swift-btn-style-name"><?php esc_html_e('Icon Only', 'swift-login'); ?></span>
                            </label>
                            <label class="swift-btn-style-option">
                                <input type="radio" name="social_button_style" value="list">
                                <div class="swift-btn-style-preview">
                                    <div class="swift-social-style-list">
                                        <button type="button" class="swift-btn swift-social-btn"><img src="<?php echo $x_icon; ?>" width="22" height="22" alt="X"><span>X</span></button>
                                        <button type="button" class="swift-btn swift-social-btn"><img src="<?php echo $x_icon; ?>" width="22" height="22" alt="X"><span>X</span></button>
                                    </div>
                                </div>
                                <span class="swift-btn-style-name"><?php esc_html_e('List', 'swift-login'); ?></span>
                            </label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Enabled Platforms', 'swift-login'); ?></th>
                    <td>
                        <div class="swift-platforms-grid" id="swift-platforms-grid">
                            <?php
                            $all = \Swift_Login\Core\Helper::all_social_platforms();
                            foreach ($all as $type => $label) :
                            ?>
                            <label class="swift-platform-item">
                                <input type="checkbox" name="social_platforms[]" value="<?php echo esc_attr($type); ?>" class="social-platform-cb">
                                <?php $icon_map = ['twitter' => 'x']; ?>
                                <img src="<?php echo esc_url(SWIFT_LOGIN_ASSETS_FRONTEND_URL . '/img/social/' . ($icon_map[$type] ?? $type) . '.png'); ?>" alt="" onerror="this.style.display='none'" width="20">
                                <span><?php echo esc_html($label); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Auto-register New Users', 'swift-login'); ?></th>
                    <td>
                        <label class="swift-toggle"><input type="checkbox" name="social_auto_register" id="social_auto_register"><span class="slider"></span></label>
                        <p class="description"><?php esc_html_e('When enabled, new users logging in via social login will automatically have a WordPress account created.', 'swift-login'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Custom Callback URL', 'swift-login'); ?></th>
                    <td>
                        <input type="url" name="social_redirect_uri" id="social_redirect_uri" class="regular-text" placeholder="<?php esc_attr_e('Leave blank to use default', 'swift-login'); ?>">
                        <p class="description"><?php printf(esc_html__('Default callback URL: %s', 'swift-login'), '<code>' . esc_html(\Swift_Login\Core\Helper::get_social_callback_url()) . '</code>'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <p class="submit">
            <button type="submit" id="swift-save-btn" class="button button-primary"><?php esc_html_e('Save Settings', 'swift-login'); ?></button>
        </p>
    </form>
    <div class="swift-card" style="margin-top:24px;">
        <h2 class="swift-card-title"><span class="dashicons dashicons-shortcode"></span> <?php esc_html_e('Theme Integration Shortcodes', 'swift-login'); ?></h2>
        <p><?php esc_html_e('If your theme has a custom login page, use the following shortcodes to embed Swift Login features into your theme template:', 'swift-login'); ?></p>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Passkey Login Button', 'swift-login'); ?></th>
                <td><code>[swift_passkey_button]</code><p class="description"><?php esc_html_e('Renders the Passkey login button on your custom login page.', 'swift-login'); ?></p></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Social Login Buttons', 'swift-login'); ?></th>
                <td><code>[swift_social_buttons]</code><p class="description"><?php esc_html_e('Renders the social login button group on your custom login page.', 'swift-login'); ?></p></td>
            </tr>
            <tr>
                <th><?php esc_html_e('PHP Template Call', 'swift-login'); ?></th>
                <td><code>do_action('login_form');</code><p class="description"><?php esc_html_e('Call this function inside your login form template to automatically output all enabled login methods.', 'swift-login'); ?></p></td>
            </tr>
        </table>
    </div>

    <p style="margin-top:24px;color:#8c8f94;font-size:13px;text-align:center;"><?php echo wp_kses(sprintf(__('Thank you for using <a href="%3$s" target="_blank" rel="noopener">Swift Login</a>, a <a href="%2$s" target="_blank" rel="noopener">WordPress</a> plugin by <a href="%1$s" target="_blank" rel="noopener">ZevoST</a>.', 'swift-login'), 'https://www.zevost.com/', 'https://wordpress.org/', 'https://www.zevost.com/product/swift-login'), ['a' => ['href' => [], 'target' => [], 'rel' => []]]); ?></p>
    <p style="margin-top:8px;text-align:center;">
        <a href="https://github.com/Zevo77/swift-login" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;color:#24292f;text-decoration:none;font-size:13px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.3 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61-.546-1.387-1.333-1.756-1.333-1.756-1.09-.745.083-.729.083-.729 1.205.084 1.84 1.237 1.84 1.237 1.07 1.834 2.807 1.304 3.492.997.108-.775.418-1.305.762-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.468-2.38 1.235-3.22-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.3 1.23a11.5 11.5 0 0 1 3.003-.404c1.02.005 2.047.138 3.003.404 2.29-1.552 3.297-1.23 3.297-1.23.653 1.652.242 2.873.118 3.176.77.84 1.233 1.91 1.233 3.22 0 4.61-2.804 5.625-5.475 5.92.43.372.823 1.102.823 2.222 0 1.606-.015 2.898-.015 3.293 0 .322.216.694.825.576C20.565 21.795 24 17.295 24 12c0-6.63-5.37-12-12-12z"/></svg>
            <?php esc_html_e('View on GitHub', 'swift-login'); ?>
        </a>
    </p>
</div>

=== Swift Login ===
Contributors: zevo
Tags: passkey, webauthn, login, social login, passwordless
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Passkey passwordless login, login page customization, and social login for WordPress.

== Description ==

Swift Login enhances the WordPress login experience with three powerful features:

**Passkey Passwordless Login**

* WebAuthn-based passkey authentication
* Supports Face ID, Touch ID, Windows Hello, and other biometric authenticators
* Users can manage multiple passkeys from their profile page
* Supports ES256 and RS256 credentials
* Option to disable password login entirely

**Login Page Customization**

* Modern card-style login interface
* Customizable logo, background color, button color, and more
* Custom CSS support
* Responsive design

**Social Login (Optional)**

* Integrates with Zhiwo Cloud aggregated social login (u.zevost.com)
* Supports 14 platforms: QQ, WeChat, Alipay, Weibo, Baidu, Douyin, Huawei, Xiaomi, Google, Microsoft, Twitter, DingTalk, Gitee, and GitHub
* Auto-register new users on first social login
* Flexible callback URL configuration
* Users can bind/unbind social accounts from their profile page

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/Swift-Login`
2. Activate the plugin from the WordPress admin **Plugins** page
3. Go to **Settings > Swift Login** to configure the plugin

**Social Login Setup**

1. Register an account at https://u.zevost.com and create an application
2. Copy your App ID and App Key
3. Enter the App ID and App Key in the plugin settings
4. Set the callback URL in the Zhiwo Cloud dashboard to the URL shown in the plugin settings
5. Select which social platforms to enable

== Shortcodes ==

Swift Login provides two shortcodes for embedding login buttons anywhere on your site (e.g. custom login pages, sidebars, or page builders):

**[swift_passkey_button]**
Renders the Passkey login button.

**[swift_social_buttons]**
Renders the social login button group. Requires social login to be enabled and configured in settings.

Example usage:

    [swift_passkey_button]
    [swift_social_buttons]

== Source Code ==

This plugin is open source. The source code is available on GitHub:
https://github.com/Zevo77/swift-login

Bug reports and pull requests are welcome.

== Frequently Asked Questions ==

= Does Passkey login work on all browsers? =

Passkey is supported in all modern browsers including Chrome, Firefox, Safari, and Edge. The button will be automatically disabled if the browser does not support the WebAuthn API.

= Can I disable password login entirely? =

Yes. Enable the "Disable Password Login" option in the plugin settings. Users will only be able to log in via Passkey or social login.

= Is social login required? =

No. Social login is completely optional and disabled by default. Passkey login and login page customization work independently.

= Where are passkeys stored? =

Passkey credentials are stored in the `wp_swift_login_passkeys` database table on your own server. No data is sent to external services for passkey authentication.

== Screenshots ==

1. Login page with Passkey button and social login buttons
2. Plugin settings page
3. User profile page — manage passkeys and social account bindings

== Changelog ==

= 1.0.0 =
* Initial release
* Passkey registration and login
* Login page customization
* Aggregated social login integration

== Upgrade Notice ==

= 1.0.0 =
Initial release.

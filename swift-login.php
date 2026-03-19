<?php
/**
 * Plugin Name: Swift Login
 * Plugin URI: https://www.zevost.com/product/swift-login
 * Description: Swift Login 为 WordPress 提供 Passkey 无密码登录、登录页面美化，以及可选的聚合社会化登录功能。
 * Author: Zevo
 * Author URI: https://www.zevost.com
 * Version: 1.0.0
 * Text Domain: swift-login
 * Domain Path: /src/languages/
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') || exit;

define('SWIFT_LOGIN_VERSION', '1.0.0');
define('SWIFT_LOGIN_PLUGIN_FILE', __FILE__);
define('SWIFT_LOGIN_PLUGIN_DIR', __DIR__);
define('SWIFT_LOGIN_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('SWIFT_LOGIN_LANGUAGES_DIR', basename(__DIR__) . '/src/languages/');
define('SWIFT_LOGIN_SRC_DIR', __DIR__ . '/src');
define('SWIFT_LOGIN_INCLUDES_DIR', __DIR__ . '/src/includes');
define('SWIFT_LOGIN_AJAX_DIR', __DIR__ . '/src/ajax');
define('SWIFT_LOGIN_HOOKS_DIR', __DIR__ . '/src/hooks');
define('SWIFT_LOGIN_ACTIONS_DIR', __DIR__ . '/src/actions');
define('SWIFT_LOGIN_MODELS_DIR', __DIR__ . '/src/models');
define('SWIFT_LOGIN_VIEWS_DIR', __DIR__ . '/src/views');
define('SWIFT_LOGIN_VIEWS_ADMIN_DIR', __DIR__ . '/src/views/admin');
define('SWIFT_LOGIN_VIEWS_FRONTEND_DIR', __DIR__ . '/src/views/frontend');
define('SWIFT_LOGIN_ASSETS_URL', plugin_dir_url(__FILE__) . 'assets');
define('SWIFT_LOGIN_ASSETS_FRONTEND_URL', SWIFT_LOGIN_ASSETS_URL . '/frontend');
define('SWIFT_LOGIN_ASSETS_ADMIN_URL', SWIFT_LOGIN_ASSETS_URL . '/admin');
define('SWIFT_LOGIN_NONCE', 'swift-login-nonce');

require_once __DIR__ . '/src/core/class-autoloader.php';
require_once __DIR__ . '/src/core/class-application.php';

$app = new Swift_Login\Core\Application();
$app->boot();

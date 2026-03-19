(function ($) {
    'use strict';

    var cfg = SwiftLoginAdmin;

    // Init color pickers
    $('.swift-color-picker').wpColorPicker();

    // Load settings on page load
    function loadSettings() {
        $.post(cfg.ajaxUrl, { action: 'swift_login_get_settings', nonce: cfg.nonce }, function (res) {
            if (!res.success) return;
            var d = res.data;

            // Passkey
            $('#passkey_enabled').prop('checked', !!d.passkey_enabled);
            $('#passkey_user_verification').val(d.passkey_user_verification || 'preferred');
            $('#passkey_timeout').val(d.passkey_timeout || 60);
            $('#disable_password_login').prop('checked', !!d.disable_password_login);

            // Login page
            $('#custom_login_enabled').prop('checked', !!d.custom_login_enabled);
            $('#login_logo_url').val(d.login_logo_url || '');
            setColor('#login_background_color', d.login_background_color || '#f0f0f1');
            setColor('#login_card_color', d.login_card_color || '#ffffff');
            setColor('#login_button_color', d.login_button_color || '#2271b1');
            setColor('#login_button_text_color', d.login_button_text_color || '#ffffff');
            $('#login_custom_css').val(d.login_custom_css || '');

            // Social
            $('#social_login_enabled').prop('checked', !!d.social_login_enabled);
            $('#social_appid').val(d.social_appid || '');
            $('#social_appkey').val(d.social_appkey || '');
            $('#social_auto_register').prop('checked', !!d.social_auto_register);
            $('#social_redirect_uri').val(d.social_redirect_uri || '');
            $('#social_api_base').val(d.social_api_base || 'https://u.zevost.com/connect.php');

            // Platforms
            var platforms = Array.isArray(d.social_platforms) && d.social_platforms.length > 0 ? d.social_platforms : ['qq', 'wx', 'google', 'github'];
            $('.social-platform-cb').each(function () {
                $(this).prop('checked', platforms.indexOf($(this).val()) !== -1);
            });
        });
    }

    function setColor(selector, value) {
        var $el = $(selector);
        if ($el.length && $el.wpColorPicker) {
            try { $el.wpColorPicker('color', value); } catch (e) { $el.val(value); }
        }
    }

    function getColor(selector) {
        var $el = $(selector);
        try { return $el.wpColorPicker('color'); } catch (e) { return $el.val(); }
    }

    function showNotice(msg, type) {
        var $n = $('#swift-login-notice');
        $n.removeClass('notice-success notice-error')
          .addClass('notice-' + type)
          .html('<p>' + msg + '</p>')
          .show();
        setTimeout(function () { $n.fadeOut(); }, 3500);
    }

    // Save settings
    $('#swift-login-settings-form').on('submit', function (e) {
        e.preventDefault();

        var platforms = [];
        $('.social-platform-cb:checked').each(function () {
            platforms.push($(this).val());
        });

        var data = {
            passkey_enabled:          $('#passkey_enabled').is(':checked'),
            passkey_user_verification:$('#passkey_user_verification').val(),
            passkey_timeout:          parseInt($('#passkey_timeout').val(), 10),
            custom_login_enabled:     $('#custom_login_enabled').is(':checked'),
            login_logo_url:           $('#login_logo_url').val(),
            login_background_color:   getColor('#login_background_color'),
            login_card_color:         getColor('#login_card_color'),
            login_button_color:       getColor('#login_button_color'),
            login_button_text_color:  getColor('#login_button_text_color'),
            login_custom_css:         $('#login_custom_css').val(),
            social_login_enabled:     $('#social_login_enabled').is(':checked'),
            social_appid:             $('#social_appid').val(),
            social_appkey:            $('#social_appkey').val(),
            social_platforms:         platforms,
            social_auto_register:     $('#social_auto_register').is(':checked'),
            social_redirect_uri:      $('#social_redirect_uri').val(),
            social_api_base:          $('#social_api_base').val(),
            disable_password_login:   $('#disable_password_login').is(':checked'),
        };

        if (data.disable_password_login) {
            if (!confirm('⚠️ 危险操作\n\n开启后，所有用户（包括管理员）将无法使用用户名和密码登录！\n\n请确认您已经成功设置了 Passkey 或社会化登录，否则将无法登录您的网站。\n\n确定要开启吗？')) {
                $btn.prop('disabled', false).text('保存设置');
                return;
            }
        }

        var $btn = $('#swift-save-btn').prop('disabled', true).text(cfg.strings.saving);

        $.post(cfg.ajaxUrl, {
            action:   'swift_login_save_settings',
            nonce:    cfg.nonce,
            settings: JSON.stringify(data),
        }, function (res) {
            if (res.success) {
                showNotice(cfg.strings.saved, 'success');
            } else {
                showNotice(res.data && res.data.message ? res.data.message : cfg.strings.saveError, 'error');
            }
        }).fail(function () {
            showNotice(cfg.strings.saveError, 'error');
        }).always(function () {
            $btn.prop('disabled', false).text('保存设置');
        });
    });

    loadSettings();

}(jQuery));

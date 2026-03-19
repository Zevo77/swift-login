(function () {
    'use strict';

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.swift-social-btn');
        if (!btn) return;

        var type    = btn.getAttribute('data-type');
        var nonce   = btn.getAttribute('data-nonce');
        var ajaxUrl = (window.SwiftLoginPasskey && window.SwiftLoginPasskey.ajaxUrl) || '';
        var loading = document.getElementById('swift-social-loading');

        if (!type || !ajaxUrl) return;

        // Disable all social buttons
        document.querySelectorAll('.swift-social-btn').forEach(function (b) {
            b.disabled = true;
        });
        if (loading) loading.style.display = 'block';

        var formData = new FormData();
        formData.append('action', 'swift_login_social_init');
        formData.append('nonce',  nonce);
        formData.append('type',   type);

        fetch(ajaxUrl, { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success && res.data && res.data.url) {
                    window.location.href = res.data.url;
                } else {
                    var msg = res.data && res.data.message ? res.data.message : '获取登录地址失败';
                    alert(msg);
                    document.querySelectorAll('.swift-social-btn').forEach(function (b) {
                        b.disabled = false;
                    });
                    if (loading) loading.style.display = 'none';
                }
            })
            .catch(function () {
                alert('网络错误，请重试');
                document.querySelectorAll('.swift-social-btn').forEach(function (b) {
                    b.disabled = false;
                });
                if (loading) loading.style.display = 'none';
            });
    });

}());

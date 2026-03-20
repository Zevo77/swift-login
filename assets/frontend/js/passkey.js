(function () {
    'use strict';

    var cfg = window.SwiftLoginPasskey || {};
    var ajaxUrl = cfg.ajaxUrl || '';
    var nonce   = cfg.nonce   || '';
    var strings = cfg.strings || {};

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
    function b64ToBuffer(b64) {
        // Support both base64url and standard base64
        var s = b64.replace(/-/g, '+').replace(/_/g, '/');
        // Add padding if needed
        while (s.length % 4 !== 0) { s += '='; }
        var bin = atob(s);
        var buf = new Uint8Array(bin.length);
        for (var i = 0; i < bin.length; i++) buf[i] = bin.charCodeAt(i);
        return buf.buffer;
    }

    function bufferToB64(buf) {
        var bytes = new Uint8Array(buf);
        var bin   = '';
        for (var i = 0; i < bytes.byteLength; i++) bin += String.fromCharCode(bytes[i]);
        return btoa(bin);
    }

    function post(action, body) {
        var formData = new FormData();
        formData.append('action', action);
        formData.append('nonce', nonce);
        // Append extra body fields as JSON string
        formData.append('data', JSON.stringify(body));
        return fetch(ajaxUrl, {
            method: 'POST',
            body:   formData,
        }).then(function (r) {
            var ct = r.headers.get('content-type') || '';
            if (!ct.includes('application/json')) {
                return r.text().then(function (t) {
                    throw new Error(strings.error || 'Operation failed. Please try again.');
                });
            }
            return r.json();
        });
    }

    function showMsg(el, msg, type) {
        if (!el) return;
        el.textContent = msg;
        el.className   = 'swift-passkey-message ' + (type || '');
        el.style.display = 'block';
    }

    function isSupported() {
        return !!(window.PublicKeyCredential && navigator.credentials && navigator.credentials.create);
    }

    // -------------------------------------------------------------------------
    // LOGIN
    // -------------------------------------------------------------------------
    var loginBtn = document.getElementById('swift-passkey-login-btn');
    var loginMsg = document.getElementById('swift-passkey-message');

    if (loginBtn) {
        if (!isSupported()) {
            loginBtn.disabled = true;
            loginBtn.title    = strings.passkeyNotSupported || 'Not supported';
        }

        loginBtn.addEventListener('click', function () {
            loginBtn.disabled = true;
            showMsg(loginMsg, '', '');

            post('swift_login_passkey_login_options', {})
                .then(function (res) {
                    if (!res.success) throw new Error(res.data && res.data.message || strings.error);
                    var opts = res.data;

                    var publicKey = {
                        challenge:        b64ToBuffer(opts.challenge),
                        rpId:             opts.rpId,
                        timeout:          opts.timeout,
                        userVerification: opts.userVerification,
                        allowCredentials: (opts.allowCredentials || []).map(function (c) {
                            return { type: c.type, id: b64ToBuffer(c.id) };
                        }),
                    };

                    return navigator.credentials.get({ publicKey: publicKey });
                })
                .then(function (assertion) {
                    var resp = assertion.response;
                    return post('swift_login_passkey_login_verify', {
                        id:       assertion.id,
                        type:     assertion.type,
                        response: {
                            clientDataJSON:    bufferToB64(resp.clientDataJSON),
                            authenticatorData: bufferToB64(resp.authenticatorData),
                            signature:         bufferToB64(resp.signature),
                            userHandle:        resp.userHandle ? bufferToB64(resp.userHandle) : null,
                        },
                    });
                })
                .then(function (res) {
                    if (!res.success) throw new Error(res.data && res.data.message || strings.error);
                    showMsg(loginMsg, strings.success || res.data.message, 'success');
                    setTimeout(function () {
                        window.location.href = res.data.redirect || window.location.href;
                    }, 800);
                })
                .catch(function (err) {
                    loginBtn.disabled = false;
                    if (err && err.name === 'NotAllowedError') return; // user cancelled
                    showMsg(loginMsg, err.message || strings.error, 'error');
                });
        });
    }

    // -------------------------------------------------------------------------
    // REGISTER (profile page)
    // -------------------------------------------------------------------------
    var registerBtn = document.getElementById('swift-register-passkey-btn');
    var profileMsg  = document.getElementById('swift-passkey-profile-message');

    if (registerBtn) {
        if (!isSupported()) {
            registerBtn.disabled = true;
            registerBtn.title    = strings.passkeyNotSupported || 'Not supported';
        }

        registerBtn.addEventListener('click', function () {
            registerBtn.disabled = true;

            post('swift_login_passkey_register_options', {})
                .then(function (res) {
                    if (!res.success) throw new Error(res.data && res.data.message || strings.error);
                    var opts = res.data;

                    var publicKey = {
                        challenge:              b64ToBuffer(opts.challenge),
                        rp:                     opts.rp,
                        user: {
                            id:          b64ToBuffer(opts.user.id),
                            name:        opts.user.name,
                            displayName: opts.user.displayName,
                        },
                        pubKeyCredParams:       opts.pubKeyCredParams,
                        timeout:                opts.timeout,
                        attestation:            opts.attestation || 'none',
                        authenticatorSelection: opts.authenticatorSelection,
                        excludeCredentials:     (opts.excludeCredentials || []).map(function (c) {
                            return { type: c.type, id: b64ToBuffer(c.id) };
                        }),
                    };

                    return navigator.credentials.create({ publicKey: publicKey });
                })
                .then(function (cred) {
                    var name = prompt('Enter a name for this Passkey (optional):', '') || '';
                    var resp = cred.response;
                    return post('swift_login_passkey_register_verify', {
                        id:   cred.id,
                        type: cred.type,
                        name: name,
                        response: {
                            clientDataJSON:    bufferToB64(resp.clientDataJSON),
                            attestationObject: bufferToB64(resp.attestationObject),
                        },
                    });
                })
                .then(function (res) {
                    if (!res.success) throw new Error(res.data && res.data.message || strings.error);
                    showMsg(profileMsg, res.data.message || strings.success, 'success');
                    setTimeout(function () { window.location.reload(); }, 1200);
                })
                .catch(function (err) {
                    registerBtn.disabled = false;
                    if (err && err.name === 'NotAllowedError') return;
                    showMsg(profileMsg, err.message || strings.error, 'error');
                });
        });
    }

    // -------------------------------------------------------------------------
    // BIND social account (profile page)
    // -------------------------------------------------------------------------
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.swift-bind-social');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        var type = btn.getAttribute('data-type');
        var msg  = document.getElementById('swift-social-binding-message');
        btn.disabled = true;
        btn.textContent = 'Redirecting…';

        var formData = new FormData();
        formData.append('action', 'swift_login_social_bind_init');
        formData.append('nonce',  nonce);
        formData.append('type',   type);

        fetch(ajaxUrl, { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success && res.data.url) {
                    window.location.href = res.data.url;
                } else {
                    btn.disabled = false;
                    btn.textContent = 'Link';
                    if (msg) { msg.textContent = (res.data && res.data.message) || 'Failed to get link URL.'; msg.className = 'swift-passkey-message error'; msg.style.display = 'block'; }
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.textContent = 'Link';
            });
    });

    // -------------------------------------------------------------------------
    // UNBIND social account (profile page)
    // -------------------------------------------------------------------------
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.swift-unbind-social');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        if (!confirm('Are you sure you want to unlink this social account?')) return;

        var type = btn.getAttribute('data-type');
        var msg  = document.getElementById('swift-social-binding-message');
        var formData = new FormData();
        formData.append('action', 'swift_login_social_unbind');
        formData.append('nonce',  nonce);
        formData.append('type',   type);

        fetch(ajaxUrl, { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    window.location.reload();
                } else {
                    btn.disabled = false;
                    if (msg) { msg.textContent = (res.data && res.data.message) || 'Failed to unlink.'; msg.className = 'swift-passkey-message error'; msg.style.display = 'block'; }
                }
            })
            .catch(function () {
                btn.disabled = false;
                if (msg) { msg.textContent = 'Unlink request failed. Please try again.'; msg.className = 'swift-passkey-message error'; msg.style.display = 'block'; }
            });
    });

    // -------------------------------------------------------------------------
    // DELETE passkey (profile page)
    // -------------------------------------------------------------------------
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.swift-delete-passkey');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        if (!confirm('Are you sure you want to delete this Passkey?')) return;

        var id = btn.getAttribute('data-id');
        var formData = new FormData();
        formData.append('action',     'swift_login_passkey_delete');
        formData.append('nonce',      nonce);
        formData.append('passkey_id', id);

        fetch(ajaxUrl, { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    var li = btn.closest('li');
                    if (li) li.remove();
                } else {
                    alert(res.data && res.data.message || strings.error);
                }
            });
    });

}());

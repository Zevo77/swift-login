![Swift Login](https://github.com/Zevo77/swift-login/blob/main/bar.png)
# Swift Login

为 WordPress 提供 Passkey 无密码登录、登录页面美化，以及聚合社会化登录功能。

插件正在接受Wordpress Team审查

**社会化登录配置**

1. 前往 https://u.zevost.com 注册账号并创建应用
2. 获取 App ID 和 App Key
3. 在插件设置中填入 App ID 和 App Key
4. 在知我云后台将回调地址设置为插件显示的回调地址
5. 选择需要启用的登录平台

**Passkey 无密码登录**
* 基于 WebAuthn 标准的 Passkey 登录
* 支持 Face ID、Touch ID、Windows Hello 等生物识别
* 用户可在个人资料页管理多个 Passkey
* 支持 ES256 和 RS256 凭据

**登录页面美化**
* 现代化卡片式登录界面
* 可自定义 Logo、背景色、按钮色等
* 支持自定义 CSS
* 响应式设计

**社会化登录（可选）**
* 对接知我云聚合登录 (u.zevost.com)
* 支持 QQ、微信、支付宝、微博、GitHub、Google 等 14 个平台
* 未绑定用户可自动注册
* 灵活的回调地址配置

**短代码**

插件提供以下短代码，可用于主题模板或页面编辑器中，将登录按钮嵌入任意位置：

**[swift_passkey_button]**
输出 Passkey 登录按钮。适合嵌入自定义登录页面或侧边栏。

**[swift_social_buttons]**
输出社会化登录按钮组（需先在设置中启用社会化登录并完成配置）。

示例：

    [swift_passkey_button]
    [swift_social_buttons]

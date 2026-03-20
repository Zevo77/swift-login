![Swift Login]([https://github.com/Zevo77/swift-login/blob/main/bar.png](https://github.com/Zevo77/swift-login/blob/main/assets/banner-1544x500.png))
# Swift Login

为 WordPress 提供 Passkey 无密码登录、登录页面美化，以及聚合社会化登录功能。

插件正在接受Wordpress Team审查
![Swift Login screenshot-1]([https://github.com/Zevo77/swift-login/blob/main/bar2.png](https://github.com/Zevo77/swift-login/blob/main/assets/screenshot-1.png))

**Passkey 无密码登录**

* 基于 WebAuthn 标准的 Passkey 认证
* 支持 Face ID、Touch ID、Windows Hello 等生物识别方式
* 用户可在个人资料页管理多个 Passkey
* 支持 ES256 和 RS256 凭据算法
* 可选择完全禁用用户名密码登录

**登录页面美化**

* 现代化卡片式登录界面
* 可自定义 Logo、页面背景色、按钮颜色等
* 支持自定义 CSS
* 响应式设计，兼容移动端

**社会化登录（可选）**

* 对接知我云聚合登录（u.zevost.com）
* 支持 14 个平台：QQ、微信、支付宝、微博、百度、抖音、华为、小米、Google、Microsoft、Twitter、钉钉、Gitee、GitHub
* 新用户首次社会化登录时可自动创建 WordPress 账号
* 灵活的回调地址配置
* 用户可在个人资料页绑定/解绑社会化账号

**安装方法**

1. 将插件文件夹上传至 `/wp-content/plugins/Swift-Login`
2. 在 WordPress 后台「插件」页面激活插件
3. 进入「设置 > Swift Login」完成配置

**社会化登录配置步骤**

1. 前往 https://u.zevost.com 注册账号并创建应用
2. 获取 App ID 和 App Key
3. 在插件设置中填入 App ID 和 App Key
4. 在知我云后台将回调地址设置为插件设置页面中显示的地址
5. 勾选需要启用的登录平台

**短代码**

Swift Login 提供以下短代码，可嵌入任意页面、侧边栏或页面构建器中：

**[swift_passkey_button]**
输出 Passkey 登录按钮，适合嵌入自定义登录页面或侧边栏。

**[swift_social_buttons]**
输出社会化登录按钮组。需先在设置中启用社会化登录并完成配置。

示例用法：

    [swift_passkey_button]
    [swift_social_buttons]

也可在主题模板的登录表单中调用：

    do_action('login_form');

此函数会自动输出所有已启用的登录方式。

**常见问题**

**Passkey 登录在哪些浏览器上可用？**

Passkey 支持所有现代浏览器，包括 Chrome、Firefox、Safari 和 Edge。如果浏览器不支持 WebAuthn API，登录按钮会自动禁用。

**可以完全禁用用户名密码登录吗？**

可以。在插件设置中启用「禁用密码登录」选项后，所有用户只能通过 Passkey 或社会化登录访问网站。请确保在开启此选项前已成功设置 Passkey 或社会化登录，否则将无法登录。

**社会化登录是必须启用的吗？**

不是。社会化登录完全可选，默认为关闭状态。Passkey 登录和登录页面美化功能可独立使用。

**Passkey 数据存储在哪里？**

Passkey 凭据存储在您自己服务器的 `wp_swift_login_passkeys` 数据库表中，Passkey 认证过程不会向任何外部服务发送数据。

**社会化登录的用户数据如何处理？**

社会化登录通过知我云聚合登录接口（u.zevost.com）获取用户基本信息（昵称、头像等），绑定关系存储在 `wp_swift_login_social` 数据库表中。

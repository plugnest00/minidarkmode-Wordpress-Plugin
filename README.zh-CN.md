# Mini Dark Mode

[English](README.md) | [繁體中文](README.zh-TW.md) | 简体中文

**轻量到极致的深色模式切换：切换一个 class，加上一组 CSS 变量。**

Mini Dark Mode 为你的网站加上深色模式开关。它不对每张图片套滤镜、也不反转整个页面，而是在 `html` 元素上切换一个 class，再用一组小小的 CSS 变量重新上色文字、背景与界面组件。页面保持锐利、图片维持你原本设计的样子，你主题自己的样式也照常运作。

📦 **从 WordPress.org 安装**（上架后）· 👤 由 PlugNest 开发 · 🏠 [plugnest.dev](https://www.plugnest.dev/)

## 功能特色

- **不对图片套滤镜、不改 `src`**——相册、地图与照片完全不受影响。只有文字、背景与组件会重新上色。
- **不会闪白**——已保存的模式在页面 `<head>` 就套用，赶在第一次绘制之前。
- **六组配色预设**——玫瑰、海洋蓝、森林绿、紫罗兰、琥珀夕阳、石板灰，一键填满所有颜色字段，之后每个字段仍可自由修改。
- **17 个颜色控制项**——强调色、链接、标题、正文、边框、网站与内容背景，另附参考色盘。每个字段都会说明它影响网站的哪个部分。
- **浮动按钮或短代码**——左下或右下角的圆形／胶囊按钮；关掉浮动按钮后，也可用 `[minidarkmode]` 放在任何位置。
- **记住访客的选择**——以浏览器为单位存储。首次来访者会套用操作系统偏好，且在访客自行选择前会跟着系统实时变动。
- **无障碍**——真正的 `button` 元素、带 `aria-pressed` 状态、清楚的键盘焦点外框、已翻译的标签，并尊重 `prefers-reduced-motion`。
- **只作用于前台**——登录页与 `wp-admin` 完全不受影响。
- **不用写 CSS 也能微调**——选目标、选属性、选颜色。目标、属性与颜色在保存时各自比对固定清单验证，输出时再检查一次。
- **干净卸载**——可依需求清除设置。

## 兼容性

核心机制（一个 class 加 CSS 变量）适用于任何主题。内置调校过的样式支持 **Astra**、**Elementor**、**Spectra (UAGB)**、**Visual Portfolio**、**Modern Events Calendar** 与 **wpDiscuz**。其他主题可用颜色控制项与「高级微调」调整。

## 安装

1. 在 WordPress 后台前往**插件 → 安装插件**，搜索「Mini Dark Mode」（或上传 zip）。
2. 启用。
3. 在**设置 → Mini Dark Mode** 调整颜色与按钮。这样就设置完成了。

## 常见问题

**它会让图片变暗或反转吗？** 不会——这正是重点。对图片套滤镜会让照片变浊，还会弄坏相册与地图。Mini Dark Mode 只重新上色文字、背景与界面组件。

**深色模式套用前会先闪一下白画面吗？** 不会。页面 `<head>` 里的一小段 inline script 会在第一次绘制前就套用已保存的模式。

**跟我的主题兼容吗？** 核心机制适用于任何主题。上面列出的主题与插件有内置调校过的样式；其他主题可用颜色控制项调整。

**可以把切换钮放在菜单或页面里吗？** 可以。关掉浮动按钮后，把 `[minidarkmode]` 短代码放在任何地方——菜单、页脚、Elementor 短代码小工具等。

**我可以自己写样式吗？** 可以。深色模式开启时，`html` 元素会带有 `mdm-dark-mode` class 与 `data-mdm-dark-mode="dark"` 属性。每个颜色都以 CSS 自定义属性公开：`--mdm-accent`、`--mdm-link`、`--mdm-link-hover`、`--mdm-heading`、`--mdm-text`、`--mdm-border`、`--mdm-bg`、`--mdm-surface`，以及 `--mdm-palette-*` 参考色值。

**会影响 wp-admin 吗？** 不会。Mini Dark Mode 只作用于前台。

## 授权

GPL-2.0-or-later 或更新版本。详见 [LICENSE](LICENSE)。

© PlugNest — [plugnest.dev](https://www.plugnest.dev/)

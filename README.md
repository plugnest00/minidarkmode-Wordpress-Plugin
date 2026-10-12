# Mini Dark Mode

English | [繁體中文](README.zh-TW.md) | [简体中文](README.zh-CN.md)

**A featherweight dark mode toggle: one class switch plus CSS variables.**

Mini Dark Mode adds a dark mode switch to your site. Instead of running a filter over every image or inverting the whole page, it toggles one class on the `html` element and lets a small set of CSS variables recolor text, backgrounds and interface components. Pages stay sharp, images stay exactly as you designed them, and your theme's own styles keep working.

📦 **Install from WordPress.org** (once published) · 👤 By PlugNest · 🏠 [plugnest.dev](https://www.plugnest.dev/)

## Highlights

- **No image filters, no `src` swapping** — galleries, maps and photos are never touched. Only text, backgrounds and components are recolored.
- **No flash of light content** — the saved mode is applied in the page head, before the first paint.
- **Six color presets** — Rose, Ocean Blue, Forest Green, Violet Purple, Amber Sunset and Slate Mono fill every color control in one click, and each field stays editable afterwards.
- **17 color controls** — accent, links, headings, body text, borders, site and content backgrounds, plus a reference palette. Every field explains which parts of your site it affects.
- **Floating button or shortcode** — a circle or pill button in the bottom-left or bottom-right corner, or place `[minidarkmode]` anywhere with the floating button turned off.
- **Remembers the visitor's choice** — stored per browser. First-time visitors get their operating system preference, and it follows live OS changes until they pick a side.
- **Accessible** — a real `button` element with `aria-pressed`, a visible focus ring, translated labels, and no motion for `prefers-reduced-motion` users.
- **Front end only** — the login page and `wp-admin` are never affected.
- **Advanced tweaks without writing CSS** — pick a target, a property and a color. Targets, properties and colors are each validated against a fixed list on save and re-checked on output.
- **Clean uninstall** — removes its options on request.

## Compatibility

The core mechanism (one class plus CSS variables) works with any theme. Tuned rules ship for **Astra**, **Elementor**, **Spectra (UAGB)**, **Visual Portfolio**, **Modern Events Calendar** and **wpDiscuz**. Anything else can be adjusted through the color controls and the Advanced tweaks area.

## Installation

1. In your WordPress admin, go to **Plugins → Add New** and search for "Mini Dark Mode" (or upload the zip).
2. Activate.
3. Configure colors and the button under **Settings → Mini Dark Mode**. That is the whole setup.

## Frequently asked questions

**Does it dim or invert images?** No — that is the point. Filters over images make photos muddy and break galleries and maps. Mini Dark Mode only recolors text, backgrounds and interface components.

**Will there be a flash of light content before dark mode applies?** No. A tiny inline script in the page head applies the saved mode before the first paint.

**Does it work with my theme?** The core mechanism works with any theme. Tuned rules ship for the themes and plugins listed above; anything else can be adjusted with the color controls.

**Can I place the toggle in my menu or a page?** Yes. Turn off the floating button and place the `[minidarkmode]` shortcode anywhere — menus, footers, an Elementor shortcode widget, and so on.

**Can I style things myself?** Yes. While dark mode is on, the `html` element carries the class `mdm-dark-mode` and the attribute `data-mdm-dark-mode="dark"`. Every color is exposed as a CSS custom property: `--mdm-accent`, `--mdm-link`, `--mdm-link-hover`, `--mdm-heading`, `--mdm-text`, `--mdm-border`, `--mdm-bg`, `--mdm-surface`, plus the `--mdm-palette-*` reference values.

**Does it affect wp-admin?** No. Mini Dark Mode is front-end only.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).

© PlugNest — [plugnest.dev](https://www.plugnest.dev/)

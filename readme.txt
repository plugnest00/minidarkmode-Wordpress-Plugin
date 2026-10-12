=== Mini Dark Mode ===
Contributors: plugnest
Tags: dark mode, night mode, dark theme, accessibility, darkmode
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.2
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A featherweight dark mode toggle: one class switch plus CSS variables. No image filters — works alongside gallery and portfolio plugins.

== Description ==

Mini Dark Mode adds a dark mode switch to your site. Instead of running filters over every image or inverting your whole page (approaches that can break galleries, maps and photos), it toggles one class on the html element and lets a small set of CSS variables recolor text, backgrounds and components. Pages stay sharp, images stay exactly as you designed them, and your theme's own styles keep working.

**What it does**

* Floating toggle button (bottom left or right) or a pill button with a text label — colors follow your accent color
* `[minidarkmode]` shortcode to place the toggle anywhere (menu, footer, page builder) — turn the floating button off and use the shortcode alone if you prefer
* Remembers each visitor's choice in their browser (localStorage)
* Falls back to the operating system preference (prefers-color-scheme) for first-time visitors, and follows OS changes live until the visitor picks a side
* Applies the saved mode before first paint — no flash of light content
* Optional keyboard shortcut: Ctrl+Alt+D
* Optional transparent header in dark mode (works out of the box with Astra; includes the generic header class)
* Elementor, Spectra (UAGB), Visual Portfolio, MEC events and wpDiscuz get dedicated dark styling
* Respect for prefers-reduced-motion: transitions are disabled for visitors who ask for less motion
* No dark mode is applied to the login page or wp-admin — front end only
* Fully internationalized; translations are delivered through translate.wordpress.org

**Color controls**

Six built-in presets (Rose, Ocean Blue, Forest Green, Violet Purple, Amber Sunset, Slate Mono) fill every color control in one click, and every field stays editable afterwards — each field explains which parts of your site it affects. Full control over the dark palette from Settings → Mini Dark Mode: accent, links, headings, body text, borders, site and content backgrounds, plus a reference palette. Text that you colored inline with your accent color is kept in the accent color instead of being brightened. One option keeps in-content headings in your accent color for sites whose light theme already uses colored content headings.

**Fine-tuning**

A dedicated "Advanced tweaks" area for per-site fine-tuning: pick a target (site header, site footer, sidebar, post content, widgets, tables, form fields, blockquotes, code blocks, links), pick a property (background, text or border colour) and pick a colour. Every rule you add applies only while dark mode is on. Targets, properties and colours are each validated against a fixed list on save and re-checked on output, so the generated stylesheet can only ever contain the declarations the plugin itself defines.

**Accessibility**

The button is a real button element with aria-pressed state, a visible focus ring for keyboard users, and translated labels. No motion for reduced-motion users, no layout shifts, no autoplaying anything.

= Credits =

* Inspired by [WP Dark Mode](https://wordpress.org/plugins/wp-dark-mode/) by WPPOOL.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/`, or install the zip via Plugins → Add New.
2. Activate.
3. Configure colors and the button under Settings → Mini Dark Mode. That's it — no other setup.

== Frequently Asked Questions ==

= Does it dim or invert images? =

No — that is the point. Filters over images make photos muddy and break galleries and maps. Mini Dark Mode only recolors text, backgrounds and interface components through CSS variables. Your images render exactly as authored.

= Will there be a flash of light content before dark mode applies? =

No. A tiny inline script in the page head applies the saved mode before the first paint.

= Does it work with my theme? =

The core mechanism (one class + CSS variables) works with any theme. Ships with tuned rules for Astra, Elementor, Spectra/UAGB, Visual Portfolio, MEC and wpDiscuz. Anything else can be adjusted with the colour controls and the Advanced tweaks area.

= Can I place the toggle in my menu or a page? =

Yes. Turn off the floating button in the settings and place the `[minidarkmode]` shortcode anywhere — menus, footers, Elementor shortcodes widget, etc.

= Does it remember the visitor's choice? =

Yes, per browser via localStorage. Visitors who never chose get their operating system's color scheme preference.

= Can I style things myself? =

Yes. While dark mode is on, the `html` element carries the class `mdm-dark-mode` and the attribute `data-mdm-dark-mode="dark"`. Every colour is exposed as a CSS custom property: `--mdm-accent`, `--mdm-link`, `--mdm-link-hover`, `--mdm-heading`, `--mdm-text`, `--mdm-border`, `--mdm-bg`, `--mdm-surface`, plus the reference palette values `--mdm-palette-*`. The toggle button itself is `.minidarkmode-toggle`.

= Does it affect wp-admin? =

No. Mini Dark Mode is front-end only.

== Screenshots ==

1. Settings → Mini Dark Mode. Toggle button placement and style, keyboard shortcut, and one-click color presets.
2. The dark palette. Reference colors, theme colors (accent, links, headings, body text, borders) and surface colors, each with a per-field note explaining what it affects.
3. Advanced tweaks. Pick a target, a property and a color — no CSS needed, and only whitelisted values can be saved.
4. The same page in light and dark mode. Text, backgrounds and components recolor; images are left exactly as authored.

== Changelog ==

= 1.0 =
* Initial release.
* Featherweight dark mode: toggles one class plus CSS variables — no image filters, no src swapping, so gallery and portfolio plugins keep rendering images as they always did.
* No flash of light content: the saved mode is applied in the page head before first paint; follows the OS color scheme until the visitor chooses.
* Six color presets (Rose, Ocean Blue, Forest Green, Violet Purple, Amber Sunset, Slate Mono) — each becomes the restore baseline; per-color restore (↺) buttons and per-field usage notes.
* 17 color controls: accent, links, headings, body text, borders, site/content backgrounds and a reference palette; the toggle button follows your accent.
* Floating toggle (circle or pill) or [minidarkmode] shortcode placement; optional Ctrl+Alt+D shortcut.
* Dedicated dark styling for Astra, Elementor, Spectra/UAGB, Visual Portfolio, MEC and wpDiscuz; dark-mode-only Advanced tweaks area.
* Transparent-header option, in-content accent headings option, reduced-motion support, focus ring.
* English base strings, fully internationalized for translation on translate.wordpress.org.
* Whitelist-based Advanced tweaks handling (targets, properties and colours validated on save and re-checked on output) and uninstall cleanup.

== Upgrade Notice ==

= 1.0 =
Initial release.

=== aGo Access ===
Contributors: agolab
Donate link: https://paypal.me/sixtovaldes
Tags: accessibility, wcag, a11y, toolbar, ada
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Accessibility toolbar with 11 tools and 7 automatic fixes. WCAG 2.2 oriented, 100% free, no limitations.

== Description ==

aGo Access adds a floating accessibility toolbar to your WordPress site, giving visitors control over their browsing experience. It also applies automatic background fixes to improve accessibility without any visible change.

**100% free. No premium. No limitations. No upsells.**

= Toolbar Tools (11) =

* **Text Size**, Increase or decrease font size
* **Contrast Modes**, High contrast, inverted colors, dark mode
* **Dyslexia-Friendly Font**, Switch to OpenDyslexic
* **Text Spacing**, Increase line height and letter spacing
* **Big Cursor**, Larger, more visible mouse cursor
* **Reading Guide**, Horizontal line follows the mouse
* **Highlight Links**, Make all links visually obvious
* **Stop Animations**, Pause GIFs, videos, CSS animations
* **Grayscale**, Remove all colors
* **Hide Images**, Focus on text content only
* **Read Aloud**, Text-to-speech using Web Speech API (no API cost)

= Automatic Background Fixes (7) =

* Skip-to-content link for keyboard navigation
* Focus visible outline for keyboard users
* ARIA landmarks for screen readers
* Language attribute on HTML tag
* Form labels verification
* Alt text warnings in admin
* Positive tabindex correction

= Additional Features =

* Accessibility score scanner (checks homepage for issues)
* 6 button positions (top/middle/bottom × left/right)
* 2 button shapes (circle, rounded rectangle)
* 4 icon options (person, wheelchair, eye, universal access)
* Customizable color
* Visitor preferences saved in localStorage
* Lightweight (< 10KB CSS+JS)
* Translations: English, Spanish, Portuguese

= WCAG 2.2 =

This plugin helps address several WCAG 2.2 success criteria including perceivable content, operable navigation, and understandable interfaces. It is not a complete compliance solution but significantly improves accessibility for most sites.

== Installation ==

1. Upload the `ago-access` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu
3. Go to aGo Herramientas → Accessibility
4. The toolbar is enabled by default, visit your site to see it
5. Customize position, shape, icon, and tools as needed

== Frequently Asked Questions ==

= Is this really 100% free? =

Yes. No premium version, no upsells, no limitations. All 11 tools and 7 fixes are available to everyone.

= Does this make my site WCAG compliant? =

It helps significantly but no single plugin can guarantee full compliance. Use it alongside good coding practices and manual testing.

= Does text-to-speech cost anything? =

No. It uses the browser's built-in Web Speech API. No external API, no cost.

= Are visitor preferences saved? =

Yes, in the browser's localStorage. Each visitor's settings persist across page loads and visits.

= Does it slow down my site? =

No. The toolbar CSS and JS together are under 10KB. The OpenDyslexic font only loads when activated.

== External services ==

This plugin does not connect to any third-party service. The optional accessibility scanner sends a single HTTP request to your own site's homepage (the same URL returned by home_url()) to analyze its markup locally on your server. No data ever leaves your installation. The dyslexia-friendly font (OpenDyslexic) and all toolbar assets are bundled with the plugin and served from your own domain. OpenDyslexic is licensed under the SIL Open Font License 1.1 (GPL-compatible).

== Privacy ==

* Plugin settings are stored locally in the wp_options table (option agoaccess_settings).
* Visitor toolbar preferences are stored only in the visitor's own browser localStorage. They are never sent to the server or to any third party.
* Read Aloud uses the browser's built-in Web Speech API; no audio or text is sent to an external service.
* On uninstall, the agoaccess_settings option is removed.

== Screenshots ==

1. Floating accessibility toolbar on the frontend
2. Toolbar panel open with all tools
3. Admin settings page with accessibility score
4. Scanner results showing detected issues

== Changelog ==

= 1.0.0 =
* Initial release.
* 11 accessibility tools in floating toolbar.
* 7 automatic background fixes.
* Accessibility score scanner.
* 6 positions, 2 shapes, 4 icons.
* Translations: English, Spanish, Brazilian Portuguese.

== Upgrade Notice ==

= 1.0.0 =
Initial release.

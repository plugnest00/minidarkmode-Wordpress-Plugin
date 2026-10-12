<?php
/**
 * Plugin Name: Mini Dark Mode
 * Description: A featherweight dark mode toggle: switches one class and a set of CSS variables. No image filters, no src swapping — works alongside gallery and portfolio plugins. Color controls, floating or inline toggle button, keyboard shortcut and shortcode included.
 * Version: 1.0
 * Author: PlugNest
 * Author URI: https://plugnest.dev
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: minidarkmode
 * Requires at least: 6.0
 * Requires PHP: 7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MINIDARKMODE_VERSION', '1.0' );

final class Mini_Dark_Mode {

	private static $instance;

	/**
	 * Memoised preset_map() result; see preset_map().
	 *
	 * @var array|null
	 */
	private $preset_map_cache = null;

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_shortcode' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'custom_css' ), 99 );
		add_action( 'wp_head', array( $this, 'bootstrap_script' ), 1 );
		add_action( 'wp_footer', array( $this, 'toggle_button' ), 20 );
	}

	/* ---------------- Settings ---------------- */

	public function register_shortcode() {
		add_shortcode( 'minidarkmode', array( $this, 'shortcode_toggle' ) );
	}

	public function admin_menu() {
		add_options_page(
			__( 'Mini Dark Mode', 'minidarkmode' ),
			__( 'Mini Dark Mode', 'minidarkmode' ),
			'manage_options',
			'minidarkmode',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Loads the settings page CSS and JS through the enqueue API.
	 *
	 * Both are attached to source-less handles that are only enqueued on this
	 * plugin's own settings screen, so nothing leaks into other admin pages.
	 * The preset colour map travels to the browser through
	 * wp_localize_script(), not by being printed inside a <script> block.
	 */
	public function admin_assets( $hook ) {
		if ( 'settings_page_minidarkmode' !== $hook ) {
			return;
		}

		wp_register_style( 'minidarkmode-admin', false, array(), MINIDARKMODE_VERSION );
		wp_enqueue_style( 'minidarkmode-admin' );
		wp_add_inline_style( 'minidarkmode-admin', '.mdm-preset--active{box-shadow:inset 0 0 0 2px var(--wp-admin-theme-color,#2271b1)!important;}' );

		wp_register_script( 'minidarkmode-admin-js', false, array(), MINIDARKMODE_VERSION, true );
		wp_enqueue_script( 'minidarkmode-admin-js' );

		$presets    = $this->preset_map();
		$presets_js = wp_json_encode( $presets );

		$script = 'document.addEventListener("click",function(e){'
			. 'var t=e.target,form=document.querySelector("form");'
			. 'if(!form||!t||!t.closest){return;}'
			. 'function fill(k,v){'
			. 'var ps=form.querySelectorAll("input[name=\'"+k+"\']");'
			. 'for(var i=0;i<ps.length;i++){ps[i].value=v;}'
			. 'var hx=form.querySelector("input[data-hex-for=\'"+k+"\']");'
			. 'if(hx){hx.value=v;}'
			. '}'
			. 'var adv=e.target&&e.target.closest?e.target.closest(".mdm-adv-add,.mdm-adv-remove"):null;'
			. 'if(adv){'
			. 'e.preventDefault();'
			. 'var wrap=document.getElementById("mdm-advanced-rows");'
			. 'if(adv.classList.contains("mdm-adv-remove")){'
			. 'var row=adv.closest(".mdm-adv-row");'
			. 'if(row&&wrap&&wrap.children.length>1){row.parentNode.removeChild(row);}'
			. '}else if(wrap){'
			. 'var last=wrap.children[wrap.children.length-1];'
			. 'if(last&&wrap.children.length<30){wrap.appendChild(last.cloneNode(true));}'
			. '}'
			. 'return;'
			. '}'
			. 'var p=t.closest(".mdm-preset");'
			. 'if(p){'
			. 'var set=' . $presets_js . ';'
			. 'set=set[p.getAttribute("data-preset")];'
			. 'if(set){'
			. 'e.preventDefault();'
			. 'var hid=document.getElementById("mdm-active-preset");'
			. 'if(hid){hid.value=p.getAttribute("data-preset");}'
			. 'var cs=document.querySelectorAll(".mdm-preset");'
			. 'for(var c2=0;c2<cs.length;c2++){cs[c2].classList.remove("mdm-preset--active");}'
			. 'p.classList.add("mdm-preset--active");'
			. 'for(var k in set){'
			. 'if(!Object.prototype.hasOwnProperty.call(set,k)){continue;}'
			. 'fill(k,set[k]);'
			. 'var rb=form.querySelector(".mdm-reset[data-key=\'"+k+"\']");'
			. 'if(rb){rb.setAttribute("data-default",set[k]);rb.innerHTML="\\u21ba "+set[k];}'
			. '}'
			. 'return;'
			. '}'
			. '}'
			. 'var r=t.closest(".mdm-reset");'
			. 'if(r){e.preventDefault();fill(r.getAttribute("data-key"),r.getAttribute("data-default"));}'
			. '});'
			. 'function mdmSync(){'
			. 'var wrap=document.getElementById("mdm-advanced-rows"),'
			. 'out=document.getElementById("mdm-advanced-value");'
			. 'if(!wrap||!out){return;}'
			. 'var rows=[];'
			. 'var list=wrap.querySelectorAll(".mdm-adv-row");'
			. 'for(var i=0;i<list.length;i++){'
			. 'var s=list[i].querySelector(".mdm-adv-sel"),'
			. 'pr=list[i].querySelector(".mdm-adv-prop"),'
			. 'co=list[i].querySelector(".mdm-adv-color");'
			. 'if(s&&pr&&co){rows.push({sel:s.value,prop:pr.value,color:co.value});}'
			. '}'
			. 'out.value=JSON.stringify(rows);'
			. '}'
			. 'document.addEventListener("change",function(e){'
			. 'var t2=e.target;'
			. 'if(t2&&t2.closest&&t2.closest(".mdm-adv-row")){mdmSync();}'
			. '});'
			. 'document.addEventListener("click",function(e){'
			. 'var t3=e.target;'
			. 'if(t3&&t3.closest&&t3.closest(".mdm-adv-add,.mdm-adv-remove")){mdmSync();}'
			. '});'
			. 'var f=document.querySelector("form");'
			. 'if(f){f.addEventListener("submit",mdmSync);}'
			. 'mdmSync();';

		wp_add_inline_script( 'minidarkmode-admin-js', $script );
	}

	/**
	 * Builds preset id => { color option key => hex } for the six presets.
	 * Shared by the settings page markup, the admin script and the front-end
	 * fallback colours.
	 *
	 * The result is memoised: the data is static, but default_color() asks
	 * for it once per colour key on every front-end request.
	 */
	private function preset_map() {
		if ( null !== $this->preset_map_cache ) {
			return $this->preset_map_cache;
		}
		$keys    = array_keys( $this->color_defaults() );
		$presets = $this->presets();
		$map     = array();
		foreach ( $presets as $id => $preset ) {
			$map[ $id ] = array_combine( $keys, $preset['colors'] );
		}
		$this->preset_map_cache = $map;
		return $this->preset_map_cache;
	}

	/**
	 * The six built-in colour presets, each a full 17-colour set in
	 * color_defaults() order.
	 */
	private function presets() {
		return array(
			'rose'   => array(
				'label'  => __( 'Rose (default)', 'minidarkmode' ),
				'colors' => array_values( $this->color_defaults() ),
			),
			'ocean'  => array(
				'label'  => __( 'Ocean Blue', 'minidarkmode' ),
				'colors' => array( '#4f9cf9', '#8cc0ff', '#eef2f8', '#d5dce6', '#16202e', '#1d2a3a', '#1a2534', '#101823', '#2c3e52', '#4f9cf9', '#4f9cf9', '#8cc0ff', '#eef2f8', '#d5dce6', '#2c3e52', '#101823', '#16202e' ),
			),
			'forest' => array(
				'label'  => __( 'Forest Green', 'minidarkmode' ),
				'colors' => array( '#4caf87', '#7fd4ae', '#eef6f0', '#d4e2d8', '#16211b', '#1d2b23', '#1a271f', '#101a14', '#2c4033', '#4caf87', '#4caf87', '#7fd4ae', '#eef6f0', '#d4e2d8', '#2c4033', '#101a14', '#16211b' ),
			),
			'violet' => array(
				'label'  => __( 'Violet Purple', 'minidarkmode' ),
				'colors' => array( '#a78bfa', '#c9b8ff', '#f2effa', '#dcd6ea', '#1b1726', '#241e33', '#201a2c', '#130f1c', '#362d4a', '#a78bfa', '#a78bfa', '#c9b8ff', '#f2effa', '#dcd6ea', '#362d4a', '#130f1c', '#1b1726' ),
			),
			'amber'  => array(
				'label'  => __( 'Amber Sunset', 'minidarkmode' ),
				'colors' => array( '#e8a04c', '#ffc47d', '#f7f2ea', '#e3d9c9', '#221c14', '#2d251a', '#271f16', '#17120c', '#453624', '#e8a04c', '#e8a04c', '#ffc47d', '#f7f2ea', '#e3d9c9', '#453624', '#17120c', '#221c14' ),
			),
			'slate'  => array(
				'label'  => __( 'Slate Mono', 'minidarkmode' ),
				'colors' => array( '#8fa3b8', '#b8c9da', '#f2f4f6', '#d0d6dc', '#1a1d21', '#232629', '#1f2225', '#121417', '#33383e', '#8fa3b8', '#8fa3b8', '#b8c9da', '#f2f4f6', '#d0d6dc', '#33383e', '#121417', '#1a1d21' ),
			),
		);
	}

	/**
	 * Option key => default color, in a fixed order. These are the "Rose"
	 * preset values (the factory default); the other presets live in
	 * render_page().
	 */
	private function color_defaults() {
		return array(
			'minidarkmode_palette_brand'        => '#e878a0',
			'minidarkmode_palette_brand_alt'    => '#ff9ec0',
			'minidarkmode_palette_heading'      => '#f0f0f8',
			'minidarkmode_palette_text'         => '#d9d9e3',
			'minidarkmode_palette_bg_primary'   => '#1d1d28',
			'minidarkmode_palette_bg_secondary' => '#262633',
			'minidarkmode_palette_bg_subtle'    => '#22222e',
			'minidarkmode_palette_bg_alt'       => '#14141b',
			'minidarkmode_palette_other'        => '#33334a',
			'minidarkmode_theme_accent'         => '#e878a0',
			'minidarkmode_theme_link'           => '#e878a0',
			'minidarkmode_theme_link_hover'     => '#ff9ec0',
			'minidarkmode_theme_heading'        => '#f0f0f8',
			'minidarkmode_theme_text'           => '#d9d9e3',
			'minidarkmode_theme_border'         => '#33334a',
			'minidarkmode_surface_bg'           => '#14141b',
			'minidarkmode_surface_content'      => '#1d1d28',
		);
	}

	public function register_settings() {
		$bool = function ( $value ) {
			return '1' === (string) $value ? '1' : '0';
		};

		register_setting( 'minidarkmode', 'minidarkmode_position', array( 'type' => 'string', 'default' => 'left', 'sanitize_callback' => array( $this, 'sanitize_position' ) ) );
		register_setting( 'minidarkmode', 'minidarkmode_preset', array( 'type' => 'string', 'default' => 'rose', 'sanitize_callback' => array( $this, 'sanitize_preset_id' ) ) );
		register_setting( 'minidarkmode', 'minidarkmode_button_style', array( 'type' => 'string', 'default' => 'circle', 'sanitize_callback' => array( $this, 'sanitize_style' ) ) );
		register_setting( 'minidarkmode', 'minidarkmode_show_icon', array( 'type' => 'string', 'default' => '1', 'sanitize_callback' => $bool ) );
		register_setting( 'minidarkmode', 'minidarkmode_floating', array( 'type' => 'string', 'default' => '1', 'sanitize_callback' => $bool ) );
		register_setting( 'minidarkmode', 'minidarkmode_shortcut', array( 'type' => 'string', 'default' => '0', 'sanitize_callback' => $bool ) );
		register_setting( 'minidarkmode', 'minidarkmode_accent_headings', array( 'type' => 'string', 'default' => '0', 'sanitize_callback' => $bool ) );
		register_setting( 'minidarkmode', 'minidarkmode_header_transparent', array( 'type' => 'string', 'default' => '1', 'sanitize_callback' => $bool ) );
		register_setting( 'minidarkmode', 'minidarkmode_advanced', array( 'type' => 'string', 'default' => '', 'sanitize_callback' => array( $this, 'sanitize_advanced' ) ) );

		$reg_color = function ( $key, $default ) {
			register_setting( 'minidarkmode', $key, array(
				'type'              => 'string',
				'default'           => $default,
				'sanitize_callback' => array( $this, 'sanitize_color' ),
			) );
		};

		// Palette, theme and surface colors share one defaults map.
		foreach ( $this->color_defaults() as $key => $default ) {
			$reg_color( $key, $default );
		}
	}

	public function sanitize_position( $value ) {
		return in_array( $value, array( 'left', 'right' ), true ) ? $value : 'left';
	}

	public function sanitize_style( $value ) {
		return in_array( $value, array( 'circle', 'pill' ), true ) ? $value : 'circle';
	}

	private function preset_ids() {
		return array( 'rose', 'ocean', 'forest', 'violet', 'amber', 'slate' );
	}

	/**
	 * The remembered preset is the restore baseline for the ↺ buttons.
	 */
	public function sanitize_preset_id( $value ) {
		return in_array( (string) $value, $this->preset_ids(), true ) ? (string) $value : 'rose';
	}

	/**
	 * Only valid CSS hex colors pass; anything else is dropped. The same
	 * pattern is re-checked on output.
	 */
	public function sanitize_color( $value ) {
		if ( is_string( $value ) && preg_match( '/^#(?:[0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $value ) ) {
			return $value;
		}
		return '';
	}

	/**
	 * The selectors the "Advanced tweaks" form can target. Anything the user
	 * picks is mapped to one of these fixed strings, so a stored value can
	 * never contain arbitrary CSS selectors.
	 */
	public function selector_options() {
		return array(
			'site-header'  => __( 'Site header', 'minidarkmode' ),
			'site-footer'  => __( 'Site footer', 'minidarkmode' ),
			'sidebar'      => __( 'Sidebar / widget area', 'minidarkmode' ),
			'post-content' => __( 'Post & page content', 'minidarkmode' ),
			'widgets'      => __( 'Widgets', 'minidarkmode' ),
			'tables'       => __( 'Tables', 'minidarkmode' ),
			'forms'        => __( 'Form fields', 'minidarkmode' ),
			'blockquote'   => __( 'Blockquotes', 'minidarkmode' ),
			'code'         => __( 'Code blocks', 'minidarkmode' ),
			'links'        => __( 'Links', 'minidarkmode' ),
		);
	}

	/**
	 * The properties the "Advanced tweaks" form can set.
	 */
	public function property_options() {
		return array(
			'background' => __( 'Background color', 'minidarkmode' ),
			'color'      => __( 'Text color', 'minidarkmode' ),
			'border'     => __( 'Border color', 'minidarkmode' ),
		);
	}

	/**
	 * Maps an option id to the actual CSS selector used in the generated
	 * stylesheet. Every value is a hard-coded string owned by the plugin.
	 */
	private function selector_css( $id ) {
		$map = array(
			'site-header'  => 'html.mdm-dark-mode .site-header,html.mdm-dark-mode .main-header-bar,html.mdm-dark-mode .ast-above-header,html.mdm-dark-mode .ast-below-header',
			'site-footer'  => 'html.mdm-dark-mode .site-footer,html.mdm-dark-mode .ast-footer-copyright',
			'sidebar'      => 'html.mdm-dark-mode .sidebar,html.mdm-dark-mode .widget-area,html.mdm-dark-mode aside',
			'post-content' => 'html.mdm-dark-mode .entry-content,html.mdm-dark-mode .post-content,html.mdm-dark-mode .page-content',
			'widgets'      => 'html.mdm-dark-mode .widget',
			'tables'       => 'html.mdm-dark-mode table,html.mdm-dark-mode th,html.mdm-dark-mode td',
			'forms'        => 'html.mdm-dark-mode input,html.mdm-dark-mode textarea,html.mdm-dark-mode select',
			'blockquote'   => 'html.mdm-dark-mode blockquote',
			'code'         => 'html.mdm-dark-mode pre,html.mdm-dark-mode code',
			'links'        => 'html.mdm-dark-mode a',
		);
		return isset( $map[ $id ] ) ? $map[ $id ] : '';
	}

	/**
	 * Maps a property id to the CSS declaration that is emitted.
	 */
	private function property_css( $id, $color ) {
		$map = array(
			'background' => 'background-color:' . $color . '!important;',
			'color'      => 'color:' . $color . '!important;',
			'border'     => 'border-color:' . $color . '!important;',
		);
		return isset( $map[ $id ] ) ? $map[ $id ] : '';
	}

	/**
	 * Validates the "Advanced tweaks" rows.
	 *
	 * Each row is reduced to three whitelisted values: a selector id, a
	 * property id and a hex colour. Rows that do not survive validation are
	 * dropped, so the stored option can never contain free-form CSS.
	 */
	public function sanitize_advanced( $value ) {
		$rows = json_decode( (string) $value, true );
		if ( ! is_array( $rows ) ) {
			return '';
		}
		$selectors = array_keys( $this->selector_options() );
		$props     = array_keys( $this->property_options() );
		$clean     = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$sel  = isset( $row['sel'] ) ? (string) $row['sel'] : '';
			$prop = isset( $row['prop'] ) ? (string) $row['prop'] : '';
			$col  = isset( $row['color'] ) ? $this->sanitize_color( $row['color'] ) : '';
			if ( ! in_array( $sel, $selectors, true ) || ! in_array( $prop, $props, true ) || '' === $col ) {
				continue;
			}
			$clean[] = array( 'sel' => $sel, 'prop' => $prop, 'color' => $col );
			if ( count( $clean ) >= 30 ) {
				break;
			}
		}
		return $clean ? wp_json_encode( $clean ) : '';
	}

	/**
	 * Reads the stored advanced rows back as an array.
	 */
	private function get_advanced_rows() {
		$rows = json_decode( (string) get_option( 'minidarkmode_advanced', '' ), true );
		if ( ! is_array( $rows ) ) {
			return array();
		}
		$selectors = array_keys( $this->selector_options() );
		$props     = array_keys( $this->property_options() );
		$out       = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$sel  = isset( $row['sel'] ) ? (string) $row['sel'] : '';
			$prop = isset( $row['prop'] ) ? (string) $row['prop'] : '';
			$col  = isset( $row['color'] ) ? $this->sanitize_color( $row['color'] ) : '';
			if ( in_array( $sel, $selectors, true ) && in_array( $prop, $props, true ) && '' !== $col ) {
				$out[] = array( 'sel' => $sel, 'prop' => $prop, 'color' => $col );
			}
		}
		return $out;
	}

	/**
	 * Turns the stored advanced rows into CSS. Selectors and properties come
	 * from plugin-owned whitelists, colours are re-validated hex values, so
	 * the result is safe by construction.
	 */
	private function advanced_css() {
		$css = '';
		foreach ( $this->get_advanced_rows() as $row ) {
			$selector = $this->selector_css( $row['sel'] );
			$decl     = $this->property_css( $row['prop'], $row['color'] );
			if ( $selector && $decl ) {
				$css .= $selector . '{' . $decl . '}';
			}
		}
		return $css;
	}

	/**
	 * Reads a colour option. Only a valid CSS hex value passes; anything
	 * else falls through to $default.
	 *
	 * When no explicit default is given the active preset colour is used, so
	 * a site that has never opened the settings screen still renders the full
	 * variable set. That matters because get_option() only falls back to a
	 * register_setting() default when it is called *without* a second
	 * argument — get_option( $key, '' ) would otherwise return '' forever and
	 * the front end would emit no colour variables at all until the settings
	 * form was saved once.
	 */
	private function get_color( $key, $default = '' ) {
		$val = get_option( $key, '' );
		if ( is_string( $val ) && preg_match( '/^#(?:[0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $val ) ) {
			return $val;
		}
		if ( '' === $default ) {
			$default = $this->default_color( $key );
		}
		return $default;
	}

	/**
	 * The colour a key should use when no valid value has been saved: the
	 * active preset's colour for that key, or the factory default.
	 */
	private function default_color( $key ) {
		$map = $this->preset_map();
		$id  = $this->active_preset_id();
		if ( isset( $map[ $id ][ $key ] ) ) {
			return $map[ $id ][ $key ];
		}
		$defaults = $this->color_defaults();
		return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	}

	/**
	 * The preset currently in use, resolved safely.
	 *
	 * Shared by the settings screen (which swatch is marked active and what
	 * the ↺ buttons restore) and the front end (the fallback colour for
	 * unsaved options), so the two can never disagree.
	 */
	private function active_preset_id() {
		$stored = (string) get_option( 'minidarkmode_preset', 'rose' );
		return in_array( $stored, $this->preset_ids(), true ) ? $stored : 'rose';
	}

	private function get_bool( $key, $default ) {
		$val = get_option( $key, $default );
		return '1' === (string) $val;
	}

	/**
	 * Expands a validated hex color to its 6-digit form plus RGB channels.
	 */
	private function accent_rgb( $hex ) {
		$h = ltrim( strtolower( $hex ), '#' );
		if ( 3 === strlen( $h ) || 4 === strlen( $h ) ) {
			$h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
		}
		$h = substr( $h, 0, 6 );
		if ( 6 !== strlen( $h ) ) {
			return false;
		}
		return array(
			'hex' => '#' . $h,
			'r'   => hexdec( substr( $h, 0, 2 ) ),
			'g'   => hexdec( substr( $h, 2, 2 ) ),
			'b'   => hexdec( substr( $h, 4, 2 ) ),
		);
	}

	/* ---------------- Admin page ---------------- */

	public function render_page() {
		// Presets are full 17-color sets in color_defaults() order; the admin
		// script fills the form from them, the user then saves as usual.
		// The remembered preset is the restore baseline: the ↺ buttons and
		// the field fallbacks take their colors from it, so each preset has
		// its own defaults.
		$presets       = $this->presets();
		$preset_map    = $this->preset_map();
		$active_id     = $this->active_preset_id();
		$active_colors = $preset_map[ $active_id ];

		$color_field = function ( $key, $label, $desc = '' ) use ( $active_colors ) {
			$val     = $this->get_color( $key, isset( $active_colors[ $key ] ) ? $active_colors[ $key ] : '' );
			$default = isset( $active_colors[ $key ] ) ? $active_colors[ $key ] : '';
			$uid     = $key . '_' . uniqid();
			?>
			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $uid ); ?>"><?php echo esc_html( $label ); ?></label></th>
				<td>
					<input type="color" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $uid ); ?>_picker" value="<?php echo esc_attr( $val ); ?>"
						oninput="document.getElementById('<?php echo esc_js( $uid ); ?>_hex').value=this.value">
					<input type="text" id="<?php echo esc_attr( $uid ); ?>_hex" data-hex-for="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $val ); ?>"
						style="width:90px;margin-left:8px;font-family:monospace;" pattern="#[a-fA-F0-9]{6}" maxlength="7"
						onchange="document.getElementById('<?php echo esc_js( $uid ); ?>_picker').value=this.value; this.form.elements['<?php echo esc_js( $key ); ?>'].value=this.value">
					<button type="button" class="button-link mdm-reset" data-key="<?php echo esc_attr( $key ); ?>" data-default="<?php echo esc_attr( $default ); ?>"
						style="margin-left:8px;font-family:monospace;font-size:11px;color:#666;text-decoration:none;vertical-align:middle;"
						title="<?php esc_attr_e( 'Restore default color', 'minidarkmode' ); ?>">↺ <?php echo esc_html( $default ); ?></button>
					<?php if ( $desc ) : ?>
						<p class="description" style="margin:4px 0 0;"><?php echo esc_html( $desc ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
			<?php
		};
		$pos      = get_option( 'minidarkmode_position', 'left' );
		$style    = get_option( 'minidarkmode_button_style', 'circle' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Mini Dark Mode — Settings', 'minidarkmode' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'minidarkmode' ); ?>

				<!-- ===== Basics ===== -->
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="minidarkmode_position"><?php esc_html_e( 'Floating button position', 'minidarkmode' ); ?></label></th>
						<td>
							<select name="minidarkmode_position" id="minidarkmode_position">
								<option value="left" <?php selected( $pos, 'left' ); ?>><?php esc_html_e( 'Bottom left', 'minidarkmode' ); ?></option>
								<option value="right" <?php selected( $pos, 'right' ); ?>><?php esc_html_e( 'Bottom right', 'minidarkmode' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="minidarkmode_button_style"><?php esc_html_e( 'Toggle button style', 'minidarkmode' ); ?></label></th>
						<td>
							<select name="minidarkmode_button_style" id="minidarkmode_button_style">
								<option value="circle" <?php selected( $style, 'circle' ); ?>><?php esc_html_e( 'Circle with moon/sun icon', 'minidarkmode' ); ?></option>
								<option value="pill" <?php selected( $style, 'pill' ); ?>><?php esc_html_e( 'Pill with text label', 'minidarkmode' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Toggle button icon', 'minidarkmode' ); ?></th>
						<td>
							<input type="hidden" name="minidarkmode_show_icon" value="0">
							<label><input type="checkbox" name="minidarkmode_show_icon" value="1" <?php checked( $this->get_bool( 'minidarkmode_show_icon', '1' ), true ); ?>> <?php esc_html_e( 'Show the moon/sun icon', 'minidarkmode' ); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Floating button', 'minidarkmode' ); ?></th>
						<td>
							<input type="hidden" name="minidarkmode_floating" value="0">
							<label><input type="checkbox" name="minidarkmode_floating" value="1" <?php checked( $this->get_bool( 'minidarkmode_floating', '1' ), true ); ?>> <?php esc_html_e( 'Show the fixed floating button (turn off to place the [minidarkmode] shortcode instead)', 'minidarkmode' ); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Keyboard shortcut', 'minidarkmode' ); ?></th>
						<td>
							<input type="hidden" name="minidarkmode_shortcut" value="0">
							<label><input type="checkbox" name="minidarkmode_shortcut" value="1" <?php checked( $this->get_bool( 'minidarkmode_shortcut', '0' ), true ); ?>> <?php esc_html_e( 'Let visitors toggle dark mode with Ctrl+Alt+D', 'minidarkmode' ); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Accent-colored content headings', 'minidarkmode' ); ?></th>
						<td>
							<input type="hidden" name="minidarkmode_accent_headings" value="0">
							<label><input type="checkbox" name="minidarkmode_accent_headings" value="1" <?php checked( $this->get_bool( 'minidarkmode_accent_headings', '0' ), true ); ?>> <?php esc_html_e( 'Keep in-content H1–H6 in the accent color instead of the heading color (for sites whose light theme uses colored content headings)', 'minidarkmode' ); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Transparent header in dark mode', 'minidarkmode' ); ?></th>
						<td>
							<input type="hidden" name="minidarkmode_header_transparent" value="0">
							<label><input type="checkbox" name="minidarkmode_header_transparent" value="1" <?php checked( $this->get_bool( 'minidarkmode_header_transparent', '1' ), true ); ?>> <?php esc_html_e( 'Keep the header background transparent in dark mode', 'minidarkmode' ); ?></label>
						</td>
					</tr>
				</table>

				<!-- ===== Color presets ===== -->
				<h2 class="title" style="margin-top:24px;"><?php esc_html_e( 'Color presets', 'minidarkmode' ); ?></h2>
				<p class="description"><?php esc_html_e( 'One click fills every color field below and becomes the restore baseline — the ↺ button next to each color restores that preset color. Swatches show: accent, content background, body text.', 'minidarkmode' ); ?></p>
				<div id="minidarkmode-presets" style="display:flex;flex-wrap:wrap;gap:8px;margin:8px 0 4px;">
					<input type="hidden" name="minidarkmode_preset" id="mdm-active-preset" value="<?php echo esc_attr( $active_id ); ?>">
					<?php foreach ( $presets as $id => $preset ) : ?>
						<button type="button" class="button mdm-preset<?php echo $id === $active_id ? ' mdm-preset--active' : ''; ?>" data-preset="<?php echo esc_attr( $id ); ?>" style="display:inline-flex;align-items:center;gap:7px;">
							<span style="display:inline-flex;padding-left:4px;">
								<span style="width:12px;height:12px;border-radius:50%;background:<?php echo esc_attr( $preset['colors'][9] ); ?>;border:1px solid rgba(0,0,0,.2);"></span>
								<span style="width:12px;height:12px;border-radius:50%;background:<?php echo esc_attr( $preset['colors'][16] ); ?>;border:1px solid rgba(0,0,0,.2);margin-left:-4px;"></span>
								<span style="width:12px;height:12px;border-radius:50%;background:<?php echo esc_attr( $preset['colors'][13] ); ?>;border:1px solid rgba(0,0,0,.2);margin-left:-4px;"></span>
							</span>
							<?php echo esc_html( $preset['label'] ); ?>
						</button>
					<?php endforeach; ?>
				</div>

				<!-- ===== Global palette (reference swatches) ===== -->
				<h2 class="title" style="margin-top:24px;"><?php esc_html_e( 'Global palette', 'minidarkmode' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Reference palette used as the fallback values for the theme and surface colors below. Edit it only if you want different fallbacks.', 'minidarkmode' ); ?></p>
				<table class="form-table" role="presentation">
					<?php
					$color_field( 'minidarkmode_palette_brand',        __( 'Brand', 'minidarkmode' ), __( 'Baseline brand color — Accent and Links fall back to this', 'minidarkmode' ) );
					$color_field( 'minidarkmode_palette_brand_alt',    __( 'Alternate brand', 'minidarkmode' ), __( 'Baseline secondary color — Link hover falls back to this', 'minidarkmode' ) );
					$color_field( 'minidarkmode_palette_heading',      __( 'Headings', 'minidarkmode' ), __( 'Baseline heading color — falls back for Theme Headings', 'minidarkmode' ) );
					$color_field( 'minidarkmode_palette_text',         __( 'Body text', 'minidarkmode' ), __( 'Baseline text color — falls back for Theme Body text', 'minidarkmode' ) );
					$color_field( 'minidarkmode_palette_bg_primary',   __( 'Primary background', 'minidarkmode' ), __( 'Baseline surface — falls back for Content background', 'minidarkmode' ) );
					$color_field( 'minidarkmode_palette_bg_secondary', __( 'Secondary background', 'minidarkmode' ), __( 'Used directly: inputs, table headers, sub-menus, breadcrumbs', 'minidarkmode' ) );
					$color_field( 'minidarkmode_palette_bg_subtle',    __( 'Subtle background', 'minidarkmode' ), __( 'Spare baseline — not applied by the bundled rules', 'minidarkmode' ) );
					$color_field( 'minidarkmode_palette_bg_alt',       __( 'Alternate background', 'minidarkmode' ), __( 'Baseline page background — falls back for Site background', 'minidarkmode' ) );
					$color_field( 'minidarkmode_palette_other',        __( 'Supporting color', 'minidarkmode' ), __( 'Baseline border color — falls back for Borders', 'minidarkmode' ) );
					?>
				</table>

				<!-- ===== Theme colors ===== -->
				<h2 class="title" style="margin-top:24px;"><?php esc_html_e( 'Theme colors', 'minidarkmode' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Directly control text and component colors in dark mode.', 'minidarkmode' ); ?></p>
				<table class="form-table" role="presentation">
					<?php
					$color_field( 'minidarkmode_theme_accent',     __( 'Accent', 'minidarkmode' ), __( 'Toggle button, blockquote borders, footer and breadcrumb links, calendar event links', 'minidarkmode' ) );
					$color_field( 'minidarkmode_theme_link',       __( 'Links', 'minidarkmode' ), __( 'Every link on the site in dark mode', 'minidarkmode' ) );
					$color_field( 'minidarkmode_theme_link_hover', __( 'Link hover', 'minidarkmode' ), __( 'Links when the pointer is over them', 'minidarkmode' ) );
					$color_field( 'minidarkmode_theme_heading',    __( 'Headings (H1–H6)', 'minidarkmode' ), __( 'Post titles, widget titles and content headings', 'minidarkmode' ) );
					$color_field( 'minidarkmode_theme_text',       __( 'Body text', 'minidarkmode' ), __( 'Paragraphs, meta text and most content', 'minidarkmode' ) );
					$color_field( 'minidarkmode_theme_border',     __( 'Borders', 'minidarkmode' ), __( 'Tables, dividers and sub-menu borders', 'minidarkmode' ) );
					?>
				</table>

				<!-- ===== Surface colors ===== -->
				<h2 class="title" style="margin-top:24px;"><?php esc_html_e( 'Surface colors', 'minidarkmode' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Directly control background and surface colors in dark mode.', 'minidarkmode' ); ?></p>
				<table class="form-table" role="presentation">
					<?php
					$color_field( 'minidarkmode_surface_bg',      __( 'Site background', 'minidarkmode' ), __( 'The base page background behind everything', 'minidarkmode' ) );
					$color_field( 'minidarkmode_surface_content', __( 'Content background', 'minidarkmode' ), __( 'Post/page cards, widgets, footer and form areas', 'minidarkmode' ) );
					?>
				</table>

				<!-- ===== Advanced tweaks ===== -->
				<h2 class="title" style="margin-top:24px;"><?php esc_html_e( 'Advanced tweaks', 'minidarkmode' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Fine-tune how a few parts of the site look in dark mode. Pick a target and a color — no CSS needed. Everything here only applies while dark mode is on.', 'minidarkmode' ); ?></p>
				<?php
				$sel_opts  = $this->selector_options();
				$prop_opts = $this->property_options();
				$rows      = $this->get_advanced_rows();
				?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Custom rules', 'minidarkmode' ); ?></th>
						<td>
							<div id="mdm-advanced-rows">
								<?php
								// Seed with one empty row so the form is usable
								// even when nothing is stored yet.
								$render_row = function ( $sel, $prop, $color ) use ( $sel_opts, $prop_opts ) {
									?>
									<p class="mdm-adv-row" style="display:flex;align-items:center;gap:6px;margin:0 0 6px;">
										<select class="mdm-adv-sel">
											<?php foreach ( $sel_opts as $id => $label ) : ?>
												<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $sel, $id ); ?>><?php echo esc_html( $label ); ?></option>
											<?php endforeach; ?>
										</select>
										<select class="mdm-adv-prop">
											<?php foreach ( $prop_opts as $id => $label ) : ?>
												<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $prop, $id ); ?>><?php echo esc_html( $label ); ?></option>
											<?php endforeach; ?>
										</select>
										<input type="color" class="mdm-adv-color" value="<?php echo esc_attr( $color ); ?>">
										<button type="button" class="button-link mdm-adv-remove" style="color:#b32d2e;text-decoration:none;"><?php esc_html_e( 'Remove', 'minidarkmode' ); ?></button>
									</p>
									<?php
								};
								if ( $rows ) {
									foreach ( $rows as $row ) {
										$render_row( $row['sel'], $row['prop'], $row['color'] );
									}
								} else {
									$render_row( 'site-header', 'background', '#1d1d28' );
								}
								?>
							</div>
							<button type="button" class="button" id="mdm-adv-add"><?php esc_html_e( '+ Add rule', 'minidarkmode' ); ?></button>
							<input type="hidden" name="minidarkmode_advanced" id="mdm-advanced-value" value="<?php echo esc_attr( get_option( 'minidarkmode_advanced', '' ) ); ?>">
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/* ---------------- Front end ---------------- */

	public function enqueue() {
		wp_enqueue_style( 'minidarkmode', plugin_dir_url( __FILE__ ) . 'style.css', array(), MINIDARKMODE_VERSION );
	}

	/**
	 * Applies the stored (or OS-preferred) mode before first paint, so there
	 * is no flash of light content. Click handling is delegated from
	 * document level because the button markup only exists in the footer.
	 */
	public function bootstrap_script() {
		$shortcut = $this->get_bool( 'minidarkmode_shortcut', '0' );
		?>
		<script id="minidarkmode-bootstrap">
		(function () {
			var KEY = 'minidarkmode-mode';
			var html = document.documentElement;

			function sysDark() {
				return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
			}
			function currentMode() {
				var saved = null;
				try { saved = localStorage.getItem(KEY); } catch (e) {}
				if (saved === 'dark' || saved === 'light') { return saved; }
				return sysDark() ? 'dark' : 'light';
			}
			function apply(mode) {
				var dark = mode === 'dark';
				html.classList.toggle('mdm-dark-mode', dark);
				html.setAttribute('data-mdm-dark-mode', dark ? 'dark' : 'light');
				var btns = document.querySelectorAll('.minidarkmode-toggle');
				for (var i = 0; i < btns.length; i++) {
					btns[i].setAttribute('aria-pressed', dark ? 'true' : 'false');
				}
			}
			function flip() {
				var next = html.classList.contains('mdm-dark-mode') ? 'light' : 'dark';
				try { localStorage.setItem(KEY, next); } catch (e) {}
				apply(next);
			}
			apply(currentMode());
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', function () { apply(currentMode()); });
			}
			document.addEventListener('click', function (e) {
				var t = e.target;
				if (t && t.closest && t.closest('.minidarkmode-toggle')) { flip(); }
			}, true);
			if (window.matchMedia) {
				var mql = window.matchMedia('(prefers-color-scheme: dark)');
				var onSys = function () {
					var saved = null;
					try { saved = localStorage.getItem(KEY); } catch (e) {}
					if (saved !== 'dark' && saved !== 'light') { apply(sysDark() ? 'dark' : 'light'); }
				};
				if (mql.addEventListener) { mql.addEventListener('change', onSys); }
				else if (mql.addListener) { mql.addListener(onSys); }
			}
			<?php if ( $shortcut ) : ?>
			document.addEventListener('keydown',function(e){if(e.ctrlKey&&e.altKey&&('d'===e.key||'D'===e.key)){e.preventDefault();flip();}});
			<?php endif; ?>
		})();
		</script>
		<?php
	}

	/**
	 * Inline styles carry the button's critical appearance so page-caching or
	 * CSS-optimization plugins cannot delay it. Colors follow --mdm-accent;
	 * a solid fallback precedes every color-mix() declaration.
	 */
	private function button_appearance( $floating, $style ) {
		$radius  = ( 'pill' === $style ) ? 'border-radius:999px;' : 'border-radius:50%;';
		$colors  = 'color:#fff;'
			. 'background:#e878a0;'
			. 'background:linear-gradient(135deg,var(--mdm-accent,#e878a0),color-mix(in srgb,var(--mdm-accent,#e878a0),#000 22%));'
			. 'box-shadow:0 10px 24px -8px rgba(0,0,0,.5);';
		$flex    = 'display:flex;align-items:center;justify-content:center;gap:8px;border:none;cursor:pointer;margin:0;line-height:1;';
		if ( ! $floating ) {
			$size = ( 'pill' === $style ) ? 'width:auto;height:44px;padding:0 18px;' : 'width:46px;height:46px;padding:0;';
			return 'position:static;' . $flex . $size . $radius . $colors;
		}
		$side = ( 'right' === get_option( 'minidarkmode_position', 'left' ) ) ? 'right' : 'left';
		$size = ( 'pill' === $style ) ? 'width:auto;height:44px;padding:0 18px;' : 'width:46px;height:46px;padding:0;';
		return 'position:fixed;bottom:20px;' . $side . ':20px;z-index:9999;' . $flex . $size . $radius . $colors;
	}

	/**
	 * Prints the toggle button. Every dynamic value is escaped where it is
	 * interpolated, so the markup is safe by construction.
	 */
	private function render_button( $floating ) {
		$style     = ( 'pill' === get_option( 'minidarkmode_button_style', 'circle' ) ) ? 'pill' : 'circle';
		$show_icon = $this->get_bool( 'minidarkmode_show_icon', '1' );
		$icon_css  = $show_icon ? '' : 'display:none;';
		$classes   = 'minidarkmode-toggle' . ( 'pill' === $style ? ' minidarkmode-toggle--pill' : '' ) . ( $floating ? '' : ' minidarkmode-toggle--inline' );

		?>
		<button type="button" class="<?php echo esc_attr( $classes ); ?>" aria-label="<?php esc_attr_e( 'Toggle dark mode', 'minidarkmode' ); ?>" aria-pressed="false"
			style="<?php echo esc_attr( $this->button_appearance( $floating, $style ) ); ?>">
			<svg class="minidarkmode-icon minidarkmode-icon-moon" style="<?php echo esc_attr( $icon_css ); ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
			<svg class="minidarkmode-icon minidarkmode-icon-sun" style="<?php echo esc_attr( $icon_css ); ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
			<?php if ( 'pill' === $style ) : ?>
				<span class="minidarkmode-label minidarkmode-label-to-dark"><?php esc_html_e( 'Dark Mode', 'minidarkmode' ); ?></span>
				<span class="minidarkmode-label minidarkmode-label-to-light"><?php esc_html_e( 'Light Mode', 'minidarkmode' ); ?></span>
			<?php endif; ?>
		</button>
		<?php
	}

	public function toggle_button() {
		if ( $this->get_bool( 'minidarkmode_floating', '1' ) ) {
			$this->render_button( true );
		}
	}

	/**
	 * Returns the toggle button HTML for the [minidarkmode] shortcode.
	 */
	public function shortcode_toggle() {
		ob_start();
		$this->render_button( false );
		return ob_get_clean();
	}

	/**
	 * Adds the color variables and the Advanced tweaks rules to the page.
	 *
	 * They are attached to a stylesheet handle of their own that is enqueued
	 * last, so they print in <head> after the theme's stylesheets and are
	 * applied with the first paint.
	 */
	public function custom_css() {
		$css = '';

		// Make the accent available to the toggle button in light mode too.
		$accent = $this->get_color( 'minidarkmode_theme_accent' );
		if ( $accent ) {
			$css .= ':root{--mdm-accent:' . $accent . ';}';
		}

		$emit = function ( array $map ) {
			$out = '';
			foreach ( $map as $var => $opt ) {
				$val = $this->get_color( $opt[0] );
				if ( $val ) {
					$out .= $var . ':' . $val . ';';
				}
			}
			return $out ? 'html.mdm-dark-mode{' . $out . '}' : '';
		};

		$css .= $emit( array(
			'--mdm-palette-brand'        => array( 'minidarkmode_palette_brand' ),
			'--mdm-palette-brand-alt'    => array( 'minidarkmode_palette_brand_alt' ),
			'--mdm-palette-heading'      => array( 'minidarkmode_palette_heading' ),
			'--mdm-palette-text'         => array( 'minidarkmode_palette_text' ),
			'--mdm-palette-bg-primary'   => array( 'minidarkmode_palette_bg_primary' ),
			'--mdm-palette-bg-secondary' => array( 'minidarkmode_palette_bg_secondary' ),
			'--mdm-palette-bg-subtle'    => array( 'minidarkmode_palette_bg_subtle' ),
			'--mdm-palette-bg-alt'       => array( 'minidarkmode_palette_bg_alt' ),
			'--mdm-palette-other'        => array( 'minidarkmode_palette_other' ),
		) );

		$css .= $emit( array(
			'--mdm-accent'     => array( 'minidarkmode_theme_accent' ),
			'--mdm-link'       => array( 'minidarkmode_theme_link' ),
			'--mdm-link-hover' => array( 'minidarkmode_theme_link_hover' ),
			'--mdm-heading'    => array( 'minidarkmode_theme_heading' ),
			'--mdm-text'       => array( 'minidarkmode_theme_text' ),
			'--mdm-border'     => array( 'minidarkmode_theme_border' ),
		) );

		$css .= $emit( array(
			'--mdm-bg'      => array( 'minidarkmode_surface_bg' ),
			'--mdm-surface' => array( 'minidarkmode_surface_content' ),
		) );

		// Inline-styled text whose color matches the accent keeps the accent
		// instead of being brightened by the generic rule in style.css.
		$accent = $this->get_color( 'minidarkmode_theme_accent' );
		if ( $accent ) {
			$rgb = $this->accent_rgb( $accent );
			if ( $rgb ) {
				$r    = $rgb['r'];
				$g    = $rgb['g'];
				$b    = $rgb['b'];
				$hex6 = strtoupper( $rgb['hex'] );
				$css .= 'html.mdm-dark-mode [style*="color:' . $rgb['hex'] . '"],'
					. 'html.mdm-dark-mode [style*="color: ' . $rgb['hex'] . '"],'
					. 'html.mdm-dark-mode [style*="color:' . $hex6 . '"],'
					. 'html.mdm-dark-mode [style*="color: ' . $hex6 . '"],'
					. 'html.mdm-dark-mode [style*="color:rgb(' . $r . ',' . $g . ',' . $b . '"],'
					. 'html.mdm-dark-mode [style*="color: rgb(' . $r . ',' . $g . ',' . $b . '"],'
					. 'html.mdm-dark-mode [style*="color:rgb(' . $r . ', ' . $g . ', ' . $b . '"],'
					. 'html.mdm-dark-mode [style*="color: rgb(' . $r . ', ' . $g . ', ' . $b . '"],'
					. 'html.mdm-dark-mode [style*="color:rgba(' . $r . ',' . $g . ',' . $b . '"],'
					. 'html.mdm-dark-mode [style*="color: rgba(' . $r . ',' . $g . ',' . $b . '"],'
					. 'html.mdm-dark-mode [style*="color:rgba(' . $r . ', ' . $g . ', ' . $b . '"],'
					. 'html.mdm-dark-mode [style*="color: rgba(' . $r . ', ' . $g . ', ' . $b . '"]'
					. '{color:var(--mdm-accent,' . $accent . ')!important;}';
			}
		}

		// Optional: in-content headings follow the accent color (sites whose
		// light theme already uses colored content headings).
		if ( $this->get_bool( 'minidarkmode_accent_headings', '0' ) ) {
			$css .= 'html.mdm-dark-mode :is(.entry-content,.post-content) '
				. ':is(h1,h2,h3,h4,h5,h6):not(.entry-title):not(:where(.elementor-widget-heading *))'
				. '{color:var(--mdm-accent,#e878a0)!important;}';
		}

		if ( $this->get_bool( 'minidarkmode_header_transparent', '1' ) ) {
			$css .= 'html.mdm-dark-mode .site-header,html.mdm-dark-mode .main-header-bar,html.mdm-dark-mode .ast-above-header,html.mdm-dark-mode .ast-below-header{background-color:transparent!important;border-color:transparent!important;box-shadow:none!important;}';
		}

		// Advanced tweaks: selectors, properties and colours are all picked
		// from plugin-owned whitelists, so this is assembled CSS, not user CSS.
		$css .= $this->advanced_css();

		if ( $css ) {
			// A separate, source-less handle that is enqueued last keeps the
			// variables and the per-site overrides after the theme's
			// stylesheets, which is what the Advanced tweaks are for.
			// WordPress prints it as an inline style block.
			wp_register_style( 'minidarkmode-dynamic', false, array(), MINIDARKMODE_VERSION );
			wp_enqueue_style( 'minidarkmode-dynamic' );
			wp_add_inline_style( 'minidarkmode-dynamic', $css );
		}
	}
}

Mini_Dark_Mode::instance();

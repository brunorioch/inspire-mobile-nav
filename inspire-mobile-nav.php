<?php
/**
 * Plugin Name:       INSPIRE Mobile Nav
 * Plugin URI:        https://inspiredm.co.uk
 * Description:        A simple, self-contained mobile navigation: a hamburger button and full-screen overlay menu you drop in with a shortcode. Placement, colours, toggle icons, overlay tint and open animation are all set from one settings page. Leaves your theme untouched.
 * Version:           1.9.4
 * Requires PHP:      7.4
 * Author:            INSPIRE
 * Author URI:        https://inspiredm.co.uk
 * License:           GPL-2.0-or-later
 * Text Domain:       inspire-mobile-nav
 *
 * Usage: add [inspire_mobile_nav] to your header (e.g. header.php) where you want
 * the hamburger to appear, or drop it into a header widget/block area.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'IMN_VERSION', '1.9.4' );
define( 'IMN_URL', plugin_dir_url( __FILE__ ) );
define( 'IMN_PATH', plugin_dir_path( __FILE__ ) );
define( 'IMN_OPTION', 'imn_settings' );

/**
 * GitHub repository the plugin updates from. Each site checks it for new
 * releases and shows the normal "Update available" notice in WP Admin.
 */
define( 'IMN_UPDATE_REPO', 'https://github.com/GITHUB-USERNAME/inspire-mobile-nav/' );

/**
 * Self-updates from GitHub releases (Plugin Update Checker library).
 *
 * To release: bump the Version header and IMN_VERSION, then publish a GitHub
 * release tagged e.g. v1.9.5 with the plugin zip attached. For a private repo,
 * add define( 'IMN_GITHUB_TOKEN', '...' ); to the site's wp-config.php.
 */
function imn_init_updater() {
	if ( version_compare( PHP_VERSION, '7.4', '<' ) || ! file_exists( IMN_PATH . 'lib/plugin-update-checker/plugin-update-checker.php' ) ) {
		return;
	}
	require_once IMN_PATH . 'lib/plugin-update-checker/plugin-update-checker.php';

	$checker = \YahnisElsts\PluginUpdateChecker\v6\PucFactory::buildUpdateChecker(
		IMN_UPDATE_REPO,
		__FILE__,
		'inspire-mobile-nav'
	);
	// Prefer the zip attached to the release (unpacks to the right folder name);
	// fall back to GitHub's source zip if none is attached.
	$checker->getVcsApi()->enableReleaseAssets( '/inspire-mobile-nav.*\.zip($|[?&#])/i' );

	if ( defined( 'IMN_GITHUB_TOKEN' ) && IMN_GITHUB_TOKEN ) {
		$checker->setAuthentication( IMN_GITHUB_TOKEN );
	}
}
imn_init_updater();

/**
 * Default settings.
 */
function imn_defaults() {
	return array(
		'theme_location'  => 'primary',    // which theme menu location to display
		'hide_theme_nav'  => true,         // auto-hide the theme's own render of that location
		'scope'           => 'mobile',     // 'mobile' (up to breakpoint) | 'all' (every width)
		'breakpoint'      => 900,          // hamburger shows at/below this width (px), mobile scope only
		'position'        => 'top-right',  // 'inline' | 'top-left' | 'top-right'
		'fixed_top'       => 20,           // fixed placement: distance from top
		'fixed_top_unit'  => 'px',         // 'px' | 'em'
		'fixed_side'      => 20,           // fixed placement: distance from left/right (per position)
		'fixed_side_unit' => 'px',         // 'px' | 'em'
		'bar_width'       => 30,           // hamburger width (px)
		'bar_thickness'   => 2,            // thickness of each bar (px)
		'bar_gap'         => 5,            // space between bars (px)
		'bar_color'       => '#1a1a1a',    // hamburger colour when closed
		'bar_color_open'  => '#ffffff',    // hamburger (X) colour when open
		'icon_style'      => 'plusminus',  // 'plusminus' | 'chevron'
		'bg_type'         => 'solid',      // 'solid' | 'gradient'
		'overlay_color'   => '#0f2e1d',    // overlay colour (solid, or gradient start)
		'overlay_color2'  => '#0b3b24',    // gradient end colour
		'gradient_angle'  => 135,          // gradient direction in degrees
		'overlay_opacity' => 97,           // 0-100 (applies to the whole overlay)
		'bg_image'        => '',           // overlay background image URL ('' = none)
		'bg_image_id'     => 0,            // attachment ID (for the media picker)
		'bg_size'         => 'cover',      // 'cover' | 'contain' | 'auto' | 'custom'
		'bg_size_custom'  => '',           // custom background-size, e.g. '80%' or '300px auto'
		'bg_position'     => 'center center', // background-position preset (or 'custom')
		'bg_position_custom' => '',        // custom background-position, e.g. '20% 50%'
		'bg_mobile_override' => false,     // use different size/position on mobile
		'bg_size_mobile'  => 'cover',      // mobile 'cover' | 'contain' | 'auto' | 'custom'
		'bg_size_mobile_custom' => '',     // mobile custom background-size
		'bg_position_mobile' => 'center center', // mobile background-position preset (or 'custom')
		'bg_position_mobile_custom' => '', // mobile custom background-position
		'text_color'      => '#ffffff',    // menu link colour on the overlay
		'text_hover_color' => '#cccccc',   // menu link colour on hover/focus
		'font_family'     => '',           // theme.json font-family slug ('' = inherit theme font)
		'font_family_custom' => '',        // custom font stack; overrides font_family when set
		'text_size'       => 1.4,          // menu link text size
		'text_size_unit'  => 'em',         // 'px' | 'em'
		'text_size_mobile_override' => false, // use a different text size on mobile
		'text_size_mobile' => 1.2,         // mobile menu link text size
		'text_size_mobile_unit' => 'em',   // 'px' | 'em'
		'text_underline'  => false,        // underline menu links
		'link_pad_y'      => 0.45,         // vertical padding on each menu link
		'link_pad_y_unit' => 'em',         // 'px' | 'em'
		'animation'       => 'slide-left', // 'slide-left' | 'slide-right' | 'slide-down' | 'fade'
		'z_index'         => 100000,       // stacking order of the menu/hamburger
	);
}

/**
 * Get merged settings (saved over defaults).
 */
function imn_get_settings() {
	$saved = get_option( IMN_OPTION, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	return wp_parse_args( $saved, imn_defaults() );
}

/**
 * Convert a hex colour + 0-100 opacity into an rgba() string.
 */
function imn_hex_to_rgba( $hex, $opacity ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
		$hex = '000000';
	}
	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );
	$a = max( 0, min( 100, (int) $opacity ) ) / 100;
	return sprintf( 'rgba(%d,%d,%d,%s)', $r, $g, $b, rtrim( rtrim( number_format( $a, 2, '.', '' ), '0' ), '.' ) );
}

/**
 * Build the overlay background value (solid rgba or a linear-gradient),
 * with the chosen opacity applied.
 */
function imn_overlay_background( $s ) {
	$c1 = imn_hex_to_rgba( $s['overlay_color'], $s['overlay_opacity'] );
	if ( 'gradient' === $s['bg_type'] ) {
		$c2    = imn_hex_to_rgba( $s['overlay_color2'], $s['overlay_opacity'] );
		$angle = (int) $s['gradient_angle'];
		return sprintf( 'linear-gradient(%ddeg, %s, %s)', $angle, $c1, $c2 );
	}
	return $c1;
}

/**
 * Build the overlay colour as an image-compatible layer (always a gradient),
 * so it can sit as a tint on top of a background image. A solid colour becomes
 * a flat two-stop gradient of the same rgba.
 */
function imn_overlay_tint( $s ) {
	$c1 = imn_hex_to_rgba( $s['overlay_color'], $s['overlay_opacity'] );
	if ( 'gradient' === $s['bg_type'] ) {
		$c2    = imn_hex_to_rgba( $s['overlay_color2'], $s['overlay_opacity'] );
		$angle = (int) $s['gradient_angle'];
		return sprintf( 'linear-gradient(%ddeg, %s, %s)', $angle, $c1, $c2 );
	}
	return sprintf( 'linear-gradient(%s, %s)', $c1, $c1 );
}

/**
 * Resolve the effective background-size value from a size mode + custom string.
 */
function imn_bg_size_value( $mode, $custom ) {
	if ( 'custom' === $mode ) {
		$custom = imn_sanitize_css_size( $custom );
		return '' !== $custom ? $custom : 'cover';
	}
	return in_array( $mode, array( 'cover', 'contain', 'auto' ), true ) ? $mode : 'cover';
}

/**
 * Sanitise a CSS background-size value to a safe subset (keywords, numbers,
 * units, %, spaces). Strips anything that could break out of the declaration.
 */
function imn_sanitize_css_size( $value ) {
	$value = preg_replace( '/[^A-Za-z0-9 %.-]/', '', (string) $value );
	return trim( $value );
}

/**
 * Whitelist of background-position presets.
 */
function imn_bg_positions() {
	return array(
		'left top',
		'center top',
		'right top',
		'left center',
		'center center',
		'right center',
		'left bottom',
		'center bottom',
		'right bottom',
	);
}

/**
 * Resolve the effective background-position from a mode + custom string.
 * 'custom' uses the free-text value (e.g. '20% 50%' or 'left 20% center').
 */
function imn_bg_position_value( $mode, $custom ) {
	if ( 'custom' === $mode ) {
		$custom = imn_sanitize_css_position( $custom );
		return '' !== $custom ? $custom : 'center center';
	}
	return in_array( $mode, imn_bg_positions(), true ) ? $mode : 'center center';
}

/**
 * Sanitise a CSS background-position value to a safe subset (keywords, numbers,
 * units, %, spaces).
 */
function imn_sanitize_css_position( $value ) {
	$value = preg_replace( '/[^A-Za-z0-9 %.-]/', '', (string) $value );
	return trim( $value );
}

/**
 * Build a CSS length string from a numeric value and a unit ('px' or 'em').
 * Trims trailing zeros so 20.00 -> "20", 1.50 -> "1.5".
 */
function imn_css_length( $value, $unit ) {
	$unit = ( 'em' === $unit ) ? 'em' : 'px';
	$num  = (float) $value;
	$num  = rtrim( rtrim( number_format( $num, 3, '.', '' ), '0' ), '.' );
	if ( '' === $num ) {
		$num = '0';
	}
	return $num . $unit;
}

/**
 * Read the font families registered by the active theme (theme.json / global
 * settings). Returns an array keyed by slug: array( slug => array( name, css ) ).
 * Empty on classic themes that don't register any (the menu then inherits the
 * theme's font).
 */
function imn_theme_font_families() {
	$out = array();
	if ( ! function_exists( 'wp_get_global_settings' ) ) {
		return $out;
	}
	$settings = wp_get_global_settings();
	if ( empty( $settings['typography']['fontFamilies'] ) ) {
		return $out;
	}

	// fontFamilies may be grouped by origin (theme/default/custom) or a flat list.
	$items = array();
	foreach ( (array) $settings['typography']['fontFamilies'] as $value ) {
		if ( is_array( $value ) && ( isset( $value['fontFamily'] ) || isset( $value['slug'] ) ) ) {
			$items[] = $value; // flat list item
		} elseif ( is_array( $value ) ) {
			foreach ( $value as $item ) {
				if ( is_array( $item ) ) {
					$items[] = $item; // grouped-by-origin list
				}
			}
		}
	}

	foreach ( $items as $item ) {
		if ( empty( $item['fontFamily'] ) ) {
			continue;
		}
		$slug = '';
		if ( ! empty( $item['slug'] ) ) {
			$slug = sanitize_key( $item['slug'] );
		} elseif ( ! empty( $item['name'] ) ) {
			$slug = sanitize_key( $item['name'] );
		}
		if ( '' === $slug ) {
			continue;
		}
		$out[ $slug ] = array(
			'name' => ! empty( $item['name'] ) ? $item['name'] : $slug,
			'css'  => imn_sanitize_font_css( $item['fontFamily'] ),
		);
	}
	return $out;
}

/**
 * Reduce a font-family stack to a safe subset of characters for output in a
 * style attribute (letters, digits, spaces, commas, quotes, hyphens, dots).
 */
function imn_sanitize_font_css( $css ) {
	$css = preg_replace( '/[^A-Za-z0-9 ,._\'"-]/', '', (string) $css );
	return trim( $css );
}

/**
 * Resolve a saved font slug to its CSS font-family value, or 'inherit'.
 */
function imn_font_family_css( $slug ) {
	if ( empty( $slug ) ) {
		return 'inherit';
	}
	$fonts = imn_theme_font_families();
	if ( isset( $fonts[ $slug ] ) && '' !== $fonts[ $slug ]['css'] ) {
		return $fonts[ $slug ]['css'];
	}
	return 'inherit';
}

/* -------------------------------------------------------------------------
 * Front-end assets
 * ---------------------------------------------------------------------- */

function imn_register_assets() {
	wp_register_style( 'inspire-mobile-nav', IMN_URL . 'assets/mobile-nav.css', array(), IMN_VERSION );
	wp_register_script( 'inspire-mobile-nav', IMN_URL . 'assets/mobile-nav.js', array( 'jquery' ), IMN_VERSION, true );

	$s = imn_get_settings();
	// In "mobile" scope, hide the hamburger and overlay above the breakpoint.
	// In "all" scope, the nav shows at every width so no hide rule is needed.
	if ( 'all' !== $s['scope'] ) {
		$bp     = (int) $s['breakpoint'];
		$inline = sprintf(
			'@media (min-width:%dpx){.imn .imn-hamburger{display:none !important;}.imn .imn-overlay{display:none !important;}}',
			$bp + 1
		);
		wp_add_inline_style( 'inspire-mobile-nav', $inline );
	}

	// Menu text size. Emitted as a stylesheet rule (not inline on the element)
	// so the optional mobile size can override it via a media query.
	$text_size = imn_css_length( $s['text_size'], $s['text_size_unit'] );
	wp_add_inline_style(
		'inspire-mobile-nav',
		sprintf( '.imn{--imn-text-size:%s;}', $text_size )
	);
	if ( ! empty( $s['text_size_mobile_override'] ) ) {
		$text_size_m = imn_css_length( $s['text_size_mobile'], $s['text_size_mobile_unit'] );
		$bp          = (int) $s['breakpoint'];
		wp_add_inline_style(
			'inspire-mobile-nav',
			sprintf( '@media (max-width:%dpx){.imn{--imn-text-size:%s;}}', $bp, $text_size_m )
		);
	}

	// Background-image size/position. Emitted as stylesheet rules (not inline on
	// the element) so the mobile media query can override the desktop values —
	// inline custom properties would win over a media query and block the override.
	if ( ! empty( $s['bg_image'] ) ) {
		$d_size = imn_bg_size_value( $s['bg_size'], $s['bg_size_custom'] );
		$d_pos  = imn_bg_position_value( $s['bg_position'], $s['bg_position_custom'] );
		wp_add_inline_style(
			'inspire-mobile-nav',
			sprintf( '.imn-has-bg-image{--imn-bg-size:%s;--imn-bg-position:%s;}', $d_size, $d_pos )
		);

		if ( ! empty( $s['bg_mobile_override'] ) ) {
			$m_size = imn_bg_size_value( $s['bg_size_mobile'], $s['bg_size_mobile_custom'] );
			$m_pos  = imn_bg_position_value( $s['bg_position_mobile'], $s['bg_position_mobile_custom'] );
			$bp     = (int) $s['breakpoint'];
			wp_add_inline_style(
				'inspire-mobile-nav',
				sprintf(
					'@media (max-width:%dpx){.imn-has-bg-image{--imn-bg-size:%s;--imn-bg-position:%s;}}',
					$bp,
					$m_size,
					$m_pos
				)
			);
		}
	}

	// Load the stylesheet in <head> on every front-end page. Enqueuing it only
	// when the nav renders (part-way down the page) makes WordPress print it in
	// the footer, so the menu flashes unstyled (a plain checkbox and an open
	// list of links) until it loads. The script can stay in the footer.
	wp_enqueue_style( 'inspire-mobile-nav' );
}
add_action( 'wp_enqueue_scripts', 'imn_register_assets' );

/* -------------------------------------------------------------------------
 * Admin assets (media picker on the settings page)
 * ---------------------------------------------------------------------- */

function imn_admin_assets( $hook ) {
	if ( 'settings_page_inspire-mobile-nav' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'inspire-mobile-nav-admin', IMN_URL . 'assets/admin.js', array( 'jquery' ), IMN_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'imn_admin_assets' );

/* -------------------------------------------------------------------------
 * Hide the theme's own render of the selected menu location.
 *
 * When "Hide theme navigation" is on, any other wp_nav_menu() call on the
 * page for the same theme_location (i.e. the theme's own header markup)
 * gets an "imn-theme-nav-hide" class added, which is then hidden at/below
 * the breakpoint (or always, for "all screen sizes" scope).
 *
 * The rule is printed in <head>, scoped with body:has(.imn) so it only
 * applies on pages that actually show the mobile nav; printing it in the
 * footer let the theme's nav flash up first. Browsers without :has() support
 * still get the footer copy, printed once the nav has been placed.
 * ---------------------------------------------------------------------- */

/**
 * Tracks (and reports) whether imn_render() has run on this page yet.
 */
function imn_in_use( $set = null ) {
	static $in_use = false;
	if ( true === $set ) {
		$in_use = true;
	}
	return $in_use;
}

/**
 * Add a hide-me class to any wp_nav_menu() call — other than our own —
 * that renders the same theme_location we're showing in the overlay.
 */
function imn_tag_theme_menu_args( $args ) {
	if ( ! empty( $args['imn_internal'] ) ) {
		return $args;
	}

	$s = imn_get_settings();
	if ( empty( $s['hide_theme_nav'] ) ) {
		return $args;
	}

	if ( empty( $args['theme_location'] ) || $args['theme_location'] !== $s['theme_location'] ) {
		return $args;
	}

	$args['menu_class'] = trim( ( ! empty( $args['menu_class'] ) ? $args['menu_class'] . ' ' : '' ) . 'imn-theme-nav-hide' );
	if ( ! empty( $args['container'] ) ) {
		$args['container_class'] = trim( ( ! empty( $args['container_class'] ) ? $args['container_class'] . ' ' : '' ) . 'imn-theme-nav-hide' );
	}
	return $args;
}
add_filter( 'wp_nav_menu_args', 'imn_tag_theme_menu_args', 20 );

/**
 * The "hide the theme's nav" rule for a given selector prefix, wrapped in the
 * breakpoint media query unless the nav shows at every width.
 */
function imn_theme_nav_hide_rule( $prefix ) {
	$s    = imn_get_settings();
	$rule = $prefix . '.imn-theme-nav-hide{display:none !important;}';
	if ( 'all' === $s['scope'] ) {
		return $rule;
	}
	return sprintf( '@media (max-width:%dpx){%s}', (int) $s['breakpoint'], $rule );
}

/** Head: hide the theme's nav before it can paint, on pages that show the mobile nav. */
function imn_print_theme_nav_hide_css_head() {
	$s = imn_get_settings();
	if ( empty( $s['hide_theme_nav'] ) ) {
		return;
	}
	echo '<style id="imn-hide-theme-nav-head">' . imn_theme_nav_hide_rule( 'body:has(.imn) ' ) . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed CSS with an integer breakpoint.
}
add_action( 'wp_head', 'imn_print_theme_nav_hide_css_head', 20 );

/**
 * Footer: the same rule without :has(), for older browsers, once we know for
 * sure the mobile nav was output somewhere on this page.
 */
function imn_print_theme_nav_hide_css() {
	if ( ! imn_in_use() ) {
		return;
	}
	$s = imn_get_settings();
	if ( empty( $s['hide_theme_nav'] ) ) {
		return;
	}
	echo '<style id="imn-hide-theme-nav">' . imn_theme_nav_hide_rule( '' ) . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed CSS with an integer breakpoint.
}
add_action( 'wp_footer', 'imn_print_theme_nav_hide_css', 5 );

/* -------------------------------------------------------------------------
 * Shortcode
 * ---------------------------------------------------------------------- */

/**
 * Build the mobile-nav markup and return it as a string.
 * Used by both the shortcode and the inspire_mobile_nav() template tag.
 */
function imn_render() {
	$s = imn_get_settings();

	// Flag that the nav is actually placed on this page, so the footer hook
	// knows whether to print the "hide the theme's own nav" CSS.
	imn_in_use( true );

	// Make sure our assets load on any page where the nav is used.
	wp_enqueue_style( 'inspire-mobile-nav' );
	wp_enqueue_script( 'inspire-mobile-nav' );

	$menu_args = array(
		'theme_location' => $s['theme_location'],
		'container'      => false,
		'menu_class'     => 'imn-menu-list',
		'fallback_cb'    => false,
		'depth'          => 2,
		'echo'           => false,
		'imn_internal'   => true, // marks this as our own call, see imn_tag_theme_menu_args().
	);
	$menu_html = wp_nav_menu( $menu_args );
	if ( empty( $menu_html ) ) {
		$menu_html = '<ul class="imn-menu-list"><li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'inspire-mobile-nav' ) . '</a></li></ul>';
	}

	$overlay_bg = imn_overlay_background( $s );

	$z_base = (int) $s['z_index'];
	$fixed_top   = imn_css_length( $s['fixed_top'], $s['fixed_top_unit'] );
	$fixed_side  = imn_css_length( $s['fixed_side'], $s['fixed_side_unit'] );
	// Note: --imn-text-size is emitted as a stylesheet rule in imn_register_assets()
	// (not inline) so the mobile text-size media query can override it.
	// A custom font stack (if set) overrides the theme.json picker.
	$font_css = imn_sanitize_font_css( $s['font_family_custom'] );
	if ( '' === $font_css ) {
		$font_css = imn_font_family_css( $s['font_family'] );
	}
	$decoration  = ! empty( $s['text_underline'] ) ? 'underline' : 'none';
	$link_pad_y  = imn_css_length( $s['link_pad_y'], $s['link_pad_y_unit'] );
	$style_vars = sprintf(
		'--imn-bar:%s;--imn-bar-open:%s;--imn-overlay-bg:%s;--imn-text:%s;--imn-text-hover:%s;--imn-font:%s;--imn-text-decoration:%s;--imn-link-pad-y:%s;--imn-bar-w:%dpx;--imn-bar-thickness:%dpx;--imn-bar-gap:%dpx;--imn-fixed-top:%s;--imn-fixed-side:%s;--imn-z:%d;--imn-z-top:%d;',
		esc_attr( $s['bar_color'] ),
		esc_attr( $s['bar_color_open'] ),
		esc_attr( $overlay_bg ),
		esc_attr( $s['text_color'] ),
		esc_attr( $s['text_hover_color'] ),
		$font_css,
		esc_attr( $decoration ),
		esc_attr( $link_pad_y ),
		(int) $s['bar_width'],
		(int) $s['bar_thickness'],
		(int) $s['bar_gap'],
		esc_attr( $fixed_top ),
		esc_attr( $fixed_side ),
		$z_base,
		$z_base + 1
	);

	// Background image (optional). The overlay colour becomes a tint layer on top.
	$bg_image = esc_url( $s['bg_image'] );
	$has_bg   = ( '' !== $bg_image );
	if ( $has_bg ) {
		$style_vars .= sprintf(
			'--imn-bg-image:url("%s");--imn-overlay-tint:%s;',
			$bg_image,
			imn_overlay_tint( $s )
		);
	}

	$class_list = array(
		'imn',
		'imn-pos-' . sanitize_html_class( $s['position'] ),
		'imn-icon-' . sanitize_html_class( $s['icon_style'] ),
		'imn-anim-' . sanitize_html_class( $s['animation'] ),
	);
	if ( $has_bg ) {
		$class_list[] = 'imn-has-bg-image';
	}
	$wrap_classes = implode( ' ', $class_list );

	// Unique-ish id in case the shortcode appears more than once.
	static $instance = 0;
	$instance++;
	$toggle_id = 'imn-toggle-' . $instance;
	$overlay_id = 'imn-overlay-' . $instance;

	ob_start();
	?>
	<div class="<?php echo esc_attr( $wrap_classes ); ?>" data-icon="<?php echo esc_attr( $s['icon_style'] ); ?>" style="<?php echo esc_attr( $style_vars ); ?>">
		<?php // The checkbox drives the overlay with CSS (no JavaScript needed). To screen readers it is a "Menu" switch: on when the menu is open. ?>
		<input type="checkbox" id="<?php echo esc_attr( $toggle_id ); ?>" class="imn-toggle" autocomplete="off" role="switch" aria-controls="<?php echo esc_attr( $overlay_id ); ?>" aria-label="<?php esc_attr_e( 'Menu', 'inspire-mobile-nav' ); ?>" />
		<?php // aria-hidden: the checkbox above carries the name, so the label isn't announced a second time. It stays clickable. ?>
		<label for="<?php echo esc_attr( $toggle_id ); ?>" class="imn-hamburger" aria-hidden="true">
			<span class="imn-sr"><?php esc_html_e( 'Toggle menu', 'inspire-mobile-nav' ); ?></span>
			<span class="imn-bar"></span>
			<span class="imn-bar"></span>
			<span class="imn-bar"></span>
		</label>
		<div class="imn-overlay" id="<?php echo esc_attr( $overlay_id ); ?>">
			<nav class="imn-menu" aria-label="<?php esc_attr_e( 'Mobile menu', 'inspire-mobile-nav' ); ?>">
				<?php echo $menu_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output is already escaped by core. ?>
			</nav>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Shortcode: [inspire_mobile_nav] — for post content, blocks or widget areas.
 */
function imn_shortcode( $atts ) {
	return imn_render();
}
add_shortcode( 'inspire_mobile_nav', 'imn_shortcode' );

/**
 * Template tag: <?php inspire_mobile_nav(); ?> — the tidiest way to place this
 * in a theme file such as header.php.
 */
function inspire_mobile_nav() {
	echo imn_render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup is escaped within imn_render().
}

/* -------------------------------------------------------------------------
 * Settings page
 * ---------------------------------------------------------------------- */

function imn_add_settings_page() {
	add_options_page(
		__( 'INSPIRE Mobile Nav', 'inspire-mobile-nav' ),
		__( 'Mobile Nav', 'inspire-mobile-nav' ),
		'manage_options',
		'inspire-mobile-nav',
		'imn_render_settings_page'
	);
}
add_action( 'admin_menu', 'imn_add_settings_page' );

function imn_register_settings() {
	register_setting(
		'imn_settings_group',
		IMN_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'imn_sanitize_settings',
			'default'           => imn_defaults(),
		)
	);
}
add_action( 'admin_init', 'imn_register_settings' );

function imn_sanitize_settings( $input ) {
	$d   = imn_defaults();
	$out = array();

	$out['theme_location'] = isset( $input['theme_location'] ) ? sanitize_text_field( $input['theme_location'] ) : $d['theme_location'];
	$out['hide_theme_nav'] = ! empty( $input['hide_theme_nav'] );
	$out['scope']          = ( isset( $input['scope'] ) && 'all' === $input['scope'] ) ? 'all' : 'mobile';
	$out['breakpoint']     = isset( $input['breakpoint'] ) ? max( 320, min( 2000, (int) $input['breakpoint'] ) ) : $d['breakpoint'];

	$positions          = array( 'inline', 'top-left', 'top-right' );
	$out['position']    = ( isset( $input['position'] ) && in_array( $input['position'], $positions, true ) ) ? $input['position'] : $d['position'];

	$out['fixed_top']       = isset( $input['fixed_top'] ) ? max( -500, min( 500, (float) $input['fixed_top'] ) ) : $d['fixed_top'];
	$out['fixed_side']      = isset( $input['fixed_side'] ) ? max( -500, min( 500, (float) $input['fixed_side'] ) ) : $d['fixed_side'];
	$out['fixed_top_unit']  = ( isset( $input['fixed_top_unit'] ) && 'em' === $input['fixed_top_unit'] ) ? 'em' : 'px';
	$out['fixed_side_unit'] = ( isset( $input['fixed_side_unit'] ) && 'em' === $input['fixed_side_unit'] ) ? 'em' : 'px';

	$out['bar_width']     = isset( $input['bar_width'] ) ? max( 10, min( 200, (int) $input['bar_width'] ) ) : $d['bar_width'];
	$out['bar_thickness'] = isset( $input['bar_thickness'] ) ? max( 1, min( 20, (int) $input['bar_thickness'] ) ) : $d['bar_thickness'];
	$out['bar_gap']       = isset( $input['bar_gap'] ) ? max( 0, min( 40, (int) $input['bar_gap'] ) ) : $d['bar_gap'];
	$out['z_index']       = isset( $input['z_index'] ) ? max( 0, min( 2147483000, (int) $input['z_index'] ) ) : $d['z_index'];

	$out['bar_color']      = imn_sanitize_hex( $input['bar_color'] ?? '', $d['bar_color'] );
	$out['bar_color_open'] = imn_sanitize_hex( $input['bar_color_open'] ?? '', $d['bar_color_open'] );
	$out['overlay_color']  = imn_sanitize_hex( $input['overlay_color'] ?? '', $d['overlay_color'] );
	$out['overlay_color2'] = imn_sanitize_hex( $input['overlay_color2'] ?? '', $d['overlay_color2'] );
	$out['text_color']       = imn_sanitize_hex( $input['text_color'] ?? '', $d['text_color'] );
	$out['text_hover_color'] = imn_sanitize_hex( $input['text_hover_color'] ?? '', $d['text_hover_color'] );

	// Font: keep only if it's a slug the active theme actually registers.
	$out['font_family'] = '';
	if ( isset( $input['font_family'] ) && '' !== $input['font_family'] ) {
		$slug  = sanitize_key( $input['font_family'] );
		$fonts = imn_theme_font_families();
		if ( isset( $fonts[ $slug ] ) ) {
			$out['font_family'] = $slug;
		}
	}

	// Custom font stack (free text), reduced to a safe subset. Overrides the picker.
	$out['font_family_custom'] = isset( $input['font_family_custom'] ) ? imn_sanitize_font_css( $input['font_family_custom'] ) : '';

	$out['text_size']      = isset( $input['text_size'] ) ? max( 0.1, min( 200, (float) $input['text_size'] ) ) : $d['text_size'];
	$out['text_size_unit'] = ( isset( $input['text_size_unit'] ) && 'px' === $input['text_size_unit'] ) ? 'px' : 'em';
	$out['text_size_mobile_override'] = ! empty( $input['text_size_mobile_override'] );
	$out['text_size_mobile']          = isset( $input['text_size_mobile'] ) ? max( 0.1, min( 200, (float) $input['text_size_mobile'] ) ) : $d['text_size_mobile'];
	$out['text_size_mobile_unit']     = ( isset( $input['text_size_mobile_unit'] ) && 'px' === $input['text_size_mobile_unit'] ) ? 'px' : 'em';
	$out['text_underline'] = ! empty( $input['text_underline'] );

	$out['link_pad_y']      = isset( $input['link_pad_y'] ) ? max( 0, min( 200, (float) $input['link_pad_y'] ) ) : $d['link_pad_y'];
	$out['link_pad_y_unit'] = ( isset( $input['link_pad_y_unit'] ) && 'px' === $input['link_pad_y_unit'] ) ? 'px' : 'em';

	$icons               = array( 'plusminus', 'chevron' );
	$out['icon_style']   = ( isset( $input['icon_style'] ) && in_array( $input['icon_style'], $icons, true ) ) ? $input['icon_style'] : $d['icon_style'];

	$out['bg_type']      = ( isset( $input['bg_type'] ) && 'gradient' === $input['bg_type'] ) ? 'gradient' : 'solid';

	$angles                = array( 0, 45, 90, 135, 180, 225, 270, 315 );
	$out['gradient_angle'] = ( isset( $input['gradient_angle'] ) && in_array( (int) $input['gradient_angle'], $angles, true ) ) ? (int) $input['gradient_angle'] : $d['gradient_angle'];

	$out['overlay_opacity'] = isset( $input['overlay_opacity'] ) ? max( 0, min( 100, (int) $input['overlay_opacity'] ) ) : $d['overlay_opacity'];

	// Background image.
	$out['bg_image']    = isset( $input['bg_image'] ) ? esc_url_raw( trim( $input['bg_image'] ) ) : '';
	$out['bg_image_id'] = isset( $input['bg_image_id'] ) ? absint( $input['bg_image_id'] ) : 0;

	$sizes                = array( 'cover', 'contain', 'auto', 'custom' );
	$out['bg_size']       = ( isset( $input['bg_size'] ) && in_array( $input['bg_size'], $sizes, true ) ) ? $input['bg_size'] : $d['bg_size'];
	$out['bg_size_custom'] = isset( $input['bg_size_custom'] ) ? imn_sanitize_css_size( $input['bg_size_custom'] ) : '';
	$positions            = array_merge( imn_bg_positions(), array( 'custom' ) );
	$out['bg_position']        = ( isset( $input['bg_position'] ) && in_array( $input['bg_position'], $positions, true ) ) ? $input['bg_position'] : $d['bg_position'];
	$out['bg_position_custom'] = isset( $input['bg_position_custom'] ) ? imn_sanitize_css_position( $input['bg_position_custom'] ) : '';

	$out['bg_mobile_override']    = ! empty( $input['bg_mobile_override'] );
	$out['bg_size_mobile']        = ( isset( $input['bg_size_mobile'] ) && in_array( $input['bg_size_mobile'], $sizes, true ) ) ? $input['bg_size_mobile'] : $d['bg_size_mobile'];
	$out['bg_size_mobile_custom'] = isset( $input['bg_size_mobile_custom'] ) ? imn_sanitize_css_size( $input['bg_size_mobile_custom'] ) : '';
	$out['bg_position_mobile']        = ( isset( $input['bg_position_mobile'] ) && in_array( $input['bg_position_mobile'], $positions, true ) ) ? $input['bg_position_mobile'] : $d['bg_position_mobile'];
	$out['bg_position_mobile_custom'] = isset( $input['bg_position_mobile_custom'] ) ? imn_sanitize_css_position( $input['bg_position_mobile_custom'] ) : '';

	$anims             = array( 'slide-left', 'slide-right', 'slide-down', 'fade' );
	$out['animation']  = ( isset( $input['animation'] ) && in_array( $input['animation'], $anims, true ) ) ? $input['animation'] : $d['animation'];

	return $out;
}

function imn_sanitize_hex( $value, $fallback ) {
	$value = sanitize_hex_color( $value );
	return $value ? $value : $fallback;
}

function imn_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s         = imn_get_settings();
	$locations = get_registered_nav_menus();
	$fonts     = imn_theme_font_families();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'INSPIRE Mobile Nav', 'inspire-mobile-nav' ); ?></h1>
		<?php imn_settings_notice(); ?>
		<p><?php esc_html_e( 'Place the mobile nav in your header where you want the hamburger to appear. In a theme file such as header.php use:', 'inspire-mobile-nav' ); ?>
			<code>&lt;?php inspire_mobile_nav(); ?&gt;</code><br />
			<?php esc_html_e( 'Or, in post content, a block or a widget area, use the shortcode:', 'inspire-mobile-nav' ); ?>
			<code>[inspire_mobile_nav]</code>
		</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'imn_settings_group' ); ?>
			<table class="form-table" role="presentation">
				<tbody>

				<tr>
					<th scope="row"><label for="imn_theme_location"><?php esc_html_e( 'Theme menu location', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<select id="imn_theme_location" name="<?php echo esc_attr( IMN_OPTION ); ?>[theme_location]">
							<?php if ( empty( $locations ) ) : ?>
								<option value="<?php echo esc_attr( $s['theme_location'] ); ?>"><?php echo esc_html( $s['theme_location'] ); ?></option>
							<?php else : ?>
								<?php foreach ( $locations as $loc_slug => $loc_label ) : ?>
									<option value="<?php echo esc_attr( $loc_slug ); ?>" <?php selected( $s['theme_location'], $loc_slug ); ?>>
										<?php echo esc_html( $loc_label . ' (' . $loc_slug . ')' ); ?>
									</option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
						<p class="description"><?php esc_html_e( 'The menu shown in the overlay. Assign menus to locations under Appearance > Menus.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Hide theme navigation', 'inspire-mobile-nav' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( IMN_OPTION ); ?>[hide_theme_nav]" value="1" <?php checked( ! empty( $s['hide_theme_nav'] ) ); ?> />
							<?php esc_html_e( 'Automatically hide your theme’s own menu for the location above, below the breakpoint (or always, if scope is set to All screen sizes)', 'inspire-mobile-nav' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Turn this off if the same menu location is also used elsewhere on the site (e.g. a footer menu) and you don’t want that hidden too.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_scope"><?php esc_html_e( 'Show navigation on', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<select id="imn_scope" name="<?php echo esc_attr( IMN_OPTION ); ?>[scope]">
							<option value="mobile" <?php selected( $s['scope'], 'mobile' ); ?>><?php esc_html_e( 'Mobile only (up to the breakpoint)', 'inspire-mobile-nav' ); ?></option>
							<option value="all" <?php selected( $s['scope'], 'all' ); ?>><?php esc_html_e( 'All screen sizes', 'inspire-mobile-nav' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Choose “All screen sizes” to use the hamburger menu as your only navigation on desktop as well. With “Hide theme navigation” on above, your theme’s own menu for that location is hidden automatically.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_position"><?php esc_html_e( 'Hamburger placement', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<select id="imn_position" name="<?php echo esc_attr( IMN_OPTION ); ?>[position]">
							<option value="inline" <?php selected( $s['position'], 'inline' ); ?>><?php esc_html_e( 'Inline (where the shortcode sits)', 'inspire-mobile-nav' ); ?></option>
							<option value="top-left" <?php selected( $s['position'], 'top-left' ); ?>><?php esc_html_e( 'Fixed top-left', 'inspire-mobile-nav' ); ?></option>
							<option value="top-right" <?php selected( $s['position'], 'top-right' ); ?>><?php esc_html_e( 'Fixed top-right', 'inspire-mobile-nav' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'The two offset settings below fine-tune the fixed placements. They are ignored for inline placement.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_fixed_top"><?php esc_html_e( 'Fixed offset — top', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<input type="number" id="imn_fixed_top" name="<?php echo esc_attr( IMN_OPTION ); ?>[fixed_top]" value="<?php echo esc_attr( $s['fixed_top'] ); ?>" step="any" style="width:6em;" />
						<select name="<?php echo esc_attr( IMN_OPTION ); ?>[fixed_top_unit]">
							<option value="px" <?php selected( $s['fixed_top_unit'], 'px' ); ?>><?php esc_html_e( 'px', 'inspire-mobile-nav' ); ?></option>
							<option value="em" <?php selected( $s['fixed_top_unit'], 'em' ); ?>><?php esc_html_e( 'em', 'inspire-mobile-nav' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Distance from the top of the screen when placement is fixed top-left or top-right.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_fixed_side"><?php esc_html_e( 'Fixed offset — left / right', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<input type="number" id="imn_fixed_side" name="<?php echo esc_attr( IMN_OPTION ); ?>[fixed_side]" value="<?php echo esc_attr( $s['fixed_side'] ); ?>" step="any" style="width:6em;" />
						<select name="<?php echo esc_attr( IMN_OPTION ); ?>[fixed_side_unit]">
							<option value="px" <?php selected( $s['fixed_side_unit'], 'px' ); ?>><?php esc_html_e( 'px', 'inspire-mobile-nav' ); ?></option>
							<option value="em" <?php selected( $s['fixed_side_unit'], 'em' ); ?>><?php esc_html_e( 'em', 'inspire-mobile-nav' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Distance from the left (for top-left) or right (for top-right) edge.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_breakpoint"><?php esc_html_e( 'Show hamburger up to (px)', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<input type="number" id="imn_breakpoint" name="<?php echo esc_attr( IMN_OPTION ); ?>[breakpoint]" value="<?php echo esc_attr( $s['breakpoint'] ); ?>" min="320" max="2000" step="1" />
						<p class="description"><?php esc_html_e( 'The hamburger and overlay only show at or below this screen width. Above it, your theme’s normal menu shows (if “Hide theme navigation” is on, it’s hidden below this width and shown above it). Ignored when “Show navigation on” is set to All screen sizes.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_bar_width"><?php esc_html_e( 'Hamburger width (px)', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<input type="number" id="imn_bar_width" name="<?php echo esc_attr( IMN_OPTION ); ?>[bar_width]" value="<?php echo esc_attr( $s['bar_width'] ); ?>" min="10" max="200" step="1" />
						<p class="description"><?php esc_html_e( 'How wide the bars are.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_bar_thickness"><?php esc_html_e( 'Bar thickness (px)', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<input type="number" id="imn_bar_thickness" name="<?php echo esc_attr( IMN_OPTION ); ?>[bar_thickness]" value="<?php echo esc_attr( $s['bar_thickness'] ); ?>" min="1" max="20" step="1" />
						<p class="description"><?php esc_html_e( 'Height of each individual bar.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_bar_gap"><?php esc_html_e( 'Space between bars (px)', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<input type="number" id="imn_bar_gap" name="<?php echo esc_attr( IMN_OPTION ); ?>[bar_gap]" value="<?php echo esc_attr( $s['bar_gap'] ); ?>" min="0" max="40" step="1" />
						<p class="description"><?php esc_html_e( 'Gap between the three bars.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Hamburger colour (closed)', 'inspire-mobile-nav' ); ?></th>
					<td><input type="color" name="<?php echo esc_attr( IMN_OPTION ); ?>[bar_color]" value="<?php echo esc_attr( $s['bar_color'] ); ?>" /></td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Hamburger colour (open / X)', 'inspire-mobile-nav' ); ?></th>
					<td><input type="color" name="<?php echo esc_attr( IMN_OPTION ); ?>[bar_color_open]" value="<?php echo esc_attr( $s['bar_color_open'] ); ?>" /></td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_icon_style"><?php esc_html_e( 'Submenu toggle icon', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<select id="imn_icon_style" name="<?php echo esc_attr( IMN_OPTION ); ?>[icon_style]">
							<option value="plusminus" <?php selected( $s['icon_style'], 'plusminus' ); ?>><?php esc_html_e( 'Plus / minus (+ / −)', 'inspire-mobile-nav' ); ?></option>
							<option value="chevron" <?php selected( $s['icon_style'], 'chevron' ); ?>><?php esc_html_e( 'Down / up arrow', 'inspire-mobile-nav' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Shown next to menu items that have a submenu. Tapping it opens the submenu; tapping the link itself still goes to the page.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_bg_type"><?php esc_html_e( 'Overlay background', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<select id="imn_bg_type" name="<?php echo esc_attr( IMN_OPTION ); ?>[bg_type]">
							<option value="solid" <?php selected( $s['bg_type'], 'solid' ); ?>><?php esc_html_e( 'Solid colour', 'inspire-mobile-nav' ); ?></option>
							<option value="gradient" <?php selected( $s['bg_type'], 'gradient' ); ?>><?php esc_html_e( 'Gradient (two colours)', 'inspire-mobile-nav' ); ?></option>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Overlay colour', 'inspire-mobile-nav' ); ?></th>
					<td>
						<input type="color" name="<?php echo esc_attr( IMN_OPTION ); ?>[overlay_color]" value="<?php echo esc_attr( $s['overlay_color'] ); ?>" />
						<p class="description"><?php esc_html_e( 'For a gradient this is the start colour.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Gradient end colour', 'inspire-mobile-nav' ); ?></th>
					<td>
						<input type="color" name="<?php echo esc_attr( IMN_OPTION ); ?>[overlay_color2]" value="<?php echo esc_attr( $s['overlay_color2'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Only used when the background is set to Gradient.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_gradient_angle"><?php esc_html_e( 'Gradient direction', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<select id="imn_gradient_angle" name="<?php echo esc_attr( IMN_OPTION ); ?>[gradient_angle]">
							<option value="180" <?php selected( (int) $s['gradient_angle'], 180 ); ?>><?php esc_html_e( 'Top → bottom', 'inspire-mobile-nav' ); ?></option>
							<option value="0" <?php selected( (int) $s['gradient_angle'], 0 ); ?>><?php esc_html_e( 'Bottom → top', 'inspire-mobile-nav' ); ?></option>
							<option value="90" <?php selected( (int) $s['gradient_angle'], 90 ); ?>><?php esc_html_e( 'Left → right', 'inspire-mobile-nav' ); ?></option>
							<option value="270" <?php selected( (int) $s['gradient_angle'], 270 ); ?>><?php esc_html_e( 'Right → left', 'inspire-mobile-nav' ); ?></option>
							<option value="135" <?php selected( (int) $s['gradient_angle'], 135 ); ?>><?php esc_html_e( 'Top-left → bottom-right', 'inspire-mobile-nav' ); ?></option>
							<option value="225" <?php selected( (int) $s['gradient_angle'], 225 ); ?>><?php esc_html_e( 'Top-right → bottom-left', 'inspire-mobile-nav' ); ?></option>
							<option value="45" <?php selected( (int) $s['gradient_angle'], 45 ); ?>><?php esc_html_e( 'Bottom-left → top-right', 'inspire-mobile-nav' ); ?></option>
							<option value="315" <?php selected( (int) $s['gradient_angle'], 315 ); ?>><?php esc_html_e( 'Bottom-right → top-left', 'inspire-mobile-nav' ); ?></option>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_overlay_opacity"><?php esc_html_e( 'Overlay opacity (%)', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<input type="number" id="imn_overlay_opacity" name="<?php echo esc_attr( IMN_OPTION ); ?>[overlay_opacity]" value="<?php echo esc_attr( $s['overlay_opacity'] ); ?>" min="0" max="100" step="1" />
						<p class="description"><?php esc_html_e( '100 = solid, lower = more see-through.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<?php
				$imn_size_labels = array(
					'cover'   => __( 'Cover (fill the overlay, may crop)', 'inspire-mobile-nav' ),
					'contain' => __( 'Contain (fit inside, no crop)', 'inspire-mobile-nav' ),
					'auto'    => __( 'Auto (image’s own size)', 'inspire-mobile-nav' ),
					'custom'  => __( 'Custom…', 'inspire-mobile-nav' ),
				);
				$imn_pos_labels = array(
					'left top'      => __( 'Left top', 'inspire-mobile-nav' ),
					'center top'    => __( 'Centre top', 'inspire-mobile-nav' ),
					'right top'     => __( 'Right top', 'inspire-mobile-nav' ),
					'left center'   => __( 'Left centre', 'inspire-mobile-nav' ),
					'center center' => __( 'Centre', 'inspire-mobile-nav' ),
					'right center'  => __( 'Right centre', 'inspire-mobile-nav' ),
					'left bottom'   => __( 'Left bottom', 'inspire-mobile-nav' ),
					'center bottom' => __( 'Centre bottom', 'inspire-mobile-nav' ),
					'right bottom'  => __( 'Right bottom', 'inspire-mobile-nav' ),
					'custom'        => __( 'Custom…', 'inspire-mobile-nav' ),
				);
				?>

				<tr>
					<th scope="row"><label for="imn_bg_image"><?php esc_html_e( 'Overlay background image', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<input type="hidden" id="imn_bg_image_id" name="<?php echo esc_attr( IMN_OPTION ); ?>[bg_image_id]" value="<?php echo esc_attr( $s['bg_image_id'] ); ?>" />
						<input type="url" id="imn_bg_image" name="<?php echo esc_attr( IMN_OPTION ); ?>[bg_image]" value="<?php echo esc_attr( $s['bg_image'] ); ?>" class="regular-text" placeholder="https://…" />
						<p>
							<button type="button" class="button" id="imn_bg_image_select"><?php esc_html_e( 'Select image', 'inspire-mobile-nav' ); ?></button>
							<button type="button" class="button" id="imn_bg_image_remove"><?php esc_html_e( 'Remove', 'inspire-mobile-nav' ); ?></button>
						</p>
						<div id="imn_bg_image_preview">
							<?php if ( ! empty( $s['bg_image'] ) ) : ?>
								<img src="<?php echo esc_url( $s['bg_image'] ); ?>" alt="" style="max-width:320px;height:auto;margin-top:8px;border:1px solid #dcdcde;border-radius:4px;display:block;" />
							<?php endif; ?>
						</div>
						<p class="description"><?php esc_html_e( 'Optional. Sits on top of the overlay colour, behind the menu text. Use a transparent image, or Contain/Auto sizing, to let the overlay colour show around or through it.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_bg_size"><?php esc_html_e( 'Background size', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<select id="imn_bg_size" name="<?php echo esc_attr( IMN_OPTION ); ?>[bg_size]">
							<?php foreach ( $imn_size_labels as $val => $label ) : ?>
								<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $s['bg_size'], $val ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<input type="text" name="<?php echo esc_attr( IMN_OPTION ); ?>[bg_size_custom]" value="<?php echo esc_attr( $s['bg_size_custom'] ); ?>" class="regular-text" placeholder="80%  or  300px auto" style="max-width:14em;" />
						<p class="description"><?php esc_html_e( 'The custom field is used only when “Custom…” is selected. Accepts any CSS background-size, e.g. 80% or 300px auto.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_bg_position"><?php esc_html_e( 'Background position', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<select id="imn_bg_position" name="<?php echo esc_attr( IMN_OPTION ); ?>[bg_position]">
							<?php foreach ( $imn_pos_labels as $val => $label ) : ?>
								<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $s['bg_position'], $val ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<input type="text" name="<?php echo esc_attr( IMN_OPTION ); ?>[bg_position_custom]" value="<?php echo esc_attr( $s['bg_position_custom'] ); ?>" class="regular-text" placeholder="20% 50%  or  left 20% center" style="max-width:16em;" />
						<p class="description"><?php esc_html_e( 'The custom field is used only when “Custom…” is selected. Accepts any CSS background-position, e.g. 20% 50% (X then Y), or left 20% center to shift a named edge.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Different image size/position on mobile', 'inspire-mobile-nav' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( IMN_OPTION ); ?>[bg_mobile_override]" value="1" <?php checked( ! empty( $s['bg_mobile_override'] ) ); ?> />
							<?php esc_html_e( 'Use separate size and position at or below the breakpoint', 'inspire-mobile-nav' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'When off, mobile uses the same size and position as above. The image itself is the same on all screens.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_bg_size_mobile"><?php esc_html_e( 'Background size (mobile)', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<select id="imn_bg_size_mobile" name="<?php echo esc_attr( IMN_OPTION ); ?>[bg_size_mobile]">
							<?php foreach ( $imn_size_labels as $val => $label ) : ?>
								<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $s['bg_size_mobile'], $val ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<input type="text" name="<?php echo esc_attr( IMN_OPTION ); ?>[bg_size_mobile_custom]" value="<?php echo esc_attr( $s['bg_size_mobile_custom'] ); ?>" class="regular-text" placeholder="80%  or  300px auto" style="max-width:14em;" />
						<p class="description"><?php esc_html_e( 'Applied only when “Different image size/position on mobile” is ticked.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_bg_position_mobile"><?php esc_html_e( 'Background position (mobile)', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<select id="imn_bg_position_mobile" name="<?php echo esc_attr( IMN_OPTION ); ?>[bg_position_mobile]">
							<?php foreach ( $imn_pos_labels as $val => $label ) : ?>
								<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $s['bg_position_mobile'], $val ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<input type="text" name="<?php echo esc_attr( IMN_OPTION ); ?>[bg_position_mobile_custom]" value="<?php echo esc_attr( $s['bg_position_mobile_custom'] ); ?>" class="regular-text" placeholder="20% 50%  or  left 20% center" style="max-width:16em;" />
						<p class="description"><?php esc_html_e( 'Applied only when the mobile override is ticked and “Custom…” is selected.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Menu text colour', 'inspire-mobile-nav' ); ?></th>
					<td><input type="color" name="<?php echo esc_attr( IMN_OPTION ); ?>[text_color]" value="<?php echo esc_attr( $s['text_color'] ); ?>" /></td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Menu text colour (hover)', 'inspire-mobile-nav' ); ?></th>
					<td>
						<input type="color" name="<?php echo esc_attr( IMN_OPTION ); ?>[text_hover_color]" value="<?php echo esc_attr( $s['text_hover_color'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Colour of a menu link when hovered or focused.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_font_family"><?php esc_html_e( 'Menu font', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<select id="imn_font_family" name="<?php echo esc_attr( IMN_OPTION ); ?>[font_family]">
							<option value="" <?php selected( $s['font_family'], '' ); ?>><?php esc_html_e( 'Theme default (inherit)', 'inspire-mobile-nav' ); ?></option>
							<?php foreach ( $fonts as $font_slug => $font ) : ?>
								<option value="<?php echo esc_attr( $font_slug ); ?>" <?php selected( $s['font_family'], $font_slug ); ?>>
									<?php echo esc_html( $font['name'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<?php
							if ( empty( $fonts ) ) {
								esc_html_e( 'Your active theme doesn’t register any fonts (no theme.json), so the menu inherits the theme’s own font. Choose a block theme, or a theme that registers fonts, to pick from a list here.', 'inspire-mobile-nav' );
							} else {
								esc_html_e( 'Fonts are read from your active theme. “Theme default” inherits whatever font your theme already uses for the menu.', 'inspire-mobile-nav' );
							}
							?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_font_family_custom"><?php esc_html_e( 'Custom font family', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<input type="text" id="imn_font_family_custom" name="<?php echo esc_attr( IMN_OPTION ); ?>[font_family_custom]" value="<?php echo esc_attr( $s['font_family_custom'] ); ?>" class="regular-text" placeholder="&quot;Poppins&quot;, sans-serif" />
						<p class="description"><?php esc_html_e( 'Optional. Type any CSS font-family stack, e.g. "Poppins", sans-serif. When set, this overrides the Menu font dropdown above. Use it when your theme loads a font some other way than theme.json. The font itself must already be loaded on the site.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_text_size"><?php esc_html_e( 'Menu text size', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<input type="number" id="imn_text_size" name="<?php echo esc_attr( IMN_OPTION ); ?>[text_size]" value="<?php echo esc_attr( $s['text_size'] ); ?>" step="any" min="0.1" style="width:6em;" />
						<select name="<?php echo esc_attr( IMN_OPTION ); ?>[text_size_unit]">
							<option value="em" <?php selected( $s['text_size_unit'], 'em' ); ?>><?php esc_html_e( 'em', 'inspire-mobile-nav' ); ?></option>
							<option value="px" <?php selected( $s['text_size_unit'], 'px' ); ?>><?php esc_html_e( 'px', 'inspire-mobile-nav' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Size of the top-level menu links. Submenu links scale to 80% of this.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Different text size on mobile', 'inspire-mobile-nav' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( IMN_OPTION ); ?>[text_size_mobile_override]" value="1" <?php checked( ! empty( $s['text_size_mobile_override'] ) ); ?> />
							<?php esc_html_e( 'Use a different menu text size at or below the breakpoint', 'inspire-mobile-nav' ); ?>
						</label>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_text_size_mobile"><?php esc_html_e( 'Menu text size (mobile)', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<input type="number" id="imn_text_size_mobile" name="<?php echo esc_attr( IMN_OPTION ); ?>[text_size_mobile]" value="<?php echo esc_attr( $s['text_size_mobile'] ); ?>" step="any" min="0.1" style="width:6em;" />
						<select name="<?php echo esc_attr( IMN_OPTION ); ?>[text_size_mobile_unit]">
							<option value="em" <?php selected( $s['text_size_mobile_unit'], 'em' ); ?>><?php esc_html_e( 'em', 'inspire-mobile-nav' ); ?></option>
							<option value="px" <?php selected( $s['text_size_mobile_unit'], 'px' ); ?>><?php esc_html_e( 'px', 'inspire-mobile-nav' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Applied only when “Different text size on mobile” is ticked. Submenu links stay at 80% of this.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_link_pad_y"><?php esc_html_e( 'Link vertical padding', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<input type="number" id="imn_link_pad_y" name="<?php echo esc_attr( IMN_OPTION ); ?>[link_pad_y]" value="<?php echo esc_attr( $s['link_pad_y'] ); ?>" step="any" min="0" style="width:6em;" />
						<select name="<?php echo esc_attr( IMN_OPTION ); ?>[link_pad_y_unit]">
							<option value="em" <?php selected( $s['link_pad_y_unit'], 'em' ); ?>><?php esc_html_e( 'em', 'inspire-mobile-nav' ); ?></option>
							<option value="px" <?php selected( $s['link_pad_y_unit'], 'px' ); ?>><?php esc_html_e( 'px', 'inspire-mobile-nav' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Space above and below each menu link. Increase it to spread the menu items out, decrease it to pack them closer.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Underline links', 'inspire-mobile-nav' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( IMN_OPTION ); ?>[text_underline]" value="1" <?php checked( ! empty( $s['text_underline'] ) ); ?> />
							<?php esc_html_e( 'Show an underline under menu links', 'inspire-mobile-nav' ); ?>
						</label>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_animation"><?php esc_html_e( 'Open animation', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<select id="imn_animation" name="<?php echo esc_attr( IMN_OPTION ); ?>[animation]">
							<option value="slide-left" <?php selected( $s['animation'], 'slide-left' ); ?>><?php esc_html_e( 'Slide in from left', 'inspire-mobile-nav' ); ?></option>
							<option value="slide-right" <?php selected( $s['animation'], 'slide-right' ); ?>><?php esc_html_e( 'Slide in from right', 'inspire-mobile-nav' ); ?></option>
							<option value="slide-down" <?php selected( $s['animation'], 'slide-down' ); ?>><?php esc_html_e( 'Slide down from top', 'inspire-mobile-nav' ); ?></option>
							<option value="fade" <?php selected( $s['animation'], 'fade' ); ?>><?php esc_html_e( 'Fade in', 'inspire-mobile-nav' ); ?></option>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="imn_z_index"><?php esc_html_e( 'Menu z-index (stacking)', 'inspire-mobile-nav' ); ?></label></th>
					<td>
						<input type="number" id="imn_z_index" name="<?php echo esc_attr( IMN_OPTION ); ?>[z_index]" value="<?php echo esc_attr( $s['z_index'] ); ?>" min="0" step="1" />
						<p class="description"><?php esc_html_e( 'Stacking order of the open menu. To keep another element (e.g. a logo) on top of the menu, set this below that element’s z-index — for a logo at 10000, use 9999 or lower.', 'inspire-mobile-nav' ); ?></p>
					</td>
				</tr>

				</tbody>
			</table>
			<?php submit_button(); ?>
		</form>

		<hr />

		<h2><?php esc_html_e( 'Export / import settings', 'inspire-mobile-nav' ); ?></h2>
		<p class="description" style="max-width:40em;">
			<?php esc_html_e( 'Move these settings to another site. Export downloads a JSON file; import it on the other site. Note: the background image itself is not included — only its URL and ID — so you may need to re-select the image on the other site.', 'inspire-mobile-nav' ); ?>
		</p>
		<p>
			<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=imn_export' ), 'imn_export' ) ); ?>">
				<?php esc_html_e( 'Export settings', 'inspire-mobile-nav' ); ?>
			</a>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" style="margin-top:1em;">
			<input type="hidden" name="action" value="imn_import" />
			<?php wp_nonce_field( 'imn_import' ); ?>
			<input type="file" name="imn_import_file" accept="application/json,.json" required />
			<?php submit_button( __( 'Import settings', 'inspire-mobile-nav' ), 'secondary', 'submit', false ); ?>
			<p class="description"><?php esc_html_e( 'Importing overwrites all current settings on this site.', 'inspire-mobile-nav' ); ?></p>
		</form>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * Export / import settings (JSON)
 * ---------------------------------------------------------------------- */

/**
 * Download the current settings as a JSON file.
 */
function imn_handle_export() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to export these settings.', 'inspire-mobile-nav' ) );
	}
	check_admin_referer( 'imn_export' );

	$payload = array(
		'plugin'   => 'inspire-mobile-nav',
		'schema'   => 1,
		'version'  => IMN_VERSION,
		'exported' => gmdate( 'c' ),
		'settings' => imn_get_settings(),
	);

	$filename = 'inspire-mobile-nav-settings-' . gmdate( 'Ymd' ) . '.json';

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	exit;
}
add_action( 'admin_post_imn_export', 'imn_handle_export' );

/**
 * Import settings from an uploaded JSON file. The decoded settings are run
 * through the normal sanitiser, so unknown keys are dropped and values clamped.
 */
function imn_handle_import() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to import these settings.', 'inspire-mobile-nav' ) );
	}
	check_admin_referer( 'imn_import' );

	$redirect = admin_url( 'options-general.php?page=inspire-mobile-nav' );

	if ( empty( $_FILES['imn_import_file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['imn_import_file']['tmp_name'] ) ) {
		wp_safe_redirect( add_query_arg( 'imn_notice', 'nofile', $redirect ) );
		exit;
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- raw JSON, decoded and sanitised below.
	$raw  = file_get_contents( $_FILES['imn_import_file']['tmp_name'] );
	$data = json_decode( $raw, true );

	if ( ! is_array( $data ) ) {
		wp_safe_redirect( add_query_arg( 'imn_notice', 'badjson', $redirect ) );
		exit;
	}

	// Accept either the wrapped export ({settings:{…}}) or a bare settings object.
	$settings = ( isset( $data['settings'] ) && is_array( $data['settings'] ) ) ? $data['settings'] : $data;

	update_option( IMN_OPTION, imn_sanitize_settings( $settings ) );

	wp_safe_redirect( add_query_arg( 'imn_notice', 'imported', $redirect ) );
	exit;
}
add_action( 'admin_post_imn_import', 'imn_handle_import' );

/**
 * Print an admin notice after an import (or import error).
 */
function imn_settings_notice() {
	if ( empty( $_GET['imn_notice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$notice = sanitize_key( wp_unslash( $_GET['imn_notice'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$map = array(
		'imported' => array( 'success', __( 'Settings imported.', 'inspire-mobile-nav' ) ),
		'nofile'   => array( 'error', __( 'No file was uploaded, or the upload failed.', 'inspire-mobile-nav' ) ),
		'badjson'  => array( 'error', __( 'That file could not be read as valid JSON.', 'inspire-mobile-nav' ) ),
	);
	if ( ! isset( $map[ $notice ] ) ) {
		return;
	}
	printf(
		'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
		esc_attr( $map[ $notice ][0] ),
		esc_html( $map[ $notice ][1] )
	);
}

/**
 * Add a "Settings" link on the Plugins screen.
 */
function imn_plugin_action_links( $links ) {
	$url = admin_url( 'options-general.php?page=inspire-mobile-nav' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'inspire-mobile-nav' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'imn_plugin_action_links' );

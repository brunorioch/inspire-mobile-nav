=== INSPIRE Mobile Nav ===
Contributors: inspiredm
Tags: mobile, menu, navigation, hamburger, responsive
Requires at least: 5.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.9.6
License: GPLv2 or later

A simple, self-contained mobile navigation: a hamburger button and full-screen
overlay menu you drop in with a shortcode. The theme is left untouched.

== What it does ==

* Adds a hamburger button and a full-screen overlay menu via a shortcode.
* One settings page controls everything: theme menu location, hamburger
  placement and colours (closed and open), submenu toggle icon (+/- or up/down
  arrow), overlay colour and transparency, menu text colour, open animation,
  and the screen width the hamburger shows at.
* Parent menu items still link to their page. The +/- (or arrow) button next to
  them opens the submenu.

== How to use ==

1. Install and activate the plugin.
2. Go to Settings > Mobile Nav and choose your options.
3. Place the mobile nav where you want the hamburger to appear:

   * In a theme file such as header.php (recommended):

         <?php inspire_mobile_nav(); ?>

   * In post content, a block or a widget area, use the shortcode:

         [inspire_mobile_nav]

== Menu ==

The overlay shows whichever menu is assigned to the theme location you pick in
Settings > Mobile Nav. Assign menus to locations under Appearance > Menus. To
use a separate mobile-only menu, create it there and assign it to the location
you select.

== Notes ==

* The hamburger and overlay only appear at or below the width you set
  ("Show hamburger up to"). Above that, your theme's normal menu shows.
* With "Hide theme navigation" turned on (the default), the theme's own
  render of the selected menu location is automatically hidden below the
  breakpoint (or always, if scope is set to "All screen sizes") — so you
  don't end up with two navs on screen at once. Turn it off if that same
  menu location is also used elsewhere on the site, e.g. in a footer menu.
* Uses jQuery (bundled with WordPress). No external dependencies.

== Changelog ==

= 1.9.6 =
* Sites upgrading from 1.1.0 keep their menu link colour on hover instead
  of switching to the new light grey default. The hover colour is set to the
  site's text colour once, on upgrade, and can be changed in Settings.

= 1.9.5 =
* WordPress now checks the minimum version (5.5) before installing or
  updating the plugin.
* Tested with WordPress 7.1.

= 1.9.4 =
* New: the plugin now updates itself from GitHub. New versions show up as
  the usual "Update available" notice under Plugins.
* Now needs PHP 7.4 or newer.

= 1.9.3 =
* Fixed: the menu could flash up unstyled (a plain checkbox and an open list
  of links) while a page loaded. The stylesheet now loads in the page <head>
  instead of the footer.
* Fixed: with "Hide theme navigation" on, the theme's own menu could show
  briefly on mobile before being hidden. The hiding rule is now in the <head>
  too (with the old footer copy kept for browsers without :has() support).

= 1.9.2 =
* Accessibility: the menu toggle is now announced as a "Menu" switch that is
  on while the menu is open, instead of an "Open menu" checkbox whose name
  stayed the same once open. Enter now opens and closes it as well as Space.
* Accessibility: the hamburger's hidden "Toggle menu" text is no longer read
  out as well as the toggle's own name.

= 1.9.1 =
* Fixed: the overlay could reappear open, without its scroll lock, when a
  visitor left a page with the menu open and came back with the Back button
  (or reloaded in some browsers). The menu now always starts closed.

= 1.9.0 =
* Added: export and import settings as a JSON file, to copy a configuration
  to another site. Imports are re-sanitised on the way in. The background
  image file is not included (only its URL and ID), so it may need
  re-selecting on the other site.

= 1.8.0 =
* Added: optional separate menu text size on mobile (px or em), applied at or
  below the breakpoint. Submenu links stay at 80% of it.

= 1.7.0 =
* Added: custom background-position option (desktop and mobile). Choose
  "Custom…" and enter any CSS value, e.g. 20% 50%, or left 20% center to
  offset a named edge by a percentage.

= 1.6.1 =
* Changed: the overlay background image now sits on top of the overlay
  colour (the colour shows around or through it) rather than being tinted
  by it.

= 1.6.0 =
* Added: overlay background image, chosen from the media library, with
  background-size (cover/contain/auto/custom) and a 9-point position control.
  The image sits on top of the overlay colour, which shows around or through
  it. Optional separate size and position on mobile (at or below the
  breakpoint); the image itself is the same on all screens.

= 1.5.0 =
* Added: link vertical padding control (px or em) to space menu items out
  or pack them closer. Defaults to 0.45em, matching previous behaviour.

= 1.4.0 =
* Added: custom font family field. Type any CSS font-family stack to override
  the theme.json font picker — useful when a theme loads its font some other
  way. The font must already be loaded on the site.

= 1.3.0 =
* Added: menu text controls — text size (px or em), a font picked from the
  active theme's registered fonts (theme.json), an underline on/off toggle,
  and a separate hover/focus colour for menu links. Submenu links scale to
  80% of the chosen size. On themes that don't register fonts, the menu
  inherits the theme's own font.

= 1.2.0 =
* Added: fixed-placement offset controls. When the hamburger is fixed
  top-left or top-right, set its distance from the top and from the side
  in px or em to fine-tune the position. Defaults to 20px / 20px, matching
  previous behaviour.

= 1.1.0 =
* Added: automatically hide the theme's own navigation for the selected
  menu location below the breakpoint (or always, on "All screen sizes"),
  with a setting to turn it off.
* Added: uninstall.php removes the plugin's saved settings on deletion.
* Improved: overlay menu now supports Escape-to-close and traps keyboard
  focus while open.

= 1.0.0 =
* First release.

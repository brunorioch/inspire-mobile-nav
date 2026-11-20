/* INSPIRE Mobile Nav
 * Opens/closes the overlay, locks scroll, and adds a submenu toggle button
 * next to items that have children. The toggle is a separate button, so
 * tapping a parent link still navigates to its page.
 *
 * Also handles keyboard accessibility while the overlay is open: Escape
 * closes it and returns focus to the hamburger toggle, and Tab/Shift+Tab
 * are trapped inside the menu so focus can't land on the hidden page
 * content behind it.
 *
 * The overlay always starts closed, including when a page is restored with
 * the Back button after being left with the menu open.
 *
 * The checkbox toggle is exposed as a "Menu" switch (on when the menu is
 * open), and Enter works as well as Space.
 */
(function ($) {
	'use strict';

	function focusableIn($context) {
		return $context
			.find('a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])')
			.filter(':visible');
	}

	function iconSvg(style) {
		if (style === 'chevron') {
			return (
				'<svg viewBox="0 0 20 20" width="20" height="20" aria-hidden="true">' +
				'<polyline points="5,8 10,13 15,8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
				'</svg>'
			);
		}
		// plus / minus
		return (
			'<svg viewBox="0 0 20 20" width="20" height="20" aria-hidden="true">' +
			'<line x1="3" y1="10" x2="17" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>' +
			'<line class="imn-vertical" x1="10" y1="3" x2="10" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>' +
			'</svg>'
		);
	}

	$(function () {
		$('.imn').each(function () {
			var $wrap = $(this);
			var $toggle = $wrap.find('.imn-toggle');
			var iconStyle = $wrap.attr('data-icon') || 'plusminus';

			function isOpen() {
				return $toggle.is(':checked');
			}

			function closeMenu() {
				$toggle.prop('checked', false);
				$('html').removeClass('imn-open');
			}

			// Space toggles the switch natively; let Enter open and close it too.
			$toggle.on('keydown', function (e) {
				if (e.key === 'Enter' || e.keyCode === 13) {
					e.preventDefault();
					this.click();
				}
			});

			// Always start closed. Some browsers restore a ticked checkbox on
			// reload or from the back/forward cache without firing "change",
			// which would show the overlay open without its scroll lock.
			closeMenu();
			$(window).on('pageshow', function (e) {
				if (e.originalEvent && e.originalEvent.persisted) {
					closeMenu();
				}
			});

			// Reflect open/closed state on <html> for scroll-lock, and move
			// focus into the overlay when it opens.
			$toggle.on('change', function () {
				var open = this.checked;
				$('html').toggleClass('imn-open', open);
				if (open) {
					var $first = focusableIn($wrap.find('.imn-overlay')).first();
					if ($first.length) {
						$first.trigger('focus');
					}
				}
			});

			// Close the overlay when a real menu link is clicked.
			$wrap.on('click', '.imn-menu a', function () {
				closeMenu();
			});

			// Escape closes the menu and returns focus to the toggle; Tab
			// and Shift+Tab are trapped within the wrapper while it's open.
			$wrap.on('keydown', function (e) {
				if (!isOpen()) {
					return;
				}

				if (e.key === 'Escape' || e.keyCode === 27) {
					closeMenu();
					$toggle.trigger('focus');
					return;
				}

				if (e.key !== 'Tab' && e.keyCode !== 9) {
					return;
				}

				var $focusable = focusableIn($wrap);
				if (!$focusable.length) {
					return;
				}
				var first = $focusable[0];
				var last = $focusable[$focusable.length - 1];

				if (e.shiftKey && document.activeElement === first) {
					e.preventDefault();
					last.focus();
				} else if (!e.shiftKey && document.activeElement === last) {
					e.preventDefault();
					first.focus();
				}
			});

			// Build a toggle button next to each parent item.
			$wrap.find('.imn-menu .menu-item-has-children').each(function () {
				var $li = $(this);
				if ($li.children('.imn-indicator').length) {
					return; // already processed
				}
				$li.children('.sub-menu').hide();
				var $btn = $(
					'<button type="button" class="imn-indicator" aria-expanded="false" aria-label="Toggle submenu">' +
						iconSvg(iconStyle) +
						'</button>'
				);
				// Insert right after the parent link.
				$li.children('a').first().after($btn);
			});

			// Toggle a submenu open/closed.
			$wrap.on('click', '.imn-indicator', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var $li = $(this).closest('.menu-item-has-children');
				var wasOpen = $li.hasClass('is-open');
				$li.toggleClass('is-open', !wasOpen);
				$(this).attr('aria-expanded', wasOpen ? 'false' : 'true');
				$li.children('.sub-menu').stop(true, true).slideToggle(200);
			});
		});
	});
})(jQuery);

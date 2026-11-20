/* INSPIRE Mobile Nav — settings page
 * Wires the "Select image" button to the WordPress media library and keeps the
 * URL field, hidden attachment-ID field, and preview in sync. The URL text
 * field still works on its own (paste a URL) if JavaScript is unavailable.
 */
(function ($) {
	'use strict';

	$(function () {
		var frame;
		var $url = $('#imn_bg_image');
		var $id = $('#imn_bg_image_id');
		var $preview = $('#imn_bg_image_preview');

		function renderPreview(url) {
			if (url) {
				$preview.html(
					'<img src="' + url + '" alt="" style="max-width:320px;height:auto;margin-top:8px;border:1px solid #dcdcde;border-radius:4px;display:block;" />'
				);
			} else {
				$preview.empty();
			}
		}

		$('#imn_bg_image_select').on('click', function (e) {
			e.preventDefault();

			if (frame) {
				frame.open();
				return;
			}

			frame = wp.media({
				title: 'Select background image',
				button: { text: 'Use this image' },
				library: { type: 'image' },
				multiple: false
			});

			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				var url = att.url;
				// Prefer a large-ish size if available.
				if (att.sizes && att.sizes.large) {
					url = att.sizes.large.url;
				} else if (att.sizes && att.sizes.full) {
					url = att.sizes.full.url;
				}
				$url.val(url);
				$id.val(att.id);
				renderPreview(url);
			});

			frame.open();
		});

		$('#imn_bg_image_remove').on('click', function (e) {
			e.preventDefault();
			$url.val('');
			$id.val('');
			renderPreview('');
		});

		// Keep the preview in sync if the URL is typed or pasted directly.
		$url.on('change', function () {
			renderPreview($url.val());
		});
	});
})(jQuery);

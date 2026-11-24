import $ from 'jquery';

import { removeFullscreenButton } from './fullscreen';
import { Shortcode } from './Shortcode';
import { handleUnsupportedVideo } from './unsupportedVideo';

interface ShortcodeRegistry {
	init(): void;
	onLightboxQuit(): void;
	reflowAll(): void;
	shortcodes: Record<string, Shortcode>;
}

export const shortcodeRegistry: ShortcodeRegistry = {
	init(): void {
		$('.sgdg-gallery-container').each((_, container) => {
			const hash = $(container).data('sgdgHash') as string | undefined;
			if (hash !== undefined) {
				this.shortcodes[hash.substring(0, 8)] = new Shortcode(
					container,
					hash
				);
			}
		});

		$(document).on('ilb:quit', () => {
			this.onLightboxQuit();
		});
		$(document).on('ilb:loaded', () => {
			handleUnsupportedVideo();
		});
	},

	onLightboxQuit(): void {
		removeFullscreenButton();
		$.each(this.shortcodes, (_, shortcode) => {
			shortcode.onLightboxQuit();
		});
	},

	reflowAll(): void {
		$.each(this.shortcodes, (_, shortcode) => {
			shortcode.reflow();
		});
	},

	shortcodes: {},
};

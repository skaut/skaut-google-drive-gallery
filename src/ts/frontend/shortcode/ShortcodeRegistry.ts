import $ from 'jquery';

import { Shortcode } from './Shortcode';

interface ShortcodeRegistry {
	init(): void;
	onLightboxQuit(): void;
	reflowAll(): void;
	shortcodes: Record<string, Shortcode>;
}

export const shortcodeRegistry: ShortcodeRegistry = {
	shortcodes: {},

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
	},

	reflowAll(): void {
		$.each(this.shortcodes, (_, shortcode) => {
			shortcode.reflow();
		});
	},

	onLightboxQuit(): void {
		$.each(this.shortcodes, (_, shortcode) => {
			shortcode.onLightboxQuit();
		});
	},
};

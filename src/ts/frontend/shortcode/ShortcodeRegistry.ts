import { Shortcode } from './Shortcode';

interface ShortcodeRegistry {
	shortcodes: Record<string, Shortcode>;
	init(): void;
	reflowAll(): void;
	onLightboxNavigation(e: Readonly<JQuery>): void;
	onLightboxQuit(): void;
}

export const ShortcodeRegistry: ShortcodeRegistry = {
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

		$(document).on(
			'start.ilb2 next.ilb2 previous.ilb2',
			(_, e: Readonly<JQuery>) => {
				this.onLightboxNavigation(e);
			}
		);
		$(document).on('quit.ilb2', () => {
			this.onLightboxQuit();
		});
	},

	reflowAll(): void {
		$.each(this.shortcodes, function (_, shortcode: Readonly<Shortcode>) {
			shortcode.reflow();
		});
	},

	onLightboxNavigation(e: Readonly<JQuery>): void {
		const hash = $(e).data('imagelightbox') as string;
		this.shortcodes[hash].onLightboxNavigation(e);
	},

	onLightboxQuit(): void {
		$.each(this.shortcodes, function (_, shortcode: Readonly<Shortcode>) {
			shortcode.onLightboxQuit();
		});
	},
};

/*
 * The fullscreen button of the lightbox.
 *
 * The lightbox is a modal `<dialog>`, which browsers refuse to show in fullscreen ("Dialog elements are invalid"), so the button of the imagelightbox library does nothing. Instead, the whole page is shown in fullscreen, with the lightbox on top of it. The button uses the ID of the library button, so that it keeps its look.
 */

const BUTTON_ID = 'ilb-fullscreen-button';

/*
 * Older Safari versions only support the prefixed API, so both variants are optional.
 */
interface FullscreenDocument {
	exitFullscreen?(): Promise<void>;
	fullscreenElement?: Element | null;
	fullscreenEnabled?: boolean;
	webkitExitFullscreen?(): void;
	webkitFullscreenElement?: Element | null;
	webkitFullscreenEnabled?: boolean;
}

interface FullscreenElement {
	requestFullscreen?(): Promise<void>;
	webkitRequestFullscreen?(): void;
}

/*
 * Adds the fullscreen button to the lightbox, if the browser supports fullscreen.
 */
export function addFullscreenButton(): void {
	const container = document.getElementById('ilb-container');
	if (
		container === null ||
		!isFullscreenSupported() ||
		document.getElementById(BUTTON_ID) !== null
	) {
		return;
	}
	const button = document.createElement('button');
	button.id = BUTTON_ID;
	button.className = 'ilb-button';
	button.innerHTML = '&#x26F6;';
	button.addEventListener('click', (e) => {
		// Don't let the lightbox treat the click as a click outside of the image (which closes it).
		e.stopPropagation();
		if (fullscreenElement() === null) {
			enterFullscreen();
		} else {
			exitFullscreen();
		}
	});
	container.appendChild(button);
}

/*
 * Removes the fullscreen button and leaves fullscreen when the lightbox is closed.
 */
export function removeFullscreenButton(): void {
	document.getElementById(BUTTON_ID)?.remove();
	exitFullscreen();
}

function enterFullscreen(): void {
	const root = document.documentElement as FullscreenElement;
	if (root.requestFullscreen !== undefined) {
		root.requestFullscreen.call(document.documentElement).catch(() => {
			// The browser refused, nothing to do.
		});
		return;
	}
	root.webkitRequestFullscreen?.call(document.documentElement);
}

function exitFullscreen(): void {
	if (fullscreenElement() === null) {
		return;
	}
	const doc = fullscreenDocument();
	if (doc.exitFullscreen !== undefined) {
		doc.exitFullscreen.call(document).catch(() => {
			// Already left fullscreen, nothing to do.
		});
		return;
	}
	doc.webkitExitFullscreen?.call(document);
}

function fullscreenDocument(): FullscreenDocument {
	return document;
}

function fullscreenElement(): Element | null {
	const doc = fullscreenDocument();
	return doc.fullscreenElement ?? doc.webkitFullscreenElement ?? null;
}

function isFullscreenSupported(): boolean {
	const doc = fullscreenDocument();
	return (
		true === doc.fullscreenEnabled || true === doc.webkitFullscreenEnabled
	);
}

/*
 * Downloading images and videos from the lightbox, if the gallery allows it.
 *
 * The gallery items that can be downloaded have the download URL in `data-sgdg-download`.
 */

const BUTTON_ID = 'sgdg-download-button';

/*
 * Returns the gallery item (link) of a video shown in the lightbox.
 */
export function findVideoItem(
	video: HTMLVideoElement
): HTMLAnchorElement | null {
	const id = video.dataset['ilb2VideoId'];
	if (id === undefined) {
		return null;
	}
	return document.querySelector<HTMLAnchorElement>(
		`a[data-ilb2-id="${CSS.escape(id)}"]`
	);
}

export function removeDownloadButton(): void {
	document.getElementById(BUTTON_ID)?.remove();
}

/*
 * Prevents downloading a video in the lightbox from its controls (and context menu) if the gallery doesn't allow downloading.
 */
export function restrictVideoDownload(): void {
	const video = document.querySelector('video#ilb-image');
	if (!(video instanceof HTMLVideoElement)) {
		return;
	}
	if (findVideoItem(video)?.dataset['sgdgDownload'] !== undefined) {
		return;
	}
	video.setAttribute('controlslist', 'nodownload');
	video.addEventListener('contextmenu', (e) => {
		e.preventDefault();
	});
}

/*
 * Shows the download button in the lightbox for the item being shown, or removes it if the item can't be downloaded.
 */
export function updateDownloadButton(item: HTMLAnchorElement): void {
	const url = item.dataset['sgdgDownload'];
	const container = document.getElementById('ilb-container');
	if (url === undefined || container === null) {
		removeDownloadButton();
		return;
	}
	let button = document.getElementById(BUTTON_ID);
	if (!(button instanceof HTMLAnchorElement)) {
		button = document.createElement('a');
		button.id = BUTTON_ID;
		button.className = 'ilb-button';
		button.title = sgdgShortcodeLocalize.download;
		button.setAttribute('aria-label', sgdgShortcodeLocalize.download);
		button.setAttribute('download', '');
		button.innerHTML =
			'<svg viewBox="0 0 24 24" width="24" height="24" fill="#fff" aria-hidden="true" focusable="false">' +
			'<path d="M11 4h2v9.17l3.59-3.58L18 11l-6 6-6-6 1.41-1.41L11 13.17V4zM5 18h14v2H5v-2z"></path>' +
			'</svg>';
		// Don't let the lightbox treat the click as a click outside of the image (which closes it).
		button.addEventListener('click', (e) => {
			e.stopPropagation();
		});
		container.appendChild(button);
	}
	(button as HTMLAnchorElement).href = url;
}

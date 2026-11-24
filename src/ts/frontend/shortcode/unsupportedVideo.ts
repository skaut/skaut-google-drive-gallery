/*
 * Shows a message instead of a video whose picture the browser can't show.
 *
 * Some browsers can play only the sound of some video formats (e.g. HEVC/H.265 videos from iPhones in browsers without HEVC support). Such a video passes the `canPlayType()` check, but its picture has a width of 0, so the lightbox showed an empty box while only the sound played.
 */
export function handleUnsupportedVideo(): void {
	const video = document.querySelector('video#ilb-image');
	if (
		!(video instanceof HTMLVideoElement) ||
		video.readyState < HTMLMediaElement.HAVE_METADATA ||
		0 < video.videoWidth
	) {
		return;
	}
	const container = video.parentElement;
	if (container === null) {
		return;
	}
	if (null !== container.querySelector('.sgdg-video-unsupported')) {
		return;
	}
	video.pause();
	// The lightbox starts the playback of the video after it is loaded.
	video.addEventListener('play', () => {
		video.pause();
	});
	video.style.visibility = 'hidden';
	const message = document.createElement('div');
	message.className = 'sgdg-video-unsupported';
	const text = document.createElement('p');
	text.textContent = sgdgShortcodeLocalize.video_unsupported;
	const link = document.createElement('a');
	link.href = video.currentSrc;
	link.download = '';
	link.textContent = sgdgShortcodeLocalize.video_download;
	const linkParagraph = document.createElement('p');
	linkParagraph.appendChild(link);
	message.appendChild(text);
	message.appendChild(linkParagraph);
	container.appendChild(message);
}

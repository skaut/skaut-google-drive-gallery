export function printError(
	response: ErrorResponse,
	localize: {
		error_header: string;
		error_trace_header: string;
	}
): string {
	let html =
		'<div class="sgdg-notice-error">' +
		'<p>' +
		'<strong>' +
		localize.error_header +
		'</strong>' +
		'</p>' +
		'<p>' +
		response.error +
		'</p>';
	if (response.trace !== undefined) {
		html +=
			'<p>' +
			'<strong>' +
			localize.error_trace_header +
			'</strong>' +
			'</p>' +
			'<p>' +
			response.trace.replace(/\n/g, '<br>') +
			'</p>';
	}
	html += '</div>';
	return html;
}

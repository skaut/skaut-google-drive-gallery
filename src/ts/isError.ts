/* exported isError */

function isError(
	data: ReadonlyDeep<
		| GalleryResponse
		| ListGalleryDirResponse
		| ListGdriveDirResponse
		| PageResponse
	>
): data is ErrorResponse {
	return 'error' in data;
}

export function isError(
	data:
		| GalleryResponse
		| ListGalleryDirResponse
		| ListGdriveDirResponse
		| PageResponse
): data is ErrorResponse {
	return 'error' in data;
}

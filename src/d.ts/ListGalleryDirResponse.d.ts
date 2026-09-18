declare interface ListGalleryDirSuccessResponse {
	directories: Array<string>;
}

type ListGalleryDirResponse = ErrorResponse | ListGalleryDirSuccessResponse;

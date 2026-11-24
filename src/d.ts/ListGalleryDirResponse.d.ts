declare interface ListGalleryDirSuccessResponse {
	directories: Array<string>;
	private_videos?: number;
}

type ListGalleryDirResponse = ErrorResponse | ListGalleryDirSuccessResponse;

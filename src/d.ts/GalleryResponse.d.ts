declare interface GallerySuccessResponse extends PageSuccessResponse {
	path?: Array<PartialDirectory>;
}

declare type GalleryResponse = ErrorResponse | GallerySuccessResponse;

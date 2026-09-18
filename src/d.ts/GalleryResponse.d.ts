declare type GalleryResponse = ErrorResponse | GallerySuccessResponse;

declare interface GallerySuccessResponse extends PageSuccessResponse {
	path?: Array<PartialDirectory>;
}

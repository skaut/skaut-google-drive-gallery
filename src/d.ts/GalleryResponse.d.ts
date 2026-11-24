declare type GalleryResponse = ErrorResponse | GallerySuccessResponse;

declare interface GallerySuccessResponse extends PageSuccessResponse {
	page_navigation?: boolean;
	path?: Array<PartialDirectory>;
}

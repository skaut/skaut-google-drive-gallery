declare type GalleryResponse = ErrorResponse | GallerySuccessResponse;

declare interface GallerySuccessResponse extends PageSuccessResponse {
	breadcrumbs_root?: string;
	path?: Array<PartialDirectory>;
}

declare type GalleryResponse = ErrorResponse | GallerySuccessResponse;

declare interface GallerySuccessResponse extends PageSuccessResponse {
	breadcrumbs_root?: string;
	page_navigation?: boolean;
	path?: Array<PartialDirectory>;
}

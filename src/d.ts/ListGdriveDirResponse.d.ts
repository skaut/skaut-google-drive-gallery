declare type ListGdriveDirResponse =
	ErrorResponse | ListGdriveDirSuccessResponse;

declare interface ListGdriveDirSuccessResponse {
	directories: Array<PartialDirectory>;
	path: Array<string>;
	resetWarn?: string;
}

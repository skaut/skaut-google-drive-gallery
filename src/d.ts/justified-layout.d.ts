declare module 'justified-layout' {
	interface Result {
		boxes: any;
		containerHeight: any;
	}

	function convertSizesToAspectRatios( input: any, config: any ): any;

	export = convertSizesToAspectRatios;
}

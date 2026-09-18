type BlockOptions =
	| 'dir_counts'
	| 'grid_height'
	| 'grid_spacing'
	| 'page_autoload'
	| 'page_size'
	| 'preview_loop'
	| 'preview_size';

type BlockOrderingOptions = 'dir_ordering' | 'image_ordering';

declare interface BlockOption {
	default: string;
	name: string;
}

declare interface BlockOrderingOption {
	default_by: string;
	default_order: string;
	name: string;
}

interface SgdgBlockLocalize {
	ajax_url: string;
	nonce: string;
	block_name: string;
	block_description: string;
	root_name: string;
	settings_override: string;
	grid_section_name: string;
	lightbox_section_name: string;
	ordering_option_ascending: string;
	ordering_option_descending: string;
	ordering_option_by_time: string;
	ordering_option_by_name: string;
}

declare const sgdgBlockLocalize: SgdgBlockLocalize &
	Record<BlockOptions, BlockOption> &
	Record<BlockOrderingOptions, BlockOrderingOption>;

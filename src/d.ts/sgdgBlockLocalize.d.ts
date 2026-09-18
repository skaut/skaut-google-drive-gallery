declare interface BlockOption {
	default: string;
	name: string;
}

declare interface BlockOrderingOption {
	default_by: string;
	default_order: string;
	name: string;
}

type BlockOptions =
	| 'dir_counts'
	| 'grid_height'
	| 'grid_spacing'
	| 'page_autoload'
	| 'page_size'
	| 'preview_loop'
	| 'preview_size';

type BlockOrderingOptions = 'dir_ordering' | 'image_ordering';

interface SgdgBlockLocalize {
	ajax_url: string;
	block_description: string;
	block_name: string;
	grid_section_name: string;
	lightbox_section_name: string;
	nonce: string;
	ordering_option_ascending: string;
	ordering_option_by_name: string;
	ordering_option_by_time: string;
	ordering_option_descending: string;
	root_name: string;
	settings_override: string;
}

declare const sgdgBlockLocalize: Record<BlockOptions, BlockOption> &
	Record<BlockOrderingOptions, BlockOrderingOption> &
	SgdgBlockLocalize;

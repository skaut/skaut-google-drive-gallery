import $ from 'jquery';
import { default as tinymce } from 'tinymce';

import { isError } from '../isError';
import { printError } from '../printError';

let path: Array<string> = [];

function tinymceSubmit(): void {
	if ($('#sgdg-tinymce-insert').attr('disabled') !== undefined) {
		return;
	}
	tinymce.activeEditor?.insertContent('[sgdg path="' + path.join('/') + '"]');
	tb_remove();
}

function tinymceHtml(): void {
	const html =
		'<div id="sgdg-tinymce-overflow">' +
		'<table id="sgdg-tinymce-table" class="widefat">' +
		'<thead>' +
		'<tr>' +
		'<th class="sgdg-tinymce-path">' +
		sgdgTinymceLocalize.root_name +
		'</th>' +
		'</tr>' +
		'</thead>' +
		'<tbody id="sgdg-tinymce-list">' +
		'</tbody>' +
		'<tfoot>' +
		'<tr>' +
		'<td class="sgdg-tinymce-path">' +
		sgdgTinymceLocalize.root_name +
		'</td>' +
		'</tr>' +
		'</tfoot>' +
		'</table>' +
		'</div>' +
		'<div class="sgdg-tinymce-footer">' +
		'<a id="sgdg-tinymce-insert" class="button button-primary">' +
		sgdgTinymceLocalize.insert_button +
		'</a>' +
		'</div>';
	$('#sgdg-tinymce-modal').html(html);
	$('#sgdg-tinymce-insert').on('click', () => {
		tinymceSubmit();
	});
}

function pathClick(this: HTMLElement): void {
	path = path.slice(0, path.indexOf($(this).data('name') as string) + 1);
	// eslint-disable-next-line @typescript-eslint/no-use-before-define -- Cyclical dependency
	ajaxQuery();
}

function tableClick(this: HTMLElement): void {
	const newDir = $(this).text();
	if ('..' === newDir) {
		path.pop();
	} else {
		path.push(newDir);
	}
	// eslint-disable-next-line @typescript-eslint/no-use-before-define -- Cyclical dependency
	ajaxQuery();
}

function success(data: Array<string>): void {
	let html = '';
	$('#sgdg-tinymce-insert').removeAttr('disabled');
	if (0 < path.length) {
		html +=
			'<tr>' +
			'<td class="row-title">' +
			'<label>' +
			'..' +
			'</label>' +
			'</td>' +
			'</tr>';
	}
	for (let i = 0; i < data.length; i++) {
		html += '<tr class="';
		if (
			(0 === path.length && 1 === i % 2) ||
			(0 < path.length && 0 === i % 2)
		) {
			html += 'alternate';
		}
		html +=
			'">' +
			'<td class="row-title">' +
			'<label>' +
			data[i] +
			'</label>' +
			'</td>' +
			'</tr>';
	}
	$('#sgdg-tinymce-list').html(html);

	html = '<a>' + sgdgTinymceLocalize.root_name + '</a>';
	for (const segment of path) {
		html += ' > <a data-name="' + segment + '">' + segment + '</a>';
	}
	$('.sgdg-tinymce-path').html(html);
	$('.sgdg-tinymce-path a').on('click', pathClick);
	$('#sgdg-tinymce-list label').on('click', tableClick);
}

function ajaxQuery(): void {
	$('#sgdg-tinymce-list').html('');
	$('#sgdg-tinymce-insert').attr('disabled', 'disabled');
	void $.get(
		sgdgTinymceLocalize.ajax_url,
		{
			_ajax_nonce: sgdgTinymceLocalize.nonce,
			action: 'list_gallery_dir',
			path,
		},
		(data: ListGalleryDirResponse) => {
			if (isError(data)) {
				$('#TB_ajaxContent').html(
					printError(data, sgdgTinymceLocalize)
				);
			} else {
				success(data.directories);
			}
		}
	);
}

function tinymceOnclick(): void {
	tinymceHtml();
	tb_show(
		sgdgTinymceLocalize.dialog_title,
		'#TB_inline?inlineId=sgdg-tinymce-modal'
	);
	path = [];
	ajaxQuery();
}

function init(): void {
	const html = '<div id="sgdg-tinymce-modal"></div>';

	$('#sgdg-tinymce-button').on('click', tinymceOnclick);
	$('body').append(html);
}
init();

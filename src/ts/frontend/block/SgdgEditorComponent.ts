import * as blockEditor from '@wordpress/block-editor';
import type { BlockEditProps } from '@wordpress/blocks';
import * as editor from '@wordpress/editor';
import { Component, createElement, Fragment } from '@wordpress/element';
import $ from 'jquery';

import { isError } from '../../isError';
import type { Attributes } from '../interfaces/Attributes';
import { SgdgSettingsOverrideComponent } from './SgdgSettingsOverrideComponent';

interface SgdgEditorComponentState {
	error: string | undefined;
	list: Array<string> | undefined;
}

export class SgdgEditorComponent extends Component<
	BlockEditProps<Attributes>,
	SgdgEditorComponentState
> {
	public constructor(props: BlockEditProps<Attributes>) {
		super(props);
		this.state = { error: undefined, list: undefined };
	}

	public override componentDidMount(): void {
		this.ajax();
	}

	public override render(): React.ReactNode {
		const { error, list } = this.state;
		const InspectorControls =
			// eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, deprecation/deprecation -- In older versions of Gutenberg, InspectorControls was on editor
			blockEditor.InspectorControls ?? editor.InspectorControls;
		if (error !== undefined) {
			return createElement(
				'div',
				{ class: 'notice notice-error' },
				createElement('p', null, error)
			);
		}
		const children = [];
		const path = this.getAttribute('path') as Array<string>;
		const pathElements: Array<React.ReactNode> = [
			createElement(
				'a',
				{
					onClick: (e: Event) => {
						this.pathClick(e);
					},
				},
				sgdgBlockLocalize.root_name
			),
		];
		if (list) {
			if (0 < path.length) {
				children.push(
					createElement(
						'tr',
						null,
						createElement(
							'td',
							{ class: 'row-title' },
							createElement(
								'label',
								{
									onClick: (e: Event) => {
										this.labelClick(e);
									},
								},
								'..'
							)
						)
					)
				);
			}
			for (let i = 0; i < list.length; i++) {
				const lineClass =
					(0 === path.length && 1 === i % 2) ||
					(0 < path.length && 0 === i % 2)
						? 'alternate'
						: '';
				children.push(
					createElement(
						'tr',
						{ class: lineClass },
						createElement(
							'td',
							{ class: 'row-title' },
							createElement(
								'label',
								{
									onClick: (e: Event) => {
										this.labelClick(e);
									},
								},
								list[i]
							)
						)
					)
				);
			}
			for (const segment of path) {
				pathElements.push(' > ');
				pathElements.push(
					createElement(
						'a',
						{
							'data-id': segment,
							onClick: (e: Event) => {
								this.pathClick(e);
							},
						},
						segment
					)
				);
			}
		}
		return createElement(Fragment, null, [
			createElement(
				InspectorControls,
				null,
				createElement(SgdgSettingsOverrideComponent, { editor: this })
			),
			createElement('table', { class: 'widefat' }, [
				createElement(
					'thead',
					null,
					createElement(
						'tr',
						null,
						createElement(
							'th',
							{ class: 'sgdg-block-editor-path' },
							pathElements
						)
					)
				),
				createElement('tbody', null, children),
				createElement(
					'tfoot',
					null,
					createElement(
						'tr',
						null,
						createElement(
							'th',
							{ class: 'sgdg-block-editor-path' },
							pathElements
						)
					)
				),
			]),
		]);
	}

	public getAttribute(
		name: string
	): Array<string> | number | string | undefined {
		const { attributes } = this.props;
		return attributes[name];
	}

	public setAttribute(
		name: string,
		value: Array<string> | number | string | undefined
	): void {
		const { setAttributes } = this.props;
		const attr: Attributes = {};
		attr[name] = value;
		setAttributes(attr);
	}

	private ajax(): void {
		void $.get(
			sgdgBlockLocalize.ajax_url,
			{
				_ajax_nonce: sgdgBlockLocalize.nonce,
				action: 'list_gallery_dir',
				path: this.getAttribute('path'),
			},
			(data: ListGalleryDirResponse) => {
				if (isError(data)) {
					this.setState({ error: data.error });
				} else {
					this.setState({ list: data.directories });
				}
			}
		);
	}

	private pathClick(e: Event): void {
		if (e.currentTarget === null) {
			return;
		}
		let path = this.getAttribute('path') as Array<string>;
		path = path.slice(
			0,
			path.indexOf($(e.currentTarget).data('id') as string) + 1
		);
		this.setAttribute('path', path);
		this.setState({ error: undefined, list: undefined }, () => {
			this.ajax();
		});
	}

	private labelClick(e: Event): void {
		if (e.currentTarget === null) {
			return;
		}
		const newDir = $(e.currentTarget).text();
		let path = this.getAttribute('path') as Array<string>;
		if ('..' === newDir) {
			path = path.slice(0, path.length - 1);
		} else {
			path = path.concat(newDir);
		}
		this.setAttribute('path', path);
		this.setState({ error: undefined, list: undefined }, () => {
			this.ajax();
		});
	}
}

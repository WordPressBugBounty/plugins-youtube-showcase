( function ( blocks, element, components, blockEditor, serverSideRender ) {

	const { __ } = wp.i18n;
	const el = element.createElement;
	const { registerBlockType } = blocks;
	const { CheckboxControl, SelectControl, TextControl, RangeControl, PanelBody } = components;
	const { InspectorControls } = blockEditor;
	const ServerSideRender = wp.serverSideRender;
	const { useEffect } = wp.element;

	registerBlockType( 'youtube-showcase/main', {
		title: __('YouTube Showcase','youtube-showcase'),
		icon: {
			foreground: '#FF0000',
			//src: 'video-alt3',
			src: 'format-video',
		},
		category: 'media',
		keywords: ["video", "youtube", "gallery", "grid", "playlist"],
		attributes: {
			type: { type: 'string', default: 'gallery' },
			featured: { type: 'boolean', default: false },
			category: { type: 'string', default: '' },
			tag: { type: 'string', default: '' },
			orderby: { type: 'string', default: 'date' },
			order: { type: 'string', default: 'DESC' },
			records_per_page: { type: 'integer', default: 8 },
		},

		edit: function ( props ) {
			const { attributes, setAttributes } = props;
			// 2. PLACE IT HERE (Top level of the edit function)
			useEffect(() => {
				const handleReset = (e) => {
					if (e.target.classList.contains('yts-reset-link')) {
						e.preventDefault();
						// This clears the sidebar settings
						setAttributes({
							category: '',
							tag: '',
							featured: false
						});
					}
				};

				// Add listener when block is loaded
				document.addEventListener('click', handleReset);

				// Clean up listener when block is removed/reloaded
				return () => document.removeEventListener('click', handleReset);
			}, []); // Empty array means this runs once on mount
			return el(
				element.Fragment,
				{},
				// 1. Sidebar Controls
				el( InspectorControls, {},
					el( PanelBody, { title: __( 'Display Settings', 'youtube-showcase' ) },
						el( SelectControl, {
							label: __('Display Type','youtube-showcase'),
							value: attributes.type,
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
							options: [
								{ label: __('Video Gallery','youtube-showcase'), value: 'gallery' },
								{ label: __('Video Grid','youtube-showcase'), value: 'grid' },
								{ label: __('✨ Video Slider (Pro)', 'youtube-showcase'), value: 'pro-slider', disabled: true },
								{ label: __('✨ Video Horizontal Slider (Pro)', 'youtube-showcase'), value: 'pro-hslider', disabled: true },
								{ label: __('✨ Video Coverflow (Pro)', 'youtube-showcase'), value: 'pro-coverflow', disabled: true },
							],
							onChange: ( value ) => setAttributes( { type: value } ),
						} )
					),
					el( PanelBody, { title: __( 'Video Filters', 'youtube-showcase' ) },
						el( CheckboxControl, {
							label: __( 'Show Featured Videos Only', 'youtube-showcase' ),
							checked: attributes.featured,
							onChange: ( val ) => setAttributes( { featured: val } ),
						} ),
						el( TextControl, {
							label: __( 'Filter by Category (Slug)', 'youtube-showcase' ),
							value: attributes.category,
							onChange: ( val ) => setAttributes( { category: val } ),
						}),
						el( TextControl, {
							label: __( 'Filter by Tag (Slug)', 'youtube-showcase' ),
							value: attributes.tag,
							onChange: ( val ) => setAttributes( { tag: val } ),
						}),
						el( SelectControl, {
							label: __( 'Order By', 'youtube-showcase' ),
							value: attributes.orderby,
							options: [
								{ label: __( 'Date Published', 'youtube-showcase' ), value: 'date' },
								{ label: __( 'Date Updated', 'youtube-showcase' ), value: 'modified' },
								{ label: __( 'Video Title', 'youtube-showcase' ), value: 'title' },
							],
							onChange: ( val ) => setAttributes( { orderby: val } ),
						}),
						el( SelectControl, {
							label: __( 'Order', 'youtube-showcase' ),
							value: attributes.order,
							options: [
								{ label: __( 'Descending', 'youtube-showcase' ), value: 'DESC' },
								{ label: __( 'Ascending', 'youtube-showcase' ), value: 'ASC' },
							],
							onChange: ( val ) => setAttributes( { order: val } ),
						}),
						el( RangeControl, {
							label: __( 'Videos per Page', 'youtube-showcase' ),
							value: attributes.records_per_page,
							onChange: ( val ) => setAttributes( { records_per_page: val } ),
							min: 1,
							max: 50,
						})
					),
					// Add this after your "Video Filters" PanelBody
					el( PanelBody, {
						title: __( 'Support & Pro Features', 'youtube-showcase' ),
						initialOpen: false, // Keep it closed by default so it's not annoying
						className: 'yts-pro-panel'
					},
						el( 'div', { className: 'yts-pro-upsell-content' },
							el( 'p', {},
								el( 'span', {
									className: 'dashicons dashicons-star-filled',
									style: { color: '#ffb900', marginRight: '8px' }
								} ),
								el( 'strong', {}, __('Unlock All Layouts', 'youtube-showcase') )
							),
							el( 'ul', { style: { paddingLeft: '20px', fontSize: '12px', lineHeight: '1.5' } },
								el( 'li', { style: { marginBottom: '8px' } },
									el( 'strong', {}, __('Auto-Sync & Stats:', 'youtube-showcase') ),
									__(' Import playlists and auto-update video stats on a schedule.', 'youtube-showcase')
								),
								el( 'li', { style: { marginBottom: '8px' } }, __('Video Sliders & Carousels', 'youtube-showcase') ),
								el( 'li', { style: { marginBottom: '8px' } }, __('Coverflow & Video Wall Layouts', 'youtube-showcase') ),
								el( 'li', { style: { marginBottom: '8px' } }, __('AJAX Live Search Results', 'youtube-showcase') )
							),
							el( 'a', {
								href: 'https://emdplugins.com/youtube-showcase/?pk_campaign=youtube-showcase&pk_source=plugin&pk_medium=link&amp;pk_content=block',
								target: '_blank',
								className: 'components-button is-primary is-busy', // is-busy gives it a nice slight animated feel in some themes
								style: { width: '100%', justifyContent: 'center', marginTop: '10px' }
							}, __('Upgrade to Pro Now', 'youtube-showcase') ),

							el( 'hr', { style: { margin: '15px 0' } } ),

							el( 'a', {
								href: 'https://support.emdplugins.com/?pk_campaign=youtube-showcase&pk_source=plugin&pk_medium=link&amp;pk_content=block',
								target: '_blank',
								style: { fontSize: '12px', textDecoration: 'none', display: 'block', textAlign: 'center' }
							}, __('Need help? View Documentation', 'youtube-showcase') )
						)
					)
				),
				// 2. The Preview Area (Now correctly inside the return)
				el( 'div', { className: 'yts-block-editor-container' },
					el( ServerSideRender, {
						block: 'youtube-showcase/main',
						attributes: attributes,
						loadingProps: { title: __('Fetching your videos...', 'youtube-showcase') },
						// This is the "Zero State" UI
						EmptyResponsePlaceholder: () => el(
							'div',
							{
								className: 'yts-editor-empty-state',
								style: {
									padding: '40px',
									border: '2px dashed #ccc',
									borderRadius: '8px',
									textAlign: 'center',
									background: '#f9f9f9'
								}
							},
							el( 'span', {
								className: 'dashicons dashicons-video-alt3',
								style: { fontSize: '48px', width: '48px', height: '48px', color: '#666' }
							} ),
							el( 'h3', { style: { margin: '10px 0' } }, __('No Videos Found', 'youtube-showcase') ),
							el( 'p', { style: { color: '#666', marginBottom: '20px' } },
								__('It looks like you haven’t added any videos yet. Add your first video to see the preview here!', 'youtube-showcase')
							),
							el( 'button', {
								className: 'components-button is-primary',
								// This opens the "Add New Video" page in a new tab
								onClick: () => window.open( 'post-new.php?post_type=emd_video', '_blank' )
							}, __('Add Your First Video', 'youtube-showcase') )
						)
					} )
				)
			);
		},
		save: () => null,
	} );

} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.components,
	window.wp.blockEditor,
	window.wp.serverSideRender
);

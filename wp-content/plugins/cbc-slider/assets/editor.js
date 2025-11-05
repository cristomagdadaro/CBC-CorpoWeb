(function () {
	const { registerBlockType } = wp.blocks;
	const { __ } = wp.i18n;
	const { useState, Fragment } = wp.element;
	const { InspectorControls, MediaUpload, MediaUploadCheck, BlockControls, AlignmentToolbar } = wp.blockEditor || wp.editor;
	const { PanelBody, ToggleControl, RangeControl, SelectControl, Button, ToolbarButton, TextareaControl, TextControl } = wp.components;

	const OVERLAY_POSITIONS = [
		{ label: __('Top Left', 'cbc-slider'), value: 'top-left' },
		{ label: __('Top Right', 'cbc-slider'), value: 'top-right' },
		{ label: __('Bottom Left', 'cbc-slider'), value: 'bottom-left' },
		{ label: __('Bottom Right', 'cbc-slider'), value: 'bottom-right' },
		{ label: __('Center', 'cbc-slider'), value: 'center' }
	];

	const OBJECT_FIT = [
		{ label: __('Cover', 'cbc-slider'), value: 'cover' },
		{ label: __('Contain', 'cbc-slider'), value: 'contain' },
		{ label: __('Fill', 'cbc-slider'), value: 'fill' },
		{ label: __('None', 'cbc-slider'), value: 'none' },
		{ label: __('Scale Down', 'cbc-slider'), value: 'scale-down' }
	];

	const ASPECT_RATIOS = [
		{ label: '16/9', value: '16/9' },
		{ label: '4/3', value: '4/3' },
		{ label: '1/1', value: '1/1' },
		{ label: '21/9', value: '21/9' },
		{ label: __('Custom height', 'cbc-slider'), value: '' }
	];

	function SlideItem({ slide, index, onChange, onRemove }) {
		const isVideo = slide.type === 'video';
		const thumbUrl = (() => {
			if (isVideo) {
				if (slide.posterUrl) return slide.posterUrl;
				return slide.url || '';
			}
			return slide.url || '';
		})();

		return (
			<div className="cbc-slider-editor__slideItem">
				<div className="cbc-slider-editor__thumb">
					{thumbUrl ? (
						<img src={thumbUrl} alt="" />
					) : (
						<div className="cbc-slider-editor__thumb--placeholder">{__('No media', 'cbc-slider')}</div>
					)}
				</div>
				<div className="cbc-slider-editor__fields">
					<MediaUploadCheck>
						<MediaUpload
							allowedTypes={[ 'image', 'video' ]}
							onSelect={(media) => {
								if (!media) return;
								const next = { ...slide };
								next.id = media.id || 0;
								next.url = media.url || '';
								next.type = media.type === 'video' ? 'video' : 'image';
								next.alt = media.alt || '';
								onChange(index, next);
							}}
							render={({ open }) => (
								<Button onClick={open} variant="secondary">
									{isVideo ? __('Replace Video', 'cbc-slider') : __('Replace Image', 'cbc-slider')}
								</Button>
							)}
						/>
					</MediaUploadCheck>

					{isVideo ? (
						<Fragment>
							<TextControl
								label={__('Video URL (optional if selected from library)', 'cbc-slider')}
								value={slide.url || ''}
								onChange={(url) => onChange(index, { ...slide, url })}
							/>
							<MediaUploadCheck>
								<MediaUpload
									allowedTypes={[ 'image' ]}
									onSelect={(media) => {
										const next = { ...slide, posterId: media.id || 0, posterUrl: media.url || '' };
										onChange(index, next);
									}}
									render={({ open }) => (
										<Button onClick={open} variant="secondary">{__('Select Poster', 'cbc-slider')}</Button>
									)}
								/>
							</MediaUploadCheck>
						</Fragment>
					) : (
						<TextControl
							label={__('Alt text', 'cbc-slider')}
							value={slide.alt || ''}
							onChange={(alt) => onChange(index, { ...slide, alt })}
						/>
					)}

					<SelectControl
						label={__('Overlay position', 'cbc-slider')}
						value={slide.overlayPosition || 'bottom-left'}
						options={OVERLAY_POSITIONS}
						onChange={(overlayPosition) => onChange(index, { ...slide, overlayPosition })}
					/>

					<TextareaControl
						label={__('Overlay HTML', 'cbc-slider')}
						help={__('Enter custom HTML (allowed tags only).', 'cbc-slider')}
						rows={4}
						value={slide.overlayHtml || ''}
						onChange={(overlayHtml) => onChange(index, { ...slide, overlayHtml })}
					/>

					<div className="cbc-slider-editor__rowBetween">
						<Button variant="tertiary" isDestructive onClick={() => onRemove(index)}>
							{__('Remove slide', 'cbc-slider')}
						</Button>
					</div>
				</div>
			</div>
		);
	}

	registerBlockType('cbc/slider', {
		title: __('CBC Slider', 'cbc-slider'),
		description: __('Slider supporting images, videos, and custom HTML overlays.', 'cbc-slider'),
		icon: 'images-alt2',
		category: 'media',
		attributes: {
			slides: { type: 'array', default: [] },
			autoplay: { type: 'boolean', default: true },
			delay: { type: 'number', default: 5000 },
			loop: { type: 'boolean', default: true },
			pauseOnHover: { type: 'boolean', default: true },
			showArrows: { type: 'boolean', default: true },
			showDots: { type: 'boolean', default: true },
			objectFit: { type: 'string', default: 'cover' },
			aspectRatio: { type: 'string', default: '16/9' },
			height: { type: 'string', default: '' },
			videoMuted: { type: 'boolean', default: true },
			videoLoop: { type: 'boolean', default: false },
			videoControls: { type: 'boolean', default: false },
		},
		edit: (props) => {
			const { attributes, setAttributes } = props;
			const slides = attributes.slides || [];

			const addSlidesFromMedia = (items) => {
				const newSlides = (Array.isArray(items) ? items : [items]).map((m) => ({
					id: m.id || 0,
					url: m.url || '',
					type: m.type === 'video' ? 'video' : 'image',
					alt: m.alt || '',
					overlayHtml: '',
					overlayPosition: 'bottom-left',
				}));
				setAttributes({ slides: [ ...slides, ...newSlides ] });
			};

			const onChangeSlide = (index, next) => {
				const copy = [ ...slides ];
				copy[index] = next;
				setAttributes({ slides: copy });
			};

			const onRemoveSlide = (index) => {
				const copy = [ ...slides ];
				copy.splice(index, 1);
				setAttributes({ slides: copy });
			};

			return (
				<Fragment>
					<BlockControls>
						<AlignmentToolbar value={attributes.align} onChange={(align) => setAttributes({ align })} />
					</BlockControls>

					<InspectorControls>
						<PanelBody title={__('Behavior', 'cbc-slider')} initialOpen={true}>
							<ToggleControl
								label={__('Autoplay', 'cbc-slider')}
								checked={!!attributes.autoplay}
								onChange={(autoplay) => setAttributes({ autoplay })}
							/>
							<RangeControl
								label={__('Delay (ms)', 'cbc-slider')}
								min={1000}
								max={15000}
								step={500}
								value={attributes.delay}
								onChange={(delay) => setAttributes({ delay })}
							/>
							<ToggleControl
								label={__('Loop', 'cbc-slider')}
								checked={!!attributes.loop}
								onChange={(loop) => setAttributes({ loop })}
							/>
							<ToggleControl
								label={__('Pause on hover', 'cbc-slider')}
								checked={!!attributes.pauseOnHover}
								onChange={(pauseOnHover) => setAttributes({ pauseOnHover })}
							/>
						</PanelBody>

						<PanelBody title={__('Appearance', 'cbc-slider')} initialOpen={false}>
							<SelectControl
								label={__('Object-fit', 'cbc-slider')}
								value={attributes.objectFit}
								options={OBJECT_FIT}
								onChange={(objectFit) => setAttributes({ objectFit })}
							/>
							<SelectControl
								label={__('Aspect ratio', 'cbc-slider')}
								value={attributes.aspectRatio}
								options={ASPECT_RATIOS}
								onChange={(aspectRatio) => setAttributes({ aspectRatio })}
							/>
							<TextControl
								label={__('Custom height (e.g., 480px, 60vh)', 'cbc-slider')}
								help={__('Overrides aspect ratio when set.', 'cbc-slider')}
								value={attributes.height}
								onChange={(height) => setAttributes({ height })}
							/>
							<ToggleControl
								label={__('Show arrows', 'cbc-slider')}
								checked={!!attributes.showArrows}
								onChange={(showArrows) => setAttributes({ showArrows })}
							/>
							<ToggleControl
								label={__('Show dots', 'cbc-slider')}
								checked={!!attributes.showDots}
								onChange={(showDots) => setAttributes({ showDots })}
							/>
						</PanelBody>

						<PanelBody title={__('Video options', 'cbc-slider')} initialOpen={false}>
							<ToggleControl
								label={__('Muted', 'cbc-slider')}
								checked={!!attributes.videoMuted}
								onChange={(videoMuted) => setAttributes({ videoMuted })}
							/>
							<ToggleControl
								label={__('Loop', 'cbc-slider')}
								checked={!!attributes.videoLoop}
								onChange={(videoLoop) => setAttributes({ videoLoop })}
							/>
							<ToggleControl
								label={__('Show native controls', 'cbc-slider')}
								checked={!!attributes.videoControls}
								onChange={(videoControls) => setAttributes({ videoControls })}
							/>
						</PanelBody>
					</InspectorControls>

					<div className="cbc-slider-editor">
						<div className="cbc-slider-editor__header">
							<MediaUploadCheck>
								<MediaUpload
									multiple
									addToGallery
									allowedTypes={[ 'image', 'video' ]}
									onSelect={addSlidesFromMedia}
									render={({ open }) => (
										<Button variant="primary" onClick={open}>{__('Add media', 'cbc-slider')}</Button>
									)}
								/>
							</MediaUploadCheck>
						</div>

						{slides.length === 0 ? (
							<div className="cbc-slider-editor__empty">{__('No slides yet. Use "Add media" to start.', 'cbc-slider')}</div>
						) : (
							<div className="cbc-slider-editor__list">
								{slides.map((slide, i) => (
									<SlideItem key={i} slide={slide} index={i} onChange={onChangeSlide} onRemove={onRemoveSlide} />
								))}
							</div>
						)}
					</div>
				</Fragment>
			);
		},
		save: () => null, // Rendered in PHP
	});
})();

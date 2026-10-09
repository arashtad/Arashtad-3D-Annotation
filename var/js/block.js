(function (blocks, element, components, data) {
    const { createElement: el, RawHTML } = element;
    const { SelectControl, Button, Spinner } = components;
    const { useSelect } = data;
    const { __ } = wp.i18n;

    blocks.registerBlockType('arashtad/annotation', {
        title: __('Arashtad 3D Annotation', 'arashtad-3d-annotation'),
        description: __('Insert an Arashtad 3D Annotation.', 'arashtad-3d-annotation'),
        icon: 'format-image',
        category: 'media',

        attributes: {
            sceneId: {
                type: 'number',
                default: 0
            }
        },

        edit: function (props) {
            const scenes = useSelect(function (select) {
                return select('core').getEntityRecords('postType', 'arashtad_annotation', {
                    per_page: -1,
                    orderby: 'title',
                    order: 'asc'
                });
            }, []);

            if (scenes === null) {
                return el('div', { className: 'arashtad-3d-annotation-block' }, el(Spinner));
            }

            const options = [{ label: __('Select a 3D Annotation', 'arashtad-3d-annotation'), value: 0 }];
            scenes.forEach(function (scene) {
                options.push({ label: scene.title.rendered, value: scene.id });
            });

            return el('div', { className: 'arashtad-3d-annotation-block' },
                el(SelectControl, {
                    label: __('Arashtad 3D Annotation', 'arashtad-3d-annotation'),
                    value: props.attributes.sceneId,
                    options: options,
                    onChange: function (value) {
                        props.setAttributes({ sceneId: parseInt(value, 10) || 0 });
                    }
                }),
                el('div', { style: { marginTop: '12px' } },
                    el(Button, {
                        variant: 'secondary',
                        isDestructive: true,
                        onClick: function () {
                            props.onReplace([]);
                        }
                    }, __('Remove Loader', 'arashtad-3d-annotation'))
                )
            );
        },

        save: function (props) {
            const sceneId = props.attributes.sceneId;
            if (!sceneId) return null;
            return el(RawHTML, null, '[arashtad_annotation id="' + sceneId + '"]');
        }
    });
})(wp.blocks, wp.element, wp.components, wp.data);
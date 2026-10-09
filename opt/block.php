<?php

if (!defined('ABSPATH'))
    exit;

add_action('init', 'arashtad_annotation_register_block');

function arashtad_annotation_register_block()
{
    wp_register_script(
        'arashtad-3d-annotation-block',
        ARASHTAD_3D_ANNOTATION_URL . 'var/js/block.js',
        [
            'wp-blocks',
            'wp-element',
            'wp-components',
            'wp-data'
        ],
        ARASHTAD_3D_ANNOTATION_VERSION,
        true
    );

    register_block_type('arashtad/annotation', [
        'api_version' => 3,
        'editor_script' => 'arashtad-3d-annotation-block',
        'title' => 'Arashtad 3D Annotation',
        'description' => 'Insert an Arashtad 3D Annotation.',
        'category' => 'media',
        'icon' => 'format-image',
        'attributes' => [
            'sceneId' => [
                'type' => 'number',
                'default' => 0
            ]
        ]
    ]);
}

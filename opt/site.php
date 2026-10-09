<?php

if (!defined('ABSPATH'))
    exit;

add_filter('script_loader_tag', 'arashtad_annotation_module_scripts', 10, 3);

function arashtad_annotation_register_assets()
{
    wp_register_script('arashtad-3d-annotation-babylon', ARASHTAD_3D_ANNOTATION_URL . 'lib/babylon/babylon.js', [], ARASHTAD_3D_ANNOTATION_VERSION, true);
    wp_register_script('arashtad-3d-annotation-loaders', ARASHTAD_3D_ANNOTATION_URL . 'lib/babylon/loaders/babylonjs.loaders.min.js', ['arashtad-3d-annotation-babylon'], ARASHTAD_3D_ANNOTATION_VERSION, true);
    wp_register_script('arashtad-3d-annotation-site', ARASHTAD_3D_ANNOTATION_URL . 'var/js/site.js', ['arashtad-3d-annotation-babylon', 'arashtad-3d-annotation-loaders'], ARASHTAD_3D_ANNOTATION_VERSION, true);
    wp_register_style('arashtad-3d-annotation-site', ARASHTAD_3D_ANNOTATION_URL . 'var/css/site.css', [], ARASHTAD_3D_ANNOTATION_VERSION);
}

function arashtad_annotation_module_scripts($tag, $handle, $src)
{
    if ($handle === 'arashtad-3d-annotation-site')
        return '<script type="module" src="' . esc_url($src) . '"></script>';  // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- Script is registered and enqueued through WordPress; this filter only adds the required module type.

    return $tag;
}

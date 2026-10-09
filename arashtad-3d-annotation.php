<?php

/**
 * Plugin Name: Arashtad 3D Annotation
 * Plugin URI: https://arashtad.com/arashtad-3d-annotation
 * Description: Display interactive Babylon.js 3D annotation scenes in WordPress.
 * Version: 2.0.0
 * Author: Arashtad
 * Author URI: https://arashtad.com
 * License: GPL-2.0
 * Text Domain: arashtad-3d-annotation
 */
if (!defined('ABSPATH'))
    exit;

define('ARASHTAD_3D_ANNOTATION_VERSION', '2.0.0');
define('ARASHTAD_3D_ANNOTATION_FILE', __FILE__);
define('ARASHTAD_3D_ANNOTATION_DIR', plugin_dir_path(__FILE__));
define('ARASHTAD_3D_ANNOTATION_URL', plugin_dir_url(__FILE__));

// Load JavaScript translations
add_action('admin_enqueue_scripts', 'arashtad_annotation_load_js_translations');

function arashtad_annotation_load_js_translations()
{
    wp_set_script_translations(
        'arashtad-admin',
        'arashtad-3d-annotation',
        ARASHTAD_3D_ANNOTATION_DIR . 'languages'
    );

    wp_set_script_translations(
        'arashtad-3d-annotation-block',
        'arashtad-3d-annotation',
        ARASHTAD_3D_ANNOTATION_DIR . 'languages'
    );
}

require_once ARASHTAD_3D_ANNOTATION_DIR . 'opt/admin.php';
require_once ARASHTAD_3D_ANNOTATION_DIR . 'opt/loader.php';
require_once ARASHTAD_3D_ANNOTATION_DIR . 'opt/shortcode.php';
require_once ARASHTAD_3D_ANNOTATION_DIR . 'opt/block.php';
require_once ARASHTAD_3D_ANNOTATION_DIR . 'opt/site.php';

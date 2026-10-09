<?php

if (!defined('ABSPATH'))
    exit;

add_action('init', 'arashtad_annotation_register_post_type');
add_action('add_meta_boxes', 'arashtad_annotation_add_meta_boxes');
add_action('save_post_arashtad_annotation', 'arashtad_annotation_save');
add_action('admin_enqueue_scripts', 'arashtad_annotation_admin_assets');
add_filter('upload_mimes', 'arashtad_annotation_upload_mimes');
add_filter('manage_arashtad_annotation_posts_columns', 'arashtad_annotation_columns');
add_action('manage_arashtad_annotation_posts_custom_column', 'arashtad_annotation_column', 10, 2);

function arashtad_annotation_register_post_type()
{
    register_post_type('arashtad_annotation', [
        'labels' => [
            'name' => __('3D Annotations', 'arashtad-3d-annotation'),
            'singular_name' => __('3D Annotation', 'arashtad-3d-annotation'),
            'add_new' => __('Add Annotation', 'arashtad-3d-annotation'),
            'add_new_item' => __('Add 3D Annotation', 'arashtad-3d-annotation'),
            'edit_item' => __('Edit 3D Annotation', 'arashtad-3d-annotation'),
            'new_item' => __('New 3D Annotation', 'arashtad-3d-annotation'),
            'view_item' => __('View 3D Annotation', 'arashtad-3d-annotation'),
            'search_items' => __('Search 3D Annotations', 'arashtad-3d-annotation'),
            'not_found' => __('No 3D Annotations found', 'arashtad-3d-annotation')
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_rest' => true,
        'rewrite' => false,
        'query_var' => false,
        'menu_icon' => plugin_dir_url(__DIR__) . 'var/images/icon.svg',
        'supports' => ['title']
    ]);
}

function arashtad_annotation_pro_features_page()
{
    require __DIR__ . '/pro-features.php';
}

add_action('admin_menu', 'arashtad_annotation_register_pro_features_page');

function arashtad_annotation_register_pro_features_page()
{
    add_submenu_page(
        'edit.php?post_type=arashtad_annotation',
        __('Pro Features', 'arashtad-3d-annotation'),
        __('Pro Features', 'arashtad-3d-annotation'),
        'manage_options',
        'arashtad-3d-annotation-pro-features',
        'arashtad_annotation_pro_features_page'
    );
}

add_action('edit_form_after_editor', function ($post) {
    if ($post->post_type !== 'arashtad_annotation')
        return;
    ?>
    <div id="arashtad_panel">
        <div id="arashtad_main"></div>
        <div id="arashtad_sidebar">
            <div id="arashtad_sidebar_top"></div>
            <div id="arashtad_sidebar_middle">
                <div id="arashtad_rail">
                    <ul id="arashtad_panel_tab">
                        <li class="active" id="arashtad_annotation_model_button">
                            <img src="<?php echo esc_url(plugin_dir_url(__DIR__)) . 'var/images/icons/media-library.svg' ?>" alt="">
                        </li>
                        <li id="arashtad_annotation_shortcode_button">
                            <img src="<?php echo esc_url(plugin_dir_url(__DIR__)) . 'var/images/icons/shortcode.svg' ?>" alt="">
                        </li>
                        <li id="arashtad_annotation_styles_button">
                            <img src="<?php echo esc_url(plugin_dir_url(__DIR__)) . 'var/images/icons/wrapper.svg' ?>" alt="">
                        </li>
                        <li id="arashtad_annotation_clear_color_button">
                            <img src="<?php echo esc_url(plugin_dir_url(__DIR__)) . 'var/images/icons/color.svg' ?>" alt="">
                        </li>
                        <li id="arashtad_annotation_model_settings_button">
                            <img src="<?php echo esc_url(plugin_dir_url(__DIR__)) . 'var/images/icons/model.svg' ?>" alt="">
                        </li>
                        <li id="arashtad_annotation_camera_button">
                            <img src="<?php echo esc_url(plugin_dir_url(__DIR__)) . 'var/images/icons/camera.svg' ?>" alt="">
                        </li>
                        <li id="arashtad_annotation_light_button">
                            <img src="<?php echo esc_url(plugin_dir_url(__DIR__)) . 'var/images/icons/light.svg' ?>" alt="">
                        </li>
                        <li id="arashtad_annotation_lazy_button">
                            <img src="<?php echo esc_url(plugin_dir_url(__DIR__)) . 'var/images/icons/loading.svg' ?>" alt="">
                        </li>
                        <li id="arashtad_annotation_resources_button">
                            <img src="<?php echo esc_url(plugin_dir_url(__DIR__)) . 'var/images/icons/help.svg' ?>" alt="">
                        </li>
                    </ul>
                </div>
                <div id="arashtad_meta_boxes"></div>
            </div>
            <div id="arashtad_sidebar_bottom"></div>
        </div>
    </div>
    <?php
});

function arashtad_annotation_add_meta_boxes()
{
    add_meta_box('arashtad_annotation_model', __('3D Model', 'arashtad-3d-annotation'), 'arashtad_annotation_model', 'arashtad_annotation', 'normal', 'high');
    add_meta_box('arashtad_annotation_preview', __('Preview', 'arashtad-3d-annotation'), 'arashtad_annotation_preview', 'arashtad_annotation', 'normal', 'high');
    add_meta_box(
        'arashtad_annotation_shortcode',
        __('Shortcode', 'arashtad-3d-annotation'),
        'arashtad_annotation_shortcode_meta_box',
        'arashtad_annotation',
        'side',
        'default'
    );
    add_meta_box('arashtad_annotation_resources', __('Resources', 'arashtad-3d-annotation'), 'arashtad_annotation_resources_callback', 'arashtad_annotation', 'normal', 'low');
}

function arashtad_annotation_model($post)
{
    wp_nonce_field('arashtad_annotation_model', 'arashtad_annotation_nonce');
    $model = get_post_meta($post->ID, '_arashtad_model', true);
    ?>
    <div class="ar-model">

        <div class="text-center bold">
            <?php esc_html_e('Model Source', 'arashtad-3d-annotation'); ?>
        </div>

        <hr>

        <div class="ar-model-media" style="width: 700px; max-width: 100%; margin: 0 auto;">
            <p>
                <input type="text" id="arashtad_model" name="arashtad_model" value="<?php echo esc_attr($model); ?>" class="widefat">
            </p>
            
            <p>
                <button type="button" class="button button-primary button-block" id="arashtad_select_model"><?php esc_html_e('Select Model', 'arashtad-3d-annotation'); ?></button>
            </p>

            <p>
                <button type="button" class="button button-block" id="arashtad_clear_model"><?php esc_html_e('Clear', 'arashtad-3d-annotation'); ?></button>
            </p>
        </div>
    </div>
    <?php
}

function arashtad_annotation_preview($post)
{
    $camera_config = get_post_meta($post->ID, '_arashtad_camera_config', true);
    $camera_defaults = [
        'camera_type' => 'ArcRotateCamera',
        'alpha' => '1.57',
        'beta' => '1.2',
        'radius' => '12',
        'target_x' => '0',
        'target_y' => '0',
        'target_z' => '0',
        'position_x' => '0',
        'position_y' => '0',
        'position_z' => '3',
        'fov' => '0.4',
        'minZ' => '0.1',
        'maxZ' => '1000',
        'use_auto_rotate' => '0',
        'idle_rotation_speed' => '0.1',
        'idle_rotation_wait_time' => '3000',
        'idle_rotation_spinup_time' => '6000'
    ];
    $camera_config = wp_parse_args(is_array($camera_config) ? $camera_config : [], $camera_defaults);

    $light_config = get_post_meta($post->ID, '_arashtad_light_config', true);
    $light_defaults = [
        'type' => 'DirectionalLight',
        'direction_x' => '0',
        'direction_y' => '-1',
        'direction_z' => '-2',
        'position_x' => '0',
        'position_y' => '5',
        'position_z' => '0',
        'intensity' => '3',
        'angle' => '1.0472',
        'exponent' => '2'
    ];
    $light_config = wp_parse_args(is_array($light_config) ? $light_config : [], $light_defaults);

    $lazy_enabled = get_post_meta($post->ID, '_arashtad_lazy_enabled', true);
    $lazy_enabled = $lazy_enabled !== '' ? (bool) $lazy_enabled : true;

    $scrolllock_enabled = get_post_meta($post->ID, '_arashtad_scrolllock_enabled', true);
    $scrolllock_enabled = $scrolllock_enabled !== '' ? (bool) $scrolllock_enabled : true;

    $poster_enabled = get_post_meta($post->ID, '_arashtad_poster_enabled', true);
    $poster_image_id = get_post_meta($post->ID, '_arashtad_poster_image_id', true);
    $poster_image_url = $poster_image_id ? wp_get_attachment_image_url($poster_image_id, 'full') : '';

    $clear_color = get_post_meta($post->ID, '_arashtad_clear_color', true);
    if (empty($clear_color)) {
        $clear_color = '0.0588, 0.0784, 0.0706, 1';
    }

    $preview_model = arashtad_annotation_get_first_model($post->ID);
    ?>
    <div class="ar-preview">
        <div class="canvas-wrapper<?php echo $scrolllock_enabled ? ' scrolllock' : ''; ?>" style="<?php echo esc_attr(arashtad_annotation_canvas_styles($post->ID)); ?>">
            <canvas
                id="arashtad-3d-annotation-admin-preview"
                class="arashtad-3d-canvas"
                clearColor="new BABYLON.Color4(<?php echo esc_attr($clear_color); ?>)"
            >
                <arashtad-runtime
                    <?php if ($lazy_enabled): ?>lazy<?php endif; ?>
                    resize="engine.resize()"
                ></arashtad-runtime>

                <arashtad-light
                    id="arashtad-3d-annotation-light"
                    type="<?php echo esc_attr($light_config['type']); ?>"
                    <?php if (in_array($light_config['type'], ['HemisphericLight', 'DirectionalLight', 'SpotLight'], true)): ?>
                    direction="new BABYLON.Vector3(
                        <?php echo esc_attr($light_config['direction_x']); ?>,
                        <?php echo esc_attr($light_config['direction_y']); ?>,
                        <?php echo esc_attr($light_config['direction_z']); ?>
                    )"
                    <?php endif; ?>
                    <?php if (in_array($light_config['type'], ['PointLight', 'SpotLight'], true)): ?>
                    position="new BABYLON.Vector3(
                        <?php echo esc_attr($light_config['position_x']); ?>,
                        <?php echo esc_attr($light_config['position_y']); ?>,
                        <?php echo esc_attr($light_config['position_z']); ?>
                    )"
                    <?php endif; ?>
                    <?php if ($light_config['type'] === 'SpotLight'): ?>
                    angle="<?php echo esc_attr($light_config['angle']); ?>"
                    exponent="<?php echo esc_attr($light_config['exponent']); ?>"
                    <?php endif; ?>
                    intensity="<?php echo esc_attr($light_config['intensity']); ?>"
                ></arashtad-light>

                <arashtad-camera
                    id="camera"
                    type="<?php echo esc_attr($camera_config['camera_type']); ?>"
                    <?php if ($camera_config['camera_type'] === 'ArcRotateCamera'): ?>
                    alpha="<?php echo esc_attr($camera_config['alpha']); ?>"
                    beta="<?php echo esc_attr($camera_config['beta']); ?>"
                    radius="<?php echo esc_attr($camera_config['radius']); ?>"
                    target="new BABYLON.Vector3(<?php echo esc_attr($camera_config['target_x']); ?>, <?php echo esc_attr($camera_config['target_y']); ?>, <?php echo esc_attr($camera_config['target_z']); ?>)"
                    <?php endif; ?>
                    position="new BABYLON.Vector3(<?php echo esc_attr($camera_config['position_x']); ?>, <?php echo esc_attr($camera_config['position_y']); ?>, <?php echo esc_attr($camera_config['position_z']); ?>)"
                    fov="<?php echo esc_attr($camera_config['fov']); ?>"
                    minZ="<?php echo esc_attr($camera_config['minZ']); ?>"
                    maxZ="<?php echo esc_attr($camera_config['maxZ']); ?>"
                    <?php if ($camera_config['use_auto_rotate'] === '1'): ?>
                    useAutoRotationBehavior="true"
                    autoRotationBehavior.idleRotationSpeed="<?php echo esc_attr($camera_config['idle_rotation_speed']); ?>"
                    autoRotationBehavior.idleRotationWaitTime="<?php echo esc_attr($camera_config['idle_rotation_wait_time']); ?>"
                    autoRotationBehavior.idleRotationSpinupTime="<?php echo esc_attr($camera_config['idle_rotation_spinup_time']); ?>"
                    <?php endif; ?>
                ></arashtad-camera>

                <?php if (!empty($preview_model['src'])): ?>
                    <arashtad-model
                        id="arashtad-3d-annotation-admin-model"
                        src="<?php echo esc_attr($preview_model['src']); ?>"
                        position="new BABYLON.Vector3(
                            <?php echo esc_attr($preview_model['position_x']); ?>,
                            <?php echo esc_attr($preview_model['position_y']); ?>,
                            <?php echo esc_attr($preview_model['position_z']); ?>
                        )"
                        rotation="new BABYLON.Vector3(
                            <?php echo esc_attr($preview_model['rotation_x']); ?>,
                            <?php echo esc_attr($preview_model['rotation_y']); ?>,
                            <?php echo esc_attr($preview_model['rotation_z']); ?>
                        )"
                        scaling="new BABYLON.Vector3(
                            <?php echo esc_attr($preview_model['scaling_x']); ?>,
                            <?php echo esc_attr($preview_model['scaling_y']); ?>,
                            <?php echo esc_attr($preview_model['scaling_z']); ?>
                        )"
                    ></arashtad-model>
                <?php endif; ?>
            </canvas>

            <div class="preloader">
                <div class="ar-spinner"></div>
                <div class="loading-counter">0%</div>
                <div class="loading-bar" data-progress="0"></div>
            </div>

            <?php if ($poster_enabled === '1' && $poster_image_url): ?>
                <div class="poster visible" onclick="this.classList.remove('visible')">
                    <img src="<?php echo esc_url($poster_image_url); ?>" alt="">
                </div>
            <?php endif; ?>

            <div class="arashtad-annotation-layer">
                <svg class="arashtad-annotation-lines"></svg>
            </div>
        </div>
    </div>
    <?php
}

function arashtad_annotation_shortcode_meta_box($post)
{
    if ($post->post_status === 'auto-draft') {
        echo '<p class="description">' . esc_html__('Save the scene to generate the shortcode.', 'arashtad-3d-annotation') . '</p>';
        return;
    }

    $shortcode = '[arashtad_annotation id="' . $post->ID . '"]';
    ?>
    <div class="ar-shortcode">

        <div class="text-center bold">
            <?php esc_html_e('Shortcode', 'arashtad-3d-annotation'); ?>
        </div>

        <hr>

        <div class="ar-shortcode-row">
            <input
                type="text"
                id="arashtad_shortcode"
                class="widefat"
                value="<?php echo esc_attr($shortcode); ?>"
                readonly
            >

            <div class="ar-spacer"></div>

            <button
                type="button"
                class="button button-block"
                id="arashtad_copy_shortcode"
            >
                <?php esc_html_e('Copy', 'arashtad-3d-annotation'); ?>
            </button>
        </div>
    </div>
    <?php
}

add_action('add_meta_boxes', 'arashtad_annotation_add_model_settings_meta_box');

function arashtad_annotation_add_model_settings_meta_box()
{
    add_meta_box('arashtad_annotation_model_settings', __('Model Settings', 'arashtad-3d-annotation'), 'arashtad_annotation_model_settings_callback', 'arashtad_annotation', 'normal', 'default');
}

function arashtad_annotation_get_models($post_id)
{
    $models = get_post_meta($post_id, '_arashtad_models', true);

    if (is_array($models) && !empty($models)) {
        return $models;
    }

    $legacy_model = get_post_meta($post_id, '_arashtad_model', true);

    if (!$legacy_model) {
        return [];
    }

    return [
        [
            'id' => 'model-1',
            'src' => $legacy_model,
            'position_x' => '0',
            'position_y' => '0',
            'position_z' => '0',
            'rotation_x' => '0',
            'rotation_y' => '0',
            'rotation_z' => '0',
            'scaling_x' => '1',
            'scaling_y' => '1',
            'scaling_z' => '1',
        ]
    ];
}

function arashtad_annotation_get_first_model($post_id)
{
    $models = arashtad_annotation_get_models($post_id);

    return !empty($models) ? $models[0] : [
        'id' => 'model-1',
        'src' => '',
        'position_x' => '0',
        'position_y' => '0',
        'position_z' => '0',
        'rotation_x' => '0',
        'rotation_y' => '0',
        'rotation_z' => '0',
        'scaling_x' => '1',
        'scaling_y' => '1',
        'scaling_z' => '1',
    ];
}

function arashtad_annotation_model_settings_callback($post)
{
    wp_nonce_field('arashtad_annotation_model_settings', 'arashtad_annotation_model_settings_nonce');

    $model = arashtad_annotation_get_first_model($post->ID);

    $defaults = [
        'id' => 'model-1',
        'src' => '',
        'position_x' => '0',
        'position_y' => '0',
        'position_z' => '0',
        'rotation_x' => '0',
        'rotation_y' => '0',
        'rotation_z' => '0',
        'scaling_x' => '1',
        'scaling_y' => '1',
        'scaling_z' => '1',
    ];

    $model = wp_parse_args($model, $defaults);

    $download_enabled = get_post_meta($post->ID, '_arashtad_download_enabled', true);
    $download_enabled = $download_enabled === '1';
    ?>

    <div class="arashtad-model-settings">

        <div class="text-center bold">
            <?php esc_html_e('Model Settings', 'arashtad-3d-annotation'); ?>
        </div>

        <hr>

        <div class="table-3-col">
            <p>
                <strong><?php esc_html_e('Position', 'arashtad-3d-annotation'); ?></strong>
            </p>

            <div class="vector-inputs">
                <input
                    type="number"
                    step="any"
                    name="arashtad_model_settings[position_x]"
                    value="<?php echo esc_attr($model['position_x']); ?>"
                    placeholder="X"
                >
                <input
                    type="number"
                    step="any"
                    name="arashtad_model_settings[position_y]"
                    value="<?php echo esc_attr($model['position_y']); ?>"
                    placeholder="Y"
                >
                <input
                    type="number"
                    step="any"
                    name="arashtad_model_settings[position_z]"
                    value="<?php echo esc_attr($model['position_z']); ?>"
                    placeholder="Z"
                >
            </div>

            <p class="description">
                <?php esc_html_e('Babylon.js Vector3 position.', 'arashtad-3d-annotation'); ?>
            </p>

            <p>
                <strong><?php esc_html_e('Rotation', 'arashtad-3d-annotation'); ?></strong>
            </p>

            <div class="vector-inputs">
                <input
                    type="number"
                    step="any"
                    name="arashtad_model_settings[rotation_x]"
                    value="<?php echo esc_attr($model['rotation_x']); ?>"
                    placeholder="X"
                >
                <input
                    type="number"
                    step="any"
                    name="arashtad_model_settings[rotation_y]"
                    value="<?php echo esc_attr($model['rotation_y']); ?>"
                    placeholder="Y"
                >
                <input
                    type="number"
                    step="any"
                    name="arashtad_model_settings[rotation_z]"
                    value="<?php echo esc_attr($model['rotation_z']); ?>"
                    placeholder="Z"
                >
            </div>

            <p class="description">
                <?php esc_html_e('Babylon.js Vector3 rotation in radians.', 'arashtad-3d-annotation'); ?>
            </p>

            <p>
                <strong><?php esc_html_e('Scaling', 'arashtad-3d-annotation'); ?></strong>
            </p>

            <div class="vector-inputs">
                <input
                    type="number"
                    step="any"
                    name="arashtad_model_settings[scaling_x]"
                    value="<?php echo esc_attr($model['scaling_x']); ?>"
                    placeholder="X"
                    style="width: 32%;"
                >
                <input
                    type="number"
                    step="any"
                    name="arashtad_model_settings[scaling_y]"
                    value="<?php echo esc_attr($model['scaling_y']); ?>"
                    placeholder="Y"
                    style="width: 32%;"
                >
                <input
                    type="number"
                    step="any"
                    name="arashtad_model_settings[scaling_z]"
                    value="<?php echo esc_attr($model['scaling_z']); ?>"
                    placeholder="Z"
                    style="width: 32%;"
                >
            </div>

            <p class="description">
                <?php esc_html_e('Babylon.js Vector3 scaling. Default: 1, 1, 1.', 'arashtad-3d-annotation'); ?>
            </p>

            <p>
                <label>
                    <input
                        type="checkbox"
                        name="arashtad_download_enabled"
                        value="1"
                        <?php echo checked($download_enabled, true, false); ?>
                    >
                    <?php esc_html_e('Enable Model Download', 'arashtad-3d-annotation'); ?>
                </label>
            </p>

            <p class="description">
                <?php esc_html_e('Display a download button below the canvas so visitors can download the scene model(s).', 'arashtad-3d-annotation'); ?>
            </p>

            <hr>

            <p class="description">
                <?php esc_html_e('The source file can be GLB, GLTF, OBJ, STL, PLY, Babylon, or any other format supported by the loaded Babylon.js loaders.', 'arashtad-3d-annotation'); ?>
            </p>
        </div>
    </div>

    <?php
}

add_action('save_post_arashtad_annotation', 'arashtad_annotation_save_model_settings', 20);

function arashtad_annotation_save_model_settings($post_id)
{
    if (
        !isset($_POST['arashtad_annotation_model_settings_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['arashtad_annotation_model_settings_nonce'])),
            'arashtad_annotation_model_settings'
        )
    ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id)) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $download_enabled = isset($_POST['arashtad_download_enabled']) ? 1 : 0;
    update_post_meta($post_id, '_arashtad_download_enabled', $download_enabled);

    $input = isset($_POST['arashtad_model_settings']) && is_array($_POST['arashtad_model_settings'])
        ? array_map('sanitize_text_field', wp_unslash($_POST['arashtad_model_settings']))
        : [];

    $defaults = [
        'id' => 'model-1',
        'src' => '',
        'position_x' => '0',
        'position_y' => '0',
        'position_z' => '0',
        'rotation_x' => '0',
        'rotation_y' => '0',
        'rotation_z' => '0',
        'scaling_x' => '1',
        'scaling_y' => '1',
        'scaling_z' => '1',
    ];

    $model = [];

    $model['id'] = isset($input['id']) ? sanitize_key($input['id']) : $defaults['id'];

    if ($model['id'] === '') {
        $model['id'] = $defaults['id'];
    }

    $model['src'] = get_post_meta($post_id, '_arashtad_model', true);

    $numeric_fields = [
        'position_x',
        'position_y',
        'position_z',
        'rotation_x',
        'rotation_y',
        'rotation_z',
        'scaling_x',
        'scaling_y',
        'scaling_z'
    ];

    foreach ($numeric_fields as $field) {
        if (isset($input[$field])) {
            $value = trim((string) $input[$field]);
            $model[$field] = is_numeric($value) ? (string) $value : $defaults[$field];
        } else {
            $model[$field] = $defaults[$field];
        }
    }

    update_post_meta($post_id, '_arashtad_models', [$model]);
}

function arashtad_annotation_save($post_id)
{
    $nonce = isset($_POST['arashtad_annotation_nonce']) ? sanitize_text_field(wp_unslash($_POST['arashtad_annotation_nonce'])) : '';

    if (!$nonce || !wp_verify_nonce($nonce, 'arashtad_annotation_model')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['arashtad_model'])) {
        $model_url = esc_url_raw(wp_unslash($_POST['arashtad_model']));
        update_post_meta($post_id, '_arashtad_model', $model_url);

        $models = arashtad_annotation_get_models($post_id);

        if (empty($models)) {
            $models = [[
                'id' => 'model-1',
                'src' => $model_url,
                'position_x' => '0',
                'position_y' => '0',
                'position_z' => '0',
                'rotation_x' => '0',
                'rotation_y' => '0',
                'rotation_z' => '0',
                'scaling_x' => '1',
                'scaling_y' => '1',
                'scaling_z' => '1',
            ]];
        } else {
            $models[0]['src'] = $model_url;
        }

        update_post_meta($post_id, '_arashtad_models', $models);
    }
}

function arashtad_annotation_columns($columns)
{
    $columns['arashtad_shortcode'] = __('Shortcode', 'arashtad-3d-annotation');
    return $columns;
}

function arashtad_annotation_column($column, $post_id)
{
    if ($column !== 'arashtad_shortcode')
        return;

    $shortcode = '[arashtad_annotation id="' . $post_id . '"]';

    echo '<input type="text" value="' . esc_attr($shortcode) . '" readonly style="width:220px">';
    echo ' <button type="button" class="button arashtad-copy-shortcode" data-shortcode="' . esc_attr($shortcode) . '">' . esc_html__('Copy', 'arashtad-3d-annotation') . '</button>';
}

function arashtad_annotation_admin_assets($hook)
{
    if (wp_doing_ajax() || wp_doing_cron()) {
        return;
    }

    $screen = get_current_screen();

    if (!$screen) {
        return;
    }

    $is_cpt_page = (
        $screen->post_type === 'arashtad_annotation' &&
        in_array($hook, ['post.php', 'post-new.php', 'edit.php'], true)
    );

    $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';  // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used only for admin page routing; no state-changing action is performed.

    $is_pro_features_page = (
        $page === 'arashtad-3d-annotation-pro-features'
    );

    $is_settings_page = (
        $hook === 'admin.php' &&
        strpos($page, 'arashtad') === 0
    );

    if (!$is_cpt_page && !$is_settings_page && !$is_pro_features_page) {
        return;
    }

    wp_enqueue_media();

    wp_enqueue_style(
        'arashtad-admin',
        ARASHTAD_3D_ANNOTATION_URL . 'var/css/admin.min.css',
        ['wp-admin'],
        ARASHTAD_3D_ANNOTATION_VERSION
    );

    wp_enqueue_style(
        'arashtad-3d-annotation',
        ARASHTAD_3D_ANNOTATION_URL . 'var/css/scene.min.css',
        ['wp-admin', 'arashtad-admin'],
        ARASHTAD_3D_ANNOTATION_VERSION
    );

    wp_enqueue_script(
        'arashtad-babylon-admin',
        ARASHTAD_3D_ANNOTATION_URL . 'lib/babylon/babylon.js',
        [],
        ARASHTAD_3D_ANNOTATION_VERSION,
        true
    );

    wp_enqueue_script(
        'arashtad-loaders-admin',
        ARASHTAD_3D_ANNOTATION_URL . 'lib/babylon/loaders/babylonjs.loaders.min.js',
        ['arashtad-babylon-admin'],
        ARASHTAD_3D_ANNOTATION_VERSION,
        true
    );

    wp_enqueue_script(
        'arashtad-admin',
        ARASHTAD_3D_ANNOTATION_URL . 'var/js/admin.js',
        ['arashtad-loaders-admin', 'jquery'],
        ARASHTAD_3D_ANNOTATION_VERSION,
        true
    );

    $current_post_id = arashtad_annotation_get_current_post_id();
    $can_edit_annotations = $current_post_id
        ? current_user_can('edit_post', $current_post_id)
        : current_user_can('edit_posts');

    wp_localize_script(
        'arashtad-admin',
        'ARASHTAD',
        [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('arashtad_nonce'),
            'plugin_url' => ARASHTAD_3D_ANNOTATION_URL,
            'post_id' => $current_post_id,
            'can_edit' => (bool) $can_edit_annotations,
            'annotations' => $current_post_id ? arashtad_annotation_get_annotations_for_js($current_post_id) : []
        ]
    );

    if ($is_pro_features_page) {
        wp_enqueue_style(
            'arashtad-pro-features',
            ARASHTAD_3D_ANNOTATION_URL . 'var/css/pro-features.min.css',
            ['arashtad-admin'],
            ARASHTAD_3D_ANNOTATION_VERSION
        );
    }
}

add_action('admin_enqueue_scripts', 'arashtad_annotation_admin_assets');

function arashtad_annotation_admin_body_class($classes)
{
    $screen = get_current_screen();
    if (!$screen) {
        return $classes;
    }
    $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';  // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used only for admin page routing; no state-changing action is performed.

    if ($screen->post_type === 'arashtad_annotation' || strpos($page, 'arashtad') === 0) {
        $classes .= ' arashtad-admin folded';

        if ($screen->post_type === 'arashtad_annotation') {
            $classes .= ' arashtad-3d-annotation-admin folded';

            if ($screen->base === 'post') {
                $classes .= ' arashtad-edit-scene folded';
            } elseif ($screen->base === 'edit') {
                $classes .= ' arashtad-list-scenes folded';
            } elseif ($screen->base === 'post-new') {
                $classes .= ' arashtad-new-scene folded';
            }
        }

        if ($screen->base === 'admin' && strpos($page, 'arashtad') === 0) {
            $classes .= ' arashtad-settings-page';
        }
    }
    return $classes;
}

add_filter('admin_body_class', 'arashtad_annotation_admin_body_class');

function arashtad_annotation_upload_mimes($mimes)
{
    $mimes['glb'] = 'model/gltf-binary';
    $mimes['gltf'] = 'model/gltf+json';
    $mimes['obj'] = 'text/plain';
    $mimes['stl'] = 'model/stl';
    $mimes['ply'] = 'application/octet-stream';
    $mimes['splat'] = 'application/octet-stream';
    $mimes['spz'] = 'application/octet-stream';
    $mimes['sog'] = 'application/octet-stream';
    $mimes['babylon'] = 'application/json';

    return $mimes;
}

add_filter('script_loader_tag', 'arashtad_annotation_admin_module_script', 10, 3);

function arashtad_annotation_admin_module_script($tag, $handle, $src)
{
    if ($handle === 'arashtad-admin') {
        return '<script type="module" src="' . esc_url($src) . '"></script>';  // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- Script is registered and enqueued through WordPress; this filter only adds the required module type.
    }

    return $tag;
}

add_action('add_meta_boxes', 'arashtad_annotation_add_style_meta_box');

function arashtad_annotation_add_style_meta_box()
{
    add_meta_box('arashtad_annotation_styles', __('Wrapper Styles', 'arashtad-3d-annotation'), 'arashtad_annotation_styles', 'arashtad_annotation', 'side', 'default');
}

function arashtad_annotation_styles($post)
{
    $defaults = [
        'position' => 'relative',
        'z_index' => '1',
        'top' => 'auto',
        'left' => 'auto',
        'bottom' => 'auto',
        'right' => 'auto',
        'transform' => 'none',
        'margin' => '0',
        'padding' => '0',
        'width' => '100%',
        'max_width' => '100%',
        'height' => 'auto',
        'max_height' => '100%',
        'aspect_ratio' => '16 / 9',
        'border' => '2px solid #333333',
        'border_radius' => '10px',
        'overflow' => 'hidden',
        'box_sizing' => 'border-box',
        'box_shadow' => '0 0 20px rgba(0, 0, 0, .4)',
        'outline' => 'none'
    ];

    $styles = get_post_meta($post->ID, '_arashtad_annotation_canvas_styles', true);
    $styles = wp_parse_args(is_array($styles) ? $styles : [], $defaults);

    $scrolllock_enabled = get_post_meta($post->ID, '_arashtad_scrolllock_enabled', true);
    $scrolllock_enabled = $scrolllock_enabled !== '' ? (bool) $scrolllock_enabled : true;

    wp_nonce_field('arashtad_annotation_canvas_styles', 'arashtad_annotation_canvas_styles_nonce');
    ?>

    <div class="arashtad-canvas-styles">

        <div class="text-center bold">
            <?php esc_html_e('Wrapper Style', 'arashtad-3d-annotation'); ?>
        </div>

        <hr>

        <p>
            <label>
                <input
                    type="checkbox"
                    name="arashtad_scrolllock_enabled"
                    value="1"
                    <?php echo checked($scrolllock_enabled, true, false); ?>
                >
                <?php esc_html_e('Enable Scroll Lock', 'arashtad-3d-annotation'); ?>
            </label>
        </p>

        <p class="description">
            <?php esc_html_e('Prevent page scrolling while interacting with the 3D canvas.', 'arashtad-3d-annotation'); ?>
        </p>

        <?php
        $poster_enabled = get_post_meta($post->ID, '_arashtad_poster_enabled', true);
        $poster_image_id = get_post_meta($post->ID, '_arashtad_poster_image_id', true);
        $poster_image_url = $poster_image_id ? wp_get_attachment_image_url($poster_image_id, 'full') : '';
        ?>

        <p>
            <label>
                <input type="checkbox" name="arashtad_poster_enabled" id="arashtad_poster_enabled" value="1" <?php echo checked($poster_enabled, '1', false); ?>>
                <?php esc_html_e('Enable Poster', 'arashtad-3d-annotation'); ?>
            </label>
        </p>

        <p class="description">
            <?php esc_html_e('Displays a poster image when the page loads. Click the poster to hide it and reveal the 3D scene.', 'arashtad-3d-annotation'); ?>
        </p>

        <div id="arashtad-poster-settings" class="<?php echo $poster_enabled === '1' ? 'enabled' : ''; ?>">
            <p>
                <label><?php esc_html_e('Poster Image', 'arashtad-3d-annotation'); ?></label>
            </p>

            <input type="hidden" name="arashtad_poster_image_id" id="arashtad_poster_image_id" value="<?php echo esc_attr($poster_image_id); ?>">

            <div id="arashtad-poster-preview" class="<?php echo $poster_image_url ? 'has-image' : ''; ?>">
                <?php if ($poster_image_url): ?>
                    <img src="<?php echo esc_url($poster_image_url); ?>" class="arashtad-poster-preview-image" alt="">
                <?php endif; ?>
            </div>

            <p>
                <button type="button" class="button" id="arashtad-select-poster"><?php esc_html_e('Select Poster', 'arashtad-3d-annotation'); ?></button>
                <button type="button" class="button" id="arashtad-clear-poster" <?php echo $poster_image_id ? '' : 'hidden'; ?>><?php esc_html_e('Clear', 'arashtad-3d-annotation'); ?></button>
            </p>
        </div>

        <p>
            <label><?php esc_html_e('Position', 'arashtad-3d-annotation'); ?></label>
            <select name="arashtad_styles[position]" class="widefat">
                <?php foreach (['static', 'relative', 'absolute', 'fixed', 'sticky'] as $value): ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php echo selected($styles['position'], $value, false); ?>><?php echo esc_html(ucfirst($value)); ?></option>
                <?php endforeach; ?>
            </select>
        </p>

        <?php
        $fields = [
            'z_index' => __('Z-Index', 'arashtad-3d-annotation'),
            'top' => __('Top', 'arashtad-3d-annotation'),
            'left' => __('Left', 'arashtad-3d-annotation'),
            'bottom' => __('Bottom', 'arashtad-3d-annotation'),
            'right' => __('Right', 'arashtad-3d-annotation'),
            'transform' => __('Transform', 'arashtad-3d-annotation'),
            'margin' => __('Margin', 'arashtad-3d-annotation'),
            'padding' => __('Padding', 'arashtad-3d-annotation'),
            'width' => __('Width', 'arashtad-3d-annotation'),
            'max_width' => __('Max Width', 'arashtad-3d-annotation'),
            'height' => __('Height', 'arashtad-3d-annotation'),
            'max_height' => __('Max Height', 'arashtad-3d-annotation'),
            'aspect_ratio' => __('Aspect Ratio', 'arashtad-3d-annotation'),
            'border' => __('Border', 'arashtad-3d-annotation'),
            'border_radius' => __('Border Radius', 'arashtad-3d-annotation'),
            'overflow' => __('Overflow', 'arashtad-3d-annotation'),
            'box_sizing' => __('Box Sizing', 'arashtad-3d-annotation'),
            'box_shadow' => __('Box Shadow', 'arashtad-3d-annotation'),
            'outline' => __('Outline', 'arashtad-3d-annotation')
        ];

        foreach ($fields as $name => $label):
            ?>
            <p>
                <label><?php echo esc_html($label); ?></label>
                <input type="text" name="arashtad_styles[<?php echo esc_attr($name); ?>]" value="<?php echo esc_attr($styles[$name]); ?>" class="widefat">
            </p>
        <?php endforeach; ?>

    </div>

    <?php
}

add_action('save_post_arashtad_annotation', 'arashtad_annotation_save_canvas_styles', 20);

function arashtad_annotation_save_canvas_styles($post_id)
{
    $nonce = isset($_POST['arashtad_annotation_canvas_styles_nonce']) ? sanitize_text_field(wp_unslash($_POST['arashtad_annotation_canvas_styles_nonce'])) : '';

    if (!$nonce || !wp_verify_nonce($nonce, 'arashtad_annotation_canvas_styles'))
        return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if (wp_is_post_revision($post_id))
        return;

    if (!current_user_can('edit_post', $post_id))
        return;

    $scrolllock_enabled = isset($_POST['arashtad_scrolllock_enabled']) ? 1 : 0;
    update_post_meta($post_id, '_arashtad_scrolllock_enabled', $scrolllock_enabled);

    $poster_enabled = isset($_POST['arashtad_poster_enabled']) ? 1 : 0;
    update_post_meta($post_id, '_arashtad_poster_enabled', $poster_enabled);

    $poster_image_id = isset($_POST['arashtad_poster_image_id']) ? absint($_POST['arashtad_poster_image_id']) : 0;
    update_post_meta($post_id, '_arashtad_poster_image_id', $poster_image_id);

    $styles = [];

    if (isset($_POST['arashtad_styles']) && is_array($_POST['arashtad_styles'])) {
        foreach (array_map('sanitize_text_field', wp_unslash($_POST['arashtad_styles'])) as $key => $value) {
            $styles[sanitize_key($key)] = sanitize_text_field($value);
        }
    }

    update_post_meta($post_id, '_arashtad_annotation_canvas_styles', $styles);
}

function arashtad_annotation_get_current_post_id()
{
    if (isset($_GET['post'])) {  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return absint(wp_unslash($_GET['post']));  // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used only to determine the current post ID; no state-changing action is performed.
    }

    if (isset($_POST['post_ID'])) {  // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
        return absint(wp_unslash($_POST['post_ID']));  // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended -- Used only to determine the current post ID; request authorization is handled by the calling action.
    }

    global $post;

    if ($post instanceof WP_Post) {
        return $post->ID;
    }

    return 0;
}

function arashtad_annotation_get_annotations_for_js($post_id)
{
    $annotations = get_post_meta($post_id, '_arashtad_annotations', true);

    if (!is_array($annotations)) {
        return [];
    }

    $result = [];

    foreach ($annotations as $id => $annotation) {
        $position = isset($annotation['position']) && is_array($annotation['position'])
            ? $annotation['position']
            : ['x' => 0, 'y' => 0, 'z' => 0];

        $result[] = [
            'id' => (string) $id,
            'title' => isset($annotation['title']) ? $annotation['title'] : '',
            'text' => isset($annotation['text']) ? $annotation['text'] : '',
            'position' => [
                'x' => isset($position['x']) ? (float) $position['x'] : 0,
                'y' => isset($position['y']) ? (float) $position['y'] : 0,
                'z' => isset($position['z']) ? (float) $position['z'] : 0,
            ],
        ];
    }

    return $result;
}

add_action('wp_ajax_arashtad_save_annotation', 'arashtad_annotation_ajax_save_annotation');

function arashtad_annotation_ajax_save_annotation()
{
    check_ajax_referer('arashtad_nonce', 'nonce');

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;

    if (!$post_id || get_post_type($post_id) !== 'arashtad_annotation') {
        wp_send_json_error(['message' => __('Invalid scene.', 'arashtad-3d-annotation')], 400);
    }

    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error(['message' => __('You are not allowed to edit this scene.', 'arashtad-3d-annotation')], 403);
    }

    $raw = isset($_POST['annotation']) ? wp_unslash($_POST['annotation']) : '';  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON payload is decoded and validated immediately after retrieval.
    $data = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        wp_send_json_error(['message' => __('Invalid annotation data.', 'arashtad-3d-annotation')], 400);
    }

    if (
        isset($data['id']) && !is_string($data['id']) ||
        isset($data['title']) && !is_string($data['title']) ||
        isset($data['text']) && !is_string($data['text']) ||
        isset($data['position']) && !is_array($data['position'])
    ) {
        wp_send_json_error(['message' => __('Invalid annotation data.', 'arashtad-3d-annotation')], 400);
    }

    $client_id = isset($data['id']) ? sanitize_text_field($data['id']) : '';
    $title = isset($data['title']) ? sanitize_text_field($data['title']) : '';
    $text = isset($data['text']) ? sanitize_textarea_field($data['text']) : '';

    if ($title === '') {
        $title = __('Untitled', 'arashtad-3d-annotation');
    }

    $raw_position = isset($data['position']) ? $data['position'] : [];

    foreach (['x', 'y', 'z'] as $axis) {
        if (isset($raw_position[$axis]) && !is_numeric($raw_position[$axis])) {
            wp_send_json_error(['message' => __('Invalid annotation position.', 'arashtad-3d-annotation')], 400);
        }
    }

    $position = [
        'x' => isset($raw_position['x']) ? (float) $raw_position['x'] : 0,
        'y' => isset($raw_position['y']) ? (float) $raw_position['y'] : 0,
        'z' => isset($raw_position['z']) ? (float) $raw_position['z'] : 0,
    ];

    $annotations = get_post_meta($post_id, '_arashtad_annotations', true);

    if (!is_array($annotations)) {
        $annotations = [];
    }

    $server_id = isset($annotations[$client_id]) ? $client_id : uniqid('arashtad_ann_', false);

    $annotations[$server_id] = [
        'title' => $title,
        'text' => $text,
        'position' => $position,
    ];

    update_post_meta($post_id, '_arashtad_annotations', $annotations);

    wp_send_json_success(['id' => $server_id]);
}

add_action('wp_ajax_arashtad_delete_annotation', 'arashtad_annotation_ajax_delete_annotation');

function arashtad_annotation_ajax_delete_annotation()
{
    check_ajax_referer('arashtad_nonce', 'nonce');

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;

    if (!$post_id || !current_user_can('edit_post', $post_id)) {
        wp_send_json_error(['message' => __('You are not allowed to edit this scene.', 'arashtad-3d-annotation')], 403);
    }

    $annotation_id = isset($_POST['annotation_id']) ? sanitize_text_field(wp_unslash($_POST['annotation_id'])) : '';

    if ($annotation_id === '') {
        wp_send_json_error(['message' => __('Missing annotation ID.', 'arashtad-3d-annotation')], 400);
    }

    $annotations = get_post_meta($post_id, '_arashtad_annotations', true);

    if (is_array($annotations) && isset($annotations[$annotation_id])) {
        unset($annotations[$annotation_id]);
        update_post_meta($post_id, '_arashtad_annotations', $annotations);
    }

    wp_send_json_success();
}

add_action('add_meta_boxes', 'arashtad_annotation_add_clear_color_meta_box');

function arashtad_annotation_add_clear_color_meta_box()
{
    add_meta_box('arashtad_annotation_clear_color', __('Clear Color', 'arashtad-3d-annotation'), 'arashtad_annotation_clear_color_callback', 'arashtad_annotation', 'side', 'default');
}

function arashtad_annotation_clear_color_callback($post)
{
    wp_nonce_field('arashtad_annotation_clear_color', 'arashtad_annotation_clear_color_nonce');

    $clear_color = get_post_meta($post->ID, '_arashtad_clear_color', true);

    if (empty($clear_color)) {
        $clear_color = '0.0588, 0.0784, 0.0706, 1';
    }
    ?>

    <div class="arashtad-clear-color-settings">

        <div class="text-center bold">
            <?php esc_html_e('clearColor', 'arashtad-3d-annotation'); ?>
        </div>

        <hr>

        <p>
            <label>
                <input type="text" name="arashtad_clear_color" value="<?php echo esc_attr($clear_color); ?>" class="widefat" placeholder="0.0588, 0.0784, 0.0706, 1">
            </label>
        </p>

        <p class="description">
            <?php esc_html_e('Enter RGBA values separated by commas (R, G, B, A). Each value between 0 and 1. Clear color appears on top of the canvas background!', 'arashtad-3d-annotation'); ?>
        </p>
    </div>

    <?php
}

add_action('save_post_arashtad_annotation', 'arashtad_annotation_save_clear_color', 20);

function arashtad_annotation_save_clear_color($post_id)
{
    $nonce = isset($_POST['arashtad_annotation_clear_color_nonce']) ? sanitize_text_field(wp_unslash($_POST['arashtad_annotation_clear_color_nonce'])) : '';

    if (!$nonce || !wp_verify_nonce($nonce, 'arashtad_annotation_clear_color'))
        return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if (wp_is_post_revision($post_id))
        return;

    if (!current_user_can('edit_post', $post_id))
        return;

    if (isset($_POST['arashtad_clear_color'])) {
        $color = sanitize_text_field(wp_unslash($_POST['arashtad_clear_color']));
        update_post_meta($post_id, '_arashtad_clear_color', $color);
    }
}

add_action('add_meta_boxes', 'arashtad_annotation_add_lazy_meta_box');

function arashtad_annotation_add_lazy_meta_box()
{
    add_meta_box('arashtad_annotation_lazy', __('Lazy Loading', 'arashtad-3d-annotation'), 'arashtad_annotation_lazy_callback', 'arashtad_annotation', 'side', 'default');
}

function arashtad_annotation_lazy_callback($post)
{
    wp_nonce_field('arashtad_annotation_lazy', 'arashtad_annotation_lazy_nonce');

    $lazy_enabled = get_post_meta($post->ID, '_arashtad_lazy_enabled', true);
    $lazy_enabled = $lazy_enabled !== '' ? (bool) $lazy_enabled : true;
    ?>

    <div class="arashtad-lazy-settings">

        <div class="text-center bold">
            <?php esc_html_e('Lazy Loading', 'arashtad-3d-annotation'); ?>
        </div>

        <hr>

        <p>
            <label>
                <input type="checkbox" name="arashtad_lazy_enabled" value="1" <?php echo checked($lazy_enabled, true, false); ?>>
                <?php esc_html_e('Enable Lazy Loading', 'arashtad-3d-annotation'); ?>
            </label>
        </p>

        <p class="description">
            <?php esc_html_e('When enabled, the 3D scene will only load when it becomes visible in the viewport. This can improve page load performance.', 'arashtad-3d-annotation'); ?>
        </p>
    </div>

    <?php
}

add_action('save_post_arashtad_annotation', 'arashtad_annotation_save_lazy_setting', 20);

function arashtad_annotation_save_lazy_setting($post_id)
{
    $nonce = isset($_POST['arashtad_annotation_lazy_nonce']) ? sanitize_text_field(wp_unslash($_POST['arashtad_annotation_lazy_nonce'])) : '';

    if (!$nonce || !wp_verify_nonce($nonce, 'arashtad_annotation_lazy'))
        return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if (wp_is_post_revision($post_id))
        return;

    if (!current_user_can('edit_post', $post_id))
        return;

    $lazy_enabled = isset($_POST['arashtad_lazy_enabled']) ? 1 : 0;
    update_post_meta($post_id, '_arashtad_lazy_enabled', $lazy_enabled);
}

add_action('add_meta_boxes', 'arashtad_annotation_add_camera_meta_box');

function arashtad_annotation_add_camera_meta_box()
{
    add_meta_box('arashtad_annotation_camera', __('Camera Settings', 'arashtad-3d-annotation'), 'arashtad_annotation_camera_callback', 'arashtad_annotation', 'normal', 'default');
}

function arashtad_annotation_camera_callback($post)
{
    wp_nonce_field('arashtad_annotation_camera', 'arashtad_annotation_camera_nonce');

    $camera_config = get_post_meta($post->ID, '_arashtad_camera_config', true);

    $defaults = [
        'camera_type' => 'ArcRotateCamera',
        'alpha' => '1.57',
        'beta' => '1.2',
        'radius' => '12',
        'target_x' => '0',
        'target_y' => '0',
        'target_z' => '0',
        'position_x' => '0',
        'position_y' => '0',
        'position_z' => '3',
        'fov' => '0.4',
        'minZ' => '0.1',
        'maxZ' => '1000',
        'use_auto_rotate' => '0',
        'idle_rotation_speed' => '0.1',
        'idle_rotation_wait_time' => '3000',
        'idle_rotation_spinup_time' => '6000'
    ];

    $camera_config = wp_parse_args(is_array($camera_config) ? $camera_config : [], $defaults);
    ?>

    <div class="arashtad-camera-settings">

        <div class="text-center bold">
            <?php esc_html_e('Model Settings', 'arashtad-3d-annotation'); ?>
        </div>

        <hr>

        <div>
            <table class="form-table">
                <tr class="camera-field">
                    <th scope="row"><label for="camera_type"><?php esc_html_e('Camera Type', 'arashtad-3d-annotation'); ?></label></th>
                    <td>
                        <select name="arashtad_camera[camera_type]" id="camera_type" class="widefat">
                            <option value="ArcRotateCamera" <?php echo selected($camera_config['camera_type'], 'ArcRotateCamera', false); ?>><?php esc_html_e('Arc Rotate Camera', 'arashtad-3d-annotation'); ?></option>
                            <option value="UniversalCamera" <?php echo selected($camera_config['camera_type'], 'UniversalCamera', false); ?>><?php esc_html_e('Universal Camera', 'arashtad-3d-annotation'); ?></option>
                            <option value="FreeCamera" <?php echo selected($camera_config['camera_type'], 'FreeCamera', false); ?>><?php esc_html_e('Free Camera', 'arashtad-3d-annotation'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Select the type of camera to use for the 3D scene.', 'arashtad-3d-annotation'); ?></p>
                    </td>
                </tr>

                <tr class="camera-field arcrotate-field">
                    <th scope="row"><label for="alpha"><?php esc_html_e('Alpha (radians)', 'arashtad-3d-annotation'); ?></label></th>
                    <td>
                        <input type="number" step="any" name="arashtad_camera[alpha]" id="alpha" value="<?php echo esc_attr($camera_config['alpha']); ?>" class="widefat">
                        <p class="description"><?php esc_html_e('Camera rotation around the target (horizontal). Default: 1.57', 'arashtad-3d-annotation'); ?></p>
                    </td>
                </tr>

                <tr class="camera-field arcrotate-field">
                    <th scope="row"><label for="beta"><?php esc_html_e('Beta (radians)', 'arashtad-3d-annotation'); ?></label></th>
                    <td>
                        <input type="number" step="any" name="arashtad_camera[beta]" id="beta" value="<?php echo esc_attr($camera_config['beta']); ?>" class="widefat">
                        <p class="description"><?php esc_html_e('Camera rotation around the target (vertical). Default: 1.2', 'arashtad-3d-annotation'); ?></p>
                    </td>
                </tr>

                <tr class="camera-field arcrotate-field">
                    <th scope="row"><label for="radius"><?php esc_html_e('Radius', 'arashtad-3d-annotation'); ?></label></th>
                    <td>
                        <input type="number" step="any" name="arashtad_camera[radius]" id="radius" value="<?php echo esc_attr($camera_config['radius']); ?>" class="widefat">
                        <p class="description"><?php esc_html_e('Distance from the target. Default: 12', 'arashtad-3d-annotation'); ?></p>
                    </td>
                </tr>

                <tr class="camera-field common-field">
                    <th scope="row"><label for="target_x"><?php esc_html_e('Target Position', 'arashtad-3d-annotation'); ?></label></th>
                    <td>
                        <div class="vector-inputs">
                            <input type="number" step="any" name="arashtad_camera[target_x]" id="target_x" value="<?php echo esc_attr($camera_config['target_x']); ?>" placeholder="X" style="width: 32%">
                            <input type="number" step="any" name="arashtad_camera[target_y]" id="target_y" value="<?php echo esc_attr($camera_config['target_y']); ?>" placeholder="Y" style="width: 32%">
                            <input type="number" step="any" name="arashtad_camera[target_z]" id="target_z" value="<?php echo esc_attr($camera_config['target_z']); ?>" placeholder="Z" style="width: 32%">
                        </div>
                        <p class="description"><?php esc_html_e('The point the camera looks at. Default: 0, 0, 0', 'arashtad-3d-annotation'); ?></p>
                    </td>
                </tr>

                <tr class="camera-field common-field">
                    <th scope="row"><label for="position_x"><?php esc_html_e('Camera Position', 'arashtad-3d-annotation'); ?></label></th>
                    <td>
                        <div class="vector-inputs">
                            <input type="number" step="any" name="arashtad_camera[position_x]" id="position_x" value="<?php echo esc_attr($camera_config['position_x']); ?>" placeholder="X" style="width: 32%">
                            <input type="number" step="any" name="arashtad_camera[position_y]" id="position_y" value="<?php echo esc_attr($camera_config['position_y']); ?>" placeholder="Y" style="width: 32%">
                            <input type="number" step="any" name="arashtad_camera[position_z]" id="position_z" value="<?php echo esc_attr($camera_config['position_z']); ?>" placeholder="Z" style="width: 32%">
                        </div>
                        <p class="description"><?php esc_html_e('The position of the camera. Default: 0, 0, 3', 'arashtad-3d-annotation'); ?></p>
                    </td>
                </tr>

                <tr class="camera-field common-field">
                    <th scope="row"><label for="fov"><?php esc_html_e('Field of View (radians)', 'arashtad-3d-annotation'); ?></label></th>
                    <td>
                        <input type="number" step="any" min="0.1" max="3.14" name="arashtad_camera[fov]" id="fov" value="<?php echo esc_attr($camera_config['fov']); ?>" class="widefat">
                        <p class="description"><?php esc_html_e('Camera field of view. Default: 0.4', 'arashtad-3d-annotation'); ?></p>
                    </td>
                </tr>

                <tr class="camera-field common-field">
                    <th scope="row"><label for="minZ"><?php esc_html_e('Min Z', 'arashtad-3d-annotation'); ?></label></th>
                    <td>
                        <input type="number" step="any" min="0" name="arashtad_camera[minZ]" id="minZ" value="<?php echo esc_attr($camera_config['minZ']); ?>" class="widefat">
                        <p class="description"><?php esc_html_e('Minimum rendering distance. Default: 0.1', 'arashtad-3d-annotation'); ?></p>
                    </td>
                </tr>

                <tr class="camera-field common-field">
                    <th scope="row"><label for="maxZ"><?php esc_html_e('Max Z', 'arashtad-3d-annotation'); ?></label></th>
                    <td>
                        <input type="number" step="any" min="0" name="arashtad_camera[maxZ]" id="maxZ" value="<?php echo esc_attr($camera_config['maxZ']); ?>" class="widefat">
                        <p class="description"><?php esc_html_e('Maximum rendering distance. Default: 1000', 'arashtad-3d-annotation'); ?></p>
                    </td>
                </tr>

                <tr class="camera-field arcrotate-field">
                    <th scope="row"><label for="use_auto_rotate"><?php esc_html_e('Auto Rotation', 'arashtad-3d-annotation'); ?></label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="arashtad_camera[use_auto_rotate]" id="use_auto_rotate" value="1" <?php echo checked($camera_config['use_auto_rotate'], '1', false); ?>>
                            <?php esc_html_e('Enable auto rotation', 'arashtad-3d-annotation'); ?>
                        </label>
                    </td>
                </tr>

                <tr class="camera-field arcrotate-field auto-rotate-field" style="<?php echo $camera_config['use_auto_rotate'] === '1' ? '' : 'display:none;'; ?>">
                    <th scope="row"><label for="idle_rotation_speed"><?php esc_html_e('Rotation Speed', 'arashtad-3d-annotation'); ?></label></th>
                    <td>
                        <input type="number" step="any" name="arashtad_camera[idle_rotation_speed]" id="idle_rotation_speed" value="<?php echo esc_attr($camera_config['idle_rotation_speed']); ?>" class="widefat">
                        <p class="description"><?php esc_html_e('Rotation speed when idle. Default: 0.1', 'arashtad-3d-annotation'); ?></p>
                    </td>
                </tr>

                <tr class="camera-field arcrotate-field auto-rotate-field" style="<?php echo $camera_config['use_auto_rotate'] === '1' ? '' : 'display:none;'; ?>">
                    <th scope="row"><label for="idle_rotation_wait_time"><?php esc_html_e('Wait Time (ms)', 'arashtad-3d-annotation'); ?></label></th>
                    <td>
                        <input type="number" step="10" name="arashtad_camera[idle_rotation_wait_time]" id="idle_rotation_wait_time" value="<?php echo esc_attr($camera_config['idle_rotation_wait_time']); ?>" class="widefat">
                        <p class="description"><?php esc_html_e('Time to wait before starting rotation. Default: 3000', 'arashtad-3d-annotation'); ?></p>
                    </td>
                </tr>

                <tr class="camera-field arcrotate-field auto-rotate-field" style="<?php echo $camera_config['use_auto_rotate'] === '1' ? '' : 'display:none;'; ?>">
                    <th scope="row"><label for="idle_rotation_spinup_time"><?php esc_html_e('Spinup Time (ms)', 'arashtad-3d-annotation'); ?></label></th>
                    <td>
                        <input type="number" step="10" name="arashtad_camera[idle_rotation_spinup_time]" id="idle_rotation_spinup_time" value="<?php echo esc_attr($camera_config['idle_rotation_spinup_time']); ?>" class="widefat">
                        <p class="description"><?php esc_html_e('Time to reach full rotation speed. Default: 6000', 'arashtad-3d-annotation'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php
}

function arashtad_annotation_resources_callback($post)
{
    ?>
    <div class="text-center resources">

        <div class="text-center bold">
            <?php esc_html_e('Resources', 'arashtad-3d-annotation'); ?>
        </div>

        <hr>

        <p>
            <a class="button button-block" href="<?php echo esc_url(admin_url('admin.php?page=arashtad-3d-annotation-pro-features')); ?>">
                <?php esc_html_e('Pro Features', 'arashtad-3d-annotation'); ?>
            </a>
        </p>

        <p>
            <a class="button button-block" href="https://market.arashtad.com/product/arashtad-3d-annotation" target="_blank">
                <?php esc_html_e('Upgrade', 'arashtad-3d-annotation'); ?>
            </a>
        </p>

        <p>
            <a class="button button-block" href="https://demo.arashtad.com/categories/wordpress-development/wp/arashtad-3d-annotation" target="_blank">
                <?php esc_html_e('Demo', 'arashtad-3d-annotation'); ?>
            </a>
        </p>

        <p>
            <a class="button button-block" href="https://doc.arashtad.com/docs/arashtad-3d-annotation" target="_blank">
                <?php esc_html_e('Documentation', 'arashtad-3d-annotation'); ?>
            </a>
        </p>

        <p>
            <a class="button button-block" href="https://tuts.arashtad.com/courses/arashtad-3d-annotation" target="_blank">
                <?php esc_html_e('Tutorials', 'arashtad-3d-annotation'); ?>
            </a>
        </p>
    </div>
    <hr>
    <div class="text-center resources">
        <?php esc_html_e('Powered by', 'arashtad-3d-annotation'); ?>  
        <a href="https://arashtad.com/arashtad-3d-runtime" target="_blank" rel="noopener">
            <?php esc_html_e('Arashtad 3D Runtime', 'arashtad-3d-annotation'); ?>
        </a>
    </div>
    <?php
}

add_action('add_meta_boxes', 'arashtad_annotation_add_light_meta_box');

function arashtad_annotation_add_light_meta_box()
{
    add_meta_box('arashtad_annotation_light', __('Light Settings', 'arashtad-3d-annotation'), 'arashtad_annotation_light_callback', 'arashtad_annotation', 'side', 'default');
}

function arashtad_annotation_light_callback($post)
{
    wp_nonce_field('arashtad_annotation_light', 'arashtad_annotation_light_nonce');

    $light_config = get_post_meta($post->ID, '_arashtad_light_config', true);

    $defaults = [
        'type' => 'DirectionalLight',
        'direction_x' => '0',
        'direction_y' => '-1',
        'direction_z' => '-2',
        'position_x' => '0',
        'position_y' => '5',
        'position_z' => '0',
        'intensity' => '3',
        'angle' => '1.0472',
        'exponent' => '2'
    ];

    $light_config = wp_parse_args(is_array($light_config) ? $light_config : [], $defaults);
    ?>

    <div class="arashtad-3d-annotation-light-settings">

        <div class="text-center bold">
            <?php esc_html_e('Light Settings', 'arashtad-3d-annotation'); ?>
        </div>

        <hr>

        <p>
            <label for="light_type"><strong><?php esc_html_e('Light Type', 'arashtad-3d-annotation'); ?></strong></label>
        </p>

        <select name="arashtad_light[type]" id="light_type" class="widefat">
            <option value="HemisphericLight" <?php echo selected($light_config['type'], 'HemisphericLight', false); ?>><?php esc_html_e('Hemispheric Light', 'arashtad-3d-annotation'); ?></option>
            <option value="PointLight" <?php echo selected($light_config['type'], 'PointLight', false); ?>><?php esc_html_e('Point Light', 'arashtad-3d-annotation'); ?></option>
            <option value="DirectionalLight" <?php echo selected($light_config['type'], 'DirectionalLight', false); ?>><?php esc_html_e('Directional Light', 'arashtad-3d-annotation'); ?></option>
            <option value="SpotLight" <?php echo selected($light_config['type'], 'SpotLight', false); ?>><?php esc_html_e('Spot Light', 'arashtad-3d-annotation'); ?></option>
        </select>

        <p class="description">
            <?php esc_html_e('Select the Babylon.js light type.', 'arashtad-3d-annotation'); ?>
        </p>

        <div class="light-direction-fields table-3-col">
            <p><strong><?php esc_html_e('Direction', 'arashtad-3d-annotation'); ?></strong></p>

            <div class="vector-inputs">
                <input type="number" step="any" name="arashtad_light[direction_x]" id="light_direction_x" value="<?php echo esc_attr($light_config['direction_x']); ?>" placeholder="X">
                <input type="number" step="any" name="arashtad_light[direction_y]" id="light_direction_y" value="<?php echo esc_attr($light_config['direction_y']); ?>" placeholder="Y">
                <input type="number" step="any" name="arashtad_light[direction_z]" id="light_direction_z" value="<?php echo esc_attr($light_config['direction_z']); ?>" placeholder="Z">
            </div>
        </div>

        <div class="light-position-fields table-3-col">
            <p><strong><?php esc_html_e('Position', 'arashtad-3d-annotation'); ?></strong></p>

            <div class="vector-inputs">
                <input type="number" step="any" name="arashtad_light[position_x]" id="light_position_x" value="<?php echo esc_attr($light_config['position_x']); ?>" placeholder="X">
                <input type="number" step="any" name="arashtad_light[position_y]" id="light_position_y" value="<?php echo esc_attr($light_config['position_y']); ?>" placeholder="Y">
                <input type="number" step="any" name="arashtad_light[position_z]" id="light_position_z" value="<?php echo esc_attr($light_config['position_z']); ?>" placeholder="Z">
            </div>
        </div>

        <div class="light-angle-fields">
            <p>
                <label for="light_angle"><strong><?php esc_html_e('Angle', 'arashtad-3d-annotation'); ?></strong></label>
            </p>

            <input type="number" step="any" name="arashtad_light[angle]" id="light_angle" value="<?php echo esc_attr($light_config['angle']); ?>" class="widefat">

            <p class="description">
                <?php esc_html_e('SpotLight cone angle in radians.', 'arashtad-3d-annotation'); ?>
            </p>
        </div>

        <div class="light-exponent-fields">
            <p>
                <label for="light_exponent"><strong><?php esc_html_e('Exponent', 'arashtad-3d-annotation'); ?></strong></label>
            </p>

            <input type="number" step="any" min="0" name="arashtad_light[exponent]" id="light_exponent" value="<?php echo esc_attr($light_config['exponent']); ?>" class="widefat">

            <p class="description">
                <?php esc_html_e('SpotLight falloff exponent.', 'arashtad-3d-annotation'); ?>
            </p>
        </div>

        <p>
            <label for="light_intensity"><strong><?php esc_html_e('Intensity', 'arashtad-3d-annotation'); ?></strong></label>
        </p>

        <input type="number" step="any" min="0" name="arashtad_light[intensity]" id="light_intensity" value="<?php echo esc_attr($light_config['intensity']); ?>" class="widefat">

    </div>

    <?php
}

add_action('save_post_arashtad_annotation', 'arashtad_annotation_save_light_config', 20);

function arashtad_annotation_save_light_config($post_id)
{
    $nonce = isset($_POST['arashtad_annotation_light_nonce']) ? sanitize_text_field(wp_unslash($_POST['arashtad_annotation_light_nonce'])) : '';

    if (!$nonce || !wp_verify_nonce($nonce, 'arashtad_annotation_light'))
        return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if (wp_is_post_revision($post_id))
        return;

    if (!current_user_can('edit_post', $post_id))
        return;

    $defaults = [
        'type' => 'DirectionalLight',
        'direction_x' => '0',
        'direction_y' => '-1',
        'direction_z' => '-2',
        'position_x' => '0',
        'position_y' => '5',
        'position_z' => '0',
        'intensity' => '3',
        'angle' => '1.0472',
        'exponent' => '2'
    ];

    $allowed_types = ['HemisphericLight', 'PointLight', 'DirectionalLight', 'SpotLight'];

    $existing = get_post_meta($post_id, '_arashtad_light_config', true);
    $config = wp_parse_args(is_array($existing) ? $existing : [], $defaults);

    if (isset($_POST['arashtad_light']) && is_array($_POST['arashtad_light'])) {
        $input = array_map('sanitize_text_field', wp_unslash($_POST['arashtad_light']));

        if (isset($input['type']) && in_array($input['type'], $allowed_types, true)) {
            $config['type'] = sanitize_text_field($input['type']);
        }

        $numeric_fields = [
            'direction_x',
            'direction_y',
            'direction_z',
            'position_x',
            'position_y',
            'position_z',
            'intensity',
            'angle',
            'exponent'
        ];

        foreach ($numeric_fields as $field) {
            if (isset($input[$field])) {
                $value = trim((string) $input[$field]);
                $config[$field] = is_numeric($value) ? (string) $value : $defaults[$field];
            }
        }
    }

    update_post_meta($post_id, '_arashtad_light_config', $config);
}

add_action('save_post_arashtad_annotation', 'arashtad_annotation_save_camera_config', 20);

function arashtad_annotation_save_camera_config($post_id)
{
    $nonce = isset($_POST['arashtad_annotation_camera_nonce']) ? sanitize_text_field(wp_unslash($_POST['arashtad_annotation_camera_nonce'])) : '';

    if (!$nonce || !wp_verify_nonce($nonce, 'arashtad_annotation_camera'))
        return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if (wp_is_post_revision($post_id))
        return;

    if (!current_user_can('edit_post', $post_id))
        return;

    $existing = get_post_meta($post_id, '_arashtad_camera_config', true);

    if (!is_array($existing)) {
        $existing = [];
    }

    $camera_config = $existing;

    $allowed_fields = [
        'camera_type',
        'alpha',
        'beta',
        'radius',
        'target_x',
        'target_y',
        'target_z',
        'position_x',
        'position_y',
        'position_z',
        'fov',
        'minZ',
        'maxZ',
        'use_auto_rotate',
        'idle_rotation_speed',
        'idle_rotation_wait_time',
        'idle_rotation_spinup_time'
    ];

    $numeric_fields = [
        'alpha',
        'beta',
        'radius',
        'fov',
        'minZ',
        'maxZ',
        'idle_rotation_speed',
        'idle_rotation_wait_time',
        'idle_rotation_spinup_time'
    ];

    $camera_config['use_auto_rotate'] = isset($_POST['arashtad_camera']['use_auto_rotate']) ? '1' : '0';

    if (isset($_POST['arashtad_camera']) && is_array($_POST['arashtad_camera'])) {
        foreach (array_map('sanitize_text_field', wp_unslash($_POST['arashtad_camera'])) as $key => $value) {
            if (in_array($key, $allowed_fields, true) && $key !== 'use_auto_rotate') {
                if (in_array($key, $numeric_fields, true)) {
                    $camera_config[$key] = is_numeric($value) ? (string) $value : '';
                } else {
                    $camera_config[$key] = sanitize_text_field($value);
                }
            }
        }
    }

    update_post_meta($post_id, '_arashtad_camera_config', $camera_config);
}

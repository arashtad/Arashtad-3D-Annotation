<?php

if (!defined('ABSPATH'))
    exit;

add_action('admin_post_arashtad_annotation_download_model', 'arashtad_annotation_download_model');
add_action('admin_post_nopriv_arashtad_annotation_download_model', 'arashtad_annotation_download_model');

add_shortcode('arashtad_annotation', 'arashtad_annotation_shortcode');

function arashtad_annotation_shortcode($atts)
{
    $atts = shortcode_atts(['id' => 0], $atts, 'arashtad_annotation');
    $post_id = absint($atts['id']);

    if (!$post_id || get_post_type($post_id) !== 'arashtad_annotation') {
        return '';
    }

    $models = arashtad_annotation_get_models($post_id);

    if (empty($models))
        return '';

    arashtad_annotation_register_frontend_assets();

    wp_enqueue_script('arashtad-3d-annotation-babylon');
    wp_enqueue_script('arashtad-3d-annotation-loaders');
    wp_enqueue_script('arashtad-3d-annotation-site');
    wp_enqueue_style('arashtad-3d-annotation-site');

    $canvas_id = 'arashtad-3d-' . $post_id . '-' . wp_unique_id();
    $light_id = $canvas_id . '-light';

    $camera_attributes = arashtad_annotation_get_camera_attributes($post_id);
    $styles = arashtad_annotation_canvas_styles($post_id);

    $lazy_enabled = get_post_meta($post_id, '_arashtad_lazy_enabled', true);
    $lazy_enabled = $lazy_enabled !== '' ? (bool) $lazy_enabled : true;

    $scrolllock_enabled = get_post_meta($post_id, '_arashtad_scrolllock_enabled', true);
    $scrolllock_enabled = $scrolllock_enabled !== '' ? (bool) $scrolllock_enabled : true;

    $poster_enabled = get_post_meta($post_id, '_arashtad_poster_enabled', true);
    $poster_image_id = get_post_meta($post_id, '_arashtad_poster_image_id', true);
    $poster_image_url = $poster_image_id ? wp_get_attachment_image_url($poster_image_id, 'full') : '';

    $clear_color = get_post_meta($post_id, '_arashtad_clear_color', true);

    if (empty($clear_color)) {
        $clear_color = '0.0588, 0.0784, 0.0706, 1';
    }

    $light_config = get_post_meta($post_id, '_arashtad_light_config', true);

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

    $download_enabled = get_post_meta($post_id, '_arashtad_download_enabled', true) === '1';

    ob_start();
    ?>

    <div class="canvas-wrapper<?php echo $scrolllock_enabled ? ' scrolllock' : ''; ?>" style="<?php echo esc_attr($styles); ?>">
        <canvas
            id="<?php echo esc_attr($canvas_id); ?>"
            class="arashtad-3d-canvas"
            clearColor="new BABYLON.Color4(<?php echo esc_attr($clear_color); ?>)"
            data-annotations="<?php echo esc_attr(wp_json_encode(arashtad_annotation_get_annotations_for_js($post_id))); ?>"
        >
            <arashtad-runtime
                <?php if ($lazy_enabled): ?>lazy<?php endif; ?>
                resize="engine.resize()"
            ></arashtad-runtime>

            <arashtad-light
                id="<?php echo esc_attr($light_id); ?>"
                type="<?php echo esc_attr($light_config['type']); ?>"
                <?php if (in_array($light_config['type'], ['HemisphericLight', 'DirectionalLight', 'SpotLight'], true)): ?>
                direction="new BABYLON.Vector3(<?php echo esc_attr($light_config['direction_x']); ?>, <?php echo esc_attr($light_config['direction_y']); ?>, <?php echo esc_attr($light_config['direction_z']); ?>)"
                <?php endif; ?>
                <?php if (in_array($light_config['type'], ['PointLight', 'SpotLight'], true)): ?>
                position="new BABYLON.Vector3(<?php echo esc_attr($light_config['position_x']); ?>, <?php echo esc_attr($light_config['position_y']); ?>, <?php echo esc_attr($light_config['position_z']); ?>)"
                <?php endif; ?>
                <?php if ($light_config['type'] === 'SpotLight'): ?>
                angle="<?php echo esc_attr($light_config['angle']); ?>"
                exponent="<?php echo esc_attr($light_config['exponent']); ?>"
                <?php endif; ?>
                intensity="<?php echo esc_attr($light_config['intensity']); ?>"
            ></arashtad-light>

            
            <arashtad-camera <?php echo $camera_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Contains server-generated, individually validated camera attributes. ?>></arashtad-camera>

            <?php
            foreach ($models as $index => $model):
                $model_id = !empty($model['id']) ? sanitize_key($model['id']) : 'model-' . ($index + 1);
                $model_id = $canvas_id . '-' . $model_id;
                $src = isset($model['src']) ? $model['src'] : '';

                if (!$src)
                    continue;
                ?>
                <arashtad-model
                    id="<?php echo esc_attr($model_id); ?>"
                    src="<?php echo esc_attr($src) ?>"
                    position="new BABYLON.Vector3(<?php echo esc_attr($model['position_x'] ?? '0'); ?>, <?php echo esc_attr($model['position_y'] ?? '0'); ?>, <?php echo esc_attr($model['position_z'] ?? '0'); ?>)"
                    rotation="new BABYLON.Vector3(<?php echo esc_attr($model['rotation_x'] ?? '0'); ?>, <?php echo esc_attr($model['rotation_y'] ?? '0'); ?>, <?php echo esc_attr($model['rotation_z'] ?? '0'); ?>)"
                    scaling="new BABYLON.Vector3(<?php echo esc_attr($model['scaling_x'] ?? '1'); ?>, <?php echo esc_attr($model['scaling_y'] ?? '1'); ?>, <?php echo esc_attr($model['scaling_z'] ?? '1'); ?>)"
                ></arashtad-model>
            <?php endforeach; ?>
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
    <?php if ($download_enabled): ?>
        <div class="arashtad-downloads">
            <?php foreach ($models as $index => $model): ?>
                <?php
                $src = isset($model['src']) ? trim((string) $model['src']) : '';

                if (!$src)
                    continue;

                $model_id = !empty($model['id']) ? sanitize_key($model['id']) : 'model-' . ($index + 1);

                $download_url = add_query_arg([
                    'action' => 'arashtad_annotation_download_model',
                    'scene' => $post_id,
                    'model' => $model_id,
                    '_wpnonce' => wp_create_nonce('arashtad_annotation_download_model_' . $post_id . '_' . $model_id)
                ], admin_url('admin-post.php'));

                $source_host = wp_parse_url($src, PHP_URL_HOST);
                $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
                $is_external = $source_host && $site_host && strtolower($source_host) !== strtolower($site_host);
                ?>
                <a
                    class="arashtad-download-button"
                    href="<?php echo esc_url($is_external ? $src : $download_url); ?>"
                    <?php echo $is_external ? 'download' : ''; ?>
                >
                    <?php esc_html_e('Download Model', 'arashtad-3d-annotation'); ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php
    return ob_get_clean();
}

function arashtad_annotation_download_model()
{
    $post_id = isset($_GET['scene']) ? absint($_GET['scene']) : 0;
    $model_id = isset($_GET['model']) ? sanitize_key(wp_unslash($_GET['model'])) : '';

    if (!$post_id || !$model_id) {
        status_header(400);
        exit;
    }

    if (get_post_type($post_id) !== 'arashtad_annotation') {
        status_header(404);
        exit;
    }

    if (get_post_meta($post_id, '_arashtad_download_enabled', true) !== '1') {
        status_header(403);
        exit;
    }

    $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

    if (!wp_verify_nonce($nonce, 'arashtad_annotation_download_model_' . $post_id . '_' . $model_id)) {
        status_header(403);
        exit;
    }

    $models = arashtad_annotation_get_models($post_id);
    $model = null;

    foreach ($models as $item) {
        $item_id = !empty($item['id']) ? sanitize_key($item['id']) : '';

        if ($item_id === $model_id) {
            $model = $item;
            break;
        }
    }

    if (!$model || empty($model['src'])) {
        status_header(404);
        exit;
    }

    $src = trim((string) $model['src']);
    $source_host = wp_parse_url($src, PHP_URL_HOST);
    $site_host = wp_parse_url(home_url(), PHP_URL_HOST);

    if ($source_host && $site_host && strtolower($source_host) !== strtolower($site_host)) {
        wp_safe_redirect($src);
        exit;
    }

    $file = arashtad_annotation_get_local_model_file($src);

    if (!$file || !is_file($file) || !is_readable($file)) {
        status_header(404);
        exit;
    }

    $filename = basename($file);
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $allowed_extensions = [
        'glb',
        'gltf',
        'obj',
        'stl',
        'ply',
        'splat',
        'spz',
        'sog',
        'babylon'
    ];

    if (!in_array($extension, $allowed_extensions, true)) {
        status_header(403);
        exit;
    }

    $mime_types = [
        'glb' => 'model/gltf-binary',
        'gltf' => 'model/gltf+json',
        'obj' => 'text/plain',
        'stl' => 'model/stl',
        'ply' => 'application/octet-stream',
        'splat' => 'application/octet-stream',
        'spz' => 'application/octet-stream',
        'sog' => 'application/octet-stream',
        'babylon' => 'application/json'
    ];

    $mime_type = $mime_types[$extension] ?? 'application/octet-stream';
    $file_size = filesize($file);

    if (ob_get_level()) {
        while (ob_get_level()) {
            ob_end_clean();
        }
    }

    nocache_headers();

    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . str_replace('"', '', $filename) . '"');
    header('Content-Length: ' . $file_size);
    header('Content-Transfer-Encoding: binary');
    header('X-Content-Type-Options: nosniff');

    global $wp_filesystem;

    if (!$wp_filesystem) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }

    echo $wp_filesystem->get_contents($file);  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Binary file must be sent unchanged.
    exit;
}

function arashtad_annotation_get_local_model_file($src)
{
    $attachment_id = attachment_url_to_postid($src);

    if ($attachment_id) {
        $file = get_attached_file($attachment_id);

        if ($file && is_file($file)) {
            return $file;
        }
    }

    $src_url = wp_parse_url($src);

    if (!$src_url || empty($src_url['path'])) {
        return false;
    }

    $site_url = wp_parse_url(home_url());

    if (!empty($src_url['host']) && !empty($site_url['host']) && strtolower($src_url['host']) !== strtolower($site_url['host'])) {
        return false;
    }

    $path = rawurldecode($src_url['path']);
    $path = '/' . ltrim($path, '/');

    $document_root = realpath(ABSPATH);

    if (!$document_root) {
        return false;
    }

    $file = realpath($document_root . $path);

    if (!$file || !is_file($file)) {
        return false;
    }

    $document_root = trailingslashit($document_root);
    $real_file = realpath($file);

    if (strpos($real_file, $document_root) !== 0) {
        return false;
    }

    return $real_file;
}

function arashtad_annotation_register_frontend_assets()
{
    if (wp_script_is('arashtad-3d-annotation-site', 'registered'))
        return;

    wp_register_script('arashtad-3d-annotation-babylon', ARASHTAD_3D_ANNOTATION_URL . 'lib/babylon/babylon.js', [], ARASHTAD_3D_ANNOTATION_VERSION, true);
    wp_register_script('arashtad-3d-annotation-loaders', ARASHTAD_3D_ANNOTATION_URL . 'lib/babylon/loaders/babylonjs.loaders.min.js', ['arashtad-3d-annotation-babylon'], ARASHTAD_3D_ANNOTATION_VERSION, true);
    wp_register_script('arashtad-3d-annotation-site', ARASHTAD_3D_ANNOTATION_URL . 'var/js/site.js', ['arashtad-3d-annotation-babylon', 'arashtad-3d-annotation-loaders'], ARASHTAD_3D_ANNOTATION_VERSION, true);
    wp_register_style('arashtad-3d-annotation-site', ARASHTAD_3D_ANNOTATION_URL . 'var/css/scene.min.css', [], ARASHTAD_3D_ANNOTATION_VERSION);
}

function arashtad_annotation_canvas_styles($id)
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

    $styles = get_post_meta($id, '_arashtad_annotation_canvas_styles', true);
    $styles = wp_parse_args(is_array($styles) ? $styles : [], $defaults);

    return sprintf(
        'position:%s;z-index:%s;top:%s;left:%s;bottom:%s;right:%s;transform:%s;margin:%s;padding:%s;width:%s;max-width:%s;height:%s;max-height:%s;aspect-ratio:%s;border:%s;border-radius:%s;overflow:%s;box-sizing:%s;box-shadow:%s;outline:%s;',
        esc_attr($styles['position']),
        esc_attr($styles['z_index']),
        esc_attr($styles['top']),
        esc_attr($styles['left']),
        esc_attr($styles['bottom']),
        esc_attr($styles['right']),
        esc_attr($styles['transform']),
        esc_attr($styles['margin']),
        esc_attr($styles['padding']),
        esc_attr($styles['width']),
        esc_attr($styles['max_width']),
        esc_attr($styles['height']),
        esc_attr($styles['max_height']),
        esc_attr($styles['aspect_ratio']),
        esc_attr($styles['border']),
        esc_attr($styles['border_radius']),
        esc_attr($styles['overflow']),
        esc_attr($styles['box_sizing']),
        esc_attr($styles['box_shadow']),
        esc_attr($styles['outline'])
    );
}

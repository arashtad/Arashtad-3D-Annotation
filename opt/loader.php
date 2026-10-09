<?php

if (!defined('ABSPATH'))
    exit;

function arashtad_annotation_defaults()
{
    return [
        'light' => [
            'type' => 'DirectionalLight',
            'direction' => [0, -1, -2],
            'intensity' => 3
        ],
        'camera' => [
            'type' => 'ArcRotateCamera',
            'alpha' => 1.57,
            'beta' => 1.2,
            'radius' => 12,
            'target' => [0, 0, 0],
            'position' => [0, 0, 3],
            'fov' => 0.4,
            'minZ' => 0.1,
            'maxZ' => 1000,
            'autoRotate' => true,
            'idleRotationSpeed' => 0,
            'idleRotationWaitTime' => 3000,
            'idleRotationSpinupTime' => 6000
        ],
        'model' => [
            'position' => [0, 0, 0],
            'rotation' => [0, 0, 0],
            'scaling' => [1, 1, 1],
            'source' => 'media',
            'url' => ''
        ]
    ];
}

function arashtad_annotation_get_config($post_id)
{
    $config = get_post_meta($post_id, '_arashtad_annotation_config', true);
    $config = is_array($config) ? $config : [];

    $config = array_replace_recursive(arashtad_annotation_defaults(), $config);

    if (!$config['model']['url'])
        $config['model']['url'] = get_post_meta($post_id, '_arashtad_model', true);

    $legacy_source = get_post_meta($post_id, '_arashtad_model_source', true);

    if ($legacy_source)
        $config['model']['source'] = $legacy_source;

    return $config;
}

function arashtad_annotation_save_config($post_id, $input)
{
    $defaults = arashtad_annotation_defaults();
    $input = is_array($input) ? $input : [];

    $config = array_replace_recursive($defaults, $input);

    $config['light']['type'] = sanitize_key($config['light']['type']);
    $config['light']['direction'] = array_map('floatval', array_values($config['light']['direction']));
    $config['light']['intensity'] = (float) $config['light']['intensity'];

    $config['camera']['type'] = sanitize_key($config['camera']['type']);
    $config['camera']['alpha'] = (float) $config['camera']['alpha'];
    $config['camera']['beta'] = (float) $config['camera']['beta'];
    $config['camera']['radius'] = (float) $config['camera']['radius'];
    $config['camera']['target'] = array_map('floatval', array_values($config['camera']['target']));
    $config['camera']['position'] = array_map('floatval', array_values($config['camera']['position']));
    $config['camera']['fov'] = (float) $config['camera']['fov'];
    $config['camera']['minZ'] = (float) $config['camera']['minZ'];
    $config['camera']['maxZ'] = (float) $config['camera']['maxZ'];
    $config['camera']['autoRotate'] = !empty($config['camera']['autoRotate']);
    $config['camera']['idleRotationSpeed'] = (float) $config['camera']['idleRotationSpeed'];
    $config['camera']['idleRotationWaitTime'] = (int) $config['camera']['idleRotationWaitTime'];
    $config['camera']['idleRotationSpinupTime'] = (int) $config['camera']['idleRotationSpinupTime'];

    $config['model']['position'] = array_map('floatval', array_values($config['model']['position']));
    $config['model']['rotation'] = array_map('floatval', array_values($config['model']['rotation']));
    $config['model']['scaling'] = array_map('floatval', array_values($config['model']['scaling']));
    $config['model']['source'] = sanitize_key($config['model']['source']);
    $config['model']['url'] = esc_url_raw($config['model']['url']);

    if (!in_array($config['model']['source'], ['media', 'internal', 'external'], true))
        $config['model']['source'] = 'media';

    update_post_meta($post_id, '_arashtad_annotation_config', $config);

    update_post_meta($post_id, '_arashtad_model_source', $config['model']['source']);
    update_post_meta($post_id, '_arashtad_model', $config['model']['url']);
}

function arashtad_annotation_vector($vector)
{
    return 'new BABYLON.Vector3(' . implode(', ', array_map('floatval', $vector)) . ')';
}

function arashtad_annotation_get_camera_attributes($post_id)
{
    $camera_config = get_post_meta($post_id, '_arashtad_camera_config', true);

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

    $config = wp_parse_args(is_array($camera_config) ? $camera_config : [], $defaults);

    $attributes = [];
    $attributes[] = 'id="camera"';
    $attributes[] = 'type="' . esc_attr($config['camera_type']) . '"';

    // ArcRotateCamera specific attributes
    if ($config['camera_type'] === 'ArcRotateCamera') {
        $attributes[] = 'alpha="' . esc_attr($config['alpha']) . '"';
        $attributes[] = 'beta="' . esc_attr($config['beta']) . '"';
        $attributes[] = 'radius="' . esc_attr($config['radius']) . '"';
        $attributes[] = 'target="new BABYLON.Vector3('
            . esc_attr($config['target_x']) . ', '
            . esc_attr($config['target_y']) . ', '
            . esc_attr($config['target_z']) . ')"';
    }

    // Common camera attributes
    $attributes[] = 'position="new BABYLON.Vector3('
        . esc_attr($config['position_x'] ?: '0') . ', '
        . esc_attr($config['position_y'] ?: '0') . ', '
        . esc_attr($config['position_z'] ?: '0') . ')"';

    $attributes[] = 'fov="' . esc_attr($config['fov']) . '"';
    $attributes[] = 'minZ="' . esc_attr($config['minZ']) . '"';
    $attributes[] = 'maxZ="' . esc_attr($config['maxZ']) . '"';

    // Auto rotation attributes
    if ($config['use_auto_rotate'] === '1') {
        $attributes[] = 'useAutoRotationBehavior="true"';
        $attributes[] = 'autoRotationBehavior.idleRotationSpeed="' . esc_attr($config['idle_rotation_speed'] ?: '0') . '"';
        $attributes[] = 'autoRotationBehavior.idleRotationWaitTime="' . esc_attr($config['idle_rotation_wait_time'] ?: '3000') . '"';
        $attributes[] = 'autoRotationBehavior.idleRotationSpinupTime="' . esc_attr($config['idle_rotation_spinup_time'] ?: '6000') . '"';
    }

    return implode(' ', $attributes);
}

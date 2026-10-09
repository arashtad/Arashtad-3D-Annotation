import { ready, loadModel } from '../../lib/arashtad/arashtad-3d-runtime.1.0.0.min.js';
import { enableInteractions } from '../../lib/arashtad/arashtad-3d-runtime-plugins.1.0.0.min.js';

const { __ } = wp.i18n;

const canvas = document.getElementById('arashtad-3d-annotation-admin-preview');
const modelInput = document.getElementById('arashtad_model');
const mediaBox = document.querySelector('.arashtad-model-media');
const selectButton = document.getElementById('arashtad_select_model');
const clearButton = document.getElementById('arashtad_clear_model');
const preloader = document.querySelector('.preloader');
const bar = document.querySelector('.loading-bar');
const posterEnabled = document.getElementById('arashtad_poster_enabled');
const posterSettings = document.getElementById('arashtad-poster-settings');
const posterImageId = document.getElementById('arashtad_poster_image_id');
const posterPreview = document.getElementById('arashtad-poster-preview');
const selectPosterButton = document.getElementById('arashtad-select-poster');
const clearPosterButton = document.getElementById('arashtad-clear-poster');

let posterFrame = null;
let scene = null;
let model = null;
let currentCamera = null;
let canvasWrapper = null;

let annotationInteractions = null;
let annotationLayer = null;
let annotationLines = null;
let annotations = [];
let selectedAnnotation = null;
let editingAnnotation = null;
let annotationCanvasRect = null;
let annotationSvgRect = null;
let lastCanvasDocRect = null;

function setCanvasState(loaded) {
    canvas.classList.toggle('loaded', loaded);
    canvas.classList.toggle('loading', !loaded);
}

function setPreloader(hidden) {
    preloader?.classList.toggle('hidden', hidden);
    bar?.classList.toggle('hidden', hidden);
}

function getSource() {
    return modelInput.value.trim();
}

function updateWrapperStyles() {
    if (!canvasWrapper) {
        canvasWrapper = canvas.closest('.canvas-wrapper');
    }
    if (!canvasWrapper) return;
    
    const styleFields = document.querySelectorAll('[name^="arashtad_styles["]');
    const styles = {};
    styleFields.forEach(field => {
        const key = field.name.match(/arashtad_styles\[([^\]]+)\]/);
        if (key && key[1]) {
            styles[key[1]] = field.value;
        }
    });
    
    const styleMap = {
        'position': styles.position || 'relative',
        'z_index': styles.z_index || '1',
        'top': styles.top || 'auto',
        'left': styles.left || 'auto',
        'bottom': styles.bottom || 'auto',
        'right': styles.right || 'auto',
        'transform': styles.transform || 'none',
        'margin': styles.margin || '0',
        'padding': styles.padding || '0',
        'width': styles.width || '100%',
        'max_width': styles.max_width || '100%',
        'height': styles.height || 'auto',
        'max_height': styles.max_height || '100%',
        'aspect_ratio': styles.aspect_ratio || '16 / 9',
        'border': styles.border || '2px solid #333333',
        'border_radius': styles.border_radius || '10px',
        'overflow': styles.overflow || 'hidden',
        'box_sizing': styles.box_sizing || 'border-box',
        'box_shadow': styles.box_shadow || '0 0 20px rgba(0, 0, 0, .4)',
        'outline': styles.outline || 'none'
    };
    
    Object.keys(styleMap).forEach(key => {
        const cssKey = key.replace(/_/g, '-');
        const value = styleMap[key];
        if (value && value !== '') {
            canvasWrapper.style[cssKey] = value;
        } else {
            canvasWrapper.style[cssKey] = '';
        }
    });

    window.dispatchEvent(new Event('resize'));
}

async function updateModelInScene() {
    if (!scene) return;

    const modelNode = scene.getTransformNodeByName?.('arashtad-3d-annotation-admin-model-root');

    if (!modelNode) return;

    const getNumber = (name, fallback) => {
        const value = parseFloat(document.querySelector(`[name="arashtad_model_settings[${name}]"]`)?.value);
        return Number.isFinite(value) ? value : fallback;
    };

    modelNode.position.x = getNumber('position_x', 0);
    modelNode.position.y = getNumber('position_y', 0);
    modelNode.position.z = getNumber('position_z', 0);

    modelNode.rotation.x = getNumber('rotation_x', 0);
    modelNode.rotation.y = getNumber('rotation_y', 0);
    modelNode.rotation.z = getNumber('rotation_z', 0);

    modelNode.scaling.x = getNumber('scaling_x', 1);
    modelNode.scaling.y = getNumber('scaling_y', 1);
    modelNode.scaling.z = getNumber('scaling_z', 1);
}

function addModelSettingsListeners() {
    const modelFields = document.querySelectorAll(
        '[name^="arashtad_model_settings[position_"], ' +
        '[name^="arashtad_model_settings[rotation_"], ' +
        '[name^="arashtad_model_settings[scaling_"]'
    );

    modelFields.forEach(field => {
        field.addEventListener('change', async () => {
            try {
                await updateModelInScene();
            } catch (error) {
                console.error('Failed to update model:', error);
            }
        });

        field.addEventListener('input', debounce(async () => {
            try {
                await updateModelInScene();
                setCanvasState(true);
                setPreloader(true);
            } catch (error) {
                console.error('Failed to update model:', error);
            }
        }, 100));
    });
}

async function updateClearColor() {
    if (!scene) return;
    const colorInput = document.querySelector('[name="arashtad_clear_color"]');
    if (!colorInput) return;
    const colorStr = colorInput.value.trim();
    if (!colorStr) return;
    const parts = colorStr.split(',').map(s => parseFloat(s.trim()));
    if (parts.length !== 4) return;
    const [r, g, b, a] = parts;
    if (scene.clearColor) {
        scene.clearColor.r = r;
        scene.clearColor.g = g;
        scene.clearColor.b = b;
        scene.clearColor.a = a;
    }
}

function getLightType() {
    return document.getElementById('light_type')?.value || 'DirectionalLight';
}

function getLightNumber(id, fallback) {
    const value = parseFloat(document.getElementById(id)?.value);
    return Number.isFinite(value) ? value : fallback;
}

function getLightConfig() {
    return {
        type: getLightType(),
        directionX: getLightNumber('light_direction_x', 0),
        directionY: getLightNumber('light_direction_y', -1),
        directionZ: getLightNumber('light_direction_z', -2),
        positionX: getLightNumber('light_position_x', 0),
        positionY: getLightNumber('light_position_y', 5),
        positionZ: getLightNumber('light_position_z', 0),
        intensity: getLightNumber('light_intensity', 3),
        angle: getLightNumber('light_angle', 1.0472),
        exponent: getLightNumber('light_exponent', 2)
    };
}

function getBabylonLight() {
    if (!scene) return null;
    return scene.getLightByName?.('arashtad-3d-annotation-light') || scene.lights?.find(light => light.id === 'arashtad-3d-annotation-light' || light.name === 'arashtad-3d-annotation-light') || null;
}

function applyLightToScene() {
    const light = getBabylonLight();
    if (!light) return false;
    const config = getLightConfig();
    light.intensity = config.intensity;
    if (['HemisphericLight', 'DirectionalLight', 'SpotLight'].includes(config.type)) {
        light.direction = new BABYLON.Vector3(config.directionX, config.directionY, config.directionZ);
    }
    if (['PointLight', 'SpotLight'].includes(config.type)) {
        light.position = new BABYLON.Vector3(config.positionX, config.positionY, config.positionZ);
    }
    if (light.angle !== undefined && config.type === 'SpotLight') {
        light.angle = config.angle;
    }
    if (light.exponent !== undefined && config.type === 'SpotLight') {
        light.exponent = config.exponent;
    }
    return true;
}

async function rebuildLight() {
    if (!scene) return null;
    const config = getLightConfig();
    const oldLight = getBabylonLight();
    if (oldLight) oldLight.dispose();
    let light;
    const name = 'arashtad-light';
    if (config.type === 'HemisphericLight') {
        light = new BABYLON.HemisphericLight(name, new BABYLON.Vector3(config.directionX, config.directionY, config.directionZ), scene);
    } else if (config.type === 'DirectionalLight') {
        light = new BABYLON.DirectionalLight(name, new BABYLON.Vector3(config.directionX, config.directionY, config.directionZ), scene);
    } else if (config.type === 'PointLight') {
        light = new BABYLON.PointLight(name, new BABYLON.Vector3(config.positionX, config.positionY, config.positionZ), scene);
    } else if (config.type === 'SpotLight') {
        light = new BABYLON.SpotLight(name, new BABYLON.Vector3(config.positionX, config.positionY, config.positionZ), new BABYLON.Vector3(config.directionX, config.directionY, config.directionZ), config.angle, config.exponent, scene);
    } else {
        return null;
    }
    light.intensity = config.intensity;
    const oldLightElement = canvas.querySelector('light#arashtad-light');
    if (oldLightElement) oldLightElement.remove();
    const lightElement = document.createElement('arashtad-light');
    lightElement.id = 'arashtad-light';
    lightElement.setAttribute('type', config.type);
    if (['HemisphericLight', 'DirectionalLight', 'SpotLight'].includes(config.type)) {
        lightElement.setAttribute('direction', `new BABYLON.Vector3(${config.directionX}, ${config.directionY}, ${config.directionZ})`);
    }
    if (['PointLight', 'SpotLight'].includes(config.type)) {
        lightElement.setAttribute('position', `new BABYLON.Vector3(${config.positionX}, ${config.positionY}, ${config.positionZ})`);
    }
    if (config.type === 'SpotLight') {
        lightElement.setAttribute('angle', config.angle);
        lightElement.setAttribute('exponent', config.exponent);
    }
    lightElement.setAttribute('intensity', config.intensity);
    const runtime = canvas.querySelector('arashtad-runtime');
    if (runtime) canvas.insertBefore(lightElement, runtime.nextSibling);
    else canvas.appendChild(lightElement);
    scene.render();
    return light;
}

async function updateLightInScene() {
    if (!scene) return;
    const light = getBabylonLight();
    const type = getLightType();
    if (light && light.getClassName?.() === type) {
        applyLightToScene();
        return;
    }
    await rebuildLight();
}

function addLightChangeListeners() {
    const lightFields = document.querySelectorAll('.arashtad-3d-annotation-light-settings input, .arashtad-3d-annotation-light-settings select');
    lightFields.forEach(field => {
        field.addEventListener('change', async () => {
            try {
                await updateLightInScene();
                toggleLightFields();
                setCanvasState(true);
                setPreloader(true);
            } catch (error) {
                console.error('Failed to update light:', error);
            }
        });
        field.addEventListener('input', debounce(async () => {
            try {
                await updateLightInScene();
                setCanvasState(true);
                setPreloader(true);
            } catch (error) {
                console.error('Failed to update light:', error);
            }
        }, 200));
    });
}

function toggleLightFields() {
    const type = document.getElementById('light_type')?.value || 'DirectionalLight';
    document.querySelectorAll('.light-direction-fields').forEach(el => {
        el.style.display = ['HemisphericLight', 'DirectionalLight', 'SpotLight'].includes(type) ? '' : 'none';
    });
    document.querySelectorAll('.light-position-fields').forEach(el => {
        el.style.display = ['PointLight', 'SpotLight'].includes(type) ? '' : 'none';
    });
    document.querySelectorAll('.light-angle-fields, .light-exponent-fields').forEach(el => {
        el.style.display = type === 'SpotLight' ? '' : 'none';
    });
}

async function reloadScene() {
    if (!canvas) return;
    try {
        const url = getSource();
        const runtime = canvas.querySelector('arashtad-runtime');
        if (runtime) {
            const existing = runtime.querySelector('arashtad-model');
            if (existing) existing.remove();
            if (url) {
                const modelElement = document.createElement('arashtad-model');
                modelElement.id = 'arashtad-3d-annotation-admin-model';
                modelElement.setAttribute('src', url);
                runtime.appendChild(modelElement);
            }
        }
        await initScene();
        setCanvasState(true);
        setPreloader(true);
    } catch (error) {
        console.error('Arashtad 3D:', error);
        setCanvasState(true);
        setPreloader(true);
    }
}

function getCameraNumber(id, fallback) {
    const value = parseFloat(document.getElementById(id)?.value);
    return Number.isFinite(value) ? value : fallback;
}

async function updateCameraInScene() {
    if (!scene || !currentCamera) return;

    const cameraType = document.getElementById('camera_type')?.value || 'ArcRotateCamera';

    const target = new BABYLON.Vector3(
        getCameraNumber('target_x', 0),
        getCameraNumber('target_y', 0),
        getCameraNumber('target_z', 0)
    );

    currentCamera.fov = getCameraNumber('fov', 0.4);
    currentCamera.minZ = getCameraNumber('minZ', 0.1);
    currentCamera.maxZ = getCameraNumber('maxZ', 1000);

    if (cameraType === 'ArcRotateCamera') {
        const position = new BABYLON.Vector3(
            getCameraNumber('position_x', currentCamera.position.x),
            getCameraNumber('position_y', currentCamera.position.y),
            getCameraNumber('position_z', currentCamera.position.z)
        );
    
        const offset = position.subtract(target);
        const radius = offset.length();
    
        if (radius > 0) {
            currentCamera.radius = radius;
            currentCamera.alpha = Math.atan2(offset.z, offset.x);
            currentCamera.beta = Math.acos(
                Math.min(1, Math.max(-1, offset.y / radius))
            );
        }
    
        currentCamera.setTarget(target);
    
        currentCamera.fov = getCameraNumber('fov', 0.4);
        currentCamera.minZ = getCameraNumber('minZ', 0.1);
        currentCamera.maxZ = getCameraNumber('maxZ', 1000);
    
        const enabled = document.getElementById('use_auto_rotate')?.checked || false;
        currentCamera.useAutoRotationBehavior = enabled;
    
        if (enabled && currentCamera.autoRotationBehavior) {
            currentCamera.autoRotationBehavior.idleRotationSpeed =
                getCameraNumber('idle_rotation_speed', 0.1);
    
            currentCamera.autoRotationBehavior.idleRotationWaitTime =
                getCameraNumber('idle_rotation_wait_time', 3000);
    
            currentCamera.autoRotationBehavior.idleRotationSpinupTime =
                getCameraNumber('idle_rotation_spinup_time', 6000);
        }
    } else {
        currentCamera.position.x = getCameraNumber('position_x', 0);
        currentCamera.position.y = getCameraNumber('position_y', 0);
        currentCamera.position.z = getCameraNumber('position_z', 3);
    
        currentCamera.setTarget(target);
    
        currentCamera.fov = getCameraNumber('fov', 0.4);
        currentCamera.minZ = getCameraNumber('minZ', 0.1);
        currentCamera.maxZ = getCameraNumber('maxZ', 1000);
    }

    scene.activeCamera = currentCamera;
}

async function rebuildCamera() {
    if (!scene || !canvas) return;

    const cameraType = document.getElementById('camera_type')?.value || 'ArcRotateCamera';

    if (currentCamera) {
        currentCamera.detachControl?.();
        currentCamera.dispose();
        currentCamera = null;
    }

    const target = new BABYLON.Vector3(
        getCameraNumber('target_x', 0),
        getCameraNumber('target_y', 0),
        getCameraNumber('target_z', 0)
    );

    let camera;

    if (cameraType === 'ArcRotateCamera') {
        camera = new BABYLON.ArcRotateCamera(
            `${canvas.id}-camera`,
            getCameraNumber('alpha', 1.57),
            getCameraNumber('beta', 1.2),
            getCameraNumber('radius', 12),
            target,
            scene
        );
    } else {
        const position = new BABYLON.Vector3(
            getCameraNumber('position_x', 0),
            getCameraNumber('position_y', 0),
            getCameraNumber('position_z', 3)
        );

        if (cameraType === 'UniversalCamera') {
            camera = new BABYLON.UniversalCamera(
                `${canvas.id}-camera`,
                position,
                scene
            );
        } else {
            camera = new BABYLON.FreeCamera(
                `${canvas.id}-camera`,
                position,
                scene
            );
        }

        camera.setTarget(target);
    }

    currentCamera = camera;
    scene.activeCamera = camera;

    await updateCameraInScene();

    currentCamera.attachControl(canvas, true);

    return currentCamera;
}

async function initScene() {
    if (scene) {
        return scene;
    }

    let camera = canvas.querySelector('arashtad-camera');

    if (!camera) {
        camera = document.createElement('arashtad-camera');
        camera.id = `${canvas.id}-camera`;
        camera.setAttribute(
            'type',
            document.getElementById('camera_type')?.value || 'ArcRotateCamera'
        );
        canvas.appendChild(camera);
    }

    scene = await ready(canvas);

    await updateClearColor();

    currentCamera = scene.activeCamera;

    if (currentCamera) {
        await updateCameraInScene();
        currentCamera.attachControl(canvas, true);
    }

    return scene;
}

function toggleCameraFields() {
    const type = document.getElementById('camera_type')?.value || '';

    document.querySelectorAll('.arcrotate-field:not(.auto-rotate-field)').forEach(el => {
        el.style.display = type === 'ArcRotateCamera' ? '' : 'none';
    });
}

function toggleAutoRotateFields() {
    const type = document.getElementById('camera_type')?.value || '';
    const checked = document.getElementById('use_auto_rotate')?.checked || false;

    document.querySelectorAll('.auto-rotate-field').forEach(el => {
        el.style.display = type === 'ArcRotateCamera' && checked ? '' : 'none';
    });
}

function addCameraChangeListeners() {
    const cameraFields = document.querySelectorAll(
        '.arashtad-camera-settings input, .arashtad-camera-settings select'
    );

    cameraFields.forEach(field => {
        field.addEventListener('change', async () => {
            try {
                if (field.id === 'camera_type') {
                    await rebuildCamera();
                } else {
                    await updateCameraInScene();
                }

                toggleCameraFields();
                toggleAutoRotateFields();
            } catch (error) {
                console.error('Failed to update camera:', error);
            }
        });

        field.addEventListener('input', debounce(async () => {
            try {
                if (field.id === 'camera_type') {
                    await rebuildCamera();
                } else {
                    await updateCameraInScene();
                }
            } catch (error) {
                console.error('Failed to update camera:', error);
            }
        }, 100));
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function initAnnotations() {
    if (!scene || !canvas || annotationInteractions) return;

    annotationInteractions = enableInteractions(scene, canvas);

    annotationLayer = document.querySelector('.arashtad-annotation-layer');
    annotationLines = annotationLayer?.querySelector('.arashtad-annotation-lines');

    if (!annotationLayer || !annotationLines) {
        console.error('Annotation overlay not found');
        return;
    }

    canvas.addEventListener('interaction:pick', event => {
        const { mesh, point } = event.detail || {};

        if (!mesh || !point)
            return;

        if (!ARASHTAD?.can_edit) return;

        const annotationPoint = annotationInteractions.createPoint(point, {
            name: `annotation-point-${Date.now()}`,
            diameter: 0.04
        });

        attachAnnotationPointToModel(annotationPoint);

        createAnnotationCard(annotationPoint);
    });

    scene.onBeforeRenderObservable.add(() => {
        checkAnnotationLayoutShift();
        updateAnnotationPositions();
    });

    const refreshAnnotationRects = () => {
        annotationCanvasRect = canvas.getBoundingClientRect();
        annotationSvgRect = annotationLines.getBoundingClientRect();
        updateAnnotationPositions();
    };

    window.addEventListener('resize', refreshAnnotationRects);
    window.addEventListener('scroll', refreshAnnotationRects, { passive: true });

    document.addEventListener('keydown', event => {
        if (event.key !== 'Delete' && event.key !== 'Backspace') return;
        if (!selectedAnnotation) return;
        if (!ARASHTAD?.can_edit) return; // NEW

        const activeTag = document.activeElement?.tagName;
        if (activeTag === 'INPUT' || activeTag === 'TEXTAREA') return;

        event.preventDefault();
        deleteAnnotation(selectedAnnotation);
    });

    loadSavedAnnotations();
}

function buildAnnotation(bPoint, data, serverId) {
    const line = document.createElementNS(
        'http://www.w3.org/2000/svg',
        'line'
    );

    line.classList.add('arashtad-annotation-line');
    line.setAttribute('vector-effect', 'non-scaling-stroke');

    annotationLines.appendChild(line);

    const card = document.createElement('div');
    card.className = 'arashtad-annotation-card';

    annotationLayer.appendChild(card);

    const annotation = {
        id: serverId || `annotation-${Date.now()}-${Math.random().toString(36).slice(2)}`,
        point: bPoint,
        line,
        card,
        data: {
            title: data?.title || '3D Annotation',
            text: data?.text || 'This annotation was created by clicking the model surface.'
        },
        saved: Boolean(serverId)
    };

    renderAnnotationCard(annotation);

    card.addEventListener('click', event => {
        event.stopPropagation();
        selectAnnotation(annotation);
    });

    card.addEventListener('dblclick', event => {
        event.stopPropagation();
        if (!ARASHTAD?.can_edit) return;
        enterEditMode(annotation);
    });

    annotations.push(annotation);

    return annotation;
}

function createAnnotationCard(point) {
    const material = new BABYLON.StandardMaterial(
        `annotation-material-${point.uniqueId}`,
        scene
    );

    material.diffuseColor = new BABYLON.Color3(1, 0.8, 0);
    material.emissiveColor = new BABYLON.Color3(1, 0.4, 0);

    point.material = material;

    const annotation = buildAnnotation(point, null, null);

    persistAnnotationToServer(annotation);
}

function updateAnnotationPositions() {
    if (!scene || !canvas || !annotations.length) return;

    const engine = scene.getEngine();
    const camera = scene.activeCamera;
    if (!camera) return;

    if (!annotationCanvasRect) annotationCanvasRect = canvas.getBoundingClientRect();
    if (!annotationSvgRect) annotationSvgRect = annotationLines.getBoundingClientRect();

    const canvasRect = annotationCanvasRect;
    const svgRect = annotationSvgRect;

    const renderWidth = engine.getRenderWidth();
    const renderHeight = engine.getRenderHeight();

    if (!renderWidth || !renderHeight || !canvasRect.width || !canvasRect.height) return;

    const viewport = camera.viewport.toGlobal(renderWidth, renderHeight);
    const transform = scene.getTransformMatrix();
    const centerX = canvasRect.left + canvasRect.width / 2;

    for (const annotation of annotations) {
        if (annotation.point.isDisposed()) continue;

        const world = annotation.point.getAbsolutePosition();
        const projected = BABYLON.Vector3.Project(world, BABYLON.Matrix.Identity(), transform, viewport);
        if (!Number.isFinite(projected.x) || !Number.isFinite(projected.y)) continue;

        const x = canvasRect.left + projected.x / renderWidth * canvasRect.width;
        const y = canvasRect.top + projected.y / renderHeight * canvasRect.height;

        // Point in the left half -> card goes further left (same side).
        // Point in the right half -> card goes further right (same side).
        const pointOnLeft = x < centerX;
        const offsetX = 45;
        const cardWidth = annotation.card.offsetWidth;
        const cardHeight = annotation.card.offsetHeight;

        let cardX = pointOnLeft ? (x - cardWidth - offsetX) : (x + offsetX);
        const offsetY = 100;
        const cardY = y - cardHeight / 2 - offsetY;

        // Clamp so the card never crosses the page's left or right edge.
        const pageWidth = document.documentElement.clientWidth;
        const maxCardX = pageWidth - cardWidth;
        cardX = Math.max(0, Math.min(cardX, maxCardX));

        annotation.card.style.left = `${cardX - svgRect.left}px`;
        annotation.card.style.top = `${cardY - svgRect.top}px`;

        const anchorX = pointOnLeft ? (cardX + cardWidth) : cardX;
        const anchorY = cardY + cardHeight / 2;

        annotation.line.setAttribute('x1', `${x - svgRect.left}`);
        annotation.line.setAttribute('y1', `${y - svgRect.top}`);
        annotation.line.setAttribute('x2', `${anchorX - svgRect.left}`);
        annotation.line.setAttribute('y2', `${anchorY - svgRect.top}`);
    }
}

function loadSavedAnnotations() {
    if (!Array.isArray(ARASHTAD?.annotations) || !ARASHTAD.annotations.length) return;
    if (!scene || !annotationInteractions) return;

    ARASHTAD.annotations.forEach(saved => {
        const bPoint = annotationInteractions.createPoint(
            new BABYLON.Vector3(0, 0, 0),
            {
                name: `annotation-point-${saved.id}`,
                diameter: 0.04
            }
        );

        // Parent BEFORE applying position so the saved coordinates are
        // treated as local (relative to the model), not world-space.
        attachAnnotationPointToModel(bPoint);
        bPoint.position.set(saved.position.x, saved.position.y, saved.position.z);

        const material = new BABYLON.StandardMaterial(`annotation-material-${bPoint.uniqueId}`, scene);
        material.diffuseColor = new BABYLON.Color3(1, 0.8, 0);
        material.emissiveColor = new BABYLON.Color3(1, 0.4, 0);
        bPoint.material = material;

        buildAnnotation(bPoint, { title: saved.title, text: saved.text }, saved.id);
    });
}

function checkAnnotationLayoutShift() {
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();

    const changed = !lastCanvasDocRect ||
        Math.abs(rect.left - lastCanvasDocRect.left) > 0.5 ||
        Math.abs(rect.top - lastCanvasDocRect.top) > 0.5 ||
        Math.abs(rect.width - lastCanvasDocRect.width) > 0.5 ||
        Math.abs(rect.height - lastCanvasDocRect.height) > 0.5;

    if (!changed) return;

    lastCanvasDocRect = {
        left: rect.left,
        top: rect.top,
        width: rect.width,
        height: rect.height,
    };

    annotationCanvasRect = rect;
    annotationSvgRect = annotationLines.getBoundingClientRect();
}

function getModelRootNode() {
    return scene?.getTransformNodeByName?.('arashtad-3d-annotation-admin-model-root') || null;
}

function attachAnnotationPointToModel(point) {
    const modelRoot = getModelRootNode();

    if (modelRoot && typeof point.setParent === 'function') {
        point.setParent(modelRoot);
    }
}

function selectAnnotation(annotation) {
    if (selectedAnnotation === annotation) return;

    if (selectedAnnotation) {
        selectedAnnotation.card.classList.remove('selected');
    }

    selectedAnnotation = annotation;
    selectedAnnotation.card.classList.add('selected');
}

function deselectAnnotation() {
    if (!selectedAnnotation) return;
    selectedAnnotation.card.classList.remove('selected');
    selectedAnnotation = null;
}

function deleteAnnotation(annotation) {
    const index = annotations.indexOf(annotation);
    if (index === -1) return;

    if (!annotation.point.isDisposed()) {
        annotation.point.material?.dispose();
        annotation.point.dispose();
    }

    annotation.card.remove();
    annotation.line.remove();

    annotations.splice(index, 1);

    if (selectedAnnotation === annotation) {
        selectedAnnotation = null;
    }

    if (!annotation.saved) return;

    fetch(ARASHTAD.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'arashtad_delete_annotation',
            nonce: ARASHTAD.nonce,
            post_id: ARASHTAD.post_id,
            annotation_id: annotation.id
        })
    })
        .then(response => response.json())
        .then(result => {
            if (!result?.success) {
                console.error('Failed to delete annotation:', result);
            }
        })
        .catch(error => console.error('Failed to delete annotation:', error));
}

function renderAnnotationCard(annotation) {
    const { card, data } = annotation;
    card.classList.remove('editing');
    card.innerHTML = `
        <strong class="arashtad-annotation-title"></strong>
        <p class="arashtad-annotation-text"></p>
    `;
    // textContent (not innerHTML) so title/text can never inject markup
    card.querySelector('.arashtad-annotation-title').textContent = data.title;
    card.querySelector('.arashtad-annotation-text').textContent = data.text;
}

function enterEditMode(annotation) {
    if (editingAnnotation && editingAnnotation !== annotation) {
        cancelEdit(editingAnnotation);
    }

    editingAnnotation = annotation;

    const { card, data } = annotation;
    card.classList.add('editing');
    card.innerHTML = `
        <div class="arashtad-annotation-form">
            <label>
                <span>Title</span>
                <input type="text" class="arashtad-annotation-input-title" />
            </label>
            <label>
                <span>Text</span>
                <textarea class="arashtad-annotation-input-text" rows="3"></textarea>
            </label>
            <div class="arashtad-annotation-form-actions">
                <button type="button" class="arashtad-annotation-save">Save</button>
                <button type="button" class="arashtad-annotation-cancel">Cancel</button>
            </div>
        </div>
    `;

    const titleInput = card.querySelector('.arashtad-annotation-input-title');
    const textInput = card.querySelector('.arashtad-annotation-input-text');
    const formWrapper = card.querySelector('.arashtad-annotation-form');
    const saveButton = card.querySelector('.arashtad-annotation-save');
    const cancelButton = card.querySelector('.arashtad-annotation-cancel');

    titleInput.value = data.title;
    textInput.value = data.text;

    // Stop clicks/dblclicks inside the edit UI from bubbling up to the card's
    // own click/dblclick handlers, or to the document-level deselect handler.
    formWrapper.addEventListener('click', event => event.stopPropagation());
    formWrapper.addEventListener('dblclick', event => event.stopPropagation());

    saveButton.addEventListener('click', () => {
        saveEdit(annotation, titleInput.value.trim(), textInput.value.trim());
    });

    cancelButton.addEventListener('click', () => {
        cancelEdit(annotation);
    });

    // Optional: let Enter key in the title field trigger Save, like a form would,
    // without needing an actual <form> element.
    titleInput.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            saveEdit(annotation, titleInput.value.trim(), textInput.value.trim());
        }
    });

    titleInput.focus();
    titleInput.select();
}

function saveEdit(annotation, title, text) {
    annotation.data.title = title || 'Untitled';
    annotation.data.text = text;

    editingAnnotation = null;
    renderAnnotationCard(annotation);

    persistAnnotationToServer(annotation);
}

function persistAnnotationToServer(annotation) {
    if (!ARASHTAD?.ajax_url || !ARASHTAD?.post_id) {
        console.error('Arashtad 3D: missing AJAX config, cannot save annotation.');
        return;
    }

    // annotation.point is already parented to the model root, so .position
    // here IS the offset relative to the model, independent of the model's
    // own position/rotation/scale.
    const localPosition = annotation.point.position;

    const payload = {
        id: annotation.id,
        title: annotation.data.title,
        text: annotation.data.text,
        position: {
            x: localPosition.x,
            y: localPosition.y,
            z: localPosition.z
        }
    };

    annotation.card.classList.add('saving');

    fetch(ARASHTAD.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'arashtad_save_annotation',
            nonce: ARASHTAD.nonce,
            post_id: ARASHTAD.post_id,
            annotation: JSON.stringify(payload)
        })
    })
        .then(response => response.json())
        .then(result => {
            annotation.card.classList.remove('saving');

            if (result?.success && result.data?.id) {
                annotation.id = result.data.id;
                annotation.saved = true;
            } else {
                console.error('Failed to save annotation:', result);
                annotation.card.classList.add('save-error');
            }
        })
        .catch(error => {
            annotation.card.classList.remove('saving');
            annotation.card.classList.add('save-error');
            console.error('Failed to save annotation:', error);
        });
}

function cancelEdit(annotation) {
    if (editingAnnotation === annotation) {
        editingAnnotation = null;
    }
    renderAnnotationCard(annotation);
}

// Click anywhere outside a card deselects the current selection
document.addEventListener('click', () => {
    deselectAnnotation();
});

async function createModel(url) {
    if (!canvas || !scene) return;

    setCanvasState(false);
    setPreloader(false);

    try {
        const runtime = canvas.querySelector('arashtad-runtime');
        const existingModel = runtime?.querySelector('arashtad-model');

        if (existingModel) {
            existingModel.remove();
        }

        const oldRoot = scene.getTransformNodeByName?.('arashtad-3d-annotation-admin-model-root');
        if (oldRoot) {
            oldRoot.dispose();
        }

        model = null;

        if (!url) {
            setCanvasState(true);
            setPreloader(true);
            return;
        }

        model = await loadModel(scene, url, 'arashtad-3d-annotation-admin-model');

        await updateModelInScene();
        await initAnnotations();

        setCanvasState(true);
        setPreloader(true);
    } catch (error) {
        console.error('Arashtad 3D Error:', error);
        setCanvasState(true);
        setPreloader(true);
    }
}

function setupModelLoadingObserver() {
    if (!canvas) return;
    
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeName === 'MODEL' && node.id === 'arashtad-3d-annotation-admin-model') {
                        // Check if model loaded successfully
                        node.addEventListener('load', () => {
                            setCanvasState(true);
                            setPreloader(true);
                        });
                        node.addEventListener('error', (e) => {
                            console.error('Model failed to load:', e);
                            setCanvasState(true);
                            setPreloader(true);
                        });
                    }
                });
            }
        });
    });
    
    observer.observe(canvas, { childList: true, subtree: true });
}

function addModelChangeListeners() {
    modelInput?.addEventListener('change', () => {
        createModel(getSource());
    });

    selectButton?.addEventListener('click', () => {
        setCanvasState(false);
        setPreloader(false);

        const frame = wp.media({
            title: __('Select 3D Model', 'arashtad-3d-annotation'),
            button: {
                text: __('Use This Model', 'arashtad-3d-annotation')
            },
            multiple: false
        });

        frame.on('select', () => {
            const attachment = frame.state().get('selection').first().toJSON();

            if (!attachment?.url) return;

            modelInput.value = attachment.url;
            modelInput.dispatchEvent(new Event('change'));
        });

        frame.open();
    });

    clearButton?.addEventListener('click', () => {
        modelInput.value = '';
        modelInput.dispatchEvent(new Event('change'));
    });
}

function addWrapperStylesListeners() {
    const styleFields = document.querySelectorAll('[name^="arashtad_styles["]');
    styleFields.forEach(field => {
        field.addEventListener('change', () => {
            updateWrapperStyles();
        });
        field.addEventListener('input', debounce(() => {
            updateWrapperStyles();
        }, 100));
    });
}

function addClearColorListener() {
    const colorInput = document.querySelector('[name="arashtad_clear_color"]');
    if (!colorInput) return;
    colorInput.addEventListener('change', async () => {
        try {
            await updateClearColor();
            setCanvasState(true);
            setPreloader(true);
        } catch (error) {
            console.error('Failed to update clear color:', error);
        }
    });
    colorInput.addEventListener('input', debounce(async () => {
        try {
            await updateClearColor();
            setCanvasState(true);
            setPreloader(true);
        } catch (error) {
            console.error('Failed to update clear color:', error);
        }
    }, 200));
}

function copyShortcode(button, value) {
    navigator.clipboard.writeText(value).then(() => {
        const text = button.textContent;
        button.textContent = __('Copied', 'arashtad-3d-annotation');
        setTimeout(() => {
            button.textContent = text;
        }, 1200);
    });
}

document.getElementById('arashtad_copy_shortcode')?.addEventListener('click', event => {
    copyShortcode(event.currentTarget, document.getElementById('arashtad_shortcode').value);
});

document.querySelectorAll('.arashtad-copy-shortcode').forEach(button => {
    button.addEventListener('click', () => {
        copyShortcode(button, button.dataset.shortcode);
    });
});

document.getElementById('arashtad-refresh-preview')?.addEventListener('click', async () => {
    try {
        setCanvasState(false);
        setPreloader(false);
        await reloadScene();
        setCanvasState(true);
        setPreloader(true);
    } catch (error) {
        console.error('Failed to refresh preview:', error);
        setCanvasState(true);
        setPreloader(true);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    if (!canvas) return;

    canvasWrapper = canvas.closest('.canvas-wrapper');
    
    addCameraChangeListeners();
    addLightChangeListeners();
    addModelChangeListeners();
    addModelSettingsListeners();
    addClearColorListener();
    addWrapperStylesListeners();
    toggleAutoRotateFields();
    toggleCameraFields();
    toggleLightFields();
    updateWrapperStyles();
    setupModelLoadingObserver();

    handlePanel();

    const initialModel = getSource();

    if (initialModel) {
        setTimeout(async () => {
            await initScene();
            await createModel(initialModel);
        }, 100);
    } else {
        setTimeout(async () => {
            await initScene();
            setCanvasState(true);
            setPreloader(true);
        }, 100);
    }

    renderMainForm();
    renderSidebar();
});

function renderMainForm() {
    const sidebar = document.getElementById('arashtad_main');
    if (!sidebar)
        return;

    [
        '#arashtad_annotation_preview'
    ].forEach(selector => {
        const box = document.querySelector(selector);
        if (box)
            sidebar.appendChild(box);
    });
}

function renderSidebar() {
    const sidebar = document.getElementById('arashtad_sidebar');
    if (!sidebar)
        return;
   

    const sidebarTop = document.getElementById('arashtad_sidebar_top');
    if (!sidebar)
        return;

    [
        '.wp-heading-inline',
        '.page-title-action',
        '#titlediv'
    ].forEach(selector => {
        const box = document.querySelector(selector);
        if (box)
            sidebarTop.appendChild(box);
    });

    const sidebarMiddle = document.getElementById('arashtad_meta_boxes');
    if (!sidebar)
        return;

    [
        '#arashtad_annotation_model',
        '#arashtad_annotation_shortcode',
        '#arashtad_annotation_styles',
        '#arashtad_annotation_clear_color',
        '#arashtad_annotation_model_settings',
        '#arashtad_annotation_camera',
        '#arashtad_annotation_light',
        '#arashtad_annotation_lazy',
        '#arashtad_annotation_resources'
    ].forEach(selector => {
        const box = document.querySelector(selector);
        if (box)
            sidebarMiddle.appendChild(box);
    });

    let modelBox = document.getElementById('arashtad_annotation_model');
    modelBox.classList.add('active');

    const sidebarBottom = document.getElementById('arashtad_sidebar_bottom');
    if (!sidebar)
        return;

    [
        '#submitdiv'
    ].forEach(selector => {
        const box = document.querySelector(selector);
        if (box)
            sidebarBottom.appendChild(box);
    });
}

function updatePosterSettings() {
    if (!posterEnabled || !posterSettings) return;
    posterSettings.classList.toggle('enabled', posterEnabled.checked);
}

posterEnabled?.addEventListener('change', updatePosterSettings);
updatePosterSettings();

selectPosterButton?.addEventListener('click', () => {
    if (!posterEnabled?.checked) return;
    if (!posterFrame) {
        posterFrame = wp.media({
            title: __('Select Poster Image', 'arashtad-3d-annotation'),
            button: { text: __('Use Poster', 'arashtad-3d-annotation') },
            multiple: false
        });
        posterFrame.on('select', () => {
            const attachment = posterFrame.state().get('selection').first().toJSON();
            if (!attachment) return;
            posterImageId.value = attachment.id;
            posterPreview.innerHTML = '';
            const image = document.createElement('img');
            image.src = attachment.url;
            image.className = 'arashtad-poster-preview-image';
            posterPreview.appendChild(image);
            posterPreview.classList.add('has-image');
            if (clearPosterButton) clearPosterButton.hidden = false;
        });
    }
    posterFrame.open();
});

clearPosterButton?.addEventListener('click', () => {
    posterImageId.value = '';
    posterPreview.innerHTML = '';
    posterPreview.classList.remove('has-image');
    clearPosterButton.hidden = true;
});

function handlePanel() {
    const tabs = document.querySelectorAll('#arashtad_panel_tab li');

    tabs.forEach(tabButton => {
        tabButton.addEventListener('click', () => {
            if (!tabButton.classList.contains('active')) {
                const tab = tabButton.id.slice(0, -7);

                tabs.forEach(tab => tab.classList.remove('active'));
                tabButton.classList.add('active');

                const activeBox = document.querySelector('#arashtad_meta_boxes .postbox.active');
                if (activeBox) {
                    activeBox.style.display = 'none';
                    activeBox.classList.remove('active');
                }

                const box = document.querySelector(`#arashtad_meta_boxes #${tab}`);
                if (box) {
                    box.style.display = 'block';
                    box.classList.add('active');
                }
            }
        });
    });

    const minorPublishing = document.querySelector('#minor-publishing');

    if (minorPublishing) {
        minorPublishing.addEventListener('click', event => {
            const rect = minorPublishing.getBoundingClientRect();

            if (event.clientY >= rect.top - 24 && event.clientY <= rect.top) {
                minorPublishing.classList.toggle('active');
            }
        });
    }
}
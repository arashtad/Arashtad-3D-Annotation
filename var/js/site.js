import { ready } from '../../lib/arashtad/arashtad-3d-runtime.1.0.0.min.js';
import { enableTouchControlsForAll, enableInteractions } from '../../lib/arashtad/arashtad-3d-runtime-plugins.1.0.0.min.js';

enableTouchControlsForAll();

function renderFrontendAnnotations(canvas, scene, interactions) {
    const wrapper = canvas.closest('.canvas-wrapper');
    if (!wrapper) return;

    const layer = wrapper.querySelector('.arashtad-annotation-layer');
    const linesSvg = layer?.querySelector('.arashtad-annotation-lines');

    if (!layer || !linesSvg) return;

    const raw = canvas.dataset.annotations;
    if (!raw) return;

    let saved;
    try {
        saved = JSON.parse(raw);
    } catch (error) {
        console.error(`Arashtad 3D [${canvas.id}]: invalid annotation data`, error);
        return;
    }

    if (!Array.isArray(saved) || !saved.length) return;

    // Resolve the model root to parent annotations to. Today there's only
    // ever one <arashtad-model> per canvas in practice (single-model
    // legacy path), so we just take the first one. If an annotation entry
    // carries a model_id in the future (multi-model support), prefer that.
    const modelElements = Array.from(canvas.querySelectorAll('arashtad-model'));

    function resolveModelRoot(entry) {
        let modelEl = null;

        if (entry.model_id) {
            modelEl = modelElements.find(el => el.id === entry.model_id
                || el.id.endsWith(`-${entry.model_id}`));
        }

        if (!modelEl) {
            modelEl = modelElements[0] || null;
        }

        if (!modelEl) return null;

        return scene.getTransformNodeByName?.(`${modelEl.id}-root`) || null;
    }

    const items = [];

    saved.forEach(entry => {
        const modelRoot = resolveModelRoot(entry);

        const point = interactions.createPoint(
            new BABYLON.Vector3(0, 0, 0),
            { name: `annotation-point-${entry.id}`, diameter: 0.04 }
        );

        // Parent BEFORE setting position so saved coordinates are treated
        // as local (relative to the model), matching how admin.js now
        // saves them.
        if (modelRoot && typeof point.setParent === 'function') {
            point.setParent(modelRoot);
        }

        point.position.set(entry.position.x, entry.position.y, entry.position.z);

        const material = new BABYLON.StandardMaterial(`annotation-material-${point.uniqueId}`, scene);
        material.diffuseColor = new BABYLON.Color3(1, 0.8, 0);
        material.emissiveColor = new BABYLON.Color3(1, 0.4, 0);
        point.material = material;

        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.classList.add('arashtad-annotation-line');
        line.setAttribute('vector-effect', 'non-scaling-stroke');
        linesSvg.appendChild(line);

        const card = document.createElement('div');
        card.className = 'arashtad-annotation-card';
        card.innerHTML = `
            <strong class="arashtad-annotation-title"></strong>
            <p class="arashtad-annotation-text"></p>
        `;
        card.querySelector('.arashtad-annotation-title').textContent = entry.title;
        card.querySelector('.arashtad-annotation-text').textContent = entry.text;
        layer.appendChild(card);

        items.push({ point, line, card });
    });

    function updatePositions() {
        const engine = scene.getEngine();
        const camera = scene.activeCamera;
        if (!camera) return;

        const canvasRect = canvas.getBoundingClientRect();
        const svgRect = linesSvg.getBoundingClientRect();

        const renderWidth = engine.getRenderWidth();
        const renderHeight = engine.getRenderHeight();
        if (!renderWidth || !renderHeight || !canvasRect.width || !canvasRect.height) return;

        const viewport = camera.viewport.toGlobal(renderWidth, renderHeight);
        const transform = scene.getTransformMatrix();
        const centerX = canvasRect.left + canvasRect.width / 2;

        items.forEach(item => {
            if (item.point.isDisposed()) return;

            const world = item.point.getAbsolutePosition();
            const projected = BABYLON.Vector3.Project(world, BABYLON.Matrix.Identity(), transform, viewport);

            const x = canvasRect.left + projected.x / renderWidth * canvasRect.width;
            const y = canvasRect.top + projected.y / renderHeight * canvasRect.height;

            const pointOnLeft = x < centerX;
            const offsetX = 45;
            const cardWidth = item.card.offsetWidth;
            const cardHeight = item.card.offsetHeight;

            let cardX = pointOnLeft ? (x - cardWidth - offsetX) : (x + offsetX);
            const offsetY = 100;
            const cardY = y - cardHeight / 2 - offsetY;

            if (!Number.isFinite(cardX) || !Number.isFinite(cardY)) {
                return; // skip this item for this frame only
            }

            // Clamp so the card never crosses the page's left or right edge.
            const pageWidth = document.documentElement.clientWidth;
            const maxCardX = pageWidth - cardWidth;
            cardX = Math.max(0, Math.min(cardX, maxCardX));

            item.card.style.left = `${cardX - svgRect.left}px`;
            item.card.style.top = `${cardY - svgRect.top}px`;

            const anchorX = pointOnLeft ? (cardX + cardWidth) : cardX;
            const anchorY = cardY + cardHeight / 2;

            const x1 = x - svgRect.left;
            const y1 = y - svgRect.top;
            const x2 = anchorX - svgRect.left;
            const y2 = anchorY - svgRect.top;

            // Guard against transient NaN values on the first render frame(s),
            // before the camera's view/projection matrices are fully computed.
            if (!Number.isFinite(x1) || !Number.isFinite(y1) || !Number.isFinite(x2) || !Number.isFinite(y2)) {
                return; // skip this item for this frame only; it self-corrects next frame
            }

            item.line.setAttribute('x1', `${x1}`);
            item.line.setAttribute('y1', `${y1}`);
            item.line.setAttribute('x2', `${x2}`);
            item.line.setAttribute('y2', `${y2}`);
        });
    }

    scene.onBeforeRenderObservable.add(updatePositions);
}

function isolateCanvasDragEvents(wrapper, canvas) {
    if (!wrapper || wrapper.__arashtadDragIsolated) return;
    wrapper.__arashtadDragIsolated = true;

    ['touchstart', 'touchmove', 'touchend', 'touchcancel'].forEach(eventName => {
        wrapper.addEventListener(eventName, event => {
            event.stopPropagation();
        }, { passive: true });
    });

    canvas.addEventListener('pointerdown', event => {
        canvas.setPointerCapture(event.pointerId);
    });

    canvas.addEventListener('pointerup', event => {
        if (canvas.hasPointerCapture(event.pointerId)) {
            canvas.releasePointerCapture(event.pointerId);
        }
    });

    canvas.addEventListener('pointercancel', event => {
        if (canvas.hasPointerCapture(event.pointerId)) {
            canvas.releasePointerCapture(event.pointerId);
        }
    });

    ['pointermove', 'pointerup', 'pointercancel', 'mousedown', 'mousemove', 'mouseup'].forEach(eventName => {
        wrapper.addEventListener(eventName, event => {
            event.stopPropagation();
        }, { passive: true });
    });
}

async function initializeCanvas(canvas) {
    try {
        const wrapper = canvas.parentElement;
        const preloader = wrapper?.querySelector('.preloader');
        isolateCanvasDragEvents(wrapper, canvas);
        const counter = preloader?.querySelector('.loading-counter');
        const bar = preloader?.querySelector('.loading-bar');

        canvas.classList.add('loading');

        let runtime = canvas.querySelector('arashtad-runtime');
        if (!runtime) {
            runtime = document.createElement('arashtad-runtime');
            runtime.setAttribute('resize', 'engine.resize()');
            canvas.appendChild(runtime);
        }

        let camera = canvas.querySelector('arashtad-camera');
        if (!camera) {
            camera = document.createElement('arashtad-camera');
            camera.id = `${canvas.id}-camera`;
            camera.setAttribute('type', 'ArcRotateCamera');
            camera.setAttribute('alpha', '1.57');
            camera.setAttribute('beta', '1.2');
            camera.setAttribute('radius', '12');
            camera.setAttribute('target', 'new BABYLON.Vector3(0, 0, 0)');
            camera.setAttribute('fov', '0.4');
            camera.setAttribute('minZ', '0.1');
            camera.setAttribute('maxZ', '1000');
            canvas.appendChild(camera);
        }

        canvas.style.background = 'transparent';

        const poster = canvas.getAttribute('poster');
        if (poster) {
            canvas.style.backgroundImage = `url("${poster}")`;
            canvas.style.backgroundSize = 'cover';
            canvas.style.backgroundPosition = 'center';
        }

        const scene = await ready(canvas);
        const interactions = enableInteractions(scene, canvas);
        renderFrontendAnnotations(canvas, scene, interactions);

        if (counter) {
            counter.textContent = '100%';
        }
        if (bar) {
            bar.style.width = '100%';
        }

        canvas.classList.remove('loading');
        canvas.classList.add('loaded');

        if (preloader) {
            setTimeout(() => {
                preloader.classList.add('hidden');
                if (bar) {
                    bar.classList.add('hidden');
                }
            }, 200);
        }

        canvas.__arashtad3dAnnotation = { scene, interactions };
        return scene;

    } catch (error) {
        console.error(`Arashtad 3D [${canvas.id}]:`, error);
        canvas.classList.remove('loading');
        canvas.classList.add('loaded');
        return null;
    }
}

let initializationQueue = Promise.resolve();

document.querySelectorAll('.arashtad-3d-canvas').forEach(canvas => {
    initializationQueue = initializationQueue
        .then(() => initializeCanvas(canvas))
        .catch(error => {
            console.error(`Arashtad 3D [${canvas.id}]:`, error);
        });
});
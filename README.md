# Arashtad 3D Annotation

**Create interactive 3D experiences directly in WordPress with Babylon.js.**

Arashtad 3D Annotation lets you display 3D models, animate them, and create interactive annotations that connect information directly to your models. Build technical documentation, educational experiences, product showcases, architectural presentations, and interactive WooCommerce experiences.

**[Live Demo](https://demo.arashtad.com/categories/wordpress-development/wp/arashtad-3d-annotation/) · [Documentation](https://doc.arashtad.com/docs/arashtad-3d-annotation/) · [WordPress.org](https://wordpress.org/plugins/arashtad-3d-annotation/) · [Pro Version](https://market.arashtad.com/product/arashtad-3d-annotation/)**

[![Watch the demonstration](https://img.youtube.com/vi/eYs8kW6BkwQ/hqdefault.jpg)](https://www.youtube.com/watch?v=eYs8kW6BkwQ)

## Features

* **Interactive 3D annotations:** Create, edit, and delete unlimited annotations with custom titles and descriptions.
* **Multiple 3D formats:** GLB, GLTF, GLTF 1.0, OBJ, STL, PLY, Compressed PLY, SPLAT, SPZ, SOG, and `.babylon`.
* **Flexible model sources:** WordPress Media Library, internal URLs, external URLs, and multiple model sources.
* **Responsive viewer:** Mouse and touch rotation, pan, zoom, fullscreen, autoplay, and auto-rotation.
* **Loading controls:** Preloader, progress bar, and lazy loading.
* **WordPress integration:** Gutenberg block, shortcode, and unlimited 3D scenes per page.
* **Scene configuration:** Camera, lighting, canvas layout, styling, and model animation settings.
* **Privacy-focused:** No telemetry, tracking, or intentional transmission of visitor data.

## Community Version

The Community version provides a complete foundation for creating responsive, interactive 3D experiences in WordPress.

### Annotations

* Create, edit, and delete unlimited annotations.
* Configure custom annotation titles and descriptions.
* Display unlimited 3D canvases on a page.

### Supported 3D Formats

* GLB, GLTF, GLTF 1.0
* OBJ, STL, PLY, Compressed PLY
* SPLAT, SPZ, SOG, `.babylon`

### Model Sources

* WordPress Media Library
* Internal and external URLs
* Multiple model sources

### Viewer and Loading

* Preloader and loading progress bar
* Lazy loading
* Auto-rotation and autoplay
* Fullscreen mode
* Mouse and touch rotation
* Pan and zoom
* Responsive and mobile-ready layout
* Poster images and model downloads

### WordPress Editor

* Gutenberg block and shortcode
* Live editor preview
* Dedicated settings panel
* Guided setup
* Model file selection, replacement, and removal

### Camera and Lighting

* Camera angle, position, alpha, beta, radius, target, FOV, minZ, and maxZ
* Light type, position, and intensity

### Canvas Styling

Configure position, Z-index, offsets, transforms, margins, padding, width, height, aspect ratio, borders, border radius, overflow, box sizing, shadows, and outlines.

### Animation and Media

* Model animation autoplay
* Configurable allowed MIME types

## Pro Version

**Everything in Community, plus advanced annotation customization and scene capabilities.**

Arashtad 3D Annotation Pro adds richer annotation content, advanced visual styling, environment settings, extended lighting and camera controls, and WooCommerce integration.

### Annotation Styling

* **Points:** Diameter, diffuse color, emissive color, and opacity.
* **Labels:** Borders, border radius, shadows, outlines, backgrounds, and text colors.
* **Lines:** Fill color and thickness.
* **Cards:** Padding, borders, border radius, backgrounds, backdrop blur, filters, shadows, outlines, and text colors.

### Rich Annotation Content

* Video, audio, and image content.
* Custom HTML content.
* Show annotation cards on initialization or on click.
* Allow annotations without titles.

### Advanced Scene Controls

* Custom CSS for the canvas and 3D objects.
* Canvas background images.
* `.env` and `.hdr` environments.
* Environment intensity, background blur, and Y-axis rotation.

### Advanced Viewer Controls

* Customizable preloader.

### Advanced Lighting

* Multiple lights and light types.
* Light color and intensity.

### Advanced Camera Controls

* Zoom speed (`wheelPrecision`).
* Zoom delta percentage.
* Wheel precision on the X, Y, and Z axes.

### WooCommerce Integration

* Replace product images and galleries with 3D models.
* Configure different models for individual product variations.

[Explore Arashtad 3D Annotation Pro](https://market.arashtad.com/product/arashtad-3d-annotation/)

## Powered by Babylon.js and Arashtad 3D Runtime

Arashtad 3D Annotation is powered by [Babylon.js](https://www.babylonjs.com/), providing the real-time 3D rendering and interaction capabilities behind the plugin.

The plugin integrates Babylon.js into a WordPress-native workflow for managing scenes, configuring models, creating annotations, and publishing interactive 3D experiences.

The bundled [Arashtad 3D Runtime](https://github.com/arashtad/Arashtad-3D-Runtime) is distributed under the MIT License. It provides reusable runtime infrastructure for Arashtad's 3D web applications and plugins.

## What Can You Build?

* Interactive 3D documentation
* Product annotations and feature explanations
* Technical illustrations
* Educational models
* Architectural and industrial presentations
* Equipment documentation
* Interactive product showcases
* 3D presentations and model guides
* Product configurators
* WooCommerce product experiences

## Performance

Arashtad 3D Annotation uses a performance-oriented runtime architecture designed for interactive web-based 3D experiences.

Features include:

* Optimized runtime code
* Lazy loading
* Loading progress
* Responsive rendering
* Mobile support
* Multiple scenes per page
* Real-time 3D rendering

## Privacy

The Community version bundles Babylon.js locally and does not use telemetry or tracking.

The bundled Babylon.js KTX2 decoder has fallback URLs to the Babylon.js CDN for WebAssembly transcoder resources. These resources are requested only when KTX2 texture transcoding requires them.

The Pro version communicates with the Arashtad API for license validation and plugin update requests. These requests transmit license-related information, not website content or visitor data.

See the [WordPress.org plugin page](https://wordpress.org/plugins/arashtad-3d-annotation/) for additional details.

## Installation

### Install from WordPress.org

1. Open **Plugins → Add New Plugin** in your WordPress dashboard.
2. Search for **Arashtad 3D Annotation**.
3. Install and activate the plugin.
4. Open the Arashtad 3D Annotation settings panel.
5. Create a scene, upload or select a 3D model, and configure its annotations.
6. Insert the scene into a page or post using the Gutenberg block or shortcode.

### Install from Source

1. Download or clone this repository.
2. Copy the plugin directory into `/wp-content/plugins/`.
3. Activate **Arashtad 3D Annotation** through the WordPress Plugins screen.
4. Create and publish your first 3D annotation scene.

## Requirements

* WordPress 6.0 or later
* PHP 7.4 or later
* A modern browser with WebGL support

## Screenshots

See the [WordPress.org plugin page](https://wordpress.org/plugins/arashtad-3d-annotation/) for screenshots of the editor, annotation controls, camera and lighting settings, Gutenberg integration, frontend viewer, and plugin updates.

## Source Code and Licenses

* **Arashtad 3D Annotation:** GPL-2.0-or-later, subject to the plugin's distributed license terms.
* **Arashtad 3D Runtime:** MIT License.
* **Babylon.js:** See the bundled third-party notices and licenses.

The runtime source is available at [github.com/arashtad/Arashtad-3D-Runtime](https://github.com/arashtad/Arashtad-3D-Runtime).

## Changelog

### 2.0.0

* Added Arashtad 3D Runtime v1.0.0.
* Added Arashtad 3D Runtime plugins v1.0.0.
* Refactored the plugin to use the runtime and its plugin system.
* Migrated from direct Babylon.js usage to runtime-supported syntax.
* Restructured the plugin architecture around the runtime plugin system.

### 1.0.0

* Initial release.

## Contributing

Bug reports, suggestions, and contributions are welcome. Please use the repository's issue tracker to report problems or propose improvements.

For usage instructions, visit the [documentation](https://doc.arashtad.com/docs/arashtad-3d-annotation/).

## Links

* [WordPress.org Plugin Directory](https://wordpress.org/plugins/arashtad-3d-annotation/)
* [Live Demo](https://demo.arashtad.com/categories/wordpress-development/wp/arashtad-3d-annotation/)
* [Documentation](https://doc.arashtad.com/docs/arashtad-3d-annotation/)
* [Pro Version](https://market.arashtad.com/product/arashtad-3d-annotation/)
* [Arashtad 3D Runtime](https://github.com/arashtad/Arashtad-3D-Runtime)
* [Babylon.js](https://www.babylonjs.com/)

---

Developed by [Arashtad](https://arashtad.com/).


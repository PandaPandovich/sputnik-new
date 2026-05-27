# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WordPress theme "Sputnik Plus" built with `@wordpress/scripts` for asset compilation and custom Gutenberg blocks. Runs on Local by Flywheel (Local Sites). Comments in code are in Russian.

## Commands

- `npm run start` — development mode with hot rebuild (watches `src/`)
- `npm run build` — production build to `build/`

No test or lint scripts are configured.

## Architecture

### Build System

Custom `webpack.config.js` extends `@wordpress/scripts` default config:
- **Entry points**: `src/js/main.js` (frontend), `src/js/editor.js` (Gutenberg editor)
- **Block entries**: auto-discovered from `src/blocks/*/block.json`
- **CSS output**: `build/styles/main.css`, `build/styles/editor.css`; block CSS stays alongside block JS
- **Static assets**: `src/img/`, `src/icons/` copied to `build/`
- RTL CSS generation is disabled

### Source Structure

- `src/js/main.js` — frontend JS bundle
- `src/js/editor.js` — editor JS bundle
- `src/scss/` — global styles (`main.scss` entry, with `global/`, `parts/`, `ui/`, `mixins/` partials)
- `src/blocks/<block-name>/` — each block has `block.json`, `index.js`, `render.php`, `style.scss`, `editor.scss`
- `src/img/`, `src/icons/` — static assets

### Custom Blocks

Blocks use server-side rendering (`render.php`). Registration uses `wp_register_block_types_from_metadata_collection` (WP 6.8+) with fallbacks for older versions. Block manifest is auto-generated during build.

Block namespace pattern: check individual `block.json` files for the registered name.

### Theme PHP

- `functions.php` — asset enqueueing, block registration, menu registration (header_menu, footer_menu)
- `acf-json/` — ACF field group JSON sync (Advanced Custom Fields)

### Dependencies

- `swiper` — slider library (frontend dependency)

# Frog Plugin

A multi-purpose WordPress plugin with various custom features for enhanced website functionality.

## Features

### Thumbnail Overlay for Galleries
- **Keyboard Navigation**: Use WASD keys to navigate through galleries
- **Spacebar Toggle**: Press spacebar to show/hide thumbnail overlay
- **Thumbnail Grid**: Click thumbnails to jump to specific slides
- **Responsive Design**: Only activates on screens wider than 1000px
- **Lightbox Integration**: Works with Simply Gallery lightboxes

### Slider Rewind Buttons
- **Smart Rewind**: Automatically shows rewind button when swiper reaches the end
- **Smooth Animation**: 600ms slide animation back to start
- **Responsive Positioning**: Adapts to different screen sizes
- **Video Slider Support**: Special controls for video content

### Accessibility Cleanup
- **UI Cleanup**: Removes unnecessary accessibility elements that clutter the interface
- **Skip Link Removal**: Cleans up redundant skip-to-content links
- **ARIA Cleanup**: Removes unnecessary ARIA landmarks

### Advanced Keyboard Navigation
- **Viewport Detection**: Only controls swiper in center of viewport
- **Accelerating Navigation**: Hold keys to move faster through galleries
- **Smart Focus Management**: Disables keyboard when typing in form fields
- **Smooth Scrolling**: Smooth transitions between different swiper instances

### Lightbox Integration
- **Drag Prevention**: Prevents accidental lightbox opening during swiper drag
- **Faster Closing**: Click anywhere on lightbox image to close
- **Shield Overlay**: Creates invisible barrier during drag operations

## Installation

1. Upload the `frogplugin` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically detect Swiper galleries and add functionality

## Usage

### For Users
- **Spacebar**: Toggle thumbnail overlay on/off
- **WASD Keys**: Navigate through gallery slides
- **Click Thumbnails**: Jump to specific slides
- **Click Outside**: Close thumbnail overlay

### For Developers
The plugin is modular and can be extended with new features:

```
frogplugin/
├── frogplugin.php          # Main plugin file
├── modules/                # Feature modules
│   └── thumbnail-overlay.php
├── assets/                 # CSS, JS, images
│   └── js/
│       └── thumbnail-overlay.js
└── README.md
```

## Requirements

- WordPress 5.0+
- Swiper galleries (MakeItEasy Slider blocks)
- Modern browser with JavaScript enabled

## Changelog

### 1.0.0
- Initial release
- Thumbnail overlay functionality
- Keyboard navigation
- Responsive design
- Lightbox integration

## Support

For support and feature requests, please visit the [GitHub repository](https://github.com/vtrart/vtrart).

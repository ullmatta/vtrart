<?php
/**
 * Accessibility Cleanup Module
 * 
 * Removes unnecessary accessibility elements that clutter the UI
 * while maintaining essential functionality.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AccessibilityCleanupModule {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function enqueue_scripts() {
        if ($this->should_load_scripts()) {
            wp_enqueue_script(
                'frogplugin-accessibility-cleanup',
                FROGPLUGIN_PLUGIN_URL . 'assets/js/accessibility-cleanup.js',
                array(),
                FROGPLUGIN_VERSION,
                true
            );
        }
    }
    
    private function should_load_scripts() {
        // Load on all pages for global cleanup
        return true;
    }
}

<?php
/**
 * Lightbox Integration Module
 * 
 * Provides enhanced lightbox functionality with drag detection
 * and faster closing mechanisms.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class LightboxIntegrationModule {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function enqueue_scripts() {
        if ($this->should_load_scripts()) {
            wp_enqueue_script(
                'frogplugin-lightbox-integration',
                FROGPLUGIN_PLUGIN_URL . 'assets/js/lightbox-integration.js',
                array(),
                FROGPLUGIN_VERSION,
                true
            );
        }
    }
    
    private function should_load_scripts() {
        global $post;
        if (!$post) return false;
        
        return has_block('makeiteasy/slider', $post) || 
               strpos($post->post_content, 'wp-block-makeiteasy-slider') !== false ||
               strpos($post->post_content, 'swiper') !== false ||
               strpos($post->post_content, 'simply-gallery') !== false;
    }
}

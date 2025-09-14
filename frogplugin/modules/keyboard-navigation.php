<?php
/**
 * Advanced Keyboard Navigation Module
 * 
 * Provides viewport-based swiper detection and accelerating navigation
 * with smart focus management.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class KeyboardNavigationModule {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_head', array($this, 'add_inline_styles'));
    }
    
    public function enqueue_scripts() {
        if ($this->should_load_scripts()) {
            wp_enqueue_script(
                'frogplugin-keyboard-navigation',
                FROGPLUGIN_PLUGIN_URL . 'assets/js/keyboard-navigation.js',
                array(),
                FROGPLUGIN_VERSION,
                true
            );
        }
    }
    
    public function add_inline_styles() {
        if (!$this->should_load_scripts()) {
            return;
        }
        ?>
        <style>
        /* Swiper Hover Debug */
        .swiper-hover-debug {
            outline: 2px solid #ff0000;
            outline-offset: 2px;
        }
        </style>
        <?php
    }
    
    private function should_load_scripts() {
        global $post;
        if (!$post) return false;
        
        return has_block('makeiteasy/slider', $post) || 
               strpos($post->post_content, 'wp-block-makeiteasy-slider') !== false ||
               strpos($post->post_content, 'swiper') !== false;
    }
}

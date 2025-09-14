<?php
/**
 * Slider Rewind Module
 * 
 * Adds smart rewind functionality to Swiper galleries.
 * Shows rewind button when swiper reaches the end.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SliderRewindModule {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_head', array($this, 'add_inline_styles'));
    }
    
    public function enqueue_scripts() {
        if ($this->should_load_scripts()) {
            wp_enqueue_script(
                'frogplugin-slider-rewind',
                FROGPLUGIN_PLUGIN_URL . 'assets/js/slider-rewind.js',
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
        /* Slider Rewind Button Styles */
        .swiper-rewind-button {
            font-size: clamp(18px, 2vw, 30px);
            opacity: 0.5;
            top: calc(50% - 46px);
            right: -16px;
            padding: 40px;
            margin: -10px;
            transition-duration: 100ms;
            letter-spacing: -5px;
            position: absolute;
            background: rgba(0,0,0,0.0);
            color: #fff;
            border: none;
            cursor: pointer;
            z-index: 10;
        }
        
        @media (min-width: 1620px) { 
            .swiper-rewind-button {
                right: -73px;
            }
        }
        
        .swiper-rewind-button:hover {
            opacity: 1;
        }
        
        /* Video Slider Support */
        .videoSlider .swiper-button-prev,
        .videoSlider .swiper-button-next {
            height: calc(100% - 70px);	
        }
        
        .videoSlider .swiper-button-prev::after,
        .videoSlider .swiper-button-next::after {
            margin-top: 70px;
        }
        
        /* Swiper Hover Debug */
        .swiper {
            transition-duration: 300ms;
        }
        
        .swiper-hover-debug {
            /* Debug styling can be added here */
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

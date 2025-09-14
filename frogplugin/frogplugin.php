<?php
/**
 * Plugin Name: Frog Plugin
 * Plugin URI: https://github.com/vtrart/vtrart
 * Description: A multi-purpose WordPress plugin with various custom features including thumbnail overlays, gallery enhancements, and more.
 * Version: 1.1.0
 * Author: Viktor Art
 * License: GPL v2 or later
 * Text Domain: frogplugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FROGPLUGIN_VERSION', '1.0.0');
define('FROGPLUGIN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FROGPLUGIN_PLUGIN_PATH', plugin_dir_path(__FILE__));

class FrogPlugin {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_head', array($this, 'add_inline_styles'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Include feature modules
        $this->include_modules();
        
        // Initialize modules
        new ThumbnailOverlayModule();
        new SliderRewindModule();
        new AccessibilityCleanupModule();
        new KeyboardNavigationModule();
        new LightboxIntegrationModule();
    }
    
    public function init() {
        // Plugin initialization
        load_plugin_textdomain('frogplugin', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    private function include_modules() {
        // Include individual feature modules
        require_once FROGPLUGIN_PLUGIN_PATH . 'modules/thumbnail-overlay.php';
        require_once FROGPLUGIN_PLUGIN_PATH . 'modules/slider-rewind.php';
        require_once FROGPLUGIN_PLUGIN_PATH . 'modules/accessibility-cleanup.php';
        require_once FROGPLUGIN_PLUGIN_PATH . 'modules/keyboard-navigation.php';
        require_once FROGPLUGIN_PLUGIN_PATH . 'modules/lightbox-integration.php';
    }
    
    public function enqueue_scripts() {
        // Only load on pages that need the features
        if ($this->should_load_scripts()) {
            wp_enqueue_script(
                'frogplugin-thumbnail-overlay',
                FROGPLUGIN_PLUGIN_URL . 'assets/js/thumbnail-overlay.js',
                array(),
                FROGPLUGIN_VERSION,
                true
            );
            
            // Localize script with AJAX data
            wp_localize_script('frogplugin-thumbnail-overlay', 'swiperThumbs', array(
                'rest_url' => rest_url('frogplugin/v1/thumbnails'),
                'nonce' => wp_create_nonce('wp_rest'),
                'thumbnail_size' => 'medium'
            ));
        }
    }
    
    public function add_inline_styles() {
        if (!$this->should_load_scripts()) {
            return;
        }
        ?>
        <style>
        /* Enhanced Thumbnail Overlay Styles */
        .GR-thumbnail-overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.95);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            padding: 1.5vw; /* margin around edges */
        }
        
        .GR-thumbnail-overlay.GR-visible {
            opacity: 1;
            visibility: visible;
        }
        
        .GR-thumbnail-overlay.GR-closing {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.6s ease, visibility 0.6s ease;
        }
        
        .GR-thumbnail-grid {
            display: grid;
            /* Each square tries to be between 200px and as big as possible (1fr) */
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 12px;
            width: 100%;
            height: 100%;
            align-content: center; /* center grid vertically if less items */
            padding: 50px;
        }
        
        .GR-thumbnail-grid img {
            width: 100%;
            aspect-ratio: 1 / 1; /* keep squares */
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            filter: grayscale(90%);
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.1s ease;
        }
        
        .GR-thumbnail-grid img:hover {
            transform: scale(1.04);
            filter: grayscale(0%);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
        }
        
        .GR-thumbnail-button {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 10000;
            padding: 10px 20px;
            background-color: transparent;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            width: 28px;
            padding: 20px;
            height: 28px;
            font-size: 16px;
            transition: background-color 0.2s;
            background-image: url("/wp-content/uploads/2025/09/tg.png"); 
            background-size: cover;
            background-repeat: no-repeat;
            font-size: 12px; /* Smaller text to fit */
            line-height: 1;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 31px solid transparent;
            opacity: 0.5;
            transform: scale(0.9);
            transition-property: transform, opacity; 
            transition-duration: 150ms;
        }
        
        .GR-thumbnail-button:hover {
            opacity: 1;
            transform: scale(1);
        }
        
        .GR-thumbnail-button.GR-active { 
            opacity: .8;
            transform: scale(1);
        }
        
        /* Swiper Slide Text Styling */
        .swiper-slide p {
            max-width: 700px;
        }
        
        @media (max-width: 768px) {
            .swiper-slide p {
                font-size: .8em;
            }
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
            outline: 2px solid #ff0000;
            outline-offset: 2px;
        }
        
        @media (max-width: 999px) {
            .GR-thumbnail-button {
                display: none;
            }
        }
        </style>
        <?php
    }
    
    public function add_admin_menu() {
        add_options_page(
            'Frog Plugin Settings',
            'Frog Plugin',
            'manage_options',
            'frogplugin-settings',
            array($this, 'admin_page')
        );
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Frog Plugin Settings</h1>
            <p>Welcome to Frog Plugin! This is your multi-purpose WordPress plugin.</p>
            
            <h2>Active Features</h2>
            <ul>
                <li>✅ Thumbnail Overlay for Galleries</li>
                <li>✅ Slider Rewind Buttons</li>
                <li>✅ Accessibility Cleanup</li>
                <li>✅ Advanced Keyboard Navigation</li>
                <li>✅ Lightbox Integration</li>
            </ul>
            
            <h2>Usage</h2>
            <p>The thumbnail overlay feature automatically activates on pages with Swiper galleries. Users can:</p>
            <ul>
                <li>Press the <strong>Spacebar</strong> to toggle thumbnail overlay</li>
                <li>Use <strong>WASD keys</strong> to navigate galleries</li>
                <li>Click thumbnails to jump to specific slides</li>
            </ul>
        </div>
        <?php
    }
    
    private function should_load_scripts() {
        global $post;
        if (!$post) return false;
        
        // Check if post content contains Swiper galleries
        return has_block('makeiteasy/slider', $post) || 
               strpos($post->post_content, 'wp-block-makeiteasy-slider') !== false;
    }
}

// Initialize the plugin
new FrogPlugin();

// Register REST API endpoints
add_action('rest_api_init', function() {
    register_rest_route('frogplugin/v1', '/thumbnails', array(
        'methods' => 'POST',
        'callback' => function($request) {
            $params = $request->get_json_params();
            $attachment_ids = $params['attachment_ids'] ?? array();
            $thumbnail_size = $params['thumbnail_size'] ?? 'medium';
            
            $thumbnails = array();
            foreach ($attachment_ids as $id) {
                $thumbnail_url = wp_get_attachment_image_url($id, $thumbnail_size);
                if ($thumbnail_url) {
                    $thumbnails[] = $thumbnail_url;
                }
            }
            
            return new WP_REST_Response($thumbnails, 200);
        },
        'permission_callback' => '__return_true'
    ));
});

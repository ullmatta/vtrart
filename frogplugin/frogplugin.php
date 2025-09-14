<?php
/**
 * Plugin Name: Frog Plugin
 * Plugin URI: https://github.com/vtrart/vtrart
 * Description: A multi-purpose WordPress plugin with various custom features including thumbnail overlays, gallery enhancements, and more.
 * Version: 1.0.0
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
    }
    
    public function init() {
        // Plugin initialization
        load_plugin_textdomain('frogplugin', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    private function include_modules() {
        // Include individual feature modules
        require_once FROGPLUGIN_PLUGIN_PATH . 'modules/thumbnail-overlay.php';
        // Add more modules here as you create them
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
        /* Thumbnail Overlay Styles */
        .GR-thumbnail-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            display: none;
            opacity: 0;
            transition: opacity 0.6s ease;
        }
        
        .GR-thumbnail-overlay.GR-visible {
            display: flex;
            opacity: 1;
        }
        
        .GR-thumbnail-overlay.GR-closing {
            opacity: 0;
        }
        
        .GR-thumbnail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 20px;
            padding: 40px;
            max-width: 1200px;
            margin: 0 auto;
            align-items: center;
            height: 100%;
            overflow-y: auto;
        }
        
        .GR-thumbnail-grid img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .GR-thumbnail-grid img:hover {
            transform: scale(1.05);
        }
        
        .GR-thumbnail-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #007cba;
            color: white;
            border: none;
            padding: 15px 20px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 124, 186, 0.3);
        }
        
        .GR-thumbnail-button:hover {
            background: #005a87;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 124, 186, 0.4);
        }
        
        .GR-thumbnail-button.GR-active {
            background: #d63638;
        }
        
        .GR-thumbnail-button.GR-active:hover {
            background: #b32d2e;
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
                <li>🔄 More features coming soon...</li>
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

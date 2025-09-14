<?php
/**
 * Plugin Name: Thumbnail Overlay for Galleries
 * Plugin URI: https://github.com/vtrart/vtrart
 * Description: Adds a thumbnail overlay feature to Swiper galleries with keyboard navigation and lightbox integration.
 * Version: 1.0.0
 * Author: Viktor Art
 * License: GPL v2 or later
 * Text Domain: thumbnail-overlay
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('THUMBNAIL_OVERLAY_VERSION', '1.0.0');
define('THUMBNAIL_OVERLAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('THUMBNAIL_OVERLAY_PLUGIN_PATH', plugin_dir_path(__FILE__));

class ThumbnailOverlay {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_get_thumbnail_urls', array($this, 'get_thumbnail_urls'));
        add_action('wp_ajax_nopriv_get_thumbnail_urls', array($this, 'get_thumbnail_urls'));
        add_action('wp_head', array($this, 'add_inline_styles'));
    }
    
    public function enqueue_scripts() {
        // Only load on pages with Swiper galleries
        if (!$this->has_swiper_galleries()) {
            return;
        }
        
        wp_enqueue_script(
            'thumbnail-overlay',
            THUMBNAIL_OVERLAY_PLUGIN_URL . 'thumbnail-overlay.js',
            array(),
            THUMBNAIL_OVERLAY_VERSION,
            true
        );
        
        // Localize script with AJAX data
        wp_localize_script('thumbnail-overlay', 'swiperThumbs', array(
            'rest_url' => rest_url('thumbnail-overlay/v1/thumbnails'),
            'nonce' => wp_create_nonce('wp_rest'),
            'thumbnail_size' => 'medium'
        ));
    }
    
    public function get_thumbnail_urls() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wp_rest')) {
            wp_die('Security check failed');
        }
        
        $attachment_ids = isset($_POST['attachment_ids']) ? $_POST['attachment_ids'] : array();
        $thumbnail_size = isset($_POST['thumbnail_size']) ? $_POST['thumbnail_size'] : 'medium';
        
        $thumbnails = array();
        
        foreach ($attachment_ids as $id) {
            $thumbnail_url = wp_get_attachment_image_url($id, $thumbnail_size);
            if ($thumbnail_url) {
                $thumbnails[] = $thumbnail_url;
            }
        }
        
        wp_send_json_success($thumbnails);
    }
    
    private function has_swiper_galleries() {
        global $post;
        if (!$post) return false;
        
        // Check if post content contains Swiper galleries
        return has_block('makeiteasy/slider', $post) || 
               strpos($post->post_content, 'wp-block-makeiteasy-slider') !== false;
    }
    
    public function add_inline_styles() {
        if (!$this->has_swiper_galleries()) {
            return;
        }
        ?>
        <style>
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
}

// Initialize the plugin
new ThumbnailOverlay();

// Register REST API endpoint
add_action('rest_api_init', function() {
    register_rest_route('thumbnail-overlay/v1', '/thumbnails', array(
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

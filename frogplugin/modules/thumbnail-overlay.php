<?php
/**
 * Thumbnail Overlay Module
 * 
 * This module handles the thumbnail overlay functionality for Swiper galleries.
 * It provides keyboard navigation, lightbox integration, and thumbnail grid display.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ThumbnailOverlayModule {
    
    public function __construct() {
        // This module is handled by the main plugin class
        // Individual module functionality can be added here if needed
    }
    
    /**
     * Check if the current page has Swiper galleries
     */
    public static function has_swiper_galleries() {
        global $post;
        if (!$post) return false;
        
        return has_block('makeiteasy/slider', $post) || 
               strpos($post->post_content, 'wp-block-makeiteasy-slider') !== false;
    }
    
    /**
     * Get thumbnail URLs for given attachment IDs
     */
    public static function get_thumbnail_urls($attachment_ids, $thumbnail_size = 'medium') {
        $thumbnails = array();
        
        foreach ($attachment_ids as $id) {
            $thumbnail_url = wp_get_attachment_image_url($id, $thumbnail_size);
            if ($thumbnail_url) {
                $thumbnails[] = $thumbnail_url;
            }
        }
        
        return $thumbnails;
    }
}

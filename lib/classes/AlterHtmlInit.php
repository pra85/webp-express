<?php

namespace WebPExpress;

include_once "AlterHtmlHelper.php";
use AlterHtmlHelper;

class AlterHtmlInit
{
    public static $options = null;

    public static function startOutputBuffer()
    {
        if (!is_admin() || (function_exists("wp_doing_ajax") && wp_doing_ajax()) || (defined( 'DOING_AJAX' ) && DOING_AJAX)) {
            ob_start('self::alterHtml');
        }
    }

    public static function alterHtml($content)
    {
        // Don't do anything with the RSS feed.
        if (is_feed()) {
            return $content;
        }

        if (get_option('webp-express-alter-html-replacement') == 'picture') {
            if(function_exists('is_amp_endpoint') && is_amp_endpoint()) {
                //for AMP pages the <picture> tag is not allowed
                return $content;
            }
            /*if (is_admin() ) {
                return $content;
            }*/
        }

        if (!isset(self::$options)) {
            self::$options = json_decode(get_option('webp-express-alter-html-options', null), true);
            //AlterHtmlHelper::$options = self::$options;
        }

        if (self::$options == null) {
            return $content;
        }

        if (get_option('webp-express-alter-html-replacement') == 'picture') {
            require_once __DIR__ . '/AlterHtmlPicture.php';
            return \WebPExpress\AlterHtmlPicture::alter($content);
        } else {
            require_once __DIR__ . '/AlterHtmlImageUrls.php';
            return \WebPExpress\AlterHtmlImageUrls::alter($content);
        }
    }

    public static function addPictureJs()
    {
        // Don't do anything with the RSS feed.
        // - and no need for PictureJs in the admin
        if ( is_feed() || is_admin() ) { return; }

        echo '<script>'
           . 'document.createElement( "picture" );'
           . 'if(!window.HTMLPictureElement && document.addEventListener) {'
                . 'window.addEventListener("DOMContentLoaded", function() {'
                    . 'var s = document.createElement("script");'
                    . 's.src = "' . plugins_url('/js/picturefill.min.js', __FILE__) . '";'
                    . 'document.body.appendChild(s);'
                . '});'
            . '}'
           . '</script>';
    }

    public static function setHooks() {

        if (get_option('webp-express-alter-html-replacement') == 'picture') {
//            add_action( 'wp_head', '\\WebPExpress\\AlterHtmlInit::addPictureJs');
        }

        if (get_option('webp-express-alter-html-hooks', 'ob') == 'ob') {
            /* TODO:
               Which hook should we use, and should we make it optional?
               - Cache enabler uses 'template_redirect'
               - ShortPixes uses 'init'

               We go with template_redirect now, because it is the "innermost".
               This lowers the risk of problems with plugins used rewriting URLs to point to CDN.
               (We need to process the output *before* the other plugin has rewritten the URLs,
                if the "Only for webps that exists" feature is enabled)
            */
            add_action( 'init', '\\WebPExpress\\AlterHtmlInit::startOutputBuffer', 1 );
            add_action( 'template_redirect', '\\WebPExpress\\AlterHtmlInit::startOutputBuffer', 10000 );

        } else {
            add_filter( 'the_content', '\\WebPExpress\\AlterHtmlInit::alterHtml', 99999 ); // priority big, so it will be executed last
            add_filter( 'the_excerpt', '\\WebPExpress\\AlterHtmlInit::alterHtml', 99999 );
            add_filter( 'post_thumbnail_html', '\\WebPExpress\\AlterHtmlInit::alterHtml', 99999);


            /*
            TODO:
            check out these hooks (used by Jecpack, in class.photon.php)

            // Images in post content and galleries
    		add_filter( 'the_content', array( __CLASS__, 'filter_the_content' ), 999999 );
    		add_filter( 'get_post_galleries', array( __CLASS__, 'filter_the_galleries' ), 999999 );
    		add_filter( 'widget_media_image_instance', array( __CLASS__, 'filter_the_image_widget' ), 999999 );

    		// Core image retrieval
    		add_filter( 'image_downsize', array( $this, 'filter_image_downsize' ), 10, 3 );
    		add_filter( 'rest_request_before_callbacks', array( $this, 'should_rest_photon_image_downsize' ), 10, 3 );
    		add_filter( 'rest_request_after_callbacks', array( $this, 'cleanup_rest_photon_image_downsize' ) );

    		// Responsive image srcset substitution
    		add_filter( 'wp_calculate_image_srcset', array( $this, 'filter_srcset_array' ), 10, 5 );
    		add_filter( 'wp_calculate_image_sizes', array( $this, 'filter_sizes' ), 1, 2 ); // Early so themes can still easily filter.

    		// Helpers for maniuplated images
    		add_action( 'wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ), 9 );
            */
        }
    }

}

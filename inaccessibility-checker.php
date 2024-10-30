<?php
/*
Plugin Name: Inaccessibility Checker
Plugin URI: http://wordpress.org/plugins/inaccessibility-checker/
Description: Check your privilege and your tags. When previewing a post, images with no alt text or caption will be highlighted red, and a warning will be shown.
Version: 0.0.3
Author: Vhati
Author URI: 
License: GPL
*/

defined( 'ABSPATH' ) OR exit;


class Inaccessibility_Checker {
	protected static $instance;

	protected $version = "0.0.3";
	protected $me = "Inaccessibility Checker";
	protected $plugin_slug = "inaccessibility-checker";


	public static function get_instance() {
		is_null(self::$instance) AND self::$instance = new self;
		return self::$instance;
	}

	/* Create a singleton instance, when plugins are loaded. */
	public static function on_init() {
		return self::get_instance();
	}

	private function __construct() {
		add_action( "init", array($this, "load_plugin_textdomain") );

		add_action( "wp_enqueue_scripts", array($this, "on_enqueue_scripts") );

		// Schedule after other filters (default priority is 10).
		add_filter( "the_content", array($this, "on_filter_preview"), 20 );
	}

	/**
	 * Load translated strings for this locale, if available.
	 *
	 * xgettext --add-comments=/ --keyword=__ --keyword=_e --package-name="Inaccessibility Checker" --package-version="0.0.1" -o "inaccessibility-checker.pot" "inaccessibility-checker.php"
	 * msginit --no-translator --locale=en_US -o "inaccessibility-checker-en_US.po" --input="inaccessibility-checker.pot"
	 * msgfmt -o "inaccessibility-checker-en_US.mo" "inaccessibility-checker-en_US.po"
	 */
	public function load_plugin_textdomain() {
		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR ."/". $domain ."/". $domain ."-". $locale .".mo" );
		load_plugin_textdomain( $domain, FALSE, dirname(plugin_basename(__FILE__)) ."/lang/" );
	}

	/**
	 * Include custom css, when populating HEAD, if previewing a post.
	 */
	public function on_enqueue_scripts() {
		if ( is_preview() ) {
			wp_register_style( $this->plugin_slug, plugin_dir_url(__FILE__) ."css/style.css" );
			wp_enqueue_style( $this->plugin_slug );
		}
	}

	/**
	 * Add a warning notice, if necessary, when previewing a post.
	 * Images without alt text will be highlighted.
	 */
	public function on_filter_preview( $content ) {
		if ( is_preview() ) {
			// Loop backward through IMG tags.
			// Bad images will be wrapped in a highlighter DIV.

			try {
				$content_modified = false;

				// Parse DOM from the string, without adding implied doctype and html/body tags.
				$doc = new DOMDocument( '1.0', 'UTF-8' );
				@$doc->loadHTML( $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

				$bad_image_count = 0;

				foreach ( $doc->getElementsByTagName( 'img' ) as $image_element ) {
					$parent_element = $image_element->parentNode;

					$bad_image = false;
					if ( trim( $image_element->getAttribute( 'alt' ) ) !== '') {
						// Alt text is set and not blank. Okay.
						$bad_image = false;
					}
					else if ( preg_match( '/(?:figure|div)/', $parent_element->tagName ) && preg_match( '/wp-caption/', $parent_element->getAttribute( 'class' ) ) ) {
						// WordPress 4.7.0: Caption wrapped "img" tags in a "figure", with "figcaption" text.
						// WordPress 3.5.2: Caption wrapped "img" tags in a "div", with "p" text.

						// This is a captioned image.
						// Don't bother nagging, unless the caption is blank.
						$bad_image = false;

						if ( !is_null( $image_element->nextSibling ) && preg_match( '/wp-caption-text/', $image_element->nextSibling->getAttribute( 'class' ) ) && trim( $image_element->nextSibling->textContent ) === '' ) {
							$bad_image = true;
						}
					}
					else {
						// Nag about alt text.
						$bad_image = true;
					}

					if ( $bad_image ) {
						$nag_element = $doc->createElement( 'div' );
						$nag_element->setAttribute( 'class', 'inaccessibility-badimg' );
						$parent_element->replaceChild( $nag_element, $image_element );
						$nag_element->appendChild( $image_element );

						$bad_image_count += 1;
						$content_modified = true;
					}
				}

				if ( $bad_image_count > 0 ) {
					// Display a warning message.

					$warning_text = sprintf( "%s: %s",
						$this->me,
						__( "Some images are missing alt text.", $this->plugin_slug )
					);

					$warning_box_element = $doc->createElement( 'div' );
					$warning_box_element->setAttribute( 'class', 'inaccessibility-notice' );
					$doc->insertBefore( $warning_box_element, $doc->firstChild );

					$warning_text_element = $doc->createElement( 'p', $warning_text );
					$warning_box_element->appendChild( $warning_text_element );

					$content_modified = true;
				}

				if ( $content_modified ) {
					// Save DOM back to a string.
					$content = $doc->saveHTML();
				}
			}
			catch ( Exception $e ) {
				// Return original $content unmodified.
			}
		}
		return $content;
	}
}


add_action( "plugins_loaded", array(Inaccessibility_Checker, "on_init") );

?>
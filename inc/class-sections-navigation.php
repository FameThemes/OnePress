<?php
/**
 * Dots Navigation class
 * Class Onepress_Dots_Navigation
 *
 * @since 2.1.0
 *
 */
class Onepress_Dots_Navigation {
	static $_instance = null;
	private $key = 'onepress_sections_nav_';

	static function get_instance(){
		if ( is_null( self::$_instance ) ){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	function get_sections(){

		$sorted_sections = apply_filters( 'onepress_frontpage_sections_order', array(
			'features', 'about', 'services', 'videolightbox', 'gallery', 'counter', 'team',  'news', 'contact'
		) );

		$sections_config = array(
			'hero' => array(
				'label' => __( 'Section: Hero', 'onepress' ),
				'title' => '',
				'default' => false
			),
			'about' => array(
				'label' => __( 'Section: About', 'onepress' ),
				'title' => __( 'About Us', 'onepress' ),
				'default' => 1
			),
			'contact' => array(
				'label' =>  __( 'Section: Contact', 'onepress' ),
				'title' => __( 'Get in touch', 'onepress' ),
				'default' => 1
			),
			'counter' => array(
				'label' => __( 'Section: Counter', 'onepress' ),
				'title' => __( 'Our Numbers', 'onepress' ),
				'default' => ''
			),
			'features' => array(
				'label' => __( 'Section: Features', 'onepress' ),
				'title' => __( 'Features', 'onepress' ),
				'default' => 1
			),
			'gallery' => array(
				'label' => __( 'Section: Gallery', 'onepress' ),
				'title' => __( 'Gallery', 'onepress' ),
				'default' => 1
			),
			'news' => array(
				'label' => __( 'Section: News', 'onepress' ),
				'title' => __( 'Latest News', 'onepress' ),
				'default' => 1
			),
			'services' => array(
				'label' => __( 'Section: Services', 'onepress' ),
				'title' => __( 'Our Services', 'onepress' ),
				'default' => 1
			),
			'team' => array(
				'label' => __( 'Section: Team', 'onepress' ),
				'title' => __( 'Our Team', 'onepress' ),
				'default' => 1
			),
			'videolightbox' => array(
				'label' => __( 'Section: Video Lightbox', 'onepress' ),
				'title' => '',
				'default' => ''
			),
		);

		$new = array(
			'hero' => $sections_config['hero']
		);


		foreach ( $sorted_sections as $id ) {
			if ( isset( $sections_config[ $id ] ) ) {
				$new[ $id ] = $sections_config[ $id ];
			}
		}

		return apply_filters( 'onepress_sections_navigation_get_sections', $new );

	}

	function get_name( $id ) {
		return $this->key.$id;
	}

	function add_customize( $wp_customize, $section_id ){

		$wp_customize->add_setting( $this->get_name( '__enable' ),
			array(
				'sanitize_callback' => 'onepress_sanitize_text',
				'default'           => false,
			)
		);
		$wp_customize->add_control(  $this->get_name( '__enable' ),
			array(
				'label'       => __( 'Enable section navigation', 'onepress' ),
				'section'     => $section_id,
				'type'        => 'checkbox',
			)
		);

		$wp_customize->add_setting( $this->get_name( '__enable_label' ),
			array(
				'sanitize_callback' => 'onepress_sanitize_text',
				'default'           => 1,
			)
		);
		$wp_customize->add_control( $this->get_name( '__enable_label' ),
			array(
				'label'       => __( 'Enable navigation labels', 'onepress' ),
				'description'       => __( 'By default navigation label is section title.', 'onepress' ),
				'section'     => $section_id,
				'type'        => 'checkbox',
			)
		);

		// Color Settings
		$wp_customize->add_setting( $this->get_name( '__color' ), array(
			'sanitize_callback'    => 'sanitize_hex_color_no_hash',
			'sanitize_js_callback' => 'maybe_hash_hex_color',
			'default'              => ''
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $this->get_name( '__color' ),
			array(
				'label'       => esc_html__( 'Dots Color', 'onepress' ),
				'section'     => $section_id,
				'description' => '',
			)
		) );


		// Section Settings
		foreach ( $this->get_sections() as $id => $args ) {

			$name = $this->get_name( $id );

			$wp_customize->add_setting( $id.'_em',
				array(
					'sanitize_callback' => 'onepress_sanitize_text',
				)
			);
			$wp_customize->add_control( new OnePress_Misc_Control( $wp_customize,  $id.'_em',
				array(
					'type'        => 'custom_message',
					'section'     => $section_id,
					'description' => '<div class="onepress-c-heading">'.esc_html( $args['label'] ).'</div>',
				)
			));

			$wp_customize->add_setting( $name,
				array(
					'sanitize_callback' => 'onepress_sanitize_checkbox',
					'default'           => $args['default'],
					//'transport'         => 'postMessage'
				)
			);
			$wp_customize->add_control( $name,
				array(
					'label'       => __( 'Enable section navigation', 'onepress' ),
					'section'     => $section_id,
					'type'        => 'checkbox',
				)
			);


			$wp_customize->add_setting( $name.'_label',
				array(
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => '',
					//'transport'         => 'postMessage'
				)
			);
			$wp_customize->add_control( $name.'_label',
				array(
					'label'       => __( 'Custom navigation label', 'OnePress' ),
					'section'     => $section_id,
				)
			);

		}


	}

	function get_settings(){

		$data = apply_filters( 'onepress_dots_navigation_get_settings', false );
		if ( $data ) {
			return $data;
		}

		$data = [];
		$sections = $this->get_sections();
		foreach ( $sections as $id => $args ) {

			if ( ! get_theme_mod( 'onepress_' . $id . '_disable', false )
			     || ( isset( $args['show_section'] )  && $args['show_section'] )
			     ) {
				$name   = $this->get_name( $id );
				$enable = get_theme_mod( $name, $args['default'] );
				if ( $enable ) {
					$data[ $id ] = array(
						'id'     => $id,
						'enable' => get_theme_mod( $name, $args['default'] ),
						'title'  => get_theme_mod( 'onepress_' . $id . '_title', $args['title'] ),
					);
					$custom_title = get_theme_mod( $this->get_name( $id.'_label' ), false );
					if( $custom_title ) {
						$data[ $id ]['title'] = $custom_title;
					}
				}
			}

		}

		return $data;
	}

	function scripts(){
		if ( get_theme_mod( $this->get_name( '__enable' ), false ) ) {
			if ( is_front_page() ) {
				wp_enqueue_script( 'jquery.bully', get_template_directory_uri() . '/assets/js/jquery.bully.js', array( 'jquery' ), false, true );
				wp_localize_script( 'jquery.bully', 'Onepress_Bully', array(
					'enable_label' => get_theme_mod( $this->get_name( '__enable_label' ), true ) ?  true : false,
					'sections' => $this->get_settings()
				) );
			}
		}
	}

	function custom_style( $code ){
		if ( get_theme_mod( $this->get_name( '__enable' ), false ) ) {
			$color = sanitize_hex_color_no_hash( get_theme_mod( $this->get_name( '__color' ) ) );
			if ( $color ) {
				$code .= " .c-bully { color: #{$color}; } ";
			}
		}
		return $code;
	}

	function init(){
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
		add_action( 'onepress_custom_css', array( $this, 'custom_style' ) );
	}

}

Onepress_Dots_Navigation::get_instance()->init();



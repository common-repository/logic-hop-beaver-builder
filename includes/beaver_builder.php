<?php

if (!defined('ABSPATH')) die;

/**
 * Beaver Builder functionality.
 *
 *
 * @since      1.0.0
 * @package    LogicHop
 */

class LogicHop_BeaverBuilder {

	/**
	 * Logic Hop
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object    $logichop    Logic Hop class
	 */
	private $logichop;

	/**
	 * Logic Hop Public class
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object    $public    Logic Hop Public class
	 */
	private $public = null;

	/**
	 * Logic Hop Admin class
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object    $admin    Logic Hop Admin class
	 */
	private $admin = null;

	/**
	 * Logic Hop conditions
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $conditions    Logic Hop Condition titles and slugs
	 */
	private $conditions = array( '' => 'Always Display' );

	/**
	 * Row count
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      integer    $row_count    Count of row with Logic Hop Conditions
	 */
	private $row_count = 0;

	/**
	 * Module count
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      integer    $module_count    Count of modules with Logic Hop Conditions
	 */
	private $module_count = 0;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    	1.0.0
	 * @param       object    $logic	LogicHop_Core functionality & logic.
	 */
	public function __construct () {
		$this->add_hooks_filters();
	}

	/**
	 * Add actions
	 *
	 * @since    	1.0.0
	 */
	public function add_hooks_filters () {
		add_action( 'logichop_after_plugin_init', array( $this, 'logichop_plugin_init' ) );
		add_action( 'logichop_after_admin_hooks', array( $this, 'logichop_admin' ), 10, 1 );
		add_action( 'logichop_after_public_hooks', array( $this, 'logichop_public' ), 10, 1 );

		add_action( 'wp_footer', array( $this, 'editor_content' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'logichop_public_enqueue_scripts', array( $this, 'dequeue_scripts' ), 10, 2 );

		add_filter( 'logichop_anti_flicker_css', array( $this, 'bypass_anti_flicker' ), 10, 1 );

		add_filter( 'fl_builder_main_menu', array( $this, 'add_tool_menu_item' ), 10, 1 );
		add_filter( 'fl_builder_keyboard_shortcuts', array( $this, 'add_keyboard_shortcuts' ), 10, 1 );

		add_action( 'fl_builder_register_settings_form', array( $this, 'register_settings_form' ), 10, 2 );

		add_filter( 'fl_builder_module_custom_class', array( $this, 'add_module_class' ), 10, 2 );
		add_filter( 'fl_builder_module_attributes', array( $this, 'add_module_attributes' ), 10, 2 );

		add_filter( 'fl_builder_row_custom_class', array( $this, 'add_row_class' ), 10, 2 );
		add_filter( 'fl_builder_row_attributes', array( $this, 'add_row_attributes' ), 10, 2 );

		add_action( 'fl_builder_before_render_row', array( $this, 'before_render_row' ), 10, 2 );
		add_action( 'fl_builder_after_render_row', array( $this, 'after_render_row' ), 10, 2 );

		add_action( 'fl_builder_before_render_module', array( $this, 'before_render_module' ), 10, 1 );
		add_action( 'fl_builder_after_render_module', array( $this, 'after_render_module' ), 10, 1 );

		add_filter( 'fl_builder_after_render_shortcodes', array( $this, 'content_filter' ), 10, 1 );

		// https://hooks.wpbeaverbuilder.com/bb-plugin/
		//add_filter( 'fl_builder_render_module_content', array( $this, 'module_html_content' ), 10, 2 );
		//add_action( 'fl_builder_before_render_column_group', array( $this, 'before_render_row' ), 10, 2 );
		//add_action( 'fl_builder_after_render_column_group', array( $this, 'after_render_row' ), 10, 2 );
	}

	/**
	 * Logic Hop plugin init complete
	 *
	 * @since    	1.0.0
	 */
	public function logichop_plugin_init ( $logichop ) {
		$this->logichop = $logichop;
	}

	/**
	 * Logic Hop Admin init complete
	 *
	 * @since    	1.0.0
	 */
	public function logichop_admin ( $admin ) {
		$this->admin = $admin;
	}

	/**
	 * Logic Hop Public init complete
	 *
	 * @since    	1.0.0
	 */
	public function logichop_public ( $public ) {
		$this->public = $public;
	}

	/**
	 * Get Conditions
	 *
	 * @since    	1.0.0
	 */
	public function logichop_get_conditions () {

		if ( ! $this->admin ) {
			return false;
		}

		if ( count( $this->conditions ) > 1 ) {
				return;
		}

		$conditions = $this->admin->conditions_get( true );

		if ( $conditions ) {
			foreach ( $conditions as $c ) {
				$this->conditions [ $c['slug'] ] = $c['name'];
			}
		}
	}

	/**
	* Beaver Builder editor active
	*
	* @since	1.0.0
	*	@return	boolean	Is builder activce
	*/
	public function builder_active () {
			if ( class_exists( 'FLBuilderModel' ) && FLBuilderModel::is_builder_active() ) {
				return true;
			}
			return false;
	}

	/**
	 * Enqueue and render Logic Hop tool palette
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts () {
		if ( $this->admin && $this->builder_active() ) {
			$this->admin->enqueue_styles( 'post.php' );
			wp_enqueue_style( 'logichop-beaver-builder', plugin_dir_url( __FILE__ ) . 'palette.css' );
 			$this->admin->enqueue_scripts( 'post.php' );
			wp_enqueue_script( 'logichop-beaver-builder', plugin_dir_url( __FILE__ ) . 'beaver-builder.js', array( 'jquery' ) );
		}
	}

	/**
	 * Dequeue scripts during preview
	 *
	 * @since    1.0.0
	 */
	public function dequeue_scripts ( $hook, $post_type ) {
		if ( $this->builder_active() ) {
			wp_dequeue_script( 'logichop-generate_preview_data' );

			do_action( 'logichop_beaver_builder_dequeue', $hook, $post_type );
		}
	}

	/**
	 * Bypass anti-flicker css during preview
	 *
	 * @since    1.0.0
	 */
	public function bypass_anti_flicker ( $bypass ) {
		if ( $this->builder_active() ) {
			return true;
		}
		return $bypass;
	}

	/**
	 * Render Logic Hop tool palette after content
	 *
	 * @since    	1.0.0
	 */
	public function editor_content () {
		if ( $this->admin && $this->builder_active() ) {
			$this->admin->editor_shortcode_modal( true );
		}
	}

	/**
	 * Add menu item to Beaver Builder
	 *
	 * @since    	1.0.0
	 */
	public function add_tool_menu_item ( $views ) {
		$views['main']['items'][59] = array (
        'label' => __( 'Toggle Hidden Logic Hop Modules', 'logic-hop'),
		    'type' => 'event',
				'eventName' => 'toggleLogicHopModules',
      	'accessory' => '&#8984;l'
      );
		return $views;
	}

	/**
	 * Add keyboard shortcuts to Beaver Builder
	 *
	 * @since    	1.0.0
	 */
	public function add_keyboard_shortcuts ( $shortcuts ) {
		$shortcuts['toggleLogicHopModules'] = array(
			'label' => __( 'Toggle Hidden Logic Hop Modules', 'logic-hop'),
			'keyCode' => 'mod+l'
		);
		return $shortcuts;
	}

	/**
	 * Add Logic Hop to Beaver Builder rows and modules
	 *
	 * @since    	1.0.0
	 */
	public function register_settings_form ( $form, $slug ) {

		$this->logichop_get_conditions();

		$tab['logic-hop'] = array(
			'title' => __( 'Logic Hop', 'logic-hop' ),
			'sections' => array(
				'logich-hop-settings' => array(
					'fields' => array(
						'logichop_condition' => array(
          		'type' => 'select',
            	'label' => __( 'Logic Hop Condition', 'logic-hop' ),
            	'default' => '',
          		'options' => $this->conditions,
          	),
						'logichop_condition_not' => array(
          		'type' => 'select',
            	'label' => __( 'Display When', 'logic-hop' ),
            	'default' => 'met',
          		'options' => array(
								'met' => __( 'Condition Met', 'logic-hop' ),
								'not_met' => __( 'Condition Not Met', 'logic-hop' )
							)
          	),
						'logichop_editor_display' => array(
          		'type' => 'select',
            	'label' => __( 'Editor Display', 'logic-hop' ),
            	'default' => 'met',
          		'options' => array(
								'show' => __( 'Show in Editor', 'logic-hop' ),
								'hide' => __( 'Hide in Editor', 'logic-hop' )
							)
          	),
						'logichop_name' => array(
          		'type' => 'text',
            	'label' => __( 'Reference Name (optional)', 'logic-hop' ),
            	'default' => ''
          	),
					)
				)
			)
		);

		if ( $slug == 'row' ) {
			$merge_form['tabs'] = $tab;
    	$form = array_merge_recursive( $form, $merge_form );
  	} else {
			$form = array_merge( $form, $tab );
		}

    return $form;
	}

	/**
	 * Add Logic Hop classes to Beaver Builder modules in editor
	 *
	 * @since    	1.0.0
	 */
	public function add_module_class ( $class, $module ) {

		if ( ! $this->builder_active() ) {
			return $class;
		}

		if ( isset( $module->settings->logichop_editor_display ) && $module->settings->logichop_editor_display == 'hide' ) {
			$class .= ' logichop-bb-hide';
		}

		if ( isset( $module->settings->logichop_condition ) && $module->settings->logichop_condition != '' ) {
			$this->module_count++;
			$class .= sprintf( ' lh-module lh-module-%s lh-%s', $this->module_count, $module->settings->logichop_condition );
		}

		return $class;
	}

	/**
	 * Add Logic Hop attributes to Beaver Builder modules in editor
	 *
	 * @since    	1.0.0
	 */
	public function add_module_attributes ( $attrs, $module ) {

		if ( ! $this->builder_active() ) {
			return $attrs;
		}

		if ( isset( $module->settings->logichop_condition ) && $module->settings->logichop_condition != '' ) {
			$attrs['data-logic-hop'] = $module->settings->logichop_condition;
			$attrs['data-logic-hop-name'] = $module->settings->logichop_name;
		}

		return $attrs;
	}

	/**
	 * Add Logic Hop classes to Beaver Builder rows in editor
	 *
	 * @since    	1.0.0
	 */
	public function add_row_class ( $class, $row ) {

		if ( ! $this->builder_active() ) {
			return $class;
		}

		if ( isset( $row->settings->logichop_editor_display ) && $row->settings->logichop_editor_display == 'hide' ) {
			$class .= ' logichop-bb-hide';
		}

		if ( isset( $row->settings->logichop_condition ) && $row->settings->logichop_condition != '' ) {
			$this->row_count++;
			$class .= sprintf( ' lh-row lh-row-%s lh-%s', $this->row_count, $row->settings->logichop_condition );
		}

		return $class;
	}

	/**
	 * Add Logic Hop attributes to Beaver Builder rows in editor
	 *
	 * @since    	1.0.0
	 */
	public function add_row_attributes ( $attrs, $row ) {

		if ( ! $this->builder_active() ) {
			return $attrs;
		}

		if ( isset( $row->settings->logichop_condition ) && $row->settings->logichop_condition != '' ) {
			$attrs['data-logic-hop'] = $row->settings->logichop_condition;
			$attrs['data-logic-hop-name'] = $row->settings->logichop_name;
		}

		return $attrs;
	}

	/**
	 * Beaver Builder before row
	 *
	 * @since    	1.0.0
	 */
	public function before_render_row ( $row, $groups ) {

		if ( $this->builder_active() ) {
			return;
		}

		$condition = ( isset( $row->settings->logichop_condition ) ) ? $row->settings->logichop_condition : '';
		$condition_not = ( isset( $row->settings->logichop_condition_not ) ) ? $row->settings->logichop_condition_not : 'met';

		if ( $condition != '' ) {
			printf( '{%% if condition: %s%s %%}',
					( $condition_not == 'not_met' ) ? '!' : '',
					$condition
				);
		}
 	}

	/**
	 * Beaver Builder after row
	 *
	 * @since    	1.0.0
	 */
	public function after_render_row ( $row, $groups ) {

		if ( $this->builder_active() ) {
			return;
		}

		$condition = ( isset( $row->settings->logichop_condition ) ) ? $row->settings->logichop_condition : '';

		if ( $condition != '' ) {
			print( '{% endif %}' );
		}
 	}

	/**
	 * Beaver Builder before module
	 *
	 * @since    	1.0.0
	 */
	public function before_render_module ( $module ) {

		if ( $this->builder_active() ) {
			return;
		}

		$condition = ( isset( $module->settings->logichop_condition ) ) ? $module->settings->logichop_condition : '';
		$condition_not = ( isset( $module->settings->logichop_condition_not ) ) ? $module->settings->logichop_condition_not : 'met';

		if ( $condition != '' ) {
			printf( '{%% if condition: %s%s %%}',
					( $condition_not == 'not_met' ) ? '!' : '',
					$condition
				);
		}
 	}

	/**
	 * Beaver Builder after module
	 *
	 * @since    	1.0.0
	 */
	public function after_render_module ( $module ) {

		if ( $this->builder_active() ) {
			return;
		}

		$condition = ( isset( $module->settings->logichop_condition ) ) ? $module->settings->logichop_condition : '';

		if ( $condition != '' ) {
			print( '{% endif %}' );
		}
 	}

	/**
	 * Process Beaver Builder content for Logic Tags
	 * Added to support Beaver Themer
	 *
	 * @since    	1.0.0
	 */
	public function content_filter ( $content ) {
		if ( ! $this->public ) {
			return $content;
		}
		return $this->public->content_filter( $content );
	}

	/**
	 * Render Beaver Builder module
	 *
	 * @since    	1.0.0
	 */
	public function module_html_content ( $out, $module ) {

		if ( $this->builder_active() ) {
			return $out;
		}

		$condition = ( isset( $module->settings->logichop_condition ) ) ? $module->settings->logichop_condition : '';
		$condition_not = ( isset( $module->settings->logichop_condition_not ) ) ? $module->settings->logichop_condition_not : 'met';

		if ( $condition != '' ) {
			return sprintf( '{%% if condition: %s%s %%}%s{%% endif %%}',
					( $condition_not == 'not_met' ) ? '!' : '',
					$condition,
					$out
				);
		}

 		return $out;
 	}

}

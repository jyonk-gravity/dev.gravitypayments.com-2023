<?php
namespace FileBird\Support;

use FileBird\Controller\Folder;

defined( 'ABSPATH' ) || exit;

class PageBuilders {
	protected $folderController;

	public function __construct() {
		$this->folderController = Folder::getInstance();
		add_action( 'init', array( $this, 'prepareRegister' ) );
	}

	public function prepareRegister() {
		// Compatible for Elementor
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$this->registerForElementor();
		}
		// Compatible for WPBakery - Work normally

		// Compatible for Beaver Builder
		if ( class_exists( 'FLBuilderLoader' ) ) {
			$this->registerForBeaver();
		}

		// Brizy Builder
		if ( class_exists( 'Brizy_Editor' ) ) {
			$this->registerForBrizy();
		}

		// Cornerstone
		if ( class_exists( 'Cornerstone_Plugin' ) ) {
			$this->registerCornerstone();
		}

		// Compatible for Divi
		if ( class_exists( 'ET_Builder_Element' ) ) {
			$this->registerForDivi();
		}

		// Compatible for Thrive
		if ( defined( 'TVE_IN_ARCHITECT' ) || class_exists( 'Thrive_Quiz_Builder' ) ) {
			$this->registerForThrive();
		}

		// Fusion Builder
		if ( class_exists( 'Fusion_Builder_Front' ) ) {
			$this->registerForFusion();
		}

		// Avada Theme
		if ( ! class_exists( 'Fusion_Builder_Front' ) && defined( 'AVADA_VERSION' ) ) {
			$this->registerAvada();
		}

		// Oxygen Builder
		if ( defined( 'CT_VERSION' ) ) {
			$this->registerOxygenBuilder();
		}

		// Tatsu Builder
		if ( defined( 'TATSU_VERSION' ) ) {
			$this->registerTatsuBuilder();
		}

		// Dokan plugin
		if ( defined( 'DOKAN_PLUGIN_VERSION' ) ) {
			$this->registerForDokan();
		}

		// Themify
		if ( defined( 'THEMIFY_VERSION' ) && class_exists( 'Themify_Builder_Model' ) ) {
			$this->registerThemify();
		}

		// Bricks
		if ( defined( 'BRICKS_VERSION' ) ) {
			$this->registerBricksBuilder();
		}
	}

	public function enqueueScripts() {
		$this->folderController->enqueueAdminScripts( 'pagebuilders' );
	}

	public function registerForElementor() {
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'enqueueScripts' ) );
	}

	public function registerForBeaver() {
		add_action( 'fl_before_sortable_enqueue', array( $this, 'enqueueScripts' ) );
	}

	public function registerForBrizy() {
		add_action( 'brizy_editor_enqueue_scripts', array( $this, 'enqueueScripts' ) );
	}

	public function registerCornerstone() {
		add_action( 'cornerstone_before_wp_editor', array( $this, 'enqueueScripts' ) );
	}

	public function registerForDivi() {
		add_action(
			'et_fb_enqueue_assets',
			function() {
				$this->enqueueScripts();
			}
		);
	}

	public function registerForThrive() {
		add_action( 'tcb_main_frame_enqueue', array( $this, 'enqueueScripts' ) );
	}

	public function registerForFusion() {
		add_action( 'fusion_builder_enqueue_live_scripts', array( $this, 'enqueueScripts' ) );
	}

	public function registerOxygenBuilder() {
		add_action( 'oxygen_enqueue_ui_scripts', array( $this, 'enqueueScripts' ) );
	}

	public function registerTatsuBuilder() {
		add_action( 'tatsu_builder_footer', array( $this, 'enqueueScripts' ) );
	}

	public function registerForDokan() {
		add_action(
			'dokan_enqueue_scripts',
			function() {
				if ( function_exists( 'dokan_is_seller_dashboard' ) ) {
					if ( ( dokan_is_seller_dashboard() || ( get_query_var( 'edit' ) && is_singular( 'product' ) ) ) || apply_filters( 'dokan_forced_load_scripts', false ) ) {
						$this->enqueueScripts();
					}
				}
			}
		);
	}

	public function registerThemify() {
		$is_admin   = is_admin() === true && \Themify_Builder_Model::hasAccess();
		$is_builder = \Themify_Builder_Model::is_frontend_editor_page();
		if ( $is_admin || $is_builder ) {
			if ( ! $is_admin ) {
				wp_enqueue_media();
			}
			add_action( 'themify_body_end', array( $this, 'enqueueScripts' ) );
		}
	}

	public function registerBricksBuilder() {
		if ( function_exists( 'bricks_is_builder' ) && \bricks_is_builder() ) {
			add_action( 'bricks_after_footer', array( $this, 'enqueueScripts' ) );
		}
	}

	public function registerAvada() {
		add_action( 'fusion_enqueue_live_scripts', array( $this, 'enqueueScripts' ) );
	}
}
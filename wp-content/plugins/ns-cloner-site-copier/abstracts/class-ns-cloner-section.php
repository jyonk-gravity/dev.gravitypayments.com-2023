<?php
/**
 * Cloner Section base class.
 *
 * @package NS_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Base class for all NS Cloner sections (self contained steps in the cloning process)
 *
 * Child classes will most commonly provide process_init(), render() and validate() functions.
 * See below for details on those - they  will all automatically be called at proper point.
 * Naming is critical, because we load everything dynamically / automatically. If properly named, sections can
 * be added to the Cloner without modifying any original code, just by using the add-on architecture and hooks.
 * General rules:
 * - the file name must be in the format 'ns-cloner-section-new-stuff.php'
 * - the class name must be in the format 'NS_Cloner_Section_New_Stuff'
 * - the $id property must be in the format 'new_stuff'
 */
abstract class NS_Cloner_Section {

	/**
	 *  Slug to use as id for the html section and any other time section needs to be referenced.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Array of mode ids that are supported by this section
	 *
	 * @var array
	 */
	public $modes_supported = array();

	/**
	 * Display order relative to other sections (lower number is displayed higher on page).
	 *
	 * @var int
	 */
	public $ui_priority;

	/**
	 * List of errors populated by the validate() method.
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * NS_Cloner_Section constructor.
	 */
	public function __construct() {
		// Automatically register these hooks so children don't have to repeat.
		add_action( 'ns_cloner_render_sections', array( $this, 'render' ), $this->ui_priority );
		// Enable new modes to use existing sections by adding supported modes.
		$this->modes_supported = apply_filters( "ns_cloner_section_{$this->id}_modes_supported", $this->modes_supported );
	}


	/**
	 * Call the process_init() method, but only if the current clone mode is supported by this section.
	 *
	 * This function gets called via NS_Cloner_Manager->doing_cloning() for each cloning process session
	 * (e.g. once when process started, once when each background process is handled, when a progress check
	 * triggers finish() in the process manager, etc). See doing_cloning() for more info.
	 */
	public function maybe_process_init() {
		if ( ns_cloner_request()->is_mode( $this->modes_supported ) ) {
			ns_cloner()->log->log( "ENTERING *process_init* for the *{$this->id}* section." );
			$this->process_init();
		}
	}

	/**
	 * Do any setup before starting the cloning process.
	 *
	 * This is where sections should add register any cloning process related hooks.
	 */
	public function process_init() {
	}

	/**
	 * Output first part of HTML for section settings page on the cloner admin page.
	 *
	 * @param string $title Title to be shown on admin page settings box.
	 * @param string $step_label Label to be shown in list of steps in clone bar at the bottom of the admin screen.
	 * @param string $help_text Additional help text to explain section usage.
	 */
	public function open_section_box( $title, $step_label = '', $help_text = '' ) {
		$mode_list = join( ' ', $this->modes_supported );
		?>
		<section class="ns-cloner-section" id="ns-cloner-section-<?php echo esc_attr( $this->id ); ?>" data-modes="<?php echo esc_attr( $mode_list ); ?>" data-button-step="<?php echo esc_attr( $step_label ); ?>">
		<div class="ns-cloner-section-header">
			<h4><?php echo esc_html( $title ); ?></h4>
			<span class="ns-cloner-section-help"><?php echo esc_html( $help_text ); ?></span>
			<span class="ns-cloner-section-collapse"></span>
		</div>
		<div class="ns-cloner-section-content">
		<?php
		do_action( "ns_cloner_open_section_box_{$this->id}" );
	}

	/**
	 * Output the HTML content of the section settings box on the cloner admin page.
	 */
	public function render() {
	}

	/**
	 * Output last part of HTML for section settings page on the cloner admin page.
	 */
	public function close_section_box() {
		do_action( "ns_cloner_close_section_box_{$this->id}" );
		?>
		</div><!-- /.ns-cloner-section-content -->
		</section><!-- /.ns-cloner-section -->
		<?php
	}

	/**
	 * Check ns_cloner_request() and any validation error messages to $this->errors
	 *
	 * @return void
	 */
	public function validate() {
	}

	/**
	 * Get array of error messages for this section.
	 *
	 * @return array
	 */
	public function get_errors() {
		return $this->errors;
	}
}

?>

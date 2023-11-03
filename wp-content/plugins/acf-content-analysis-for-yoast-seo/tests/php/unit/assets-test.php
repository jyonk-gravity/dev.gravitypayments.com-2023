<?php

namespace Yoast\WP\ACF\Tests;

use Brain\Monkey\Functions;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Yoast_ACF_Analysis_Assets;

/**
 * Class Assets_Test.
 *
 * @covers Yoast_ACF_Analysis_Assets
 */
class Assets_Test extends TestCase {

	/**
	 * Whether or not to preserve the global state.
	 *
	 * @var bool
	 */
	protected $preserveGlobalState = false;

	/**
	 * Whether or not to run each test in a separate process.
	 *
	 * @var bool
	 */
	protected $runTestInSeparateProcess = true;

	/**
	 * Test the init hook and determines whether the proper assets are loaded.
	 *
	 * @return void
	 */
	public function testInitHook() {
		\define( 'AC_SEO_ACF_ANALYSIS_PLUGIN_FILE', '/directory/file' );
		Functions\expect( 'get_plugin_data' )
			->once()
			->with( \AC_SEO_ACF_ANALYSIS_PLUGIN_FILE )
			->andReturn(
				[
					'Version' => '2.0.0',
				]
			);

		$testee = new Yoast_ACF_Analysis_Assets();
		$testee->init();

		$this->assertSame( 11, \has_action( 'admin_enqueue_scripts', [ $testee, 'enqueue_scripts' ] ) );
	}
}

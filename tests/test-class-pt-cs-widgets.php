<?php

class PT_CS_Widgets_Test extends WP_UnitTestCase {

	/**
	 * Test if the widget file exists.
	 */
	function test_main_plugin_file_exists() {
		$this->assertFileExists( PT_CS_VIEWS_DIR . 'widgets.php', 'Widgets file: views/widgets.php is missing!' );
	}
}

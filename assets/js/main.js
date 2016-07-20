/* global BoltsSliderCaptions, BoltsVars */

// config
require.config( {
	paths: {
		jquery:        'assets/js/src/fix.jquery',
		ptcs:          'assets/js/src/pt-custom-sidebars',
		wpmuui:        'assets/js/src/wpmu-ui',
	},
	shim: {
		ptcs: {
			deps: [
				'wpmuui',
			]
		},
	}
} );

require( [
	'jquery',
	'wpmuui',
	'ptcs',
] );

/* global BoltsSliderCaptions, BoltsVars */

// config
require.config( {
	paths: {
		jquery:        'assets/js/fix.jquery',
		ptcs:          'assets/js/pt-custom-sidebars',
		wpmuui:        'assets/js/wpmu-ui',
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

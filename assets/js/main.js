/* global BoltsSliderCaptions, BoltsVars */

// config
require.config( {
	paths: {
		jquery:        'assets/js/fix.jquery',
		ptcs:          'assets/js/pt-custom-sidebars',
		wpmuui:        'assets/js/wpmu-ui',
		select2:       'assets/js/select2',
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
	'select2',
	'ptcs',
] );

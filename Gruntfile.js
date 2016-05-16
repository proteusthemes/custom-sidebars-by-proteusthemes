module.exports = function ( grunt ) {
	// Auto-load the needed grunt tasks
	// require('load-grunt-tasks')(grunt);
	require( 'load-grunt-tasks' )( grunt, { pattern: ['grunt-*'] } );

	var config = {
		tmpdir:                  '.tmp/',
		phpFileRegex:            '[^/]+\.php$',
		phpFileInSubfolderRegex: '.*?\.php$',
		pluginSlug:              'proteusthemes-custom-sidebars',
	};

	// configuration
	grunt.initConfig( {
		pgk: grunt.file.readJSON( 'package.json' ),

		config: config,

		// requireJS optimizer
		// https://github.com/gruntjs/grunt-contrib-requirejs
		requirejs: {
			build: {
				// Options: https://github.com/jrburke/r.js/blob/master/build/example.build.js
				options: {
					baseUrl:                 '',
					mainConfigFile:          'assets/js/main.js',
					optimize:                'uglify2',
					preserveLicenseComments: true,
					useStrict:               true,
					wrap:                    true,
					name:                    'bower_components/almond/almond',
					include:                 'assets/js/main',
					out:                     'assets/js/main.min.js'
				}
			}
		},

		// https://github.com/gruntjs/grunt-contrib-cssmin
		cssmin: {
			options: {
				shorthandCompacting: false,
				roundingPrecision: -1
			},
			target: {
				files: {
					'assets/css/main.min.css': ['assets/css/src/*.css']
				}
			}
		},

		// // https://github.com/gruntjs/grunt-contrib-copy
		// copy: {
		// 	// create new directory for deployment
		// 	build: {
		// 		expand: true,
		// 		dot:    false,
		// 		dest:   config.pluginSlug + '/',
		// 		src:    [
		// 			'*.php',
		// 			'readme.txt',
		// 			'assets/**',
		// 			'inc/**',
		// 			'languages/**',
		// 		],
		// 		flatten: false
		// 	}
		// },

		// https://www.npmjs.com/package/grunt-wp-i18n
		makepot: {
			plugin: {
				options: {
					domainPath:      'languages/',
					include:         [config.phpFileRegex, '^inc/'+config.phpFileInSubfolderRegex, '^views/'+config.phpFileInSubfolderRegex],
					mainFile:        config.pluginSlug + '.php',
					potComments:     'Copyright (C) {year} ProteusThemes \n# This file is distributed under the GPL 2.0.',
					potFilename:     'pt-cs.pot',
					potHeaders:      {
						poedit:                 true,
						'report-msgid-bugs-to': 'http://support.proteusthemes.com/',
					},
					type:            'wp-plugin',
					updateTimestamp: false,
					updatePoFiles:   true,
				}
			},
		},

		// https://www.npmjs.com/package/grunt-po2mo
		po2mo: {
			files: {
				src:    'languages/*.po',
				expand: true,
			},
		},

		wp_readme_to_markdown: {
			your_target: {
				files: {
					'readme.md': 'readme.txt'
				},
			},
		},

	} );

	// Main build task
	grunt.registerTask( 'build', [
		'requirejs:build',
		'cssmin',
	] );

	// update languages files
	grunt.registerTask( 'plugin_i18n', [
		'makepot:plugin',
		'po2mo',
	] );

	// update languages files
	grunt.registerTask( 'readme', [
		'wp_readme_to_markdown',
	] );

};
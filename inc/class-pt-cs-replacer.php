<?php
/**
 * This class actually replaces sidebars on the frontend. *Singleton*
 *
 * @package pt-cs
 */

// Initialize this class in the main plugin class.
add_action( 'pt_cs_init', array( 'PT_CS_Replacer', 'instance' ) );

/**
 * This class actually replaces sidebars on the frontend.
 */
class PT_CS_Replacer extends PT_CS_Main {

	private $original_post_id = 0;

	/**
	 * Returns the singleton object.
	 */
	public static function instance() {
		static $inst = null;

		if ( null === $inst ) {
			$inst = new PT_CS_Replacer();
		}

		return $inst;
	}

	/**
	 * Constructor is private -> singleton.
	 */
	private function __construct() {
		add_action( 'widgets_init', array( $this, 'register_custom_sidebars' ) );

		// Frontend hooks.
		if ( ! is_admin() ) {
			add_action( 'wp_head', array( $this, 'replace_sidebars' ) );
			add_action( 'wp', array( $this, 'store_original_post_id' ) );
		}
	}

	/**
	 * Tell WordPress about the custom sidebars.
	 */
	public function register_custom_sidebars() {
		$sb = self::get_custom_sidebars();

		foreach ( $sb as $sidebar ) {
			/**
			 * Filter sidebar options for custom sidebars.
			 *
			 * @param  array $sidebar Options used by WordPress to display the sidebar.
			 */
			$sidebar = apply_filters( 'cs_sidebar_params', $sidebar );

			register_sidebar( $sidebar );
		}
	}

	/**
	 * Stores the original post id before any plugin (buddypress) can modify this data, to show the proper sidebar.
	 */
	public function store_original_post_id() {
		global $post;

		if ( isset( $post->ID ) ) {
			$this->original_post_id = $post->ID;
		}
	}

	/**
	 * Replace the sidebars on current page with some custom sidebars.
	 * Sidebars are replaced by directly modifying the WordPress globals
	 * `$_wp_sidebars_widgets` and `$wp_registered_sidebars`
	 *
	 * What it really does: it not replacing a specific *sidebar* but simply
	 * replacing all widgets inside the theme sidebars with the widgets of the
	 * custom defined sidebars.
	 */
	public function replace_sidebars() {
		global $_wp_sidebars_widgets,
			$wp_registered_sidebars,
			$wp_registered_widgets;

		$expl = CustomSidebarsExplain::do_explain();

		$expl && do_action( 'cs_explain', '<h4>Replace sidebars</h4>', true );

		do_action( 'cs_before_replace_sidebars' );

		/**
		 * Original sidebar configuration by WordPress:
		 * Lists sidebars and all widgets inside each sidebar.
		 */
		$original_widgets = $_wp_sidebars_widgets;

		$defaults = self::get_options();

		/**
		 * Fires before determining sidebar replacements.
		 *
		 * @param  array $defaults Array of the default sidebars for the page.
		 */
		do_action( 'cs_predetermine_replacements', $defaults );

		$replacements = $this->determine_replacements( $defaults );

		foreach ( $replacements as $sb_id => $replace_info ) {
			if ( ! is_array( $replace_info ) || count( $replace_info ) < 3 ) {
				$expl && do_action( 'cs_explain', 'Replacement for "' . $sb_id . '": -none-' );
				continue;
			}

			// Fix rare message "illegal offset type in isset or empty".
			$replacement = (string) @$replace_info[0];
			$replacement_type = (string) @$replace_info[1];
			$extra_index = (string) @$replace_info[2];

			$check = $this->is_valid_replacement( $sb_id, $replacement, $replacement_type, $extra_index );

			if ( $check ) {
				$expl && do_action( 'cs_explain', 'Replacement for "' . $sb_id . '": ' . $replacement );

				if ( 0 === count( $original_widgets[ $replacement ] ) ) {

					// No widgets on custom sidebar, show nothing.
					$wp_registered_widgets['csemptywidget'] = $this->get_empty_widget();
					$_wp_sidebars_widgets[ $sb_id ] = array( 'csemptywidget' );
				} else {
					$_wp_sidebars_widgets[ $sb_id ] = $original_widgets[ $replacement ];

					/**
					 * When custom sidebars use some wrapper code (before_title,
					 * after_title, ...) then we need to strip-slashes for this
					 * wrapper code to work properly
					 */
					$sidebar_for_replacing = $wp_registered_sidebars[ $replacement ];
					if ( $this->has_wrapper_code( $sidebar_for_replacing ) ) {
						$sidebar_for_replacing = $this->clean_wrapper_code( $sidebar_for_replacing );
						$wp_registered_sidebars[ $sb_id ] = $sidebar_for_replacing;
					}
				}
				$wp_registered_sidebars[ $sb_id ]['class'] = $replacement;
			} else {
				$expl && do_action( 'cs_explain', 'Replacement for "' . $sb_id . '": -none-' );
			}
		}
	}

	/**
	 * THIS IS THE ACTUAL LOGIC OF THE PLUGIN
	 *
	 * Here we find out if some sidebars should be replaced, and if it is
	 * replaced we determine which custom sidebar to use.
	 *
	 * @param   array $options Plugin options with the replacement rules.
	 * @return  array List of the replaced sidebars.
	 */
	public function determine_replacements( $options ) {
		global $post,
			$sidebar_category;

		$sidebars          = self::get_options( 'modifiable' );
		$replacements_todo = count( $sidebars );
		$replacements      = array();
		$expl              = CustomSidebarsExplain::do_explain();

		foreach ( $sidebars as $sb ) {
			$replacements[ $sb ] = false;
		}

		if ( is_single() ) {
			/*
			 * 1 |== Single posts/pages --------------------------------------------
			 */
			$post_type = get_post_type();
			$expl && do_action( 'cs_explain', 'Type 1: Single ' . ucfirst( $post_type ) );

			if ( ! self::supported_post_type( $post_type ) ) {
				$expl && do_action( 'cs_explain', 'Invalid post type, use default sidebars.' );
				return $options;
			}

			// 1.1 Check if replacements are defined in the post metadata.
			$reps = self::get_post_meta( $this->original_post_id );
			foreach ( $sidebars as $sb_id ) {
				if ( is_array( $reps ) && ! empty( $reps[ $sb_id ] ) ) {
					$replacements[ $sb_id ] = array(
						$reps[ $sb_id ],
						'particular',
						-1,
					);
					$replacements_todo -= 1;
				}
			}

			// 1.2 Try to use the parents metadata.
			if ( 0 !== $post->post_parent && $replacements_todo > 0 ) {
				$reps = self::get_post_meta( $post->post_parent );
				foreach ( $sidebars as $sb_id ) {
					if ( $replacements[ $sb_id ] ) { continue; }
					if ( is_array( $reps ) && ! empty( $reps[ $sb_id ] ) ) {
						$replacements[ $sb_id ] = array(
							$reps[ $sb_id ],
							'particular',
							-1,
						);
						$replacements_todo -= 1;
					}
				}
			}

			// 1.3 If no metadata set then use the category settings.
			if ( $replacements_todo > 0 ) {
				$categories = self::get_sorted_categories();
				$ind        = count( $categories ) - 1;
				while ( $replacements_todo > 0 && $ind >= 0 ) {
					$cat_id = $categories[ $ind ]->cat_ID;
					foreach ( $sidebars as $sb_id ) {
						if ( $replacements[ $sb_id ] ) { continue; }
						if ( ! empty( $options['category_single'][ $cat_id ][ $sb_id ] ) ) {
							$replacements[ $sb_id ] = array(
								$options['category_single'][ $cat_id ][ $sb_id ],
								'category_single',
								$sidebar_category,
							);
							$replacements_todo -= 1;
						}
					}
					$ind -= 1;
				}
			}

			// 1.4 Look for post-type level replacements.
			if ( $replacements_todo > 0 ) {
				foreach ( $sidebars as $sb_id ) {
					if ( $replacements[ $sb_id ] ) { continue; }
					if ( isset( $options['post_type_single'][ $post_type ] ) && ! empty( $options['post_type_single'][ $post_type ][ $sb_id ] ) ) {
						$replacements[ $sb_id ] = array(
							$options['post_type_single'][ $post_type ][ $sb_id ],
							'post_type_single',
							$post_type,
						);
						$replacements_todo -= 1;
					}
				}
			}
		} else if ( is_category() ) {
			/*
			 * 2 |== Category archive ----------------------------------------------
			 */
			$expl && do_action( 'cs_explain', 'Type 2: Category Archive' );

			// 2.1 Start at current category and travel up all parents
			$category_object = get_queried_object();
			$current_category = $category_object->term_id;
			while ( 0 !== $current_category && $replacements_todo > 0 ) {
				foreach ( $sidebars as $sb_id ) {
					if ( $replacements[ $sb_id ] ) { continue; }
					if ( ! empty( $options['category_archive'][ $current_category ][ $sb_id ] ) ) {
						$replacements[ $sb_id ] = array(
							$options['category_archive'][ $current_category ][ $sb_id ],
							'category_archive',
							$current_category,
						);
						$replacements_todo -= 1;
					}
				}
				$current_category = $category_object->category_parent;
				if ( 0 !== $current_category ) {
					$category_object = get_category( $current_category );
				}
			}
		} else if ( is_search() ) {
			/*
			 * 3 |== Search --------------------------------------------------------
			 * Must be before the post-type archive section; otherwise a search with
			 * no results is recognized as post-type archive...
			 */
			$expl && do_action( 'cs_explain', 'Type 3: Search Results' );

			foreach ( $sidebars as $sb_id ) {
				if ( ! empty( $options['search'][ $sb_id ] ) ) {
					$replacements[ $sb_id ] = array(
						$options['search'][ $sb_id ],
						'search',
						-1,
					);
				}
			}
		} else if ( ! is_category() && ! is_singular() && 'post' !== get_post_type() ) {
			/*
			 * 4 |== Post-Tpe Archive ----------------------------------------------
			 * `get_post_type() != 'post'` .. post-archive = post-index (see 7)
			 */
			$post_type = get_post_type();
			$expl && do_action( 'cs_explain', 'Type 4: ' . ucfirst( $post_type ) . ' Archive' );

			if ( ! self::supported_post_type( $post_type ) ) {
				$expl && do_action( 'cs_explain', 'Invalid post type, use default sidebars.' );
				return $options;
			}

			foreach ( $sidebars as $sb_id ) {
				if (
					isset( $options['post_type_archive'][ $post_type ] )
					&& ! empty( $options['post_type_archive'][ $post_type ][ $sb_id ] )
				) {
					$replacements[ $sb_id ] = array(
						$options['post_type_archive'][ $post_type ][ $sb_id ],
						'post_type_archive',
						$post_type,
					);
					$replacements_todo -= 1;
				}
			}
		} else if ( is_page() && ! is_front_page() ) {
			/*
			 * 5 |== Page ----------------------------------------------------------
			 * `! is_front_page()` .. in case the site uses static front page.
			 */
			$post_type = get_post_type();
			$expl && do_action( 'cs_explain', 'Type 5: ' . ucfirst( $post_type ) );

			if ( ! self::supported_post_type( $post_type ) ) {
				$expl && do_action( 'cs_explain', 'Invalid post type, use default sidebars.' );
				return $options;
			}

			// 5.1 Check if replacements are defined in the post metadata.
			$reps = self::get_post_meta( $this->original_post_id );
			foreach ( $sidebars as $sb_id ) {
				if ( is_array( $reps ) && ! empty( $reps[ $sb_id ] ) ) {
					$replacements[ $sb_id ] = array(
						$reps[ $sb_id ],
						'particular',
						-1,
					);
					$replacements_todo -= 1;
				}
			}

			// 5.2 Try to use the parents metadata.
			if ( $post->post_parent != 0 && $replacements_todo > 0 ) {
				$reps = self::get_post_meta( $post->post_parent );
				foreach ( $sidebars as $sb_id ) {
					if ( $replacements[$sb_id] ) { continue; }
					if ( is_array( $reps )
						&& ! empty( $reps[$sb_id] )
					) {
						$replacements[$sb_id] = array(
							$reps[$sb_id],
							'particular',
							-1,
						);
						$replacements_todo -= 1;
					}
				}
			}

			// 5.3 Look for post-type level replacements.
			if ( $replacements_todo > 0 ) {
				foreach ( $sidebars as $sb_id ) {
					if ( $replacements[ $sb_id ] ) { continue; }
					if ( isset( $options['post_type_single'][ $post_type ] )
						&& ! empty( $options['post_type_single'][ $post_type ][ $sb_id ] )
					) {
						$replacements[ $sb_id ] = array(
							$options['post_type_single'][ $post_type ][ $sb_id ],
							'post_type_single',
							$post_type,
						);
						$replacements_todo -= 1;
					}
				}
			}
		} else if ( is_front_page() ) {
			/*
			 * 6 |== Front Page ----------------------------------------------------
			 * The front-page of the site. Either the post-index (default) or a static front-page.
			 */
			$expl && do_action( 'cs_explain', 'Type 6: Front Page' );

			if ( ! is_home() ) {
				// A static front-page. Maybe we need the post-meta data...
				$reps_post   = self::get_post_meta( $this->original_post_id );
				$reps_parent = self::get_post_meta( $post->post_parent );
			}

			foreach ( $sidebars as $sb_id ) {

				// First check if there is a 'Front Page' replacement.
				if ( ! empty( $options['blog'][ $sb_id ] ) ) {
					$replacements[ $sb_id ] = array(
						$options['blog'][ $sb_id ],
						'blog',
						-1,
					);
				} else if ( ! is_home() ) {
					// There is no 'Front Page' reaplcement and this is a static
					// front page, so check if the page has a replacement.

					// 6.1 Check if replacements are defined in the post metadata.
					if ( is_array( $reps_post ) && ! empty( $reps_post[ $sb_id ] ) ) {
						$replacements[ $sb_id ] = array(
							$reps_post[ $sb_id ],
							'particular',
							-1,
						);
						$replacements_todo -= 1;
					}

					// 6.2 Try to use the parents metadata.
					if ( 0 !== $post->post_parent && $replacements_todo > 0 ) {
						if ( $replacements[ $sb_id ] ) { continue; }
						if ( is_array( $reps_parent ) && ! empty( $reps_parent[ $sb_id ] ) ) {
							$replacements[ $sb_id ] = array(
								$reps_parent[ $sb_id ],
								'particular',
								-1,
							);
							$replacements_todo -= 1;
						}
					}
				}
			}
		} else if ( is_home() ) {
			/*
			 * 7 |== Post Index ----------------------------------------------------
			 * The post-index of the site. Either
			 * - the front-page (default)
			 * - when a static front page is used the post-index page.
			 *
			 * Note: When the default front-page is used the condition 6
			 * "is_front_page" above is used and this node is never executed.
			 */

			$expl && do_action( 'cs_explain', 'Type 7: Post Index' );

			foreach ( $sidebars as $sb_id ) {
				if ( ! empty( $options['post_type_archive']['post'][ $sb_id ] ) ) {
					$replacements[ $sb_id ] = array(
						$options['post_type_archive']['post'][ $sb_id ],
						'postindex',
						-1,
					);
				}
			}
		} else if ( is_tag() ) {
			/*
			 * 8 |== Tag archive ---------------------------------------------------
			 */
			$expl && do_action( 'cs_explain', 'Type 8: Tag Archive' );

			foreach ( $sidebars as $sb_id ) {
				if ( ! empty( $options['tags'][ $sb_id ] ) ) {
					$replacements[ $sb_id ] = array(
						$options['tags'][ $sb_id ],
						'tags',
						-1,
					);
				}
			}
		} else if ( is_author() ) {
			/*
			 * 9 |== Author archive ------------------------------------------------
			 */
			$author_object  = get_queried_object();
			$current_author = $author_object->ID;
			$expl && do_action( 'cs_explain', 'Type 9: Author Archive' );

			// 9.2 Then check if there is an "Any authors" sidebar
			if ( $replacements_todo > 0 ) {
				foreach ( $sidebars as $sb_id ) {
					if ( $replacements[ $sb_id ] ) { continue; }
					if ( ! empty( $options['authors'][ $sb_id ] ) ) {
						$replacements[ $sb_id ] = array(
							$options['authors'][ $sb_id ],
							'authors',
							-1,
						);
					}
				}
			}
		} else if ( is_date() ) {
			/*
			 * 10 |== Date archive -------------------------------------------------
			 */
			$expl && do_action( 'cs_explain', 'Type 10: Date Archive' );

			foreach ( $sidebars as $sb_id ) {
				if ( ! empty( $options['date'][ $sb_id ] ) ) {
					$replacements[ $sb_id ] = array(
						$options['date'][ $sb_id ],
						'date',
						-1,
					);
				}
			}
		} else if ( is_404() ) {
			/*
			 * 11 |== 404 not found ------------------------------------------------
			 */
			$expl && do_action( 'cs_explain', 'Type 11: 404 not found' );

			foreach ( $sidebars as $sb_id ) {
				if ( ! empty( $options['404'][ $sb_id ] ) ) {
					$replacements[ $sb_id ] = array(
						$options['404'][ $sb_id ],
						'404',
						-1,
					);
				}
			}
		}

		/**
		 * Filter the replaced sidebars before they are processed by the plugin.
		 *
		 * @param  array $replacements List of the final/replaced sidebars.
		 */
		$replacements = apply_filters( 'cs_replace_sidebars', $replacements );

		return $replacements;
	}



	/**
	 * Makes sure that the replacement sidebar exists.
	 * If the custom sidebar does not exist then the WordPress/Post options are
	 * updated to remove the invalid option.
	 *
	 * @param string     $sb_id The original sidebar (the one that is replaced).
	 * @param string     $replacement ID of the custom sidebar that should be used.
	 * @param string     $method Info where the replacement setting is saved.
	 * @param int|string $extra_index Depends on $method - can be either one: empty/post-type/category-ID.
	 * @return bool
	 */
	public function is_valid_replacement( $sb_id, $replacement, $method, $extra_index ) {
		global $wp_registered_sidebars;
		$options = self::get_options();

		if ( isset( $wp_registered_sidebars[ $replacement ] ) ) {

			// Everything okay, we can use the replacement.
			return true;
		}

		/*
		 * The replacement sidebar was not registered. Something's wrong, so we
		 * update the options and not try to replace this sidebar again.
		 */
		if ( 'particular' == $method ) {

			// Invalid replacement was found in post-meta data.
			$sidebars = self::get_post_meta( $this->original_post_id );
			if ( $sidebars && isset( $sidebars[ $sb_id ] ) ) {
				unset( $sidebars[ $sb_id ] );
				self::set_post_meta( $this->original_post_id, $sidebars );
			}
		} else {

			// Invalid replacement is defined in wordpress options table.
			if ( isset( $options[ $method ] ) ) {
				if ( -1 != $extra_index &&
					isset( $options[ $method ][ $extra_index ] ) &&
					isset( $options[ $method ][ $extra_index ][ $sb_id ] )
				) {
					unset( $options[ $method ][ $extra_index ][ $sb_id ] );
					self::set_options( $options );
				}

				if ( 1 == $extra_index &&
					isset( $options[ $method ] ) &&
					isset( $options[ $method ][ $sb_id ] )
				) {
					unset( $options[ $method ][ $sb_id ] );
					self::set_options( $options );
				}
			}
		}

		return false;
	}

	/**
	 * Returns an empty dummy-widget. This dummy widget is used when a custom sidebar has no widgets.
	 */
	public function get_empty_widget() {
		$widget = new PT_CS_Empty_Widget();
		return array(
			'name'        => 'CS Empty Widget',
			'id'          => 'csemptywidget',
			'callback'    => array( $widget, 'display_callback' ),
			'params'      => array( array( 'number' => 2 ) ),
			'classname'   => 'PTCustomSidebarsEmptyWidget',
			'description' => 'CS dummy widget',
		);
	}

	/**
	 * Checks if the specified sidebar uses custom wrapper code.
	 *
	 * @param array $sidebar Sidebar data.
	 * @return bool
	 */
	public function has_wrapper_code( $sidebar ) {
		return ( strlen( trim( $sidebar['before_widget'] ) ) ||
			strlen( trim( $sidebar['after_widget'] ) ) ||
			strlen( trim( $sidebar['before_title'] ) ) ||
			strlen( trim( $sidebar['after_title'] ) )
		);
	}

	/**
	 * Clean the slashes of the custom sidebar wrapper code.
	 *
	 * @param array $sidebar Sidebar data.
	 */
	public function clean_wrapper_code( $sidebar ) {
		$sidebar['before_widget'] = stripslashes( $sidebar['before_widget'] );
		$sidebar['after_widget']  = stripslashes( $sidebar['after_widget'] );
		$sidebar['before_title']  = stripslashes( $sidebar['before_title'] );
		$sidebar['after_title']   = stripslashes( $sidebar['after_title'] );
		return $sidebar;
	}
}

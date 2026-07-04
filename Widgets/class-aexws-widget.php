<?php
/**
 * Archives Widget Extended widget class.
 *
 * @package Archives_Widget_Extended
 */

namespace AEXWS\Widgets;

use WP_Widget;

/**
 * Extended Archives widget.
 *
 * Mirrors the core WP_Widget_Archives output when only the native options
 * (title, dropdown, count) are set, and adds three options:
 *  - post_type: render archives for any public post type with has_archive=true.
 *  - extra_classes: additional CSS classes appended to the widget root.
 *  - item_classes: CSS classes appended to each <li> in the archives list.
 */
class AEXWS_Widget extends WP_Widget {

	/**
	 * Sets up the widget metadata and registers it with WP_Widget.
	 */
	public function __construct() {
		parent::__construct(
			'archives_extended',
			__( 'Archives Widget Extended', 'archives-extended' ),
			array(
				'classname'                   => 'widget_archive',
				'description'                 => __( 'An extended monthly archive list with custom post type and CSS class options.', 'archives-extended' ),
				'customize_selective_refresh' => true,
				'show_instance_in_rest'       => true,
			)
		);
	}

	/**
	 * Returns the post types eligible for archive rendering.
	 *
	 * Public post types with has_archive=true, plus `post` (which has its
	 * own archive at the blog home but does not carry has_archive=true).
	 *
	 * @return array<string, \WP_Post_Type>
	 */
	private function get_available_post_types(): array {
		$post_types = get_post_types(
			array(
				'public'      => true,
				'has_archive' => true,
			),
			'objects'
		);

		// `post` is always a valid archive target even though it doesn't carry
		// has_archive=true (its archive is the blog home).
		if ( ! isset( $post_types['post'] ) ) {
			$post_obj = get_post_type_object( 'post' );
			if ( $post_obj ) {
				$post_types = array( 'post' => $post_obj ) + $post_types;
			}
		}

		return $post_types;
	}

	/**
	 * Sanitizes a whitespace-separated class string into a normalized list.
	 *
	 * @param string $raw_classes Whitespace-separated class string from user input.
	 * @return string Space-separated list of unique, sanitized class tokens.
	 */
	private function sanitize_class_list( string $raw_classes ): string {
		$tokens = preg_split( '/\s+/', trim( $raw_classes ) );
		if ( false === $tokens ) {
			return '';
		}
		$sanitized = array();
		foreach ( $tokens as $token ) {
			if ( '' === $token ) {
				continue;
			}
			$clean = sanitize_html_class( $token );
			if ( '' !== $clean ) {
				$sanitized[] = $clean;
			}
		}
		return implode( ' ', array_unique( $sanitized ) );
	}

	/**
	 * Renders the widget output.
	 *
	 * @param array $args     Sidebar arguments (before_widget, after_widget, etc.).
	 * @param array $instance Saved widget settings.
	 */
	public function widget( $args, $instance ) {
		$default_title = __( 'Archives', 'archives-extended' );
		$title         = ! empty( $instance['title'] ) ? $instance['title'] : $default_title;

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$count         = ! empty( $instance['count'] ) ? '1' : '0';
		$dropdown      = ! empty( $instance['dropdown'] ) ? '1' : '0';
		$post_type     = ! empty( $instance['post_type'] ) ? (string) $instance['post_type'] : 'post';
		$extra_classes = ! empty( $instance['extra_classes'] ) ? (string) $instance['extra_classes'] : '';
		$item_classes  = ! empty( $instance['item_classes'] ) ? (string) $instance['item_classes'] : '';

		$available = $this->get_available_post_types();
		if ( ! isset( $available[ $post_type ] ) ) {
			$post_type = 'post';
		}

		// Inject extra classes into the root widget element by augmenting
		// the class="widget_archive" attribute in before_widget.
		$before_widget = $args['before_widget'];
		if ( '' !== $extra_classes ) {
			$before_widget = preg_replace(
				'/(class\s*=\s*")([^"]*\bwidget_archive\b[^"]*)(")/i',
				'$1$2 ' . esc_attr( $extra_classes ) . '$3',
				$before_widget,
				1
			);
		}

		echo $before_widget; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		// Hook for adding classes to each archive <li>. Registered only when
		// item_classes is non-empty and removed immediately after the call so
		// it never bleeds into other widgets.
		$item_filter = null;
		if ( '' !== $item_classes ) {
			$item_filter = static function ( $link_html ) use ( $item_classes ) {
				if ( str_contains( $link_html, '<li>' ) ) {
					return preg_replace(
						'/<li>/',
						'<li class="' . esc_attr( $item_classes ) . '">',
						$link_html,
						1
					);
				}
				if ( preg_match( '/<li\s+class\s*=\s*"([^"]*)"/i', $link_html ) ) {
					return preg_replace(
						'/<li\s+class\s*=\s*"([^"]*)"/i',
						'<li class="$1 ' . esc_attr( $item_classes ) . '"',
						$link_html,
						1
					);
				}
				return $link_html;
			};
		}

		if ( $dropdown ) {
			$dropdown_id = "{$this->id_base}-dropdown-{$this->number}";
			?>
			<label class="screen-reader-text" for="<?php echo esc_attr( $dropdown_id ); ?>"><?php echo esc_html( $title ); ?></label>
			<select id="<?php echo esc_attr( $dropdown_id ); ?>" name="archive-dropdown" data-aexws-dropdown>
				<?php
				/** This filter is documented in wp-includes/widgets/class-wp-widget-archives.php */
				$dropdown_args = apply_filters(
					'widget_archives_dropdown_args',
					array(
						'type'            => 'monthly',
						'format'          => 'option',
						'show_post_count' => $count,
						'post_type'       => $post_type,
					),
					$instance
				);

				switch ( $dropdown_args['type'] ) {
					case 'yearly':
						$label = __( 'Select Year', 'archives-extended' );
						break;
					case 'monthly':
						$label = __( 'Select Month', 'archives-extended' );
						break;
					case 'daily':
						$label = __( 'Select Day', 'archives-extended' );
						break;
					case 'weekly':
						$label = __( 'Select Week', 'archives-extended' );
						break;
					default:
						$label = __( 'Select Post', 'archives-extended' );
						break;
				}
				?>
				<option value=""><?php echo esc_html( $label ); ?></option>
				<?php wp_get_archives( $dropdown_args ); ?>
			</select>
			<?php
		} else {
			$format = current_theme_supports( 'html5', 'navigation-widgets' ) ? 'html5' : 'xhtml';

			/** This filter is documented in wp-includes/widgets/class-wp-nav-menu-widget.php */
			$format = apply_filters( 'navigation_widgets_format', $format );

			if ( 'html5' === $format ) {
				$title      = trim( wp_strip_all_tags( $title ) );
				$aria_label = $title ? $title : $default_title;
				echo '<nav aria-label="' . esc_attr( $aria_label ) . '">';
			}

			if ( null !== $item_filter ) {
				add_filter( 'get_archives_link', $item_filter );
			}
			?>
			<ul>
				<?php
				/** This filter is documented in wp-includes/widgets/class-wp-widget-archives.php */
				wp_get_archives(
					apply_filters(
						'widget_archives_args',
						array(
							'type'            => 'monthly',
							'show_post_count' => $count,
							'post_type'       => $post_type,
						),
						$instance
					)
				);
				?>
			</ul>
			<?php
			if ( null !== $item_filter ) {
				remove_filter( 'get_archives_link', $item_filter );
			}

			if ( 'html5' === $format ) {
				echo '</nav>';
			}
		}

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Sanitizes and saves widget instance settings.
	 *
	 * @param array $new_instance Submitted form values.
	 * @param array $old_instance Previously saved values.
	 * @return array Sanitized settings to persist.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance     = $old_instance;
		$new_instance = wp_parse_args(
			(array) $new_instance,
			array(
				'title'         => '',
				'count'         => 0,
				'dropdown'      => '',
				'post_type'     => 'post',
				'extra_classes' => '',
				'item_classes'  => '',
			)
		);

		$instance['title']    = sanitize_text_field( $new_instance['title'] );
		$instance['count']    = $new_instance['count'] ? 1 : 0;
		$instance['dropdown'] = $new_instance['dropdown'] ? 1 : 0;

		$available             = $this->get_available_post_types();
		$candidate             = (string) $new_instance['post_type'];
		$instance['post_type'] = isset( $available[ $candidate ] ) ? $candidate : 'post';

		$instance['extra_classes'] = $this->sanitize_class_list( (string) $new_instance['extra_classes'] );
		$instance['item_classes']  = $this->sanitize_class_list( (string) $new_instance['item_classes'] );

		return $instance;
	}

	/**
	 * Renders the widget configuration form in the admin sidebar.
	 *
	 * @param array $instance Saved widget settings.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'         => '',
				'count'         => 0,
				'dropdown'      => '',
				'post_type'     => 'post',
				'extra_classes' => '',
				'item_classes'  => '',
			)
		);

		$post_types = $this->get_available_post_types();
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'archives-extended' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>"><?php esc_html_e( 'Post type:', 'archives-extended' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_type' ) ); ?>">
				<?php foreach ( $post_types as $slug => $pt ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>"<?php selected( $instance['post_type'], $slug ); ?>><?php echo esc_html( $pt->labels->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'extra_classes' ) ); ?>"><?php esc_html_e( 'Additional container classes:', 'archives-extended' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'extra_classes' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'extra_classes' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['extra_classes'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'item_classes' ) ); ?>"><?php esc_html_e( 'Additional list item classes:', 'archives-extended' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'item_classes' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'item_classes' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['item_classes'] ); ?>" />
		</p>
		<p>
			<input class="checkbox" type="checkbox"<?php checked( $instance['dropdown'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'dropdown' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'dropdown' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'dropdown' ) ); ?>"><?php esc_html_e( 'Display as dropdown', 'archives-extended' ); ?></label>
			<br />
			<input class="checkbox" type="checkbox"<?php checked( $instance['count'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Show post counts', 'archives-extended' ); ?></label>
		</p>
		<?php
	}
}

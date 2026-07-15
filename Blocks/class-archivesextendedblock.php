<?php
/**
 * Archives Widget Extended block registration and server-side rendering.
 *
 * @package Archives_Widget_Extended
 */

namespace AEXWS\Blocks;

use WP_Post_Type;

/**
 * Server-rendered block that mirrors core/archives with three extras:
 * post type selection, root container classes, per-item classes.
 */
class ArchivesExtendedBlock {

	private const BLOCK_NAME = 'aex/archives-widget-extended';

	/**
	 * Allowed values for the `type` attribute.
	 */
	private const ALLOWED_TYPES = array( 'monthly', 'yearly', 'weekly', 'daily' );

	/**
	 *
	 * Default block initialization
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Registers the block type from its compiled metadata.
	 */
	public function register(): void {
		$metadata_dir = AEXWS_PLUGIN_DIR . 'Block/archives-widget-extended/build/archives-widget-extended';
		if ( ! file_exists( $metadata_dir . '/block.json' ) ) {
			return;
		}

		register_block_type_from_metadata(
			$metadata_dir,
			array(
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	/**
	 * Renders the block on the server.
	 *
	 * @param array<string, mixed> $attributes Block attributes (hydrated from block.json defaults by WP_Block).
	 */
	public function render( array $attributes ): string {
		$type                = $this->resolve_type( $attributes['type'] ?? 'monthly' );
		$show_post_count     = ! empty( $attributes['showPostCounts'] );
		$display_as_dropdown = ! empty( $attributes['displayAsDropdown'] );
		$show_label          = ! empty( $attributes['showLabel'] ) || ! isset( $attributes['showLabel'] );
		$post_type           = $this->resolve_post_type( (string) ( $attributes['postType'] ?? 'post' ) );
		$extra_classes       = $this->sanitize_class_list( (string) ( $attributes['extraClasses'] ?? '' ) );
		$item_classes        = $this->sanitize_class_list( (string) ( $attributes['itemClasses'] ?? '' ) );

		if ( $display_as_dropdown ) {
			return $this->render_dropdown( $type, $show_post_count, $show_label, $post_type, $extra_classes );
		}

		return $this->render_list( $type, $show_post_count, $post_type, $extra_classes, $item_classes );
	}

	/**
	 * Renders the dropdown variant.
	 *
	 * @param string $type            Archive grouping (monthly|yearly|weekly|daily).
	 * @param bool   $show_post_count Whether to append the post count to each option label.
	 * @param bool   $show_label      Whether the dropdown's <label> is visible or screen-reader-only.
	 * @param string $post_type       Validated post type slug to query archives for.
	 * @param string $extra_classes   Sanitized whitespace-separated class list to append to the wrapper.
	 */
	private function render_dropdown(
		string $type,
		bool $show_post_count,
		bool $show_label,
		string $post_type,
		string $extra_classes
	): string {
		$wrapper_class = 'wp-block-archives-dropdown wp-block-aex-archives-widget-extended-dropdown';
		if ( '' !== $extra_classes ) {
			$wrapper_class .= ' ' . $extra_classes;
		}

		/**
		 * Again, we're keeping the original widget structure, warts and all,
		 * in order to keep it drop-in compatible with the OG widget.
		 */
		$dropdown_id = wp_unique_id( 'wp-block-aex-archives-widget-extended-' );
		$title       = __( 'Archives' ); //phpcs:ignore WordPress.WP.I18n.MissingArgDomain

		/** This filter is documented in wp-includes/widgets/class-wp-widget-archives.php */
		$dropdown_args = apply_filters(
			'widget_archives_dropdown_args',
			array(
				'type'            => $type,
				'format'          => 'option',
				'show_post_count' => $show_post_count,
				'post_type'       => $post_type,
				'echo'            => 0,
			)
		);

		$archives = wp_get_archives( $dropdown_args );

		$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $wrapper_class ) );

		/**
		 * There's no text domain on the strings for the original Block.
		 * Let's keep it this way for compatibility with pre-existing translations.
		 */
		switch ( $dropdown_args['type'] ?? $type ) {
			case 'yearly':
				$label = __( 'Select Year' ); //phpcs:ignore WordPress.WP.I18n.MissingArgDomain
				break;
			case 'monthly':
				$label = __( 'Select Month' ); //phpcs:ignore WordPress.WP.I18n.MissingArgDomain
				break;
			case 'daily':
				$label = __( 'Select Day' ); //phpcs:ignore WordPress.WP.I18n.MissingArgDomain
				break;
			case 'weekly':
				$label = __( 'Select Week' ); //phpcs:ignore WordPress.WP.I18n.MissingArgDomain
				break;
			default:
				$label = __( 'Select Post' ); //phpcs:ignore WordPress.WP.I18n.MissingArgDomain
				break;
		}

		$label_class = 'wp-block-archives__label wp-block-aex-archives-widget-extended__label';
		if ( ! $show_label ) {
			$label_class .= ' screen-reader-text';
		}

		$block_content  = '<label for="' . esc_attr( $dropdown_id ) . '" class="' . esc_attr( $label_class ) . '">' . esc_html( $title ) . '</label>';
		$block_content .= '<select id="' . esc_attr( $dropdown_id ) . '" name="archive-dropdown" data-aex-archive-dropdown="1">';
		$block_content .= '<option value="">' . esc_html( $label ) . '</option>';
		$block_content .= $archives;
		$block_content .= '</select>';
		$block_content .= $this->build_dropdown_script( $dropdown_id );

		return sprintf( '<div %1$s>%2$s</div>', $wrapper_attributes, $block_content );
	}

	/**
	 * Generates the inline navigation script for the archives dropdown.
	 *
	 * Mirrors core's `block_core_archives_build_dropdown_script()`
	 * (wp-includes/blocks/archives.php, WP 6.9.0). Copied locally so the
	 * block keeps working below that version floor.
	 *
	 * @param string $dropdown_id ID of the rendered <select>.
	 */
	private function build_dropdown_script( string $dropdown_id ): string {
		$exports = array( $dropdown_id, home_url() );

		ob_start();
		?>
		<script>
		( ( [ dropdownId, homeUrl ] ) => {
			const dropdown = document.getElementById( dropdownId );
			function onSelectChange() {
				setTimeout( () => {
					if ( 'escape' === dropdown.dataset.lastkey ) {
						return;
					}
					if ( dropdown.value ) {
						location.href = dropdown.value;
					}
				}, 250 );
			}
			function onKeyUp( event ) {
				if ( 'Escape' === event.key ) {
					dropdown.dataset.lastkey = 'escape';
				} else {
					delete dropdown.dataset.lastkey;
				}
			}
			function onClick() {
				delete dropdown.dataset.lastkey;
			}
			dropdown.addEventListener( 'keyup', onKeyUp );
			dropdown.addEventListener( 'click', onClick );
			dropdown.addEventListener( 'change', onSelectChange );
		} )( <?php echo wp_json_encode( $exports, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES ); ?> );
		</script>
		<?php
		$script = (string) ob_get_clean();

		return wp_get_inline_script_tag(
			trim( str_replace( array( '<script>', '</script>' ), '', $script ) ) .
			"\n//# sourceURL=" . rawurlencode( __METHOD__ )
		);
	}

	/**
	 * Renders the list variant.
	 *
	 * @param string $type            Archive grouping (monthly|yearly|weekly|daily).
	 * @param bool   $show_post_count Whether to append the post count to each list item.
	 * @param string $post_type       Validated post type slug to query archives for.
	 * @param string $extra_classes   Sanitized whitespace-separated class list to append to the wrapper.
	 * @param string $item_classes    Sanitized whitespace-separated class list applied to each <li>.
	 */
	private function render_list(
		string $type,
		bool $show_post_count,
		string $post_type,
		string $extra_classes,
		string $item_classes
	): string {
		$wrapper_class = 'wp-block-archives-list wp-block-aex-archives-widget-extended-list';
		if ( '' !== $extra_classes ) {
			$wrapper_class .= ' ' . $extra_classes;
		}

		/** This filter is documented in wp-includes/widgets/class-wp-widget-archives.php */
		$archives_args = apply_filters(
			'widget_archives_args',
			array(
				'type'            => $type,
				'show_post_count' => $show_post_count,
				'post_type'       => $post_type,
				'echo'            => 0,
			)
		);

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
			add_filter( 'get_archives_link', $item_filter );
		}

		$archives = wp_get_archives( $archives_args );

		if ( null !== $item_filter ) {
			remove_filter( 'get_archives_link', $item_filter );
		}

		$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $wrapper_class ) );

		if ( empty( $archives ) ) {
			return sprintf(
				'<div %1$s>%2$s</div>',
				$wrapper_attributes,
				esc_html__( 'No archives to show.' ) //phpcs:ignore WordPress.WP.I18n.MissingArgDomain
			);
		}

		return sprintf( '<ul %1$s>%2$s</ul>', $wrapper_attributes, $archives );
	}

	/**
	 * Returns the post types eligible for archive rendering.
	 *
	 * Public post types with has_archive=true, plus `post` (whose archive is
	 * the blog home but which does not carry has_archive=true).
	 *
	 * @return array<string, WP_Post_Type>
	 */
	private function get_available_post_types(): array {
		$post_types = get_post_types(
			array(
				'public'      => true,
				'has_archive' => true,
			),
			'objects'
		);

		if ( ! isset( $post_types['post'] ) ) {
			$post_obj = get_post_type_object( 'post' );
			if ( $post_obj ) {
				$post_types = array( 'post' => $post_obj ) + $post_types;
			}
		}

		return $post_types;
	}

	/**
	 * Validates the `type` attribute against the allowed archive types.
	 *
	 * @param string $candidate Raw value coming from the saved block attributes.
	 */
	private function resolve_type( string $candidate ): string {
		return in_array( $candidate, self::ALLOWED_TYPES, true ) ? $candidate : 'monthly';
	}

	/**
	 * Validates the `postType` attribute against publicly-archived post types.
	 *
	 * @param string $candidate Raw post type slug coming from the saved block attributes.
	 */
	private function resolve_post_type( string $candidate ): string {
		$available = $this->get_available_post_types();
		return isset( $available[ $candidate ] ) ? $candidate : 'post';
	}

	/**
	 * Sanitizes a whitespace-separated class string into a normalized list.
	 *
	 * @param string $raw_classes Whitespace-separated class string from user input.
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
}

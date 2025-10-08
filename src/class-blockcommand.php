<?php
/**
 * Generate block files based on provided data
 *
 * @package Parkour
 */

namespace Parkour;

use WP_CLI;
use function Laravel\Prompts\text;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;
use function Laravel\Prompts\warning;

/**
 * Scaffold ACF blocks for Timber themes with style. PARKOUR!
 */
class BlockCommand {

	/**
	 * Creates a new ACF block with all necessary files
	 *
	 * ## OPTIONS
	 *
	 * [<name>]
	 * : The block name (kebab-case). If not provided, you'll be prompted.
	 *
	 * [--theme=<theme>]
	 * : The theme slug. Defaults to current active theme.
	 *
	 * [--skip-prompts]
	 * : Skip interactive prompts and use defaults
	 *
	 * ## EXAMPLES
	 *
	 *     # Interactive mode (recommended)
	 *     wp parkour create
	 *
	 *     # Quick mode with block name
	 *     wp parkour create hero-section
	 *
	 *     # Specify everything
	 *     wp parkour create hero-section --theme=herdpress
	 *
	 * @when after_wp_load
	 *
	 * @param array $args Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function create( $args, $assoc_args ) {
		info( '🏃 PARKOUR! Let\'s create a block...' );

		$theme_slug = $assoc_args['theme'] ?? get_stylesheet();
		$theme_path = get_stylesheet_directory();

		if ( ! file_exists( $theme_path ) ) {
			error( "Theme directory not found: {$theme_path}" );
			return;
		}

		$skip_prompts = isset( $assoc_args['skip-prompts'] );

		$block_name = $args[0] ?? null;

		if ( ! $block_name && ! $skip_prompts ) {
			$block_name = text(
				label: 'What is the block name?',
				placeholder: 'hero-section',
				required: true,
				validate: fn ( $value ) => $this->validate_block_name( $value )
			);
		} elseif ( ! $block_name ) {
			error( 'Block name is required when using --skip-prompts' );
			return;
		}

		$block_name = strtolower( trim( $block_name ) );
		$block_name = preg_replace( '/[^a-z0-9-]/', '-', $block_name );

		$block_data = $this->gather_block_data( $block_name, $theme_slug, $skip_prompts );

		if ( ! $skip_prompts ) {
			info( "\n📦 Block Summary:" );
			info( "  Name: {$block_data['name']}" );
			info( "  Title: {$block_data['title']}" );
			info( "  Function: {$block_data['function_name']}" );

			$confirmed = confirm(
				label: 'Create this block?',
				default: true
			);

			if ( ! $confirmed ) {
				warning( 'Block creation cancelled.' );
				return;
			}
		}

		try {
			$generator = new BlockGenerator( $theme_path );
			$generator->create( $block_data );

			info( "\n✅ Block created successfully!" );
			info( "\n📁 Files created:" );
			info( "  - blocks/{$block_name}/block.json" );
			info( "  - blocks/{$block_name}/callback.php" );
			info( "  - views/blocks/{$block_name}.twig" );

			if ( $block_data['include_js'] ) {
				info( "  - blocks/{$block_name}/{$block_name}.js" );
			}

			if ( $block_data['include_css'] ) {
				info( "  - blocks/{$block_name}/{$block_name}.css" );
			}

			info( "\n🎉 PARKOUR! Block '{$block_data['title']}' is ready to use!" );

		} catch ( \Exception $e ) {
			error( 'Failed to create block: ' . $e->getMessage() );
		}
	}

	/**
	 * Gather all block data through prompts
	 *
	 * @param string $block_name The block name.
	 * @param string $theme_slug The theme slug.
	 * @param bool   $skip_prompts Whether to skip prompts and use defaults.
	 * @return array The gathered block data.
	 */
	private function gather_block_data( $block_name, $theme_slug, $skip_prompts ) {
		$function_name = $theme_slug . '_' . str_replace( '-', '_', $block_name ) . '_block';

		if ( $skip_prompts ) {
			return array(
				'name'               => $block_name,
				'title'              => $this->name_to_title( $block_name ),
				'description'        => '',
				'category'           => $theme_slug,
				'icon'               => 'admin-customizer',
				'keywords'           => array(),
				'function_name'      => $function_name,
				'theme_slug'         => $theme_slug,
				'include_js'         => false,
				'include_css'        => false,
				'supports_anchor'    => true,
				'supports_classname' => true,
			);
		}

		$title = text(
			label: 'Block title (human-readable)',
			default: $this->name_to_title( $block_name ),
			required: true
		);

		$description = text(
			label: 'Block description (optional)',
			placeholder: 'A brief description of what this block does...'
		);

		$category = text(
			label: 'Block category',
			default: $theme_slug,
			hint: 'The category slug (e.g., herdpress, custom, layout)'
		);

		$icon = select(
			label: 'Choose an icon',
			options: array(
				'admin-customizer' => 'Customizer (default)',
				'editor-justify'   => 'Justify',
				'grid-view'        => 'Grid',
				'columns'          => 'Columns',
				'layout'           => 'Layout',
				'admin-post'       => 'Post',
				'media-default'    => 'Media',
				'format-gallery'   => 'Gallery',
				'format-image'     => 'Image',
				'format-video'     => 'Video',
			),
			default: 'admin-customizer'
		);

		$keywords_input = text(
			label: 'Keywords (comma-separated, optional)',
			placeholder: 'accordion, faq, toggle'
		);

		$keywords = $keywords_input
			? array_map( 'trim', explode( ',', $keywords_input ) )
			: array();

		$include_js = confirm(
			label: 'Include JavaScript file?',
			default: false
		);

		$include_css = confirm(
			label: 'Include CSS file?',
			default: false
		);

		return array(
			'name'               => $block_name,
			'title'              => $title,
			'description'        => $description,
			'category'           => $category,
			'icon'               => $icon,
			'keywords'           => $keywords,
			'function_name'      => $function_name,
			'theme_slug'         => $theme_slug,
			'include_js'         => $include_js,
			'include_css'        => $include_css,
			'supports_anchor'    => true,
			'supports_classname' => true,
		);
	}

	/**
	 * Validate block name
	 *
	 * @param string $name The block name to validate.
	 * @return string|null Error message if invalid, null if valid.
	 */
	private function validate_block_name( $name ) {
		if ( empty( $name ) ) {
			return 'Block name is required';
		}

		if ( ! preg_match( '/^[a-z0-9-]+$/', strtolower( $name ) ) ) {
			return 'Block name must be lowercase letters, numbers, and hyphens only';
		}

		return null;
	}

	/**
	 * Convert block name to title
	 *
	 * @param string $name The block name in kebab-case.
	 * @return string The block title in Title Case.
	 */
	private function name_to_title( $name ) {
		return ucwords( str_replace( '-', ' ', $name ) );
	}
}
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'parkour', BlockCommand::class );
}

<?php
/**
 * Generate block files based on provided data
 *
 * @package Parkour
 */

namespace Parkour;

use Mustache_Engine;

/**
 * Generate block files based on provided data
 *
 * @package Parkour
 */
class BlockGenerator {

	/**
	 * Theme path
	 *
	 * @var string
	 */
	private $theme_path;

	/**
	 * Mustache engine instance
	 *
	 * @var Mustache_Engine
	 */
	private $mustache;

	/**
	 * Constructor
	 *
	 * @param string $theme_path The path to the theme where blocks will be created.
	 */
	public function __construct( $theme_path ) {
		$this->theme_path = $theme_path;
		$this->mustache   = new Mustache_Engine(
			array(
				'loader' => new \Mustache_Loader_FilesystemLoader(
					dirname( __DIR__ ) . '/templates'
				),
			)
		);
	}

	/**
	 * Create the block files
	 *
	 * @param array $data The block data.
	 * @throws \Exception If file creation fails.
	 */
	public function create( $data ) {
		$block_dir = $this->theme_path . '/blocks/' . $data['name'];

		if ( ! file_exists( $block_dir ) ) {
			mkdir( $block_dir, 0755, true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
		}

		$views_dir = $this->theme_path . '/views/blocks';
		if ( ! file_exists( $views_dir ) ) {
			mkdir( $views_dir, 0755, true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
		}

		$this->create_block_json( $block_dir, $data );
		$this->create_callback_php( $block_dir, $data );
		$this->create_twig_template( $views_dir, $data );

		if ( $data['include_js'] ) {
			$this->create_javascript( $block_dir, $data );
		}

		if ( $data['include_css'] ) {
			$this->create_stylesheet( $block_dir, $data );
		}
	}

	/**
	 * Create block.json
	 *
	 * @param string $dir  The directory to create the file in.
	 * @param array  $data The block data.
	 * @return void
	 */
	private function create_block_json( $dir, $data ) {
		$keywords_json = '';
		if ( ! empty( $data['keywords'] ) ) {
			$keywords_json = wp_json_encode( $data['keywords'], JSON_UNESCAPED_SLASHES );
		}

		$content = $this->mustache->render(
			'block.json',
			array(
				'name'               => $data['name'],
				'title'              => $data['title'],
				'description'        => $data['description'],
				'category'           => $data['category'],
				'icon'               => $data['icon'],
				'keywords'           => $keywords_json,
				'theme_slug'         => $data['theme_slug'],
				'function_name'      => $data['function_name'],
				'supports_anchor'    => $data['supports_anchor'],
				'supports_classname' => $data['supports_classname'],
			)
		);

		file_put_contents( $dir . '/block.json', $content ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}

	/**
	 * Create callback.php
	 *
	 * @param string $dir The directory to create the file in.
	 * @param array  $data The block data.
	 * @return void
	 */
	private function create_callback_php( $dir, $data ) {
		$content = $this->mustache->render(
			'callback.php',
			array(
				'name'          => $data['name'],
				'title'         => $data['title'],
				'theme_slug'    => $data['theme_slug'],
				'function_name' => $data['function_name'],
				'class_prefix'  => str_replace( '-', '_', $data['name'] ),
				'include_js'    => $data['include_js'],
				'include_css'   => $data['include_css'],
			)
		);

		file_put_contents( $dir . '/callback.php', $content ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}

	/**
	 * Create Twig template
	 *
	 * @param string $dir The directory to create the file in.
	 * @param array  $data The block data.
	 * @return void
	 */
	private function create_twig_template( $dir, $data ) {
		$content = $this->mustache->render(
			'block.twig',
			array(
				'name'       => $data['name'],
				'title'      => $data['title'],
				'theme_slug' => $data['theme_slug'],
			)
		);

		file_put_contents( $dir . '/' . $data['name'] . '.twig', $content ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}

	/**
	 * Create JavaScript file
	 *
	 * @param string $dir The directory to create the file in.
	 * @param array  $data The block data.
	 * @return void
	 */
	private function create_javascript( $dir, $data ) {
		$content = $this->mustache->render(
			'block.js',
			array(
				'name'  => $data['name'],
				'title' => $data['title'],
			)
		);

		file_put_contents( $dir . '/' . $data['name'] . '.js', $content ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}

	/**
	 * Create CSS file
	 *
	 * @param string $dir The directory to create the file in.
	 * @param array  $data The block data.
	 * @return void
	 */
	private function create_stylesheet( $dir, $data ) {
		$content = $this->mustache->render(
			'block.css',
			array(
				'name'       => $data['name'],
				'title'      => $data['title'],
				'theme_slug' => $data['theme_slug'],
			)
		);

		file_put_contents( $dir . '/' . $data['name'] . '.css', $content ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}
}

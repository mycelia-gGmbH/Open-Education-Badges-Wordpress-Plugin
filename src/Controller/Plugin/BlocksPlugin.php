<?php

namespace DisruptiveElements\OpenEducationBadges\Controller\Plugin;

Use DisruptiveElements\OpenEducationBadges\Controller\Plugin;
Use DisruptiveElements\OpenEducationBadges\Util\Utils;

class BlocksPlugin {

	public function __construct() { }

	public static function register_hooks() {

		// htm (https://github.com/developit/htm)
		wp_register_script(
			'oeb-blocks-htm',
			Plugin::PLUGIN_URL . "/assets/blocks/htm.js",
			[],
			filemtime(Plugin::PLUGIN_DIR . "/assets/blocks/htm.js")
		);

		// register block categories
		add_filter('block_categories_all', function($params_categories, $post) {
			return array_merge(
				$params_categories,
				[[
					'slug' => 'OpenEducationBadges',
					'name' => 'Open Education Badges',
				]]
			);
		}, 10, 2);

		self::register_block('issue-badge');

		add_filter('request', [static::class, 'filter_request']);

	}

	private static function register_block($block_name) {

		register_block_type("oeb/$block_name", [
			'render_callback' => [static::class, 'render_block'],
			'editor_script' => "oeb-$block_name-editor"
		]);

		$asset_url = Plugin::PLUGIN_URL . '/assets/blocks/' . $block_name;
		$asset_path = Plugin::PLUGIN_DIR . '/assets/blocks/' . $block_name;
		wp_register_script(
			"oeb-$block_name-editor",
			"$asset_url/editor.js",
			[
				'oeb-blocks-htm',
				'wp-block-editor',
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-api-fetch'
			],
			filemtime("$asset_path/editor.js")
		);
	}

	public static function render_block($attributes, $content, $block) {

		$slug = substr($block->name, strrpos($block->name, '/') + 1);

		if(file_exists(Plugin::PLUGIN_DIR . "/templates/blocks/block_oeb_{$slug}.php")) {
			ob_start();
			include(Plugin::PLUGIN_DIR . "/templates/blocks/block_oeb_{$slug}.php");
			return ob_get_clean();
		}

		return "";
	}

	public static function filter_request($request) {
		if (!empty($request)) {
		}

		return $request;
	}
}


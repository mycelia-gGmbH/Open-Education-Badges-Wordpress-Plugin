<?php
/**
 * Plugin Name:     Open Education Badges
 * Plugin URI:      https://openbadges.education
 * Description:     Badges vergeben und anzeigen
 * Author:          Stefan Trautvetter
 * Author URI:      https://www.esirion.de
 * Text Domain:     openeducationbadges
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 */

require __DIR__ . '/vendor/autoload.php';

add_action('init', [\DisruptiveElements\OpenEducationBadges\Controller\Plugin::class, 'register_hooks']);

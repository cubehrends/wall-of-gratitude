<?php
/*
Plugin Name: Wall of Gratitude
Description: Displays a wall where every brick represents a donor
Version: 2.2
Author: Christian Behrends
Author URI: http://webdevtrust.com/wall-of-gratitude-demo/
License: GPL
Copyright: Christian Behrends
*/
function wog_shortcode( $atts, $content = null ) {

	$path = getcwd() . '/wp-content/uploads' . $content;
	if ( file_exists( $path ) ) {

		$csv = array_map('str_getcsv', file( $path ));

		# processing atts
		$attributes = shortcode_atts( array(
			'avada-popover' => '0',
			'popover-title' => 'Thanks to',
			'currency' => '$'
		), $atts );

		# checking if Avada popover is available
		if ( $attributes['avada-popover'] ) {

			if ( shortcode_exists( 'popover' ) ) {
				$wog = '<div id="wog" data-popover="avada">';
			} else {
				$wog = '<p style="text-align:center; color:red;">Avada Popover is chosen to display donor-data (avada-popover=\"1\"). Please make sure that you have Avada or an Avada-Child-Theme active when choosing this option.</p><div id="wog">';
			}

		} else {

			$wog = '<div id="wog" data-popover="custom">';

		}

		# reavealing donated amount like +5000$
		$amount_steps = array(100, 250, 500, 1000, 2500, 5000, 10000, 50000);
		$initial = 0;
		$initial_backup = 0;

		# processing csv-rows
		foreach ( $csv as $bricknum => $brick ) {

			$origin = $brick[0];
			$name = $brick[1];
			$amount = $brick[3];

			# building initial according to amount_steps
			foreach ( $amount_steps as $stepnum => $step ) {
				if ( $amount <= $step ) {
					$initial = $step;
					break;
				}
			}
			unset($step);

			# display initial only once
			if ( $initial <> $initial_backup ) {
				$initial_backup = $initial;
				$echo_initial = $initial . $attributes['currency'];
			} else {
				$echo_initial = '';
			}

			$donor = $name . '&nbsp;' . $origin;

			if ( $attributes['avada-popover'] == 1 ) {
				$wog .= do_shortcode('[popover title="' . $attributes['popover-title'] . '" title_bg_color="" content="' . $donor . '" content_bg_color="" bordercolor="" textcolor="" trigger="hover" placement="" class="" id=""]' . '<span id="brick-' . $bricknum . '" class="brick" data-title="' . $attributes['popover-title'] . '" data-content="' . $donor . '"><img src="' . plugins_url( 'img/brick.png', __FILE__ ) . '"><span class="initial">' . $echo_initial . '</span></span>' . '[/popover]');
			} else {
				$wog .= '<span><span id="brick-' . $bricknum . '" class="brick" data-title="' . $attributes['popover-title'] . '" data-content="' . $donor . '"><img src="' . plugins_url( 'img/brick.png', __FILE__ ) . '"><span class="initial">' . $echo_initial . '</span></span></span>';
			}

		}
		$wog .= '</div>';
		unset($brick);

	} else {

		$wog = '<h3>CSV-file ' . $content . ' not found</h3><p style="text-align:center; color:red;">Please enter the path to the CSV-file inside the shortcode-brackets like <blockquote>[wog]/2016/03/wog.csv[/wog]</blockquote></p>';

	}

	return $wog;
}
add_shortcode( 'wog', 'wog_shortcode' );

/*
	loading scripts only if wog shortcode is used in post
*/
function wog_scripts() {
	global $post;
	if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'wog') ) {
		wp_enqueue_style( 'wog-css', plugin_dir_url(__FILE__) . 'style.css', array(), '1.0' );
		wp_enqueue_script( 'wog-js', plugin_dir_url(__FILE__) . 'js/wog.js', array('jquery'), '1.0', true);
	}
}
add_action( 'wp_enqueue_scripts', 'wog_scripts');
<?php
/*
Plugin Name: Wir Machen Schule Dankesmauer
Description: Stellt die Dankesmauer anhand eines Shortcodes mit dem Pfad zur Spenden-CSV-Datei dar.
Version: 2.3
Author: Christian Behrends
Author URI: https://webdevtrust.com/
License: GPL
Copyright: Christian Behrends
*/
function dankesmauer_shortcode( $atts, $content = null ) {

	$path = getcwd() . '/wp-content/uploads' . $content;
	if ( file_exists( $path ) ) {

		$csv = array_map('str_getcsv', file( $path ));

		# processing atts
		$attributes = shortcode_atts( array(
			'avada-popover' => '0',
			'danke-text' => 'Danke'
		), $atts );

		# checking if Avada popover is available
		if ( $attributes['avada-popover'] && ! shortcode_exists( 'fusion_popover' ) ) {
			$dankesmauer = '<p style="text-align:center; color:red;">Zur Anzeige der Spender ist Avada Popover gewählt (avada-popover=\"1\"). Bitte stellen Sie sicher, dass Avada oder ein Avada-Child-Theme aktiv ist, wenn Sie Avada-Popover benutzen möchten.</p><div id="dankesmauer">';
		} else {
			$dankesmauer = '<div id="dankesmauer">';
		}

		$ort_inital = '';

		# processing csv-rows
		foreach ( $csv as $key => $brick ) {

			$ort = $brick[0];
			$name =$brick[1];

			if ( $ort_inital == strtoupper( $ort[0] ) ) {
				$echo_initial = '&nbsp;';
			} else {
				$ort_inital = strtoupper( $ort[0] );
				$echo_initial = $ort_inital;
			}

			$spender = $name . ', ' . $ort;

			if ( $attributes['avada-popover'] == 1 ) {
				$dankesmauer .= do_shortcode('[fusion_popover title="' . $attributes['danke-text'] . '" title_bg_color="" content="' . $spender . '" content_bg_color="" bordercolor="" textcolor="" trigger="hover" placement="" class="" id=""]' . '<span id="brick-' . $key . '" class="brick" style="display: none;">&nbsp;<span class="initial">' . $echo_initial . '</span></span>' . '[/fusion_popover]');
			} else {
				$dankesmauer .= '<span class="fusion-popover"><span id="brick-' . $key . '" class="brick" style="display: none;">&nbsp;<span class="initial">' . $echo_initial . '</span></span></span>';
			}

		}
		$dankesmauer .= '</div>';

	} else {

		$dankesmauer = '<h3>CSV-Datei für Dankesmauer nicht gefunden</h3><p style="text-align:center; color:red;">Bitte geben Sie im Shortcode den Dateinamen relativ vom Uploads-Verzeichnis an. Beispielsweise <blockquote>[dankesmauer]/2016/03/dankesmauer.csv[/dankesmauer]</blockquote></p>';

	}

	return $dankesmauer;
}
add_shortcode( 'dankesmauer', 'dankesmauer_shortcode' );

/*
	loading scripts but only, if dankesmauser shortcode is used in post
*/
function dankesmauer_scripts() {
	global $post;
	if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'dankesmauer') ) {
		wp_enqueue_style( 'dankesmauer-css', plugin_dir_url(__FILE__) . 'style.css', array(), '1.0' );
		wp_enqueue_script( 'dankesmauer-js', plugin_dir_url(__FILE__) . 'js/dankesmauer.js', array('jquery'), '1.0', true);
	}
}
add_action( 'wp_enqueue_scripts', 'dankesmauer_scripts');

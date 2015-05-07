<?php
/* Copyright 2015 Patrick Facco (email : pedrick[at]tiscali[dot]it)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
 * Plugin Name: wordpress-projects-map
 * Plugin URI: http://github.com/p3dr16k/wordpress-projects-map
 * Description: This plugin display map on wordpress. 
 * If you create an advanced custom field named location composed by an element
 * with googlemaps type named coordinate, all fields are shown on map. 
 * Version: 1.0
 * Author: Patrick Facco
 * Author URI: http://github.com/p3dr16k
 * License: GPL2
*/

//very low priority
add_action('wp_enqueue_scripts', 'wptuts_scripts', 200);
add_shortcode('projectsmap', 'display_map_shortcode');



//shortcode [projectsmap location="italia|europa|mondo" zoom="4" minzoom="4"  maxzoom="15"]
function display_map_shortcode($attrs)
{
	extract(shortcode_atts(array(
			'location' => 'mondo',
			'zoom' => 5,
			'minzoom' => 7,
			'maxzoom' => 15	 		 
				),$attrs)
			);

	display_map($location, $zoom, $minzoom, $maxzoom);
}

/** Appends a map with the projects as a marker on a specified custom post type
 */
function display_map($type, $zoom, $minzoom, $maxzoom) 
{	
	//centers for all maps type
	$centers = array('italia' => array('lat' => 42.8764623, 'lon' => 12.5183387), 
				     'europa' => array('lat' => 46.5392980, 'lon' => 7.7001682), 
				     'mondo' => array('lat' => 42.7338830, 'lon' => 25.4858300));

	//javascript call	
	echo '<script type="text/javascript">jQuery(function(){'.
				'initialize('.$centers[$type]['lat'].','.$centers[$type]['lon'].', '.$zoom.', 1, true, '.$minzoom.', '.$maxzoom.');'.
				 add_marker($type).
				'});'.				
				'</script>'.		 
		 '<div id="map-container"></div>';	
}

function add_marker($type)
{	
	global $wpdb;
	
	//db query		
	$query_all = 'SELECT terms.term_id FROM '.$wpdb->terms.
		' AS terms LEFT JOIN '.$wpdb->term_taxonomy.
		' AS taxonomy ON terms.term_id = taxonomy.term_id ';
		
	$query_it = $query_all.' WHERE taxonomy.description LIKE \'%italia%\'';
	
	$query_eu = $query_it.' OR taxonomy.description LIKE \'%europa%\'';	
	
	$query_tot = $query_eu.' OR taxonomy.description LIKE \'%mondo%\'';	
	
	if(strcmp($type, 'italia') === 0)
	{
		$query = $query_it;
	}
	else if(strcmp($type, 'europa') === 0)
	{
		$query = $query_eu;
	}
	else
	{
		$query = $query_tot;
	}
	
	$results = $wpdb->get_results($query, ARRAY_N);
	
	if($results)
	{
		$length = count($results);		
		$res = '';
		for($i = 0; $i < $length; $i++)
		{	
			$query_coord = 'SELECT option_value FROM '.$wpdb->options.
				' WHERE option_name = \'location_'.$results[$i][0].'_coordinate\'';
			
			$coordinates =  $wpdb->get_results($query_coord, ARRAY_N);
			
			if($coordinates)
			{				
				$tmp = extractCoord($coordinates[0][0]);				
				$res .= 'setTimeout(function(){drawMarker("marker","'.$tmp[0].'", "'.$tmp[1].'", "'.plugins_url().'/wordpress-projects-map/img/map-marker.png", null)},'.($i * 5).');';		
			}
			
		}				
		return $res;
	}	
}

function extractCoord($string)
{
	$pattern = '/(-?\d{1,3}\.\d+)/';
	preg_match_all($pattern, $string, $matches);
	
	$result = array();
	$result[0] = $matches[0][0];
	$result[1] = $matches[0][1];
	return $result;	
}

/**
 * This function include the javascript required in the head section of the page
 */
function wptuts_scripts()
{   
    wp_register_script('smap', plugins_url().'/wordpress-projects-map/include/js/simple_maps.js', null, null, true);
    wp_enqueue_style( 'mapstyle', plugins_url().'/wordpress-projects-map/include/css/mapstyle.css', null, null, 'all');
     
    wp_enqueue_script('smap');
}

?>

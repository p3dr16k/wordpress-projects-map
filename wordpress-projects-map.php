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


//shortcode [projectsmap location="italia|europa|mondo" zoom="4" minzoom="4"  maxzoom="15" single="0|1"]
function display_map_shortcode($attrs)
{
	extract(shortcode_atts(array(
			'location' => 'mondo',
			'zoom' => 5,
			'minzoom' => 7,
			'maxzoom' => 15,
			'single' => 0	 		 
				),$attrs)
			);

	display_map($location, $zoom, $minzoom, $maxzoom, $single);
}

/** Appends a map with the projects as a marker on a specified custom post type
 */
function display_map($type, $zoom, $minzoom, $maxzoom, $single) 
{	
	//centers for all maps type
	$centers = array('piemonte' => array('lat' => 45.0056495, 'lon' => 7.9850535), 
					 'italia' => array('lat' => 42.8764623, 'lon' => 12.5183387), 
				     'europa' => array('lat' => 46.5392980, 'lon' => 7.7001682), 
				     'mondo' => array('lat' => 42.7338830, 'lon' => 25.4858300));

	if($single)
	{	
		$terms = get_the_terms( get_the_ID(), 'location');
		foreach ( $terms as $term ) 
		{
			$termID[] = $term->term_id;
		}		
		$term_id= $termID[0]; 
		$coords = get_coord_by_term_id($term_id);		
		$marker_title = get_the_title(get_the_ID());
		
		$embedding = '<script type="text/javascript">jQuery(function(){'.
					'initialize('.$coords[0].','.$coords[1].', '.$zoom.', 1, true, '.$minzoom.', '.$maxzoom.');'.
					'setTimeout(function(){drawMarker("'.$marker_title.'","'.$coords[0].'", "'.$coords[1].'", "'.plugins_url().'/wordpress-projects-map/img/map-marker.png", null)},5);'.
					'});'.				
					'</script>';;
		$embedding .= '<div id="map-container" class="map-container-single"></div>';	
	}
	else
	{		
		//javascript call	
		$embedding = '<script type="text/javascript">jQuery(function(){'.
					'initialize('.$centers[$type]['lat'].','.$centers[$type]['lon'].', '.$zoom.', 1, true, '.$minzoom.', '.$maxzoom.');'.
					 add_marker($type).
					'});'.				
					'</script>';		 
		$embedding .= '<div id="map-container" class="map-container-full"></div>';	
	}
	
	echo $embedding;
}

function get_query($type)
{
	global $wpdb;
	$query_all = 'SELECT option_name FROM '.$wpdb->options;
	$query_piedmont = $query_all.' WHERE option_value = \'Piemonte\'';
	$query_italy = $query_piedmont.' OR option_value = \'Italia\'';
	$query_europe = $query_italy.' OR option_value = \'Europa\'';
	
	if(strcmp($type, 'piemonte') === 0)
	{
		return $query_piedmont;
	}
	else if(strcmp($type, 'italia') === 0)
	{
		return $query_italy;
	}
	else if(strcmp($type, 'europa') === 0)
	{
		return $query_europe;
	}
	else
	{
		return $query_all.' WHERE option_name LIKE \'location_%_area_geografica\'';		
	}
}
function add_marker($type)
{
	global $wpdb;
	$query = get_query($type);	
	$ids_to_parse = $wpdb->get_results($query, ARRAY_N);
	$ids_length = count($ids_to_parse);
	$res = '';
	for($i = 0; $i < $ids_length; $i++)
	{
		//the term id
		$real_id = extract_id($ids_to_parse[$i][0]);		
		
		$catinfo = get_term_by( 'id', $real_id, 'location' );		
		
		
		$query_coord = 'SELECT option_value FROM '.$wpdb->options.
				' WHERE option_name = \'location_'.$real_id.'_coordinate\'';
			
		$coordinates =  $wpdb->get_results($query_coord, ARRAY_N);
			
		if($coordinates)
		{				
			$tmp = extractCoord($coordinates[0][0]);			
			$res .= 'setTimeout(function(){drawMarker("'.$catinfo->slug.'","'.$tmp[0].'", "'.$tmp[1].'", "'.plugins_url().'/wordpress-projects-map/img/map-marker.png", "'.$catinfo->slug.'")},'.($i * 5).');';		
		}
	}
	return $res;
}

function extract_id($to_parse)
{
	$pattern = '/\d+/';
	preg_match_all($pattern, $to_parse, $matches);	
	$result = $matches[0][0];	
	return $result;	
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

function get_coord_by_term_id($term_id)
{
	global $wpdb;
	$res = array();
	
	$query_coord = 'SELECT option_value FROM '.$wpdb->options.
				' WHERE option_name = \'location_'.$term_id.'_coordinate\'';
			
	$coordinates =  $wpdb->get_results($query_coord, ARRAY_N);
	
	$res = extractCoord($coordinates[0][0]);			
	
	return $res;
}

/**
 * This function include the javascript required in the footer section of the page
 */
function wptuts_scripts()
{   
    wp_register_script('smap', plugins_url().'/wordpress-projects-map/include/js/simple_maps.js', null, null, true);    
    wp_enqueue_style( 'mapstyle', plugins_url().'/wordpress-projects-map/include/css/mapstyle.css', null, null, 'all');         
    wp_enqueue_script('smap');
    
}

?>

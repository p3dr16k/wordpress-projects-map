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

/*=======================================================================
*FILE: simple_maps.js
*
*DESCRIPTION:  Simple library for google maps management
*REQUIREMENTS: javascript google maps v.3 or higher
*AUTHOR:	   Patrick Facco (pedrick[at]tiscali[dot]it), 
*COMPANY:      CSP s.c. a r.l. "Innovazione nelle ICT"
*VERSION:  	   1.0
*CREATED:      25/09/2014 10:33:46 CEST
*LICENSE:      GNU/GPLv2
*========================================================================
* 
*/


/** Simple library for google maps management.
 *  Require javascript google maps v.3 or higher
 *  @author Patrick Facco
 * 
 */
//global variable for store map object
var map;

//array used for map ids
var mapsId = [ 
					google.maps.MapTypeId.HYBRID, 
					google.maps.MapTypeId.ROADMAP,
					google.maps.MapTypeId.SATELLITE,
					google.maps.MapTypeId.TERRAIN
					];
					
//geocoder
var geocoder;
					
/** Function for map initialization			 
 * @param myLat starting latitude for map
 * @param myLon starting longitude for map
 * @param myZoom starting zoom for map
 * @param showControls boolean flag for enable/disable default map controls
 * @param type type for map (0 = hybrid, 1 = roadmap, 2 = satellite, 3 = terrain)
 */
function initialize(myLat, myLon, myZoom, type, showControls, newMinZoom, newMaxZoom) 
{
		var mapOptions = {
		  center: { lat: myLat, lng: myLon},
		  zoom: myZoom,
		  mapTypeId: mapsId[type],
		  disableDefaultUI: !showControls,		  
		  minZoom: newMinZoom,
		  maxZoom: newMaxZoom,		  
		  scrollwheel: false,
		  styles: [
					  {
						featureType: "all",
						stylers: [
						  { saturation: -80 }
						]
					  },{
						featureType: "road.arterial",
						elementType: "geometry",
						stylers: [
						  { hue: "#00ffee" },
						  { saturation: 50 }
						]
					  },{
						featureType: "poi.business",
						elementType: "labels",
						stylers: [
						  { visibility: "off" }
						]
					  }
					]
				 
			};
		map = new google.maps.Map(document.getElementById('map-container'),	mapOptions);
		map.setOptions({'scrollwheel':false});
					
		map.setTilt(0);
					
}
		     
/** Getter for map zoom
 * @return cuurent zoom of the map
 */
function getZoom()
{
	return map.getZoom();
}

/** Setter for map zoom
 * @param new zoom for the map
 */
function setZoom(newZoom)
{
	map.setZoom(newZoom);
}
			
/** Setter for map center
 * @param lat latitude of new center
 * @param lon longitude of new center
 */
function setCenter(lat, lon, z)
{
	map.setCenter(new google.maps.LatLng(parseFloat(lat), parseFloat(lon)));
	if(z !== -1)
	{
		map.setZoom(z);
	}
}
			
/** Getter from map center
 * @return center of map as a string
 */
function getCenter()
{
	return map.getCenter().toString().replace("(", "").replace(")","").replace(" ","");				
}
			
/** Zoom in one level if possible
 * @param lat latitude where zoom in
 * @param lon longitude where zoom in
 */
function zoomIn(lat, lon)
{
	setCenter(lat, lon);
	map.setZoom(map.getZoom() + 1);
}
			
/** Zoom out one level if possible
 * @param lat latitude where zoom out
 * @param lon longitude where zoom out
 */
function zoomOut(lat, lon)
{
	setCenter(lat, lon);
	map.setZoom(map.getZoom() - 1);
}			
			
/** Get size of map
 * @return size of a map
 */
function getSize()
{
	var div = map.getDiv();
	var x = div.offsetWidth;
	var y = div.offsetHeight;
	return new google.maps.Point(x, y).toString().replace("(", "").replace(")","").replace(" ","");
}
			
/** Get bounds of map
 * @return bounds of a map as a string
 */
function getLatLngBounds()
{				
	var printable = map.getBounds().toString();
	
	while(printable.indexOf("(") > -1 || printable.indexOf(")") > -1 || printable.indexOf(" ") > -1)
	{
		printable = printable.replace("(", "").replace(")","").replace(" ", "");
	}
    return printable;
}
			
/** Function for pan to a lat lon poins
 * @param lat latitude of point where pan to
 * @param lon longitude of point where pan to
 */
function panTo(lat, lon)
{
	map.panTo(new google.maps.LatLng(parseFloat(lat), parseFloat(lon)));
}
			
/**
 * Returns the zoom level at which the given rectangular region fits in the map view. 
 * The zoom level is computed for the currently selected map type. 		
 * @param {google.maps.LatLngBounds} bounds 
 * @return {Number} zoom level
**/
function getBoundsZoomLevel(bounds)
{
	  var MAX_ZOOM = map.mapTypes.get( map.getMapTypeId() ).maxZoom || 21 ;
	  var MIN_ZOOM = map.mapTypes.get( map.getMapTypeId() ).minZoom || 0 ;

	  var ne= map.getProjection().fromLatLngToPoint( bounds.getNorthEast() );
	  var sw= map.getProjection().fromLatLngToPoint( bounds.getSouthWest() ); 

	  var worldCoordWidth = Math.abs(ne.x-sw.x);
	  var worldCoordHeight = Math.abs(ne.y-sw.y);

	  //Fit padding in pixels 
	  var FIT_PAD = 40;

	  for( var zoom = MAX_ZOOM; zoom >= MIN_ZOOM; --zoom )
	  { 
		  if( worldCoordWidth*(1<<zoom)+2*FIT_PAD < $(map.getDiv()).width() && 
			  worldCoordHeight*(1<<zoom)+2*FIT_PAD < $(map.getDiv()).height() )
		  {
			  return zoom;
		  }
	  }
	  return 0;
}
			
/** Function for add marker on map
 * @param myTitle a title for marker
 * @param latitude for marker
 * @param longitude for marker
 * @param img path of the image to use
 * @param callback the callback invoked when marker is clicked
*/
function drawMarker(myTitle, lat, lon, img, callback)
{
	//marker creation
	var marker = new google.maps.Marker({
		  position: new google.maps.LatLng(parseFloat(lat), parseFloat(lon)),
		  map: map,
		  title: myTitle,
		  icon: img
    });
				  
	marker.setAnimation(google.maps.Animation.DROP);
    //Setting the callback
	google.maps.event.addListener(marker, 'click', function(){				
						if(callback != null)
						{
							var loc_splitted = window.location.href.split('/');						
							loc_splitted[loc_splitted.length - 2] = 'blog/location/';						
							loc_splitted.push(callback);
							var url = loc_splitted.toString().replace(/,/g, '/').replace('\/\/\/', '/');
							window.location.href = url;
						}
					});				 
				 				  
}

/** Function for add marker on map
 * @param myTitle a title for marker
 * @param loc a string that rapresents a location
 * @param img path of the image to use
 * @param callback the callback invoked when marker is clicked
*/
function drawMarkerByLoc(myTitle, loc, img, callback)
{	
	if(geocoder === null || geocoder === undefined)
	{
		geocoder = new google.maps.Geocoder();
	}
	var marker;
	geocoder.geocode( { 'address': loc}, function(results, status) 
		{
			if (status == google.maps.GeocoderStatus.OK) 
			{				
				map.setCenter(results[0].geometry.location);
				marker = new google.maps.Marker({
					map: map,
					position: results[0].geometry.location,
					tiyle: myTitle,
					icon: img
				});
				marker.setAnimation(google.maps.Animation.DROP);
				//Setting the callback
				google.maps.event.addListener(marker, 'click', callback);
			}
			else 
			{
				console.log('limit exceeded');
			}
		});	   				  
}
			
/**Function for polygon dawning
 * @param Polygon object see google maps API			
 */
function drawPolygon(toDraw)
{
	toDraw.setMap(map);
}
			
/**Function for polygon dawning
 * @param coordinates String that rapresents an array of coordinates
 * @param myStrokeColor String that rapresents stroke color of polygon
 * @param myFillColor String that rapresents fill color of polygon
 * @param myStrokeOpacity stroke opacity of polygon
 * @param myStrokeWeight stroke weight of polygon			
 * @param myFillOpacity fill opacity of polygon			
 */
function drawPolygonByArray(coordinates, myStrokeColor, myFillColor, myStrokeOpacity, myStrokeWeight, myFillOpacity)
{
	var points = [];
	
	var tmpPointsArray = coordinates.split(",");
			
	var i = 0;
	var j = 0;
	while(i < tmpPointsArray.length - 1)
	{
		points[j] = new google.maps.LatLng(tmpPointsArray[i], tmpPointsArray[i + 1]);
		i = i + 2;
		j++;
	}
				
	var polygonToDraw = new google.maps.Polygon({
		paths: points,
		strokeColor: myStrokeColor,
		strokeOpacity: myStrokeOpacity,
		strokeWeight: myStrokeWeight,
		fillColor: myFillColor,
		fillOpacity: myFillOpacity
	});
				
	polygonToDraw.setMap(map);
}

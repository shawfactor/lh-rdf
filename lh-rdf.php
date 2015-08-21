<?php
/*
Plugin Name: LH RDF
Plugin URI: http://lhero.org/plugins/lh-rdf/
Description: Adds a semantic/SIOC RDF feed to Wordpress
Author: Peter Shaw
Version: 1.2
Author URI: http://shawfactor.com/

== Changelog ==

= 1.0 =
*Complete code overhaul

= 1.1 =
*Bug fix after testing

= 1.2 =
*Fixed feed



License:
Released under the GPL license
http://www.gnu.org/copyleft/gpl.html

Copyright 2011  Peter Shaw  (email : pete@localhero.biz)


This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published bythe Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



class LH_rdf_plugin {

var $format_mapper = array (
//"jsonld" => "text/turtle",
"json" => "application/json",
"n3" => "text/n3",
"ntriples" => "application/n-triples",
"php" => "application/x-httpd-php-source",
"rdfxml"  => "application/rdf+xml",
"turtle" => "text/turtle",
//"dot" => "text/vnd.graphviz",
//"gif" => "image/gif",
//"png" => "image/png",
//"svg" => "image/svg+xml"

);

var $standard_namespaces;


public function return_namespaces(){


$namespaces  = array (
"lh"  => "http://localhero.biz/namespace/lhero/",
"sioc" => "http://rdfs.org/sioc/ns#",
"dc" => "http://purl.org/dc/elements/1.1/",
"content" => "http://purl.org/rss/1.0/modules/content/",
"rss" => "http://purl.org/rss/1.0/",
"dcterms" => "http://purl.org/dc/terms/",
"admin" => "http://webns.net/mvcb/",
"skos" => "http://www.w3.org/2004/02/skos/core#",
"sioct" => "http://rdfs.org/sioc/types#",
"bio" => "http://purl.org/vocab/bio/0.1/",
"img" => "http://jibbering.com/2002/3/svg/#",
"ore" => "http://www.openarchives.org/ore/terms/",
"void" => "http://rdfs.org/ns/void#",
);


$namespaces = apply_filters( "lh_rdf_namespaces", $namespaces);


return $namespaces;


}


private function get_link($format) {
 
global $post;
	
if ( is_singular() ){

$base_mid = add_query_arg( "feed", "lhrdf", get_permalink() );

$base_mid = add_query_arg( "format", $format, $base_mid );


} elseif (is_author()){

$base_mid = add_query_arg( "feed", "lhrdf", get_author_posts_url($post->post_author) );

$base_mid = add_query_arg( "format", $format, $base_mid );

} else { 

$base_mid = add_query_arg( "feed", "lhrdf", "http://$_SERVER[HTTP_HOST]/" );

$base_mid = add_query_arg( "format", $format, $base_mid );


}


return $base_mid;

}




public function map_mime_request() {


if (isset($_REQUEST['format'])) {

$format = preg_replace("/[^\w\-]+/", '', strtolower($_REQUEST['format']));

} else {

$format = 'rdfxml';

}


if ($this->format_mapper[$format]){

nocache_headers();

header('Content-Type: ' . $this->format_mapper[$format] . '; charset=' . get_option('blog_charset'), true);

return $format;

} else {

nocache_headers();

header('Content-Type: application/rdf+xml; charset=' . get_option('blog_charset'), true);



return "rdfxml";

}

}

function is_rdf_request() {
	if ( $_SERVER['HTTP_ACCEPT'] == 'application/rdf+xml' ) {
		return true;
	}
	return false;
}

function get_rdf_link() {
 
global $post;
	
if ( is_singular() ){

$base_mid = get_permalink()."?feed=lhrdf";


} elseif (is_author()){

$base_mid = get_author_posts_url($post->post_author);

$base_mid .= "?feed=lhrdf";

} else { 

$base_mid = "http://$_SERVER[HTTP_HOST]";

$base_mid .= "/";

$base_mid .= "?feed=lhrdf";


}


return $base_mid;

}


public function do_feed_enhanced() {

?>
<html>
<head>
	<title>InContext Example</title>
	
	<!-- Visualizer CSS files -->
	<link type="text/css" href="<?php echo plugins_url( '/context/content/visualizer.css', __FILE__ ); ?>" media="screen" rel="Stylesheet" /> 
	<link type="text/css" href="<?php echo plugins_url( '/context/content/visualizer-skin.css', __FILE__ ); ?>" media="screen" rel="Stylesheet" /> 
	
	<!-- Add transparent PNG support to IE6 -->
	<!-- Visualizer IE6 CSS file -->
	<!--[if IE 6]>
		<script type="text/javascript" src="<?php echo plugins_url( '/context/content/iepngfix_tilebg.js', __FILE__ ); ?>"></script>
		<link type="text/css" href="<?php echo plugins_url( '/context/content/visualizer-ie6.css', __FILE__ ); ?>" rel="Stylesheet" />	
	<![endif]-->

	<!-- Visualizer release version script include file -->
	<script type="text/javascript" src="<?php echo plugins_url( '/context/scripts/visualizer_compiled_min.js?foo=bar', __FILE__ ); ?>"></script>

	<!-- Visualizer example code -->
	<script type="text/javascript">
		// initialize the visualizer, center the visualizer on an object identified by "http://pof.tnw.utwente.nl/" 
		var app = new VisualizerApp("visualizer_canvas", "http://royalparktouch.com",
			{ // configuration options, see the configuration options documentation page for more information
				debug: true,
				dataUrl: "<?php echo $this->get_rdf_link(); ?>",
				schemaUrl: "<?php echo plugins_url( '/context/example/example_schema.json', __FILE__ ); ?>",
				schemaFormat: "application/json",
				titleProperties: ["http://purl.org/dc/terms/title", "http://xmlns.com/foaf/0.1/name"],
				dontShowProperties: ["http://purl.utwente.nl/ns/escape-system.owl#id", "http://purl.utwente.nl/ns/escape-system.owl#resourceUri"],
				annotationTypeId: "http://purl.utwente.nl/ns/escape-annotations.owl#RelationAnnotation",
				objectAnnotationTypeId: "http://purl.utwente.nl/ns/escape-annotations.owl#object",
				subjectAnnotationTypeId: "http://purl.utwente.nl/ns/escape-annotations.owl#subject",
				descriptionAnnotationTypeId: "http://purl.org/dc/terms/description",
				imageTypeId: "http://xmlns.com/foaf/0.1/img",
				useHistoryManager: true,
				baseClassTypes: {
					"http://purl.utwente.nl/ns/escape-pubtypes.owl#Publication": "publication",
					"http://purl.org/dc/dcmitype/MovingImage": "video",
					"http://purl.utwente.nl/ns/escape-events.owl#Event": "event",
					"http://purl.utwente.nl/ns/escape-projects.owl#Topic": "topic"
			}
		});

		// subscribe to event that listens for object load
		app.subscribe("*.load-object", function (event) {
			document.getElementById('showObjectHolder').innerHTML = event.data;
		});

		// subscribe to event that listens for uri clicks
		app.subscribe("*.uri-click", function (event) {
			document.getElementById('uriClickDiv').innerHTML = "uri-click - " + event.data;
		});
		
		// subscribe to event that listens for object uri clicks
		app.subscribe("*.object-uri-click", function (event) {
			document.getElementById('uriClickDiv').innerHTML = "object-uri-click - Id: " + event.data.id + " - ExternalId: " + event.data.externalId;
		});
	</script>
</head>
<body>

	<!-- Example button to demonstrate events coming from outside of the visualizer -->
	<button onclick="app.loadObject('http://dx.doi.org/10.1126/science.289.5487.2114')">External Call Load Demo</button>
	<!-- Example place holders to display information coming from visualizer events -->
	Current object: <span id="showObjectHolder"></span>
	<div id="uriClickDiv"></div>
	<hr />

	<!-- The location where the visualizer will be drawn -->
	<div id="visualizer_canvas"></div>

</body>
</html>
<?php
}


public function do_feed_rdf() {

$format = $this->map_mime_request();

include('library/EasyRdf.php');

include('library/object-handlers.php');

include('library/php-json-ld.php');

$doc[] = (object)array(
"@id" => "http://dbpedia.org/resource/John_Lennon",
  "http://schema.org/name" => "Manu Sporny",
  "http://schema.org/url" => (object)array("@id" => "http://manu.sporny.org/"),
  "http://schema.org/image" => (object)array("@id" => "http://manu.sporny.org/images/manu.png")
);

$doc[] = (object)array(
"@id" => "http://dbpedia.org/resource/John_walton",
  "http://schema.org/name" => "Manu walton",
  "http://schema.org/url" => (object)array("@id" => "http://manu.sporny.org/"),
  "http://schema.org/image" => (object)array("@id" => "http://manu.sporny.org/images/manu.png")
);

$context = (object)array(
  "name" => "http://schema.org/name",
  "homepage" => (object)array("@id" => "http://schema.org/url", "@type" => "@id"),
  "image" => (object)array("@id" => "http://schema.org/image", "@type" => "@id")
);

// compact a document according to a particular context
// see: http://json-ld.org/spec/latest/json-ld/#compacted-document-form
$compacted = jsonld_compact($doc, $context);

//echo json_encode($compacted, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);



foreach ($this->return_namespaces() as $key => $value){

EasyRdf_Namespace::set($key, $value);


}

EasyRdf_Namespace::delete("rss");

$graph = new EasyRdf_Graph();

$lh_rdf_object_handlers = new LH_rdf_object_handlers($format);


if ($theobject = get_queried_object()){

$thetype = get_class($theobject);

$action = "do_content_".$thetype;

$graph = $lh_rdf_object_handlers->$action($graph,$theobject);

} elseif ( is_attachment() ) {


global $wp_query;




if ($wp_query->query[attachment_id]){


$args=array( 'post__in' => array($wp_query->query[attachment_id]) , 'post_type' => 'attachment' );


} else {

$args=array(
	'name'           => $wp_query->query[attachment],
	'post_type'      => 'attachment',
	'posts_per_page' => 1
);

}

$attachment_post = get_posts($args);

$graph = $lh_rdf_object_handlers->do_content_attachment($graph,$attachment_post[0]);

} else {

global $wp_query;

$graph = $lh_rdf_object_handlers->do_content_wp_query($graph,$wp_query);


}

$graph = apply_filters( "lh_rdf_graph", $graph);

$serialize = $graph->serialise($format);

$etag = md5($serialize); 

header("Etag: ".$etag); 

echo $serialize;


}

function add_link_to_head() {

echo "\n\n<!-- Start LH RDF -->\n";
	
foreach ($this->format_mapper as $key => $value){

echo "<link rel=\"meta\" type=\"".$value."\" title=\"SIOC\"  href=\"".$this->get_link($key)."\" />\n";

}

echo "<!-- End LH RDF -->\n\n";

}

private function check_if_rdf_request() {

foreach ($this->format_mapper as $key => $value){

if ( $_SERVER['HTTP_ACCEPT'] == $value ) {

$return = $key;

}

} 

if (!$return){

return false;

} else {

return $return;

}

}

function get_control() {

if (!is_feed()){


if ( $format = $this->check_if_rdf_request() ) {

$redir = $this->get_link($format);

if ( !empty($redir) ) {
@header( "Location: " .  $redir );
die();
}



}



} 
	
}

public static function query_var($vars) {
    $vars[] = '__datadump';
    return $vars;
  }

public static function parse_query($wp) {
    if (!array_key_exists('__datadump', $wp->query_vars)) {
      return;
    }

//$format = $this->map_mime_request();

include('library/EasyRdf.php');

include('library/object-handlers.php');

foreach ($this->standard_namespaces as $key => $value){

EasyRdf_Namespace::set($key, $value);


}

EasyRdf_Namespace::delete("rss");

$graph = new EasyRdf_Graph();

//$lh_rdf_object_handlers = new LH_rdf_object_handlers($format);



die;

}

public function init() {

add_feed('lhrdf', array($this, 'do_feed_rdf'));
add_feed('lhenhanced', array($this, 'do_feed_enhanced'));

}


public function __construct() {

add_action('init', array($this, 'init'));
add_action('template_redirect', array($this, 'get_control'));
add_action('wp_head', array($this, 'add_link_to_head'));
add_filter('query_vars', array($this, 'query_var'));
add_action('parse_query', array($this, 'parse_query'));

}

}

$lh_rdf = new LH_rdf_plugin();



include_once('library/relationships.php');





?>
<?php
$wp_did_header = true;
require_once( dirname(__FILE__) . '/../../../wp-load.php' );
wp();

require_once (dirname(__FILE__)."/class/ns_category_image.php");

$res= ns_category_image::delete_image($_REQUEST["category_id"]);
echo $res;

?>

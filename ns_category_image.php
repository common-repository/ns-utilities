<?php

$uploads = wp_upload_dir();


$path = $uploads['basedir'] . "/ns_category_images/";
$url = $uploads['baseurl'] . "/ns_category_images/";





define("NS_CAT_IMAGES_BASE_PATH",$path);
define("NS_CAT_IMAGES_BASE_URL",$url);

require_once dirname(__FILE__) . "/class/ns_object_class.php";

register_activation_hook(dirname(__FILE__) . "/ns_utilities.php", 'create_category_image_folder');

function create_category_image_folder(){
   if (!is_dir(constant('NS_CAT_IMAGES_BASE_PATH')) ){
       if (!wp_mkdir_p(constant('NS_CAT_IMAGES_BASE_PATH'))){
           echo "CANNOT CREATE!!" . constant('NS_CAT_IMAGES_BASE_PATH');
       }
   }

}

create_category_image_folder();

?>
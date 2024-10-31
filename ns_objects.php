<?php
register_activation_hook(dirname(__FILE__) . "/ns_utilities.php", 'create_table_ns_objects'); 

require_once dirname(__FILE__) . "/class/ns_object_class.php";
  
function create_table_ns_objects() {
    global $wpdb;
    global $nslt_dbVersion;

    $tablename = ns_object::get_table_name();   
        
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE {$table_name} (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`object_type` VARCHAR( 255 ) NOT NULL ,
`value` TEXT NOT NULL ,
`related_with` VARCHAR( 255 ) NOT NULL ,
`external_keys` TEXT NOT NULL ,
`active` TINYINT NOT NULL ,
INDEX ( `object_type` , `related_with` , `active` )
);";
                
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        add_option("nsu_dbVersion", $nslt_dbVersion);
    }

    
}
  
if (!function_exists("get_object_by_id")){
    function get_object_by_id($obj_id){
        return ns_object::get_by_id($obj_id);
    }
}
if (!function_exists("create_object")){
    function create_object($object_type,$value,$related_with = "",$external_keys = false,$active = true,$save = true){
        return ns_object::create($object_type,$value,$related_with ,$external_keys,$active,$save);
    }
}


  
  
  
?>
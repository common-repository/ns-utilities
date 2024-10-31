<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


function get_childs_category($cat_id){
	global $wpdb;

    $args = array(
        "parent" => $cat_id,
        "hide_empty" => 0,
        "hierarchical" => false
    );
    $categories = get_categories( $args );
    
    $previous = array();
   	foreach($categories as $cat){
   		$previous[]=get_category_meta($cat->term_id,'ns_tbp_rel_previous');
   	}
   	
   	$ordered = array();
   	$previous_cat_id=0;
   	
   	for($i=0;$i<count($categories);$i++){
   		$pos=array_search($previous_cat_id,$previous);
   		//echo "***".$previous_cat_id;
   		$ordered[]=$categories[$pos];
   		$last_cat=$ordered[$i];
   		$previous_cat_id=$last_cat->term_id;
   	}
   	
   	return $ordered;

    //return $categories;
}


?>
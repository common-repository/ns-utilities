<?php

/**
 * Description of ns_category_meta
 *
 * @author joseantonio
 */
class ns_category_meta {
    
	private $id;
	private $term_id;
	private $meta_key;
	private $meta_value;


        /**
         * Nos retorna el meta de una categoria y si no lo tiene mira a ver si su padre
         * lo tiene, segun el param $father
         * @global <type> $wpdb
         * @param <type> $term_id
         * @param <type> $meta_key
         * @param <type> $father ;-1 todos hasta llegar al ultimo,0 no busca, n con n>0 busca hasta ese nivel
         * @return <type>
         */
   	static function get_category_meta($term_id,$meta_key,$father=0){
            global $wpdb;

            $sqlStr = "SELECT meta_value FROM ".$wpdb->prefix."ns_category_meta
                                    WHERE (term_id='".$term_id."' AND meta_key='".$meta_key."' )";

            $rs = mysql_query($sqlStr) or die($sqlStr . " --> " . mysql_error());
           // echo ' '.$sqlStr;
            if ( $rs && mysql_numrows($rs)>0 ){

                    $category_meta = mysql_fetch_object($rs);
                    return unserialize($category_meta->meta_value);
            }
            else{
//                Si esta categoria no tiene ese meta mira si su padre (de wordpress)lo tiene
//                en caso de que se lo especifiquemos con el parametro $father
                if($father==-1){
                    $category_obj= get_term( $term_id, 'category' );
                    
                    if($category_obj->parent){
                     	return get_category_meta($category_obj->parent ,$meta_key,$father);
                    }
                }elseif($father>0){
                    $category_obj= get_term( $term_id, 'category' );

                    if($category_obj->parent){
                     	return get_category_meta($category_obj->parent ,$meta_key,$father-1);
                    }

                }
                return false;

            }
   	}


   	static function update_category_meta($term_id,$meta_key,$meta_value){
                global $wpdb;
       	
                if(is_numeric($meta_value)){
   			$meta_value=sprintf("%d",$meta_value);
   		}
       	
                $meta_value = serialize($meta_value);
       	
       	$already_inserted=false;
       	
       	$sqlStr = "SELECT * FROM ".$wpdb->prefix."ns_category_meta
                    		WHERE (term_id='".$term_id."' AND meta_key='".$meta_key."' )";
		
       	$rs = mysql_query($sqlStr) or die($sqlStr . " --> " . mysql_error());

       	if ( $rs && mysql_numrows($rs)>0 ){  	
	       	// If already exists key, update
	       	$sqlStr = "UPDATE ".$wpdb->prefix."ns_category_meta SET meta_value='{$meta_value}'
	            			WHERE (term_id='{$term_id}' AND meta_key='{$meta_key}')";
                                        //echo $sqlStr;
	    }
	    else{        
	        // Else: insert
	        $sqlStr = "INSERT INTO ".$wpdb->prefix."ns_category_meta(term_id,meta_key,meta_value) VALUES ('{$term_id}','{$meta_key}','{$meta_value}')";
	    }
        
       	if(mysql_query($sqlStr) or die($sqlStr . " --> " . mysql_error())){
       		return true;
       	}
       	else{
       		return false;
       	}

   	}
   	
   	static function delete_category_meta($term_id,$meta_key){
       	global $wpdb;
        $sqlStr = "DELETE FROM ".$wpdb->prefix."ns_category_meta
                    	WHERE (term_id='{$term_id}' AND meta_key='{$meta_key}' )";
                    	
        $rs = mysql_query($sqlStr) or die($sqlStr . " --> " . mysql_error());
       	
       	return  $rs;
   	}
   
   	static function get_categories_with_meta($meta_key, $meta_value, $parent_category = 0, $depth = 0){
   		global $wpdb;
   		
   		if(is_numeric($meta_value)){
   			$meta_value=sprintf("%d",$meta_value);
   		}
   		
   		$meta_value = serialize($meta_value);
   		
   		//echo $meta_value;
   		
   		$sqlStr = "SELECT * FROM ".$wpdb->prefix."ns_category_meta
   					WHERE (meta_key='{$meta_key}' AND meta_value='{$meta_value}')";
   		
   		//echo $sqlStr;
   		
   		$rs = mysql_query($sqlStr) or die($sqlStr . " --> " . mysql_error());
   		
   		if( $rs && mysql_num_rows($rs) > 0){
	   		$result=array();
	   		
	   		while($obj = mysql_fetch_object($rs)){
	   			$result[]=$obj->term_id;
	   		}
	   		
	   		if($depth == 1){
	   			$method = 'parent';
	   		}
	   		else{
	   			$method = 'child_of';
	   		}
	   		
	   		$args = array(
			    'type'          => 'post',
			    'hide_empty'    => 0,
			    'include'       => implode(',',$result),
			    'taxonomy'      => 'category',
			    'depth'			=> $depth,
			    $method			=> $parent_category
			);
			
			return get_categories($args);
	   		
		}	
		else{
			return false;
		}   		

   	}

}

function get_category_meta($term_id,$meta_key,$father=0){
	return ns_category_meta::get_category_meta($term_id,$meta_key,$father);
}

function update_category_meta($term_id,$meta_key,$meta_value){
	return ns_category_meta::update_category_meta($term_id,$meta_key,$meta_value);
}

function delete_category_meta($term_id,$meta_key){
	return ns_category_meta::delete_category_meta($term_id,$meta_key);
}

?>
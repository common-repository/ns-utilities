<?php

global $wpdb;

/**
 * @class NSTimebasedPostsRelative
 *
 * 		This class defines the behaviour of the entire plugin, managing all the neccesary data,
 *		defining helper methods for accessing the protected posts, its categories, storing the
 *		posts and user metas. It also defines a widget for displaying recent unblocking information
 *		to the users.
 *
 */
class NSTimebasedPostsRelative{

	/**
	 * @brief Relative date calculator.
	 *
	 *		This function gets a date diff'ed forward the days specified as argument.
	 *
	 * @param $date The original date.
	 * @param $diff The days that the date must be incremented.
	 * @return A timestamp representing the date+diff days
	 */
	static function getDatetime($date, $diff){
		return strtotime("+$diff days", strtotime($date));
	}
	
	/**
	 * @brief Query for all time based posts.
	 *
	 * @returns All time based posts.
	 */
	static function getAllTimebasedPosts(){
		$args = array(
					'numberposts' => -1,
					'post_status' => 'publish',
					'meta_query' => array(
						array(
							'key' => 'ns_tbp_days',
							'value' => '0',
							'compare' => '>'
						)
					)
				);
				
		return get_posts( $args );
	}
	
	
	
	
	    /**
     * @brief Query for all time based posts ordered.
     *
     * @returns All time based posts with a order.
     */
    static function getAllTimebasedPostsOrdered(){
        $args = array(
                    'numberposts' => -1,
                    'post_status' => 'publish',
                    'order'=>'ASC',
                    'orderby'=>'meta_value',
                    'meta_key'=>'ns_tbp_days',
                    'meta_query' => array(
                        array(
                            'key' => 'ns_tbp_days',
                            'value' => '0',
                            'compare' => '>'
                        )
                    )
                );
                
        return get_posts( $args );
    }
	
	
	
	
	
	
	
	
	/**
	 * @brief Query for all posts that are not time based.
	 *
	 * @returns All the posts that are not time based.
	 */
	static function getAllNotTimebasedPosts(){
		$args = array( 'numberposts' => -1 );
		$posts = get_posts( $args );
		$tb_posts = NSTimebasedPostsRelative::getAllTimebasedPosts();
		
		$result = array();
		
		foreach($posts as $post){
			if( !in_array($post, $tb_posts) ){
				$result[] = $post;
			}
		}
		
		return $result;
	}
	
	/**
	 * @brief Query for all time based posts that belongs to the specified category.
	 *
	 * @params $cat_id The ID of the category.
	 * @returns All time based posts of the specified category.
	 */
	static function getTimebasedPosts($cat_id){
		$args = array(
					'numberposts' => -1,
					'post_status' => 'publish',
					'meta_query' => array(
						array(
							'key' => 'ns_tbp_days',
							'value' => '0',
							'compare' => '>'
						),
						array(
							'key' => 'ns_tbp_membership',
							'value' => $cat_id,
							'compare' => '='
						)
					)
				);
               // echo "getTimebasedPosts: cat= $cat_id <br>";
		return get_posts( $args );
	}
	
	
	
	
	
	    /**
     * @brief Query for all time based posts that belongs to the specified category with a order.
     *
     * @params $cat_id The ID of the category.
     * @returns All time based posts of the specified category with a order.
     */
    static function getTimebasedPostsOrdered($cat_id){
        $args = array(
                    'numberposts' => -1,
                    'post_status' => 'publish',
                    'order'=>'ASC',
                    'orderby'=>'meta_value_num',
                    'meta_key'=>'ns_tbp_days',
                    'meta_query' => array(
                        array(
                            'key' => 'ns_tbp_days',
                            'value' => '0',
                            'compare' => '>'
                        ),
                        array(
                            'key' => 'ns_tbp_membership',
                            'value' => $cat_id,
                            'compare' => '='
                        )
                    )
                );
                
        return get_posts( $args );
    }
    
	
	
	
	
	
	
	
	static function getTimebasedPostsVisited($cat_id){
	
		global $user_ID;
		
		$postsarray=NSTimebasedPostsRelative::getTimebasedPostsOrdered($cat_id);
		
		
		$array_visited_posts=get_user_meta($user_ID, 'ns_tbp_visited_'.$cat_id);
		$lastdays=-1;
		foreach ($postsarray as $p){
	        $new=get_post_meta($p->ID,'ns_tbp_days',true);
	        $aux=new stdClass();
	        if(!$array_visited_posts || !in_array($p->ID,$array_visited_posts[0])){
	           
	            $aux->post=$p;
	            $aux->visited=false;
	            $aux->image=NSTimebasedPostsRelative::getPostThumbnail($p->ID);
	            $aux->days=$new;
	            
	        }else{
	            $aux->post=$p;
	            $aux->visited=true;
	            $aux->image=NSTimebasedPostsRelative::getPostThumbnail($p->ID);
	            $aux->days=$new;
	        }
	        if($lastdays==$new){
	            
	            $lastel=array_pop($post_array_visited);
	            if(is_array($lastel)){
	                $lastel[]=$aux;
	                $aux=$lastel;
	
	            }else{
	                $arr = array();
	                $arr[]=$lastel;
	                $arr[]=$aux;
	                $aux=$arr;
	                
	            }
	        
	
	            
	        }else{
	             
	            $lastdays=$new;
	           
	        }
	        $post_array_visited[]=$aux;
	
		}
		
		foreach ( $post_array_visited as &$v){
		
	        if(is_array($v)){
	            $element=$v[0];
	            if( ($meta=get_user_meta($user_ID,'ns_tbp_order_'.$element->days,true)) != '' ){
	            
	                NSTimebasedPostsRelative::sortPosts($meta,$v);
	                
	                
	                
	            }
	        }
	        
	        
	    }
	            //ordernar usort
		return $post_array_visited;
		
		
		
	}
    
    static function cmp($a, $b){
        global $array_posts;

        $pa=$a->post;
        $pb=$b->post;
        return (array_search($pa->ID,$array_posts) < array_search($pb->ID,$array_posts)) ? -1 : 1;
    }

    static function sortPosts($meta,&$v){
        global $array_posts;
        $array_posts=explode(',',$meta);
        
        usort($v, 'NSTimebasedPostsRelative::cmp');
    }


	/**
	 * @brief Retrieve the days of a post.
	 * 
	 * @param $post_id The id of a post.
	 * @return The days for the given post.
	 */
	static function getDays($post_id){
		return get_post_meta($post_id, 'ns_tbp_days', true);
	}
	
	/**
	 * @brief Retrieve the memberhsip level (category id) of a post.
	 * 
	 * @param $post_id The id of a post.
	 * @return The membership level for the given post.
	 */
	static function getMembershipLevel($post_id){
		return get_post_meta($post_id, 'ns_tbp_membership', true);
	}
	
	/**
	 * @brief Retrieve the points of a post.
	 *
	 *		This function gets the points of a given post. If required, it also calculates the points
	 *		based on the inmediate previous post of the same category.
	 *
	 * @param $post_id The id of a post.
	 * @param $compute If the method must calculate the points (default=false).
	 * @return The points of the given post.
	 */
/*	static function getPoints($post_id, $compute = false){
		global $wpdb;
		
		$points = get_post_meta($post_id, 'ns_tbp_points', true);
		$my_days = NSTimebasedPostsRelative::getDays($post_id);
		$my_level = NSTimebasedPostsRelative::getMembershipLevel($post_id);
		
		if($compute){
			if( !$points || $points == 0 || $points == ''){
				$days = $wpdb->get_results("SELECT `meta_value` FROM $wpdb->postmeta WHERE `meta_key` = 'ns_tbp_days' AND `post_id` IN (SELECT post_id FROM $wpdb->postmeta WHERE `meta_key` = 'ns_tbp_membership' AND `meta_value` = ".$my_level.")", ARRAY_N);
				
				rsort($days, SORT_NUMERIC);
				
				foreach($days as $day){
					if($day[0] < $my_days){
						$prev_day = $day[0];
						break;
					}
				}
				
				$points = ( $my_days - $prev_day);
				
				
			}
			update_post_meta($post_id, 'ns_tbp_points', $points);
		}
		return $points;
		
	}
*/
	static function updateTBPost($post_id, $days, $membership_level){
        //static function updateTBPost($post_id, $days, $membership_level, $points = 0){
		if($days > 0 && $membership_level != ''){
			update_post_meta($post_id, 'ns_tbp_days', $days);
			//update_post_meta($post_id, 'ns_tbp_points', $points);
			update_post_meta($post_id, 'ns_tbp_membership', $membership_level);
		}
	}
	
	/**
	 * @brief Update the days and membership level metas of all the posts passed as argument.
	 *
	 * @param $post_ids Array with the ids of the posts to be updated.
	 * @param $days Array containing the days for each post, indexed by post_id.
	 * @param $membership_levels Array containing the memberhsip levels for each post, indexed by post_id.
	 *
	 */
	static function updateTBPosts($post_ids, $days, $membership_levels){
		$posts = NSTimebasedPostsRelative::getAllTimebasedPosts();
        if (!is_array($post_ids)){ //Michele : 10/05/2011
        	$post_ids=array();
        }
        foreach($posts as $post){
                // There was a timebased post that now isn't time based, remove
                // metas
                if( !in_array($post->ID, $post_ids) ){
                        delete_post_meta($post->ID, 'ns_tbp_days');
                        delete_post_meta($post->ID, 'ns_tbp_membership');
                }
        }

        foreach($post_ids as $post_id){
                //$points = get_post_meta($post_id, 'ns_tbp_points', true);
                //NSTimebasedPostsRelative::updateTBPost($post_id, $days[$post_id], $membership_levels[$post_id], $points);
                NSTimebasedPostsRelative::updateTBPost($post_id, $days[$post_id], $membership_levels[$post_id]);
        }
        
	}
	
	/**
	 * @brief Retrieves all the parent categories defined in the blog.
	 *
	 * @return All the parent categories (categories which its parent is 0).
	 */
	static function getCourses(){
		$args = array(
			'parent'				   => 0,
		    'orderby'                  => 'name',
		    'order'                    => 'ASC',
		    'hide_empty'               => 0,
		    'hierarchical'             => 0,
		    'taxonomy'                 => 'category',
		    'pad_counts'               => false );
		    
		return get_categories( $args );
	}
	
	static function filterPostsContents($posts){
		global $user_ID;
		
		foreach($posts as $post){
			$post->post_content = NSTimebasedPostsRelative::filterPostContent($post->ID, $user_ID, $post->post_content);
		}
		return $posts;
	}
	
	static function savePostMetas($post_id){
		NSTimebasedPostsRelative::updateTBPost($post_id, $_REQUEST['tbp_days'], $_REQUEST['tbp_level'], $_REQUEST['tbp_points']);	
	}
	
	/**
	 * @brief Get all the posts for the current user except the disallowed ones.
	 *
	 * @return Array with the posts that the user is allowed to see.
	 */
	static function filterDisallowedPosts($posts){
	    global $user_ID;
            global $userdata;
            get_currentuserinfo();
            //echo "**** user level: ".$userdata->user_level;
            if ($userdata->user_level >= 10){
                return $posts;
            }
	
		$timebasedposts_beforetime_text = get_option('timebasedposts_beforetime_text');
		
	    if (count($posts)>1){
	        $posts_ret = array();
	        foreach ($posts as $p){
	            if (strpos($p->post_content, $timebasedposts_beforetime_text) === false){
	                $posts_ret[] = $p;            
	            }
	        }
	        return $posts_ret;
	    }
	    else {
	        return $posts;
	    }      
	}

	/**
	 * @brief Admin options page definition
	 *
	 *		This function prints the entire HTML of the plugin option page.
	 *
	 */
	static function getAdminOptionsPage() {
		//Update Option
		if ('process' == $_POST['stage']) {
		    update_option('timebasedposts_beforetime_text', $_POST['timebasedposts_beforetime_text']);
		    update_option('timebasedposts_tag', $_POST['timebasedposts_tag']);
		    NSTimebasedPostsRelative::updateTBPosts($_POST['timebasedposts_posts'], $_POST['timebasedposts_days'], $_POST['timebasedposts_levels']);
		}
		
		//Main Option Page
		$timebasedposts_tag = get_option('timebasedposts_tag');
		$timebasedposts_beforetime_text = get_option('timebasedposts_beforetime_text');
		?>
		<form name="update_hidepost" method="post" >
		<input type="hidden" name="stage" value="process" /> 
			<div class="wrap">
				<h2><?php _e('Timebased Posts Options', 'ns_timebasedposts'); ?></h2>
				<br/>
				<div id="poststuff" class="ui-sortable meta-box-sortables">
					<div id="nstimebasedposts_options" class="postbox">
						<h3><?php _e('General Configuration', 'ns_timebasedposts'); ?></h3>
						<div class="inside" id="inside">
							<p>	
								<label><?php _e('Text to show before time elapsed:', 'ns_timebasedposts'); ?></label> 
								<input name="timebasedposts_beforetime_text" type="text" id="timebasedposts_beforetime_text" value="<?php echo $timebasedposts_beforetime_text; ?>" size="90" />	
							</p>
					
							<p>
								<label><?php _e('Tag:', 'ns_timebasedposts'); ?></label>
								<input name="timebasedposts_tag" type="text" id="timebasedposts_tag" value="<?php echo $timebasedposts_tag; ?>" size="12" />
								<br><?php _e('Example:', 'ns_timebasedposts'); ?> <small><?php _e('<i>protect</i>. Do not include <i>[</i> and <i>/]</i>', 'ns_timebasedposts'); ?></small>
								<br><?php _e('Hint:', 'ns_timebasedposts'); ?> <small><?php _e('Use [protect /] inside the posts', 'ns_timebasedposts'); ?></small>
							</p>
						</div>
					</div>		
				</div>
				
			<p class="submit" style="padding-top:0px;">
		    	<input type="submit" name="Submit" value="<?php _e('Save Options', 'ns_timebasedposts'); ?>  &raquo;" />
		    </p>
		    
		    
		    <?php
		    	$parent_categories = NSTimebasedPostsRelative::getCourses();
		    	$categories = $parent_categories;
		    	$categories[] = 'not-time-based';
		    
		    	foreach($categories as $category){ 
		    		if($category == 'not-time-based'){
		    			$posts = NSTimebasedPostsRelative::getAllNotTimebasedPosts();
		    			$cat_name = __('Not time based', 'ns_timebasedposts');
		    		}
		    		else{
		    			$posts = NSTimebasedPostsRelative::getTimebasedPosts($category->term_id);
                                        //echo serialize($posts);
		    			$cat_name = $category->name;
		    		}
		    		
		    		if(count($posts)>0){
		    
		    ?>
		    <h3><?php echo $cat_name; ?></h3>
			<table class="wp-list-table widefat fixed" cellspacing="0">
				<thead>
				<tr>
					<th scope='col' id='id' class='manage-column column-cb check-column'  style=""></th>
					<th scope='col' id='name' class='manage-column desc'  style=""><span><?php _e('Post Name', 'ns_timebasedposts'); ?></span></th>
					<th scope='col' id='days' class='manage-column desc'  style="width: 150px;"><span><?php _e('Days', 'ns_timebasedposts'); ?></span></th>
					<th scope='col' id='membersip_level' class='manage-column desc' style="width: 200px;"><span><?php _e('Membership level', 'ns_timebasedposts'); ?></span></th>
				</tr>
				</thead>
			
				<tfoot>
				<tr>
					<th scope='col' id='id' class='manage-column column-cb check-column'  style=""></th>
					<th scope='col' id='name' class='manage-column desc'  style=""><span><?php _e('Post Name', 'ns_timebasedposts'); ?></span></th>
					<th scope='col' id='days' class='manage-column desc'  style="width: 150px;"><span><?php _e('Days', 'ns_timebasedposts'); ?></span></th>
					<th scope='col' id='membersip_level' class='manage-column desc' style="width: 200px;"><span><?php _e('Membership level', 'ns_timebasedposts'); ?></span></th>
				</tr>	
				</tfoot>
			
				<tbody id="the-list" class='list:user'>
					<?php 
					
					foreach ($posts as $post){
						if( NSTimebasedPostsRelative::getDays($post->ID) > 0 ){ $checked="checked";} else{$checked="";} 
					?>
							<tr class="alternate">
								<th scope='row' class='check-column'><input type='checkbox' name='timebasedposts_posts[]' value='<?php echo $post->ID; ?>' class='id' <?php echo $checked; ?> /></th>
								<td class="postname column-postname"><a href="<?php echo get_permalink($post->ID);?>" /><?php echo $post->post_title;?></a></td>
								<td class="days column-days"><input type='text' name='timebasedposts_days[<?php echo $post->ID;?>]' value='<?php echo NSTimebasedPostsRelative::getDays($post->ID);?>' size='6' style="margin-right: 8px; text-align:right;" /><?php _e('days', 'ns_timebasedposts'); ?></td>
								<td class="membership column-membership">
									<select name='timebasedposts_levels[<?php echo $post->ID;?>]'>
										<option value="" selected="selected"><?php _e('Unselected', 'ns_timebasedposts'); ?></option>
										<?php foreach($parent_categories as $category) { ?>
										<option value="<?php echo $category->term_id;?>" <?php if(NSTimebasedPostsRelative::getMembershipLevel($post->ID) == $category->term_id){ echo 'selected'; } ?> ><?php echo $category->name;?></option>
										<?php } ?>
									</select>
								</td>
							</tr>
					<?php } ?>
				</tbody>
			</table>
			<br/><br/>
			<?php 
					} // End if
				} // End for
			?>
			
					<br><?php _e('Note:', 'ns_timebasedposts'); ?> <small><?php _e('Refresh this page everytime you add new protected posts', 'ns_timebasedposts'); ?></small>
				</p>
				
				<p class="submit" style="padding-top:0px;">
		    		<input type="submit" name="Submit" value="<?php _e('Save Options', 'ns_timebasedposts'); ?>  &raquo;" />
		   		</p>
			</div>
		</form>
		<?php
	}
	
	/**
	 * @brief Shows the input box on the "edit post" view for inserting the days and membership level values.
	 *
	 * @param $content The content of the post.
	 * @return The HTML and Javascript code for the input box.
	 * @see savePostMetas
	 */
	static function showTimebasedPostsFields($content){
		global $wpdb;
		
		$all_categories = NSTimebasedPostsRelative::getCourses();
		$post_id = $_REQUEST['post'];
		
		$days = NSTimebasedPostsRelative::getDays($post_id);
		$level = NSTimebasedPostsRelative::getMembershipLevel($post_id);
//		$points = NSTimebasedPostsRelative::getPoints($post_id, true);
		
//		$fields = '<table><tr><td><small>'.__('Days:', 'ns_timebasedposts').'</small></td><td><input type="text" name="tbp_days" size="5" style="text-align:right" value="'.$days.'" />&nbsp;'.__('days', 'ns_timebasedposts').'</td></tr><tr><td><small>'.__('Points:', 'ns_timebasedposts').'</small></td><td><input type="text" name="tbp_points" size="5" style="text-align:right" value="'.$points.'" />&nbsp;'.__('points', 'ns_timebasedposts').'</td></tr><tr><td><small>'.__('Membership Level:', 'ns_timebasedposts').'</small></td><td>';
                $fields = '<table><tr><td><small>'.__('Days:', 'ns_timebasedposts').'</small></td><td><input type="text" name="tbp_days" size="5" style="text-align:right" value="'.$days.'" />&nbsp;'.__('days', 'ns_timebasedposts').'</td></tr><tr><td><small>'.__('Membership Level:', 'ns_timebasedposts').'</small></td><td>';
		
		$fields .= '<select name="tbp_level" id="tbp_level">';
		
		$fields .= '<option value="">'.__('Select a category','ns_timebasedposts').'</option>';
		
		foreach($all_categories as $category){
			$selected = "";
			
			if($category->term_id == $level){
				$selected = "selected";
			}
		
			$fields .= '<option value="'.$category->term_id.'" '.$selected.'>'.$category->name.'</option>';
		}	
		
		$fields .= '</select>';
		$fields .='</td></tr></table>';
	
		$html = '
			<script type="text/javascript">
				var thediv = document.getElementById("side-sortables");
				
				var tbp_vars = document.createElement("div");
				tbp_vars.id = "tbp_vars";
				tbp_vars.className = "postbox";			
				
				var handle = document.createElement("div");
				
				handle.className = "handlediv";
				handle.title = "Close";
				
				var jump = document.createElement("br");
				
				handle.appendChild = jump;
				
				var hthree = document.createElement("h3");
				hthree.className = "hndle";
				var thespan = document.createElement("span");
				thespan.innerText = thespan.textContent= "'.__('Timebased Posts Settings','ns_timebasedposts').'";
				
				var inside = document.createElement("div");
				inside.id = "tbp_inside";
				inside.className = "inside";
				inside.innerText = inside.textContent= "";
				
				hthree.appendChild(thespan);
				
				tbp_vars.appendChild(handle);
				tbp_vars.appendChild(hthree);
				tbp_vars.appendChild(inside);
				
				thediv.insertBefore(tbp_vars, document.getElementById("categorydiv"));
				
				$("tbp_inside").innerHTML = \''.$fields.'\';
			</script>
		';
	
		return $content.$html;
	}
	

	static function filterPostContent($post_ID, $user_ID, $content){
	
		$timebasedposts_tag = get_option('timebasedposts_tag');
		$timebasedposts_beforetime_text = get_option('timebasedposts_beforetime_text');
		
		$tag = "[$timebasedposts_tag /]";
		
		$code_start = strpos($content, "[$timebasedposts_tag ");
		if (($code_start !== false)) $code_end = strpos($content, ' /]', $code_start);
	
		if (($code_start === false) || ($code_end === false)) { 
			// This content is not time based, do not filter it
	        return $content; 
		}
		else{
			// This content IS time based, filter based on the current user
			// and the post metas ns_tbp_days and ns_tbp_membership
			$days = NSTimebasedPostsRelative::getDays($post_ID);
			$category_id = NSTimebasedPostsRelative::getMembershipLevel($post_ID);
			$category = get_term_by('term_id', $category_id, 'category');
	       
			// If has protect tag && date time is elapsed
		    if (($user_ID != 0) && is_numeric($days)){ 
	            $todayStamp = mktime();
	            
	            $cat_id = $category->term_id;
				
	            $date_registered = get_usermeta($user_ID, "users_date_category_$cat_id");
	            
				$dueDatetimeStamp=getDatetime($date_registered, $days);
				
				$post_ID_permission = NSTimebasedPostsRelative::getFirstSameDate($user_ID, $days, $cat_id);
	            
	            if(($todayStamp - $dueDatetimeStamp) >= 0 && $post_ID_permission == $post_ID){
	                $new_content = $content;
	                
					$new_content = str_ireplace($tag,'',$new_content); //Remove tag
				}
				else{	                         
					$contentToShow = substr($content, 0, strpos($content, $tag));
					$new_content = $contentToShow . $timebasedposts_beforetime_text;			
					$new_content = str_ireplace($tag, '', $new_content) ;
				}
				
		    }
		    
	    	return $new_content;
	    }
	}
	
	
	static function getFirstSameDate($user_ID, $days, $cat_id){
	
	
	$order_posts=get_user_meta($user_ID,'ns_tbp_order_'.$days,true);
	
	$order_posts_array=explode(',',$order_posts);
	

	
	
	
    $visited_posts=get_user_meta($user_ID,'ns_tbp_visited_'.$cat_id,false);
    

    
    
    foreach($order_posts_array as $p){

        if(is_array($visited_posts[0]) && !in_array($p,$visited_posts[0])){
            return $p;
        }
        
    }

	return 0;
	
	}
	
	
	
	
	static function getPostCategory($post_id){
		//Michele Cumer - Ritorna la categoria giusta per il contenuto (nuova versione della sidebar del time release)
		global $tbp_categories_slugs;
		$slug = "";
		
		foreach ($tbp_categories_slugs as $s){
			if ($slug != ""){
		    	$slug .= " OR ";
		  	}
		  	$slug.= "wp_terms.slug = '$s'";
		}
		$sqlStr = "SELECT wp_term_relationships.object_id,wp_terms.slug FROM wp_term_relationships INNER JOIN wp_term_taxonomy ON wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id INNER JOIN wp_terms ON wp_term_taxonomy.term_id = wp_terms.term_id WHERE wp_term_taxonomy.taxonomy='category' AND ( $slug ) AND wp_term_relationships.object_id='$post_id'";
		//echo "*** ".$sqlStr. " ***";
		$rsCats = mysql_query($sqlStr);
		$cat = false;
		
		if ($rsCats && mysql_num_rows($rsCats)>0){
			while($c = mysql_fetch_object($rsCats)){
				if (!$cat){
					$cat = $c->slug;
				}
				else if ($cat == "platinum-training-program"){
					$cat = $c->slug;   
				}
			}
		}
		return $cat;
	
	}
     
     static function timebasedpostsWidget($args){
		extract($args);
		global $current_user, $user_ID, $widget_posts, $timebasedposts_tag;
		global $tbp_categories_slugs;
		
		$timebasedposts_posts = NSTimebasedPostsRelative::getAllTimebasedPosts();
		
        $filled_categories = array();
        
        foreach ($tbp_categories_slugs as $s){   
            $filled_categories[$s]["before"] = false;
            $filled_categories[$s]["after"] = false;
            $filled_categories[$s]["datediff_old"] = "";
            $filled_categories[$s]["textafter"] = "";        
        }
        
		$bg_path = getBarPath();
		
		$options = get_option('widget_timebasedpostswidget');
		
		
		$bargraph_width=empty($options['bargraph_width']) ? "100" : $options['bargraph_width'];
		$bargraph_height=empty($options['bargraph_height']) ? "10" : $options['bargraph_height'];
		$bargraph_border=empty($options['bargraph_border']) ? "1" : $options['bargraph_border'];
		$bargraph_bg=empty($options['bargraph_bg']) ? '140,140,140' : $options['bargraph_bg'];
		$bargraph_bar=empty($options['bargraph_bar']) ? '0,0,255' : $options['bargraph_bar'];
		$bargraph_borderc=empty($options['bargraph_borderc']) ? '50,50,50' : $options['bargraph_borderc'];

		$title = empty($options['title']) ? '' : $options['title'];
		$textbefore = empty($options['textbefore']) ? '' : $options['textbefore'];
		$textafter = empty($options['textafter']) ? '' : $options['textafter'];
		
		if( count($timebasedposts_posts)>0 ){
		
			foreach ($widget_posts as $post) {
				$post_ID = $post->ID;
				// This content IS time based, filter based on the current user
				// and the post metas ns_tbp_days and ns_tbp_membership
				$days = NSTimebasedPostsRelative::getDays($post_ID);
				$category_id = NSTimebasedPostsRelative::getMembershipLevel($post_ID);
				$category = get_term_by('term_id', $category_id, 'category');
		       
				// If has protect tag && date time is elapsed
			    if (($user_ID != 0) && is_numeric($days)){ 
		            $todayStamp = mktime();
		            
		            $cat_id = $category->term_id;
					
		            $date_registered = get_usermeta($user_ID, "users_date_category_$cat_id");
		            
					$dueDatetimeStamp=getDatetime($date_registered, $days);
		                                        
                    $dateDiff = $dueDatetimeStamp - $todayStamp;
					$post_slug_category = getPostCategory($post->ID);                               
                                
				    if($dateDiff >= 0) //-time left
				    {
					    if(!isset($prevSmallDateDiff) || ($dateDiff < $prevSmallDateDiff))
					    {
						    $prevSmallDateDiff = $dateDiff;
						    $Days = floor($dateDiff/(60*60*24));
						    $TotalDays = $Days;
						    $Hours = floor(($dateDiff-($Days*60*60*24))/(60*60));
						    $Minutes = floor(($dateDiff-($Days*60*60*24)-($Hours*60*60))/60);
						    $Seconds = floor($dateDiff-($Days*60*60*24)-($Hours*60*60)-($Minutes*60));
						    $Months = floor($dateDiff/(60*60*24*30));
						    $Years  = floor($dateDiff/(60*60*24*365));
						    
						    $percent_left = floor(($Days/$days) * 100);
						    $percent_done = 100 - $percent_left;
						    $Days = $Days % 30;
						    $bargraph = '<img src="' . $bg_path . '?done=' . $percent_done . '&amp;height=' . $options['bargraph_height'] . '&amp;width=' . $options['bargraph_width'] . '&amp;border=' . $options['bargraph_border'] . '&amp;border_c=' . $options['bargraph_borderc'] . '&amp;bar=' . $options['bargraph_bar'] . '&amp;bg=' . $options['bargraph_bg'] . '" alt="' . $percent_done . '% done" height="' . $options['bargraph_height'] . '" width="' . $options['bargraph_width'] . '" />';
						    $messagein = array('[USERNAME]', '[GIFT_DAYS]', '[YEARS_LEFT]', '[MONTHS_LEFT]', '[DAYS_LEFT]', '[TOTAL_DAYS_LEFT]', '[HOURS_LEFT]', '[MINUTE_LEFT]', '[SECONDS_LEFT]', '[PERCENT_DONE]', '[PERCENT_LEFT]', '[BARGRAPH]', '[URL]');
						    $messageout = array($current_user->display_name, $days, $Years, $Months, $Days, $TotalDays, $Hours, $Minutes, $Seconds, $percent_done, $percent_left, $bargraph, $post->guid);
						    
						    $textbeforeout = str_replace($messagein, $messageout, $textbefore)."<br><br>";
                            $textbeforeout = "<img src=\"".get_bloginfo('template_url')."/images/$post_slug_category.jpg\" style=\"float:left;margin-right:7px;\">". $textbeforeout;
                            $post_cat_d_w = $post_cat_d;
					    }
				    }
				    else //+ time elapsed
				    {  //Moduli gia sbloccati
                        
					    $dateDiff = abs($dateDiff);
                        if (!$filled_categories[$post_slug_category]["after"] || $filled_categories[$post_slug_category]["datediff_old"] == "" || $filled_categories[$post_slug_category]["datediff_old"] > $dateDiff ){
                            $filled_categories[$post_slug_category]["datediff_old"] = $dateDiff;
                            $filled_categories[$post_slug_category]["after"] = true;
                            
					        $Days = floor($dateDiff/(60*60*24));
					        $TotalDays = $Days;
					        $Hours = floor(($dateDiff-($Days*60*60*24))/(60*60));
					        $Minutes = floor(($dateDiff-($Days*60*60*24)-($Hours*60*60))/60);
					        $Seconds = floor($dateDiff-($Days*60*60*24)-($Hours*60*60)-($Minutes*60));
					        $Months = floor($dateDiff/(60*60*24*30));
					        $Years  = floor($dateDiff/(60*60*24*365));
					        $Days = $Days % 30;
					        if ($Days>1){
                                $date_sblocco = "$Days giorni";
                            }
                            elseif ($Days==1) {
                                $date_sblocco = "$Days giorno";          
                            }
                            elseif ($Months == 0) {
                                $date_sblocco = "Oggi" ;       
                            }
                            if ($Months > 0 ){
                                $date_sblocco = "$Months mesi e ". $date_sblocco;       
                            }
                            
					        $messagein = array( '[GIFT_DAYS]', '[YEARS_ELAPSED]', '[MONTHS_ELAPSED]', '[DAYS_ELAPSED]', '[TOTAL_DAYS_ELAPSED]', '[HOURS_ELAPSED]', '[MINUTE_ELAPSED]', '[SECONDS_ELAPSED]', '[URL]','[DATE_SBLOCCO]','[POST_TITLE]');
                            $messageout = array($days, $Years, $Months, $Days, $TotalDays, $Hours, $Minutes, $Seconds, $post->guid,$date_sblocco,$post->post_title);
                            
					        $textafterout = str_replace($messagein, $messageout, $textafter)."<br><br>";
                            $filled_categories[$post_slug_category]["textafter"] = "<img src=\"".get_bloginfo('template_url')."/images/$post_slug_category.jpg\" style=\"float:left;margin-right:7px;\">".$textafterout;
                            
                        }
				    }
                }    
					
			}
		}
        //Michele Cumer - Cambio lo sfondo in base alla categoria del contenuto!
        if ($post_cat_d_w == "platinum" ){
            $style_w = " style=\"background:#FFF;\"";
        }
        else if ($post_cat_d_w == "gold"){
            $style_w = " style=\"background:transparent;\"";            
        }
        $textafterout = "";
		foreach ($filled_categories as $fc){
            $textafterout .= $fc["textafter"];        
        }
        if ($textafterout!="") {
            $textafterout = "<b>Ciao $current_user->display_name!</b><br>" . $textafterout;
        }
        echo '<div id="timepost"'.$style_w.'>';
		echo $before_widget;
		
		echo $before_title . ''.$title.'' . $after_title;
		$intermedio = "<center><div style=\"height:1px;min-height:1px;width:90%;min-width:90%;background:#c6c6c6;margin-top:5px;margin-bottom:7px;\"><img src=\"".get_bloginfo('template_url')."/images/dot.gif\"></div></center>";
        if ($textafterout=="" || $textbeforeout== ""){
            $intermedio = "";    
        }
        echo $textafterout.$intermedio.$textbeforeout;
		echo $after_widget.'</div>';
	}
	
	static function timebasedpostsWidgetControl(){

		// Collect our widget's options.
		$options = $newoptions = get_option('widget_timebasedpostswidget');

		// This is for handing the control form submission.
		if ( $_POST['timebasedpostswidget-submit'] )
		{
			// Clean up control form submission options
			$newoptions['title'] = stripslashes($_POST['timebasedpostswidget-title']);
			$newoptions['textbefore'] = stripslashes($_POST['timebasedpostswidget-textbefore']);
			$newoptions['textafter'] = stripslashes($_POST['timebasedpostswidget-textafter']);
			
			$newoptions['bargraph_width'] = strip_tags(stripslashes($_POST['timebasedpostswidget-bargraph_width']));
			$newoptions['bargraph_height'] = strip_tags(stripslashes($_POST['timebasedpostswidget-bargraph_height']));
			$newoptions['bargraph_border'] = strip_tags(stripslashes($_POST['timebasedpostswidget-bargraph_border']));
			$newoptions['bargraph_bg'] = strip_tags(stripslashes($_POST['timebasedpostswidget-bargraph_bg']));
			$newoptions['bargraph_bar'] = strip_tags(stripslashes($_POST['timebasedpostswidget-bargraph_bar']));
			$newoptions['bargraph_borderc'] = strip_tags(stripslashes($_POST['timebasedpostswidget-bargraph_borderc']));
		}

		// If original widget options do not match control form
		// submission options, update them.
		if ( $options != $newoptions )
		{
			$options = $newoptions;
			update_option('widget_timebasedpostswidget', $options);
		}

		// Format options as valid HTML. Hey, why not.
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$textbefore = htmlspecialchars($options['textbefore'], ENT_QUOTES);
		$textafter = htmlspecialchars($options['textafter'], ENT_QUOTES);
		
		$bargraph_width=empty($options['bargraph_width']) ? "100" : $options['bargraph_width'];
		$bargraph_height=empty($options['bargraph_height']) ? "10" : $options['bargraph_height'];
		$bargraph_border=empty($options['bargraph_border']) ? "1" : $options['bargraph_border'];
		$bargraph_bg=empty($options['bargraph_bg']) ? '140,140,140' : $options['bargraph_bg'];
		$bargraph_bar=empty($options['bargraph_bar']) ? '0,0,255' : $options['bargraph_bar'];
		$bargraph_borderc=empty($options['bargraph_borderc']) ? '50,50,50' : $options['bargraph_borderc'];
		?>
	<div>
	<label for="timebasedpostswidget-title">
	Title:<BR><input style="width:95%" type="text" id="timebasedpostswidget-title" name="timebasedpostswidget-title" value="<?php echo $title; ?>" /></label> 
	
	<br><br><label for="timebasedpostswidget-textbefore">Text before time elapsed:&nbsp;Note: <small>Use these special variables: [USERNAME], [GIFT_DAYS], [YEARS_LEFT], [MONTHS_LEFT], [DAYS_LEFT], [TOTAL_DAYS_LEFT], [HOURS_LEFT], [MINUTE_LEFT], [SECONDS_LEFT], [PERCENT_DONE], [PERCENT_LEFT], [BARGRAPH], [URL]</small>
	<BR><textarea style="width:95%; height:30px" type="text" id="timebasedpostswidget-textbefore" name="timebasedpostswidget-textbefore"><?php echo $textbefore; ?></textarea>
	
	</label> 
	
	<br><br>
	<label for="timebasedpostswidget-textafter">
	Text after time elapsed:&nbsp;Note: <small>Use these special variables: [USERNAME], [GIFT_DAYS], [YEARS_ELAPSED], [MONTHS_ELAPSED], [DAYS_ELAPSED], [TOTAL_DAYS_ELAPSED], [HOURS_ELAPSED], [MINUTE_ELAPSED], [SECONDS_ELAPSED], [URL]</small>
	<br><textarea style="width:95%; height:30px" type="text" id="timebasedpostswidget-textafter" name="timebasedpostswidget-textafter"><?php echo $textafter; ?></textarea>
	
	</label> 
	
	<p><h3>Bar graph options</h3></p>
	
	<label for="timebasedpostswidget-bargraph_width">
	Width:<BR><input style="width:95%" type="text" id="timebasedpostswidget-bargraph_width" name="timebasedpostswidget-bargraph_width" value="<?php echo $bargraph_width; ?>" /></label> 
	<br>
	<label for="timebasedpostswidget-bargraph_height">
	Height:<BR><input style="width:95%" type="text" id="timebasedpostswidget-bargraph_height" name="timebasedpostswidget-bargraph_height" value="<?php echo $bargraph_height; ?>" /></label> 
	<br>
	<label for="timebasedpostswidget-bargraph_border">
	Border:<BR><input style="width:95%" type="text" id="timebasedpostswidget-bargraph_border" name="timebasedpostswidget-bargraph_border" value="<?php echo $bargraph_border; ?>" /></label> 
	<br>
	<label for="timebasedpostswidget-bargraph_bg">
	Background:<BR><input style="width:95%" type="text" id="timebasedpostswidget-bargraph_bg" name="timebasedpostswidget-bargraph_bg" value="<?php echo $bargraph_bg; ?>" /></label>
	<br>
	<label for="timebasedpostswidget-bargraph_bar">
	Bar:<BR><input style="width:95%" type="text" id="timebasedpostswidget-bargraph_bar" name="timebasedpostswidget-bargraph_bar" value="<?php echo $bargraph_bar; ?>" /></label> 
	<br>
	<label for="timebasedpostswidget-bargraph_borderc">
	Border color:<BR><input style="width:95%" type="text" id="timebasedpostswidget-bargraph_borderc" name="timebasedpostswidget-bargraph_borderc" value="<?php echo $bargraph_borderc; ?>" /></label> 
	<input type="hidden" name="timebasedpostswidget-submit" value="true">
	</div>
		<?php
		// end of widget_timebasedpostswidget_control()
	}
	
	/**
	 * @brief Get the Timebased Post Categories
	 *
	 *		This function gets all the categories that contains some Timebased Post.
	 *
	 * @return Array containing the categories with a Timebased Post.
	 */
	static function getCategoryObjects(){
		$categories=array();
		$allpost=NSTimebasedPostsRelative::getAllTimebasedPosts();
		
		foreach($allpost as $p){
			$cat=NSTimebasedPostsRelative::getMembershipLevel($p->ID);
			if(!in_array($cat,$categories)){
				$categories[]=get_term_by('id',$cat,'category');
			}
		}
		
		return $categories;
	}
	
        /**
    * @brief Add the points columns to the user view page.
    *
    *
    * @param $columns Array which contain the columns of the user view page.
    * @return Array which contain the columns of the user view page.
    */

/*
    static function addPointsColumn($columns) {

        $cats=NSTimebasedPostsRelative::getCourses();
        foreach($cats as $c){
            $columns['points'.$c->term_id] = 'Points'.$c->term_id;
            }
        return $columns;

    }
*/


    /**
    * @brief Show the fields to insert the points manually.
    *
    *
    * @param $user Object that contain information about the current user.
    */
    
    static function extraUserProfileFields( $user ) { 
    $cats=NSTimebasedPostsRelative::getCourses();

    ?>
<!--
    <h3><?//php _e("Extra profile information", "blank"); ?></h3>
    
    <table class="form-table">
    <?//php foreach($cats as $c) { ?>
    <tr>
    <th><label for="points_<?//=$c->term_id?>"><?//php _e("Points_".$c->name); ?></label></th>
    <td>
   < <input type="text" name="points_<?//=$c->term_id?>" id="points_<?//=$c->term_id?>" value="<?//php echo esc_attr( get_the_author_meta( 'tbp_user_points'.$c->term_id, $user->ID ) ); ?>" class="regular-text" /><br />
    <span class="description"><?//php _e("Enter the points."); ?></span>
    </td>
    </tr>
    <?//php } ?>
    </table>
-->
    <?php }



    /**
    * @brief Save the number of user points if you insert manually.
    *
    *
    * @param $user_id The id of a current user.
    */
/*  static function saveExtraUserProfileFields( $user_id ) {

    $cats=NSTimebasedPostsRelative::getCourses();
    
    if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }

    foreach ($cats as $c){
    
    update_user_meta( $user_id, 'ns_tbp_user_points'.$c->term_id, $_POST['points_'.$c->term_id] ,true);
    }

    }
*/

    /**
    * @brief Fill the points column with the current value
    *
    *
    * @param $value
    * @param $column_name The name of the filled column
    * @param $id The id of the current user
    * @return The number of points in the column.
    */
/*    static function mysiteCustomColumn($value, $column_name, $id) {

        $cats=NSTimebasedPostsRelative::getCourses();
        foreach($cats as $c){
        if( $column_name == 'points'.$c->term_id ) {
            return get_user_meta($id, 'ns_tbp_used_points_'.$c->term_id,true);
        }
        }
    
    }
*/
    
    
    
    /**
    * @brief Get the category of a given post.
    *
    *      This function query the parent category of a given post.
    *
    * @param $post_id The id of a post.
    * @return The id of the category that the post belongs to.
    */
    static function getPostCategoryID($post_id){

        $slug=getPostCategory($post_id);
        $category=get_term_by( 'slug', $slug,'category' );
        return $category->term_id;
        

    }
    
    
    
    /**
    * @brief Make the difference of two dates.
    *
    *      The result is a number of days.
    *
    * @param $date1 the first date.
    * @param $date2 the second date.
    * @return The number of days result of the difference.
    */

    static function diffDays($date1,$date2){

        $diff=abs(strtotime($date1)-strtotime($date2));
        return floor($diff/60/60/24);
    }
    
    
    /**
    * @brief Update the information about the post and points when the user access in a post the first time.
    *
    *
    * @param $current_posts The posts in the current page.
    * @return The posts in the current page.
    */

    static function updateVisited($current_posts){

        if(count($current_posts)==1 && is_single()){
            
            global $user_ID;
        
            $postid = $current_posts[0]->ID;
            

            
            $catid=NSTimebasedPostsRelative::getMembershipLevel($postid);
            
            $array_visited_posts=get_user_meta($user_ID, 'ns_tbp_visited_'.$catid);
            $array_visited_posts=$array_visited_posts[0];
            
//            $used_points=get_user_meta($user_ID, 'ns_tbp_used_points_'.$catid,true);
//            $post_points=get_post_meta($postid,'ns_tbp_points',true);
            
            $regdate=get_user_meta($user_ID,'users_date_category_'.$catid,true);
            $days=NSTimebasedPostsRelative::diffDays(date('Y-m-d'),$regdate);
            
            $postdays = NSTimebasedPostsRelative::getDays($postid);
            
            
            
            $post_ID_permission = NSTimebasedPostsRelative::getFirstSameDate($user_ID, $postdays, $catid);

//            if( ($used_points+$post_points) <= $days && $post_ID_permission==$postid){
//
//                if( !$array_visited_posts || !in_array( sprintf("%d",$postid), $array_visited_posts)){
//                    $array_visited_posts[]=$postid;
//
//                    update_user_meta($user_ID,'ns_tbp_visited_'.$catid,$array_visited_posts);
//
//
//
//
//                    if(!$used_points || $used_points==''){
//                        $used_points=0;
//                    }
//
//
//
//                    $used_points+=intval($post_points);
//
//                    update_user_meta($user_ID,'ns_tbp_used_points_'.$catid,$used_points);
//
//
//                }
//
//            }
        }
        return $current_posts;

    }


/*
    static function getRemainingPoints($uid,$cat_id){
    
    
        $used_points=get_user_meta($uid, 'ns_tbp_used_points_'.$cat_id,true);
        
        $regdate=get_user_meta($uid,'users_date_category_'.$cat_id,true);
        $days=NSTimebasedPostsRelative::diffDays(date('Y-m-d'),$regdate);
        
        $remaindays= $days - $used_points;
        
        return $remaindays;

    }
*/
    
    static function getPostThumbnail( $post_id ) {
    
        $args = array(
            'meta_key' => array( 'Thumbnail', 'thumbnail' ),
            'post_id' => $post_id,
            'attachment' => true,
            'the_post_thumbnail' => true,
            'size' => 'thumbnail',
            'default_image' => false,
            'order_of_image' => 1,
            'link_to_post' => true,
            'image_class' => false,
            'image_scan' => false,
            'width' => false,
            'height' => false,
            'format' => 'img',
            'meta_key_save' => false,
            'callback' => null,
            'cache' => true,
            'echo' => true,
        );

    /* Search the post's content for the <img /> tag and get its URL. */
    preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i', get_post_field( 'post_content', $args['post_id'] ), $matches );

    /* If there is a match for the image, return its URL. */
    if ( isset( $matches ) && $matches[1][0] )
        return array( 'url' => $matches[1][0] );

    return false;
    }


    static function nsTbpPageInstall() {

        global $wpdb;

        $the_page_title = __('Timebased posts', 'ns_timebasedposts');
        $the_page_name = 'timebased-posts';

        // the menu entry...
        delete_option("ns_tbp_page_title");
        add_option("ns_tbp_page_title", $the_page_title, '', 'yes');
        // the slug...
        delete_option("ns_tbp_content_page_name");
        add_option("ns_tbp_page_name", $the_page_name, '', 'yes');
        // the id...
        delete_option("ns_tbp_page_id");
        add_option("ns_tbp_page_id", '0', '', 'yes');

        $the_page = get_page_by_title( $the_page_title );

        if ( ! $the_page ) {

            // Create post object
            $_p = array();
            $_p['post_title'] = $the_page_title;
            $_p['post_content'] = "[ns-tbp /]";
            $_p['post_status'] = 'publish';
            $_p['post_type'] = 'page';
            $_p['comment_status'] = 'closed';
            $_p['ping_status'] = 'closed';
            $_p['post_category'] = array(1); // the default 'Uncatrgorised'

            // Insert the post into the database
            $the_page_id = wp_insert_post( $_p );
            

        }
        else {
            // the plugin may have been previously active and the page may just be trashed...

            $the_page_id = $the_page->ID;

            //make sure the page is not trashed...
            $the_page->post_status = 'publish';
            $the_page_id = wp_update_post( $the_page );

        }
        
        add_post_meta($the_page_id, '_allow_all', true, true) or update_post_meta($the_page_id, '_allow_all', true);
        
        delete_option( 'ns_tbp_page_id' );
        add_option( 'ns_tbp_page_id', $the_page_id );

    }

    static function nsTbpPageRemove() {

        global $wpdb;

        $the_page_title = get_option( "ns_tbp_page_title" );
        $the_page_name = get_option( "ns_tbp_page_name" );

        //  the id of our page...
        $the_page_id = get_option( 'ns_tbp_page_id' );
        if( $the_page_id ) {

            wp_delete_post( $the_page_id ); // this will trash, not delete

        }

        delete_option("ns_tbp_page_title");
        delete_option("ns_tbp_page_name");
        delete_option("ns_tbp_id");

    }






    static function filterNSTbpTag($content) {
        global $wpdb;
        global $current_user;
        get_currentuserinfo();
        
        $user_id = $current_user->id;
        
        $tag = "[ns-tbp /]";
        
        ob_start();
            include( dirname(__FILE__)."/../ns_tbp_list.php");
            $list = ob_get_contents();
        ob_clean();
            
        $new_content = str_replace($tag, $list, $content);
        
        return $new_content;
        
    }
    
    static function saveTbpOrder($uid,$days,$order_str){
        
        update_user_meta($uid,'ns_tbp_order_'.$days,$order_str);
    
    }
    
    
    static function getTbpOrder($uid,$days){
    
        $order_str=get_user_meta($uid,'ns_tbp_order_'.$days,true);
        
        return $order_str;
        
    
    }

    static function getTimebasedPostsVisitedInArray($cat){
    
        $posts = NSTimebasedPostsRelative::getTimebasedPostsVisited($cat);

        $post_array=array();


        foreach($posts as $po){

            if(is_array($po) && count($po)>0){
                               
                foreach($po as $p){
                    $post_array[]=$p;
                }   
            }else{
                    $post_array[]=$po;
            }
            
        }
        return $post_array;
   }

	  
}
// End of NSTimebasedPostsRelative Class


// Workaround: aliasing the method
if ( !function_exists('getDatetime')){

	function getDatetime($date, $diff){
		return NSTimebasedPostsRelative::getDatetime($date, $diff);
	}
}
    

?>
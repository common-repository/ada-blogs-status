<?php
/*
Plugin Name:
Plugin URI: http://www.wpmudev.org/project/list-all
Description: Creates a list of all blogs on a WPMU site.  CAPitalZ modified this file.
Author: Andrew Billits, CAPitalZ
Author URI: http://wpmudev.org
Version: 0.0.6
*/

global $day_diff;
$day_diff = "1";	//number of days gap before including the blog in the widgets - to hold the spam blogs from getting attention immediately

function echoArrayBlogList($arrayName, $show_desc = true, $show_avatar = true, $avatar_size = 16, $cache_key = '') {
    global $begin_wrap;
    global $end_wrap;
    
    $intArrayCount = 0;
    $bid = '';
	
    foreach ($arrayName as $arrayElement) {
        if (count($arrayElement) > 1) {
            echoArrayBlogList($arrayElement, $show_desc, $show_avatar, $avatar_size);
        } else {
            $intArrayCount = $intArrayCount + 1;
            if ($intArrayCount == 1) {
                $bid = $arrayElement;
				
				if ( substr_count($arrayElement['domain'], '.', 1) == 1 ) return; 	//exclude the main blog
                $tmp_name = get_blog_option( $bid, "blogname");
                $tmp_description = get_blog_option( $bid, "blogdescription");
				
                echo $begin_wrap;
				if($show_avatar) {
					$blog_users = get_users($arrayElement['blog_id']);
					$blog_users = array_slice( $blog_users, 0, 1 );
                	echo get_avatar( $blog_users[0]->user_id, $avatar_size );
				}
				echo "<a href='http://" . $arrayElement['domain'] . $arrayElement['path'] ."' title ='" . $tmp_description . "'>" . $tmp_name . "</a>";
				if($show_desc)
                	echo "<br /><span class='sub'>" . $tmp_description . "</span>";
				echo $end_wrap;
            }
        }
    }				
}

function ada_get_most_blogs($blogs, $show_desc = true, $show_avatar = true, $avatar_size = 16, $cache_key = '', $begin_wrap = '<li>', $end_wrap = '</li>') {
	
	if(!is_array($blogs)) return;
	
	$most_blogs = get_site_option( "ada_blogs_most_" . $cache_key );
	$update = false;
	if( is_array( $most_blogs ) ) {
    
    
		
    
    if (array_key_exists("time",$most_blogs))
  		{
    
 			 if( ( $most_blogs['time'] + 360 ) < time() ) { // cache for 360 seconds.
           	 $update = true;
      	 	 }
 	 }
	else
 	 {
 		  $update = true;
 	 }

    
    
	} else {
		$update = true;
	}
	
	if( $update == true ) {
		unset( $most_blogs );
		foreach($blogs as $blog){
			
			
			$blog = (is_array($blog)==1?$blog: (array) $blog);
			
 
		if($blog):
		
					

			//if ( substr_count($blog['domain'], '.', 1) == 1 ) continue; 	//exclude the main blog
			$most_blogs[$blog['blog_id']] = $blog;
			$most_blogs[$blog['blog_id']]['name'] = get_blog_option( $blog['blog_id'], "blogname");
			$most_blogs[$blog['blog_id']]['description'] = get_blog_option( $blog['blog_id'], "blogdescription");
			$most_blogs[$blog['blog_id']]['show_desc'] = $show_desc;
			$most_blogs[$blog['blog_id']]['show_avatar'] = $show_avatar;
        			

			if($show_avatar) {
				$blog_users = get_users($blog['blog_id']);
				$blog_users = array_slice( $blog_users, 0, 1 );
				$most_blogs[$blog['blog_id']]['avatar'] = get_avatar( $blog_users[0]->user_id, $avatar_size );
			}
		endif;
		}
		update_site_option( "ada_blogs_most_" . $cache_key, $most_blogs );
	}


	if(isset($most_blogs)) echo_blog_list($most_blogs, $begin_wrap, $end_wrap);
}

function echo_blog_list($blogs, $begin_wrap = '<li>', $end_wrap = '</li>') {
	
	foreach($blogs as $blog){
		echo $begin_wrap;
		if($blog['show_avatar'])
			echo $blog['avatar'];
		echo "<a href='http://" . $blog['domain'] . $blog['path'] ."' title ='" . $blog['description'] . "'>" . $blog['name'] . "</a>";
		if($blog['show_desc'])
			echo "<br /><span class='sub'>" . $blog['description'] . "</span>";
		echo $end_wrap;
	}
}
function list_all_wpmu_blogs($tmp_limit, $tmp_name_or_url, $tmp_begin_wrap, $tmp_end_wrap, $tmp_order) {
    global $wpdb;
    global $begin_wrap;
    global $end_wrap;
	global $day_diff;

    if ($tmp_limit == "") {
        //no limit
    } else {
        $limit = "LIMIT " . $tmp_limit;
    }
    if ($tmp_name_or_url == "") {
        $name_or_url = "name";
    } else {
        if ($tmp_name_or_url == "name") {
            $name_or_url = "name";
        } else {
            $name_or_url = "url";
        }
    }
    if ($tmp_begin_wrap == "" || $tmp_end_wrap == "" ) {
        $begin_wrap = "<p>";
        $end_wrap = "</p>";
    } else {
        $begin_wrap = $tmp_begin_wrap;
        $end_wrap = $tmp_end_wrap;
    }
    if ($tmp_order == "") {
        $order = "ORDER BY last_updated DESC";
    } else {
        if ($tmp_order == "updated") {
            $order = "ORDER BY last_updated DESC";
        }
        if ($tmp_order == "first_created") {
            $order = "ORDER BY blog_id ASC";
        }
        if ($tmp_order == "last_created") {
            $order = "ORDER BY blog_id DESC";
        }
    }
	
    $blog_list = $wpdb->get_results( "SELECT blog_id, domain, path FROM " . $wpdb->blogs. " WHERE public = '1' AND archived = '0' AND mature = '0' AND spam = '0' AND deleted ='0' AND (last_updated <= DATE_SUB(CURRENT_DATE(), INTERVAL " . $day_diff . " DAY)) " . $order . " " . $limit . "", ARRAY_A );
	
	if (count($blog_list) < 2){ // we don't want to display if there is no recent blogs nor the main blog
        echo "<p>There are currently no recent blogs</p>";
    } else {
        echoArrayBlogList($blog_list);
    }
}
?>
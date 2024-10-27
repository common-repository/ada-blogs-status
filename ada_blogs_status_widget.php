	<?php
/*
Plugin Name: Ada Blogs Status
Plugin URI: http://adadaa.net/1uthavi/new-updated-active-blogs-widgets/
Description: Adds a sidebar widget to let you display new/updated/active blogs from WordPress Multi-Site/ WordPressMU.
Author: CAPitalZ
Version: 0.3.0
Author URI: http://adadaa.net/1uthavi/
*/

function ada_widget_blogs_status_init(){
	
	$ada_plugin_name = 'ada_blogs_status_widget';	//this variable is not visible everywhere
	
	require_once(dirname(__FILE__) . "/list-all.php");	
	
	if ( !function_exists('ada_get_most_blogs') )
		return;

	function ada_get_tabbed_opts($selected, $default) {
		$ada_plugin_name = 'ada_blogs_status_widget';
		$opts = array(
			'none' => __('None', $ada_plugin_name),
			'updated' => __('Updated Blogs', $ada_plugin_name),
			'new' => __('New Blogs', $ada_plugin_name),
			'active' => __('Active Blogs', $ada_plugin_name),
		);
	
		if ( !$selected ) $selected = $default;
		
		foreach ( $opts as $id => $val ) { ?>

<option value="<?php echo $id; ?>" <?php if ( $selected == $id ) echo 'selected="selected"'; ?>> <?php echo $val; ?> </option>
<?php }
	}	
	
	function ada_widget_blogs_status($args) {
		global $wpdb;
		
		extract($args, EXTR_SKIP);
		
		wp_enqueue_script('jquery','','','',true);
		echo "<link rel='stylesheet' href='" . WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)) . "/css/default.css' type='text/css' />";
		echo "<script src='" . WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)) . "/js/default.js'  /></script>";
		
		$options = get_option('ada_widget_tabbed_sidebar');
		$show_desc = ($options['show_desc'] == 'yes') ? true : false;
		$show_avatar = ($options['show_avatar'] == 'yes') ? true : false;
		$avatar_size = abs($options['avatar_size']);
		$limit = abs($options['limit']);
	?>
<div id="ada-multi-sidebar" class="clearfix">
  <h5 class="widgettitle">
    <?php _e('Blogs\' Activity'); ?>
  </h5>
  <ul id="Ctabs" class="tabs clearfix">
    <?php ada_render_sidebar_tabs( $options['order'] ) ?>
  </ul>
  <?php	
    if (is_array($options) || is_object($options))
{
		foreach( $options['order'] as $tab ) :
		switch($tab) :
		
		case 'updated':
		
			//hard coded values
			$order = 'updated';
		?>
  <div style="display:block;" id="s-updated" class="widgetcontainer clearfix tabcontent ">
    <ul>
      <?php ada_get_most_blogs(get_last_updated(false,0,$limit),$show_desc, $show_avatar, $avatar_size, $tab); ?>
    </ul>
  </div>
  <!-- #s-updated -->
  <?php
		break;
		
		case 'new':
		
			//hard coded values
			$order = 'last_created';
		?>
  <div id="s-new" class="widgetcontainer clearfix tabcontent">
    <ul>
      <?php ada_get_most_blogs(get_sites(0,$limit,false),$show_desc, $show_avatar, $avatar_size, $tab); ?>
    </ul>
  </div>
  <!-- #s-new -->
  <?php
		break;
		
		case 'active':
		
			//hard coded values
			$order = 'last_created';
			$echo_results = false;
		?>
  <div id="s-active" class="widgetcontainer clearfix tabcontent">
    <ul>
      <?php //this will give Active Blogs List based on counts of Posts, Pages, Comments, and Trackbacks.
			ada_get_most_blogs(get_most_active_blogs($limit,$echo_results),$show_desc, $show_avatar, $avatar_size, $tab);
			?>
    </ul>
  </div>
  <!-- #s-active -->
            
  <?php
		break;
		
		endswitch;
		endforeach;
    }
	?>
   
  <script type="text/javascript">
	jQuery(document).ready(function($) {
 
		$('.tabs a').click(function(){
			switch_tabs($(this));
			return false;
		});
	 
		switch_tabs($('.defaulttab'));
		
		function switch_tabs(obj)
		{
			$('.tabcontent').hide();
			$('.tabs a').removeClass("selected");
			
			var id = obj.attr("rel");
			$('#'+id).show();
			obj.addClass("selected");
		}
	 
	});
	</script> 
</div>
<!-- #ada-multi-sidebar -->
<?php
	}
	
	function ada_widget_blogs_status_control() {
		$ada_plugin_name = 'ada_blogs_status_widget';
		
		$options = $newoptions = get_option('ada_widget_tabbed_sidebar');
		if ( isset($_POST['ada_blogs_status_widget_submit']) ) {
			$newoptions['order'] = $_POST['ada_blogs_status_widget_order'];
			$newoptions['show_desc'] = $_POST['ada_show_desc'];
			$newoptions['show_avatar'] = $_POST['ada_show_avatar'];
			$newoptions['avatar_size'] = abs($_POST['ada_avatar_size']);
			$newoptions['limit'] = abs($_POST['ada_limit']);
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('ada_widget_tabbed_sidebar', $options);
		}
		$order = $options['order'];
		$show_desc = $options['show_desc'];
		$show_avatar = $options['show_avatar'];
		$avatar_size = abs($options['avatar_size']);
		$limit = abs($options['limit']);
		if( !$order ) $order = array('updated', 'new', 'active');
		if( !$show_desc ) $show_desc = 'yes';
		if( !$avatar_size ) $avatar_size = '16';
		if( !$limit ) $limit = '10';
	?>
<p>
  <label for="ada_blogs_status_widget_order">
    <?php _e('Tabbed Sidebar Order:', $ada_plugin_name); ?>
  </label>
  <select name="ada_blogs_status_widget_order[0]" style="width: 200px">
    <?php ada_get_tabbed_opts( $order[0], 'updated'); ?>
  </select>
  <br />
  <select name="ada_blogs_status_widget_order[1]" style="width: 200px">
    <?php ada_get_tabbed_opts( $order[1], 'new'); ?>
  </select>
  <br />
  <select name="ada_blogs_status_widget_order[2]" style="width: 200px">
    <?php ada_get_tabbed_opts( $order[2], 'active'); ?>
  </select>
  <br />
  <br />
  <label for="ada_limit" title="<?php _e('Only enter integer', $ada_plugin_name); ?>">
    <?php _e('Limit:', $ada_plugin_name); ?>
  </label>
  <input style="width: 75px;" id="ada_limit" name="ada_limit" title="<?php _e('Only enter integer', $ada_plugin_name); ?>" type="text" value="<?php echo $limit; ?>" />
  <br />
  <label for="ada_show_desc">
    <?php _e('Show blog description:', $ada_plugin_name); ?>
  </label>
  <select style="width: 75px;" id="ada_show_desc" name="ada_show_desc">
    
    ';
				
    
    <option value="yes"<?php selected('yes', $show_desc); ?>>Yes</option>
    <option value="no"<?php selected('no', $show_desc); ?>>No</option>
  </select>
  <br />
  <label for="ada_show_avatar">
    <?php _e('Show avatar:', $ada_plugin_name); ?>
  </label>
  <select style="width: 75px;" id="ada_show_avatar" name="ada_show_avatar">
    
    ';
				
    
    <option value="yes"<?php selected('yes', $show_desc); ?>>Yes</option>
    <option value="no"<?php selected('no', $show_desc); ?>>No</option>
  </select>
  <br />
  <label for="ada_avatar_size" title="<?php _e('If yes is selected for \'Show avatar:\'', $ada_plugin_name); ?>">
    <?php _e('Avatar size:', $ada_plugin_name); ?>
  </label>
  <select style="width: 75px;" title="<?php _e('If yes is selected for \'Show avatar:\'', $ada_plugin_name); ?>" id="ada_avatar_size" name="ada_avatar_size">
    
    ';
				
    
    <option value="16"<?php selected('16', $avatar_size); ?>>16 x 16</option>
    <option value="32"<?php selected('32', $avatar_size); ?>>32 x 32</option>
  </select>
</p>
<input type="hidden" id="ada_blogs_status_widget_submit" name="ada_blogs_status_widget_submit" value="1" />
<?php	
	}
	
	function ada_render_sidebar_tabs($order) {
		$ada_plugin_name = 'ada_blogs_status_widget';
		
		$order = array_unique($order);
		$list = array(
			'updated'	=> __('Updated', $ada_plugin_name),
			'new'		=> __('New', $ada_plugin_name),
			'active'	=> __('Active', $ada_plugin_name),
		);
		
		$count = 0;
		if (is_array($order) || is_object($order))
{
		foreach ($order as $t) { ?>
<?php if ( $t != 'none' ) : ?>
<li><a  onclick="openCity(event, 's-<?php echo $t; ?>')" href="#s-<?php echo $t; ?>"   <?php if($count==0) echo 'class="defaulttab tablinks"'; else echo 'class="tablinks"'; ?> rel="s-<?php echo $t; ?>"><span><?php echo $list[$t]; ?></span></a></li>
<?php endif ?>
<?php $count++; }
	}
    }
	
	// Register Widgets
	function ada_register_widget() {
		$ada_plugin_name = 'ada_blogs_status_widget';
		
		
wp_register_sidebar_widget($ada_plugin_name,'Ada Blog Status','ada_widget_blogs_status');
wp_register_widget_control($ada_plugin_name,'Ada Blog Status','ada_widget_blogs_status_control');
//register_sidebar_widget( array(__('Ada Blogs Status', $ada_plugin_name), 'widgets'), 'ada_widget_blogs_status');

//register_widget_control( array(__('Ada Blogs Status', $ada_plugin_name), 'widgets'), 'ada_widget_blogs_status_control');

		
	}
	
	ada_register_widget();
}
		
		add_action('widgets_init', 'ada_widget_blogs_status_init');
?>


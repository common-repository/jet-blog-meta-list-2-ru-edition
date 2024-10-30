<?php
/*
Plugin Name: Jet Blog Meta List
URI: http://milordk.ru
Author: Jettochkin
Author URI: http://milordk.ru
Plugin URI: http://milordk.ru/r-lichnoe/opyt-l/cms/jet-blog-meta-list.html
Donate URI: http://milordk.ru/uslugi.html
Description: ru-Вывод списка (облака) блогов с сортировкой по последнему обновлению (последняя активность на блоге). en-Provides a list of blogs sorted by last update (the last activity on the blog).
Tags: BuddyPress, Wordpress MU, meta, blog, could
Version: 1.0
*/
?>
<?

function jetget_blog_metalist( $start = 0, $num = 10, $deprecated = '', $jincount = 1 ) {
	global $wpdb;
	$blogs = get_site_option( "blog_list" );
	$limit = $num +1;
	$update = false;
        $limit = $num+1;
	if( is_array( $blogs ) ) {
		if( ( $blogs['time'] + 60 ) < time() ) { // cache for 60 seconds.
			$update = true;
		}
	} else {
		$update = true;
	}

	if( $update == true ) {
		unset( $blogs );
		$blogs = $wpdb->get_results( $wpdb->prepare("SELECT blog_id, domain, path FROM $wpdb->blogs WHERE site_id = %d AND public = '1' AND archived = '0' AND mature = '0' AND spam = '0' AND deleted = '0' ORDER BY RAND() LIMIT ".$limit, $wpdb->siteid), ARRAY_A );
		foreach ( (array) $blogs as $details ) {
		
		$blog_list[ $details['blog_id'] ] = $details;
		if ($jincount=='1')
		  { 
		$blog_list[ $details['blog_id'] ]['postcount'] = $wpdb->get_var( "SELECT COUNT(ID) FROM " . $wpdb->base_prefix . $details['blog_id'] . "_posts WHERE post_status='publish' AND post_type='post'" );
		
		 }		
		}
		unset( $blogs );
		$blogs = $blog_list;
		update_site_option( "blog_list", $blogs );
	}

	if( false == is_array( $blogs ) )
		return array();

	if( $num == 'all' ) {
		return array_slice( $blogs, $start, count( $blogs ) );
	} else {
		return array_slice( $blogs, $start, $num );
	}
}

class BlogMetaList extends WP_Widget {
	function BlogMetaList() {
		parent::WP_Widget(false, $name = __('Jet Blog Meta List','blogmetalist') );
	}

	function widget($args, $instance) {
		extract( $args );
		echo $before_widget;
		echo $before_title . $instance['title'] . $after_title;
		$blog_list = jetget_blog_metalist(1, $instance['number'], true, $instance['jincount']);
		/* $jincount = $instance['jincount']; */
		$nummeta=0;
		$emstart=1;
		$jincount = isset($instance['jincount']) ? $instance['jincount']: false;  
        	?>
		<p align="center"> 
		<span><a href="http://milordk.ru/r-lichnoe/opyt-l/cms/jet-blog-meta-list.html" title="Jet Blog Meta List"><? echo '&diams; ';?></a></span>
		<? foreach ($blog_list AS $blog) { ?>
			<? $nummeta++;
			$blog_details = get_blog_details($blog['blog_id']);
			if ($jincount=='1')
			{
			$emcount = $blog['postcount']; 
			
			if ($emcount > $emcountbefore) { $emstart=($emstart+0.15); } else {
			if ($emcount <> $emcountbefore)
			{ 
			 $emstart=($emstart-0.15);
			}
			 }
			} else { $emstart = 1+(rand(1,10)/10); } 
			$emcountbefore=$emcount;
			 ?>
			<span style="font-size: <?php echo $emstart; ?>em;">			
            <?
			$jblogurl = get_blogaddress_by_id($blog['blog_id']);
			echo '<a href="'.$jblogurl.'" title="'.$blog_details->blogname.' - '.$emcount.'">'.$blog_details->blogname.'</a> ';
			echo '</span>';
			if ($nummeta<($instance['number']+1)) {
				echo '&diams; ';
			}
		} ?>
		</p>
		<? echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = strip_tags($new_instance['number']);
		$instance['jincount'] = $new_instance['jincount'];
		return $instance;
	}

	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'number'=>'','jincount'=>'1'));
		$title = strip_tags( $instance['title']); 
		$number = strip_tags( $instance['number']);
                $jincount = $instance['jincount'];
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'buddypress'); ?>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo attribute_escape( stripslashes( $title ) ); ?>" /></label></p>
		<p><?php 
		if (WPLANG == 'ru_RU' or WPLANG == 'ru_RU_lite' ) { echo 'Количество блогов для отображения:'; } else { echo 'Blogs count for show:'; }
		?></p>
		<p><input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo attribute_escape( stripslashes( $number ) ); ?>" /></label></p>
		<p><?php 
		if (WPLANG == 'ru_RU' or WPLANG == 'ru_RU_lite' ) { echo 'Учитывать количество записей блога:'; } else { echo 'Dependence on post count:'; }
		?></p>
                <p><input class="checkbox" type="checkbox" <?php if ($jincount) {echo '"checked"';} ?> id="<? echo $this->get_field_id('jincount'); ?>" name="<? echo $this->get_field_name('jincount'); ?>" value="1" /></p>
	<?php
	}
}
add_action('widgets_init', create_function('', 'return register_widget("BlogMetaList");'));

?>
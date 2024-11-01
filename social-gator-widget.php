<?php
/**
 * Plugin Name: Social Gator
 * Description: A aggreGATOR of all of your social networks
 * Version: 1.0
 * Author: Brian Onorio
 * Author URI: http://www.o3strategies.com
 */




class SocialGator extends WP_Widget {

	var $title;
	var $feeds;
	var $number;
	var $show_time;
	//var $show_icons;

	/**
	 * Widget setup.
	 */
	function SocialGator() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'social-gator', 'description' => __('A widget that aggregates your social feeds', 'social-gator') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'social-gator' );

		/* Create the widget. */
		$this->WP_Widget( 'social-gator', __('Social Gator', 'social-gator'), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );
		$this->title = $instance['title'];
		$this->feeds['fb'] = $instance['fb_rss'];
		$this->feeds['tw'] = $instance['tw_rss'];
		$this->feeds['yt'] = $instance['yt_rss'];
		$this->feeds['pc'] = $instance['pc_rss'];
		$this->feeds['fr'] = $instance['fr_rss'];
		$this->number = $instance['number'];
		$this->show_time = $instance['show_time'];
		//$this->show_icons = $instance['show_icons'];
		
		require_once (ABSPATH . WPINC . '/rss.php');
		//Adjust cache setting
		if ( !defined('MAGPIE_CACHE_AGE') ) {
			define('MAGPIE_CACHE_AGE', 5*60); // five minutes
		}
			
		
		$str = "<h3>" . $this->title . "</h3>";
		
		/* Before widget (defined by themes). */
		echo $before_widget;

		/* The Meat */
		//loop through feeds and create new array to be sorted chronologically
		$master = array();
		foreach($this->feeds as $feed) {
			if($feed) {
				$rss = fetch_rss($feed);
				if($rss) {
					$i = 1;
					foreach($rss->items as $item) {
						$date = strtotime($item['pubdate']);
						$date = date("c",$date);
						$master[$date] = $item;
						if($i >= $this->number) {
							break;
						}
						$i++;
					}
				}
			}
		}
		
		//sort chronologically			
		krsort($master);
		
		//start output
		$i = 1;
		$str .= "<ul id=\"social-gator\">";
		foreach($master as $item) {
			if(substr($item['link'],0,23) == "http://www.facebook.com") {
				$class = "s-facebook";
			} elseif(substr($item['link'],0,18) == "http://twitter.com") {
				$class = "s-twitter";
			} elseif(substr($item['link'],0,22) == "http://www.youtube.com") {
				$class = "s-youtube";
			} elseif(substr($item['link'],0,27) == "http://picasaweb.google.com") {
				$class = "s-picasa";
			} elseif(substr($item['link'],0,21) == "http://www.flickr.com") {
				$class = "s-flickr";
			} else {
				$class = "s";
			}
			$str .= "<li class=\"" . $class . "\"><a href=\"" . $item['link'] . "\" target=\"_blank\">" . $item['title'] . "</a>";
			if($this->show_time) {
				// Get the date + time of the last update from the RSS feed.
				$pubdate = $item[pubdate];

				// Convert this string to a time.
				$pubdate = strtotime($pubdate);

				// Calculate how long it's been since the status was updated.
				$today = time();
				$difference = $today - $pubdate;
	
				// Display how long it's been since the last update.
				$str .= "<p>(Updated ";

				// Show days if it's been more than a day.
				if(floor($difference / 86400) > 0) {
					$str .= floor($difference / 86400);
					if(floor($difference / 86400) == 1) { 
						$str .= ' day ago)'; 
					} else { 
						$str .= ' days ago)'; 
					}
				}

				// Show hours if it's been more than an hour.
				elseif(floor($difference / 3600) > 0) {				
					$str .= floor($difference / 3600);
					if(floor($difference / 3600) == 1) { 
						$str .= ' hour ago)'; 
					} else { 
						$str .= ' hours ago)'; 
					}
				}

				// Show minutes if it's been more than a minute.
				else {
					$str .= floor($difference / 60);
					$difference -= 60 * floor($difference / 60);
					if(floor($difference / 60) == 1) { 
						$str .= ' minute ago)'; 
					} else { 
						$str .= ' minutes ago)'; 
					}
				}

			}
			if($i >= $this->number) {
				break;
			}
			$i++;
		}
		$str .= "</ul>";
		
		echo $str;		
		
		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['fb_rss'] = $new_instance['fb_rss'];
		$instance['tw_rss'] = $new_instance['tw_rss'];
		$instance['yt_rss'] = $new_instance['yt_rss'];
		$instance['pc_rss'] = $new_instance['pc_rss'];
		$instance['fr_rss'] = $new_instance['fr_rss'];
		$instance['number'] = $new_instance['number'];
		$instance['show_time'] = $new_instance['show_time'];

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => __('Social Feeds', 'example'), 'number' => '5','show_time' => true );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		
				<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
		
		
		
		<!-- Facebook Feed RSS -->
		<p>
			<label for="<?php echo $this->get_field_id( 'fb_rss' ); ?>">Facebook RSS</label>
			<input id="<?php echo $this->get_field_id( 'fb_rss' ); ?>" name="<?php echo $this->get_field_name( 'fb_rss' ); ?>" value="<?php echo $instance['fb_rss']; ?>" style="width:100%;" />
		</p>
		
		<!-- Twitter Feed RSS -->
		<p>
			<label for="<?php echo $this->get_field_id( 'tw_rss' ); ?>">Twitter RSS</label>
			<input id="<?php echo $this->get_field_id( 'tw_rss' ); ?>" name="<?php echo $this->get_field_name( 'tw_rss' ); ?>" value="<?php echo $instance['tw_rss']; ?>" style="width:100%;" />
		</p>
		
		<!-- YouTube Feed RSS -->
		<p>
			<label for="<?php echo $this->get_field_id( 'yt_rss' ); ?>">YouTube RSS</label>
			<input id="<?php echo $this->get_field_id( 'yt_rss' ); ?>" name="<?php echo $this->get_field_name( 'yt_rss' ); ?>" value="<?php echo $instance['yt_rss']; ?>" style="width:100%;" />
		</p>
		
		<!-- Picasa Feed RSS -->
		<p>
			<label for="<?php echo $this->get_field_id( 'pc_rss' ); ?>">Picasa RSS</label>
			<input id="<?php echo $this->get_field_id( 'pc_rss' ); ?>" name="<?php echo $this->get_field_name( 'pc_rss' ); ?>" value="<?php echo $instance['pc_rss']; ?>" style="width:100%;" />
		</p>
		
		<!-- Flickr Feed RSS -->
		<p>
			<label for="<?php echo $this->get_field_id( 'fr_rss' ); ?>">Flickr RSS</label>
			<input id="<?php echo $this->get_field_id( 'fr_rss' ); ?>" name="<?php echo $this->get_field_name( 'fr_rss' ); ?>" value="<?php echo $instance['fr_rss']; ?>" style="width:100%;" />
		</p>

		<!-- Number of stories: Select Box -->
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>">Stories</label> 
			<select id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" class="widefat" style="width:100%;">
				<option <?php if ( '1' == $instance['number'] ) echo 'selected="selected"'; ?>>1</option>
				<option <?php if ( '2' == $instance['number'] ) echo 'selected="selected"'; ?>>2</option>
				<option <?php if ( '3' == $instance['number'] ) echo 'selected="selected"'; ?>>3</option>
				<option <?php if ( '4' == $instance['number'] ) echo 'selected="selected"'; ?>>4</option>
				<option <?php if ( '5' == $instance['number'] ) echo 'selected="selected"'; ?>>5</option>
				<option <?php if ( '6' == $instance['number'] ) echo 'selected="selected"'; ?>>6</option>
				<option <?php if ( '7' == $instance['number'] ) echo 'selected="selected"'; ?>>7</option>
				<option <?php if ( '8' == $instance['number'] ) echo 'selected="selected"'; ?>>8</option>
				<option <?php if ( '9' == $instance['number'] ) echo 'selected="selected"'; ?>>9</option>
				<option <?php if ( '10' == $instance['number'] ) echo 'selected="selected"'; ?>>10</option>
			</select>
		</p>

		<!-- Show Time? Checkbox -->
		<p>
			<input class="checkbox" type="checkbox" <?php echo ($instance['show_time']) ? "checked" : ""; ?> id="<?php echo $this->get_field_id( 'show_time' ); ?>" name="<?php echo $this->get_field_name( 'show_time' ); ?>" /> 
			<label for="<?php echo $this->get_field_id( 'show_time' ); ?>">Display Timestamps?</label>
		</p>
		

	<?php
	}
	
}

function gator_styles() {
	$style = "<link rel=\"stylesheet\" href=\"/wp-content/plugins/social-gator/social-gator.css\" type=\"text/css\" media=\"screen\" />";
	echo $style;
}

add_action('wp_head','gator_styles');
add_action('widgets_init', create_function('', 'return register_widget("SocialGator");'));

?>
<?php
/*
Plugin Name: Dummy Hit Counter
Plugin URI: http://tech-en.planetkips.nl/plugin/dummy-hit
Description: Shows a dummy number of hits on your blog.
Version: 1.0
Author: Jasper Kips
Author URI: http://inekris.xs4all.nl/jasper-kips
License: GPL3
*/

require_once( plugin_dir_path( __FILE__) . 'dummy-hit-spider-detect.php' );

class dummy_hit extends WP_Widget {
	
	private $version = 1.0;
	// Constructor
	function dummy_hit() {
		// And since we localize, load the text domain
		load_plugin_textdomain( 'dummy-hit', false, dirname(plugin_basename(__FILE__)) . '/lang/' );
		parent::WP_Widget( false, $name = __( 'Dummy Counter', 'dummy-hit' ) );
	}
	
	
	// Form
	function form ( $instance ) {
	
		if ( $instance ) {
			// There are instance variables stored.
			// Yet we presume nothing
			if ( ! isset( $instance['title'] ) ) $instance['title'] = '';
			if ( ! isset( $instance['text'] ) ) $instance['text'] = '';
			if ( ! isset( $instance['start'] ) || ! is_int( $instance['start'] ) ) $instance['start'] = 0;
			if ( ! isset( $instance['interval'] ) || ! is_int( $instance['interval'] ) ) $instance['interval'] = 900;
			if ( ! isset( $instance['last_request'] ) || !is_int( $instance['last_request'] ) ) $instance['last_request'] = time();
			if ( ! isset( $instance['count_admin'] ) ) $instance['count_admin'] = 'export';
			if ( ! isset( $instance['count_robots'] ) ) $instance['count_robots'] = 0;
			
			// Now we have either stored, or defaults
			$title = esc_attr( $instance['title'] );
			$text = esc_attr( $instance['text'] );
			$start = (integer) $instance['start'];
			$interval = (integer) $instance['interval'];
			$last_request = (integer) $instance['last_request'];
			$count_admin = $instance['count_admin'];
			$count_robots = $instance['count_robots'];
		} else {
			
			// There are no instance variables stored
			// So we set defaults
			$title = '';
			$text = '';
			$start = 0;
			$interval = 900;
			$last_request = time();
			$count_admin = 'export';
			$count_robots = 0;
			
			
		}
		
		// We need to determine user levels 
		// This is dirty, but since roles are alphanumeric, without 
		// hierarchy, we determine a capability per role, which is hierarchic.
		$user_levels =	array(	__( '(Super)Administrator', 'dummy-hit' ) => 'export',
								__( 'Editor', 'dummy-hit' ) => 'edit_pages',
								__( 'Author', 'dummy-hit' ) => 'publish_posts',
								__( 'Contributor', 'dummy-hit' ) => 'edit_posts',
								__( 'Subscriber', 'dummy-hit' ) => 'read',
						);
	
	?>
	<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Widget Title', 'dummy-hit' ); ?></label>
	<input class="widefat" id="<?php $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
	</p>
	<p>
	<label for="<?php echo $this->get_field_id('text'); ?>"><?php _e( 'Explanatory text before counter', 'dummy-hit' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>" type="text" value="<?php echo $text; ?>" />
	</p>
	<p>
	<label for="<?php echo $this->get_field_id( 'start' ); ?>"><?php _e( 'Number of Hits to Start From', 'dummy-hit' ); ?></label>
	<input class="widefat" type="text" id="<?php echo $this->get_field_id('start'); ?>" name="<?php echo $this->get_field_name('start'); ?>" value="<?php echo $start; ?>" />
	</p>
	<p>
	<label for="<?php echo $this->get_field_id( 'interval' ); ?>"><?php _e( 'Number of seconds after last before random hits will be added. It is also the mean number of seconds between two simulated hits.', 'dummy-hit'); ?></label>
	<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'interval '); ?>" name="<?php echo $this->get_field_name( 'interval' ); ?>" value="<?php echo $interval ?>" />
	</p>
	<p>
	<label for="<?php echo $this->get_field_id( 'count_admin' ); ?>"><?php _e( 'The minimal user level to ignore for the counter', 'dummy-hit' ); ?></label><br />
	<select id="<?php echo $this->get_field_id( 'count_admin' ); ?>" name="<?php echo $this->get_field_name('count_admin'); ?>">
	<option value="none" <?php 
		if ( $count_admin == 'none' ) {
			?>selected="selected" <?php  
		} ?>
		><?php _e( 'Count all human hits', 'dummy-hit' ); ?></option>
	<?php foreach ( $user_levels as $level_name => $privilege ) {
		?><option value="<?php echo $privilege; ?>" <?php if ( $privilege == $count_admin ) {
												?> selected="selected" <?php
												} ?> 
		><?php echo $level_name ?></option><?php
	}
	?>
	</select>
	</p>
	<p>
	<label for="<?php echo $this->get_field_id( 'count_robots' ); ?>"><?php _e( 'Count robot and spider hits', 'dummy-hit' ); ?></label>
	<input class="widefat" type="checkbox" id="<?php echo $this->get_field_id( 'count_robots' ); ?>" name="<?php echo $this->get_field_name( 'count_robots' ); ?>" <?php checked( $count_robots, 1 ); ?> value='1' />
	</p>
	<input type="hidden" id="<?php echo $this->get_field_id('last_request'); ?>" name="<?php echo $this->get_field_name('last_request'); ?>" value="<?php echo $last_request; ?>" />
	
	
	<?php
	}
	
	// Update Options
	function update ( $new_instance, $old_instance ) {
		$instance = $old_instance;
		// Fields from the form above
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['text'] = strip_tags( $new_instance['text'] );
		$instance['textarea'] = strip_tags( $new_instance['textarea'] );
		$instance['start'] = (integer) $new_instance['start'];
		$instance['interval'] =  (integer) $new_instance['interval'];
		$instance['last_request'] = (integer) $new_instance['last_request'];
		$instance['count_admin'] = strip_tags( $new_instance['count_admin'] );
		// count_robots is a checkbox, which won't be posted is unchecked
		if ( ! isset($new_instance['count_robots'] ) ) {
			$instance['count_robots'] = 0;
		} else {
			$instance['count_robots'] = $new_instance['count_robots'];
		}
		// Clean up the mess from older versions, and convert if necessary
		if ( ! isset( $instance['version'] ) || $instance['version'] < $this->version  ) {
			$instance['version'] = $this->version;
			if ( isset( $instance['user_level_to_exclude'] ) ) unset( $instance['user_level_to_exclude'] );
		}
		return $instance;
	}
	
	// Display the widget
	function widget ( $args, $instance ) {
		extract( $args );
		
		// Do we count the robots as well?
		$count_robots = $instance['count_robots'];
		
		// The function robot_agent is defined in dummy-hit-spider-detect
		if ( robot_agent() ) {
			if ( $count_robots ) return;
		}
		$title = apply_filters( 'widget_title', $instance['title'] );
		$text = $instance['text'];
		$start = $instance['start'];
		$interval = $instance['interval'];
		$last_request = $instance['last_request'];
		$count_admin = $instance['count_admin'];
		$add_extra = 0;
		
		// If for some reason start isn't set.
		if ( ! isset( $start ) ) return;
		echo $before_widget;
		echo '<div class="widget-text wp_widget_plugin_box">';

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		if ( $text ) {
			echo '<p class="wp_plugin_text">' . $text .'</p>';
		}
		
		// The counting stuff
		
		// Do not count admin views
		if ( 'none' != $count_admin && current_user_can( $count_admin ) ) {
				$add_extra = 0;

		} else {
			
			// Determine if enough time has eleapsed to add random counts
			$diff = time() - $last_request;
			if ( $interval > 0 && $diff > $interval ) {
				// Add random hits. By dividing the $interval ( mean time between hits ) we make sure to get on average 1 hit every $interval seconds
				// ( And yes I can mathematically prove it )
				$mod = intval( $diff / ( $interval / 10 ) );
				$add_extra = rand( 0, $mod );
			}
			$add_extra = $add_extra + 1;
		}
		
		// Updadte the counter
		$start = $start + $add_extra;
	    $last_request = time();
	    
	    // Show the counter, it is at least 5 digits long.
	    ?>
	    <div class="dummy-hit-counter" align="left"><?php
		$image = plugins_url( 'img/', __FILE__);
	    for ($i = 0; $i < (5 - strlen($start)); $i++) {
		    	echo "<img src='". $image . "/0.gif'>";
	           }               
		echo preg_replace('/(\d)/', "<img src='". $image. "/$1.gif'>", $start);
		?></div>
		
		<?php
		// Get the options, we got them in $instance, but we need to change them
		$ze_options = $this->get_settings();
		// Update the options, ie save the counter value, and the last_request
	    $ze_options[ $this->number ]['start'] = $start;
	    $ze_options[ $this->number ]['last_request'] = $last_request;
	    // Save them
	    $this->save_settings( $ze_options);
	   
		echo '</div>';
		echo $after_widget;
		
	}
	
	
}

// Register the widget

add_action ( 'widgets_init', create_function( '', 'return register_widget("dummy_hit" );' ) );
<?php
/*
Plugin Name: fanWidget  
Description: College Football Schedule for your WP Blog/Site
Author: Alvin Kreitman 
Version: 1.0
Plugin URI: http://www.techkismet.com/fanwidget_ncaa_football_for_wp
Author URI: http://techkismet.com
License: MIT License - http://www.opensource.org/licenses/mit-license.php
Warranties: See Licensing Information
Last Modified: 09/07/2007
*/

// Add the widget to plugin loading
function widget_fanwidget_init() {

        // Check for the sidebar widget functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
        	return;
	
	// This var is the URL to link to promote this wordpress widget
        $home_url = "http://techkismet.com/fanwidget_ncaa_football_for_wp";

	// configuration form  
	function widget_fanwidget_control() {
		$options = $newoptions = get_option('widget_fanwidget');
		if ($_POST['fanwidget-submit']) {
			$newoptions['team_key'] = $_POST['fanwidget-team'];
			$newoptions['fanwidget-title'] = $_POST['fanwidget-title'];
		}

		if ($options != $newoptions) {
			$options = $newoptions;
			update_option('widget_fanwidget', $options);
		}

		// These XML files are for the use of this widget only.  If you want access to the data, please contact me.
		$xmlstr = file_get_contents("http://fanwidgets.com/wp-widgets/ncaafb_teams_xml.php");
		$xml = new SimpleXMLElement($xmlstr);
	
		# Build select dropdown
		$s = "<select name='fanwidget-team'>";
		foreach($xml->teams->entity as $e) {
			$s .= '<option value="' . $e->md5key . '"' . ($e->md5key == $options['team_key'] ? ' selected' : '') . '>' . $e->DisplayName . '</option>';
		}
		$s .= "</select>";

		?>
			<p style="text-align:left;"><label>Title: &nbsp; <input type="text" name="fanwidget-title" value = "<?= $options['fanwidget-title'] ?>"></label> </p>
			<p style="text-align:left;"><label>Team: &nbsp;
			<?= $s ?></label></p>
			<input type="hidden" id="fanwidget-submit" name="fanwidget-submit" value="1">
		<?php
	
        }

	function widget_load_stylesheet() {
		echo '<link href="http://fanwidgets.com/wp-widgets/css/widget_style.css" rel="stylesheet" type="text/css" />';
	}

        // This shows the widget on the sidebar
        function widget_fanwidget($args) {
                extract($args);
		$options = get_option('widget_fanwidget');
		$title = $options['fanwidget-title']; 

		// These XML files are for the use of this widget only.  If you want access to the data, please contact me.
	        // Page used as a parameter in XML requests.
	        $p = get_bloginfo('url') . $_SERVER['REQUEST_URI'];

		$xmlstr = file_get_contents("http://fanwidgets.com/wp-widgets/ncaafb_schedule_xml.php?id={$options['team_key']}&p={$p}");
		$xml = new SimpleXMLElement($xmlstr);

		?>	
		<?php echo $before_widget; ?>
                	<?php print($before_title . $title . $after_title)  ?>

<div id="fanwidgets">
<h2><?= $xml->team_name ?> 
	<?php 
	$rank = (int) $xml->national_rank;
	if($rank > 0) { 
		?> 
		<span>(<?= $rank ?>)</span>
		<?php
	} 
	?>
	</h2>
	<div class="rec">Record <?= $xml->overall_record ?> (Con:<?= $xml->conference_record ?>)</div>


<ul id="schedule">
	<?php
		foreach ($xml->game as $g) { 
			$team = short_text($g->opponent_name, 19, "..")
			?>
			<li><div class="date"><?= $g->game_date ?></div>
			<div class="team"><?= ($g->home_away == "away" ? "at " : "") . $team ?></div>
			<?php
				if ($g->result == "0") {
					?>
					<div class="result"><?= $g->game_time ?></div>
					<?php
				} else {
					?>
					<div class="result <?= ($g->result == "W" ? " win" : " loss") ?>">
					<?= $g->result . " " . $g->score ?></div>
					<?php
				}
			?>
			<div class="clear"></div>
			</li>
		<?php
		}
	?>
</ul>

<p class="time">* All times are ET</p> 

<!-- to support this widget, please leave this link in here -->
<a href="<?= $home_url ?>"><img src="http://fanwidgets.com/wp-widgets/images/plus.gif" class="plus" border="0" /></a>
<div class="clear"></div>

</div>
<!-- please do not remove this comment or code  ||widget_key==b248e38421ff3cd18b6b2bb7174a87f2|| -->

	        <?php echo $after_widget; ?>
		<?php
	}

        // Tell the sidebar about the Twitter widget and its control
        register_sidebar_widget('fanWidget', 'widget_fanwidget');
        register_widget_control('fanWidget', 'widget_fanwidget_control');
	add_action('wp_head', 'widget_load_stylesheet');

	function short_text($text,$num,$trail) {

     		$chars = $num;

    		if (strlen($text) > $chars) {
       
            		$text = $text." ";
         		$text = substr($text,0,$chars);
         		#$text = substr($text,0,strrpos($text,' '));
         		$text = $text."$trail";
    		}

     		return $text;
	}

}

add_action('widgets_init', 'widget_fanwidget_init');

?>

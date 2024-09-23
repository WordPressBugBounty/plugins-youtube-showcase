<?php
/**
 * Settings Glossary Functions
 *
 * @package YOUTUBE_SHOWCASE
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
add_action('youtube_showcase_settings_glossary', 'youtube_showcase_settings_glossary');
/**
 * Display glossary information
 * @since WPAS 4.0
 *
 * @return html
 */
function youtube_showcase_settings_glossary() {
	global $title;
?>
<div class="wrap">
<h2><?php echo esc_html($title); ?></h2>
<p><?php esc_html_e('YouTube Showcase is a powerful but simple-to-use YouTube video gallery plugin with responsive frontend.', 'youtube-showcase'); ?></p>
<p><?php esc_html_e('The below are the definitions of entities, attributes, and terms included in Youtube Showcase.', 'youtube-showcase'); ?></p>
<div id="glossary" class="accordion-container">
<ul class="outer-border">
<li id="emd_video" class="control-section accordion-section open">
<h3 class="accordion-section-title hndle" tabindex="1"><?php esc_html_e('Videos', 'youtube-showcase'); ?></h3>
<div class="accordion-section-content">
<div class="inside">
<table class="form-table"><p class"lead"><?php esc_html_e('Videos are YouTube videos identified by Video ID.', 'youtube-showcase'); ?></p><tr><th style='font-size: 1.1em;color:cadetblue;border-bottom: 1px dashed;padding-bottom: 10px;' colspan=2><div><?php esc_html_e('Attributes', 'youtube-showcase'); ?></div></th></tr>
<tr>
<th><?php esc_html_e('Title', 'youtube-showcase'); ?></th>
<td><?php esc_html_e(' Title is a required field. Title does not have a default value. ', 'youtube-showcase'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Content', 'youtube-showcase'); ?></th>
<td><?php esc_html_e(' Content does not have a default value. ', 'youtube-showcase'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Excerpt', 'youtube-showcase'); ?></th>
<td><?php esc_html_e(' Excerpt does not have a default value. ', 'youtube-showcase'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Video Key', 'youtube-showcase'); ?></th>
<td><?php esc_html_e('<p>The unique 11 digit alphanumeric video key found on the YouTube video. For example; in https://www.youtube.com/watch?v=uVgWZd7oGOk. uVgWZd7oGOk is the video id.</p> Video Key is a required field. Being a unique identifier, it uniquely distinguishes each instance of Video entity. Video Key is filterable in the admin area. Video Key does not have a default value. ', 'youtube-showcase'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Featured', 'youtube-showcase'); ?></th>
<td><?php esc_html_e('Adds the video to featured video list. Featured is filterable in the admin area. Featured does not have a default value. ', 'youtube-showcase'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Video Image Resolution', 'youtube-showcase'); ?></th>
<td><?php esc_html_e('<p>Sets the resolution of video thumbnail image. The image size for each option;<br />
<strong>Medium</strong> - 320 x 180, <strong>High</strong> - 480x360, <strong>Standard</strong> - 640 x 480, <strong>Max</strong> -1280 x 720</p> Video Image Resolution has a default value of <b>\'mq\'</b>.Video Image Resolution is displayed as a dropdown and has predefined values of: sd, mq, hq, maxres.', 'youtube-showcase'); ?></td>
</tr>
<tr>
<th><?php esc_html_e('Video Autoplay', 'youtube-showcase'); ?></th>
<td><?php esc_html_e('When set the player starts video automatically. It may not work in all devices due to vendor preferences. Video Autoplay does not have a default value. ', 'youtube-showcase'); ?></td>
</tr><tr><th style='font-size:1.1em;color:cadetblue;border-bottom: 1px dashed;padding-bottom: 10px;' colspan=2><div><?php esc_html_e('Taxonomies', 'youtube-showcase'); ?></div></th></tr>
<tr>
<th><?php esc_html_e('Category', 'youtube-showcase'); ?></th>

<td><?php esc_html_e(' Category supports parent-child relationships like categories', 'youtube-showcase'); ?>. <?php esc_html_e('Category does not have a default value', 'youtube-showcase'); ?>.<div class="taxdef-block"><p><?php esc_html_e('There are no preset values for <b>Category.</b>', 'youtube-showcase'); ?></p></div></td>
</tr>

<tr>
<th><?php esc_html_e('Tag', 'youtube-showcase'); ?></th>

<td><?php esc_html_e(' Tag supports parent-child relationships like categories', 'youtube-showcase'); ?>. <?php esc_html_e('Tag does not have a default value', 'youtube-showcase'); ?>.<div class="taxdef-block"><p><?php esc_html_e('There are no preset values for <b>Tag.</b>', 'youtube-showcase'); ?></p></div></td>
</tr>
</table>
</div>
</div>
</li>
</ul>
</div>
</div>
<?php
}
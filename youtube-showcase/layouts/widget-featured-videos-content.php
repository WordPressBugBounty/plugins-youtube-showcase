<?php if (!defined('ABSPATH')) exit;
$real_post = $post;
$ent_attrs = get_option('youtube_showcase_attr_list');
?>
<div class="emd-video-wrapper" style="display:inline-block;border:1px solid lightgray;border-radius:4px;width:100%;padding:0;margin:5px;">
<a title="<?php echo get_the_title(); ?>" href="<?php echo esc_url(get_permalink()); ?>"><img style="width:100%;height:auto;padding:0" src="https://img.youtube.com/vi/<?php echo esc_html(emd_mb_meta('emd_video_key')); ?>
/<?php if (emd_get_attr_val('youtube_showcase', $post->ID, 'emd_video', 'emd_video_thumbnail_resolution', 'key')) {
	echo emd_get_attr_val('youtube_showcase', $post->ID, 'emd_video', 'emd_video_thumbnail_resolution', 'key');
} else {
	echo 'mq';
} ?>default.jpg" alt="<?php echo get_the_title(); ?>" />
<div class="video-title widget" style="padding:0 5px"><?php echo get_the_title(); ?></div>
</a>
</div>
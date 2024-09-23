<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;
?>
<div id="emd-login-container">
<form id="emd_login_form" class="emdloginreg-container emd_form" action="<?php echo get_permalink($post->ID); ?>" method="post">
<fieldset>
<legend><?php esc_html_e( 'Log into Your Account', 'youtube-showcase' ); ?></legend>
<div class="emd-form-row emd-row" style="display:flex;">
<div class="emd-form-field emd-col emd-md-12 emd-sm-12 emd-xs-12 emd-reg">
<div class="emd-form-group emd-login-username">
<label for="emd_user_login"><span><?php esc_html_e( 'Username or Email', 'youtube-showcase' ); ?></span>
<span class="emd-fieldicons-wrap">
<a href="#" data-html="true" tabindex=-1 data-toggle="tooltip" title="<?php esc_html_e('Username or Email field is required','youtube-showcase');?>" id="req_user_login" class="helptip">
<span class="field-icons required" aria-required="true"></span></a>
</span>
</label>
<input name="emd_user_login" id="emd_user_login" class="text required emd-input-md emd-form-control" type="text"/>
</div>
</div>
</div>
<div class="emd-form-row emd-row" style="display:flex;">
<div class="emd-form-field emd-col emd-md-12 emd-sm-12 emd-xs-12 emd-reg">
<div class="emd-form-group emd-login-password">
<label for="emd_user_pass"><span><?php esc_html_e( 'Password', 'youtube-showcase' ); ?></span>
<span class="emd-fieldicons-wrap">
<a href="#" data-html="true" tabindex=-1 data-toggle="tooltip" title="<?php esc_html_e('Password field is required','youtube-showcase');?>" id="req_user_pass" class="helptip">
<span class="field-icons required" aria-required="true"></span>
</span>
</label>
<input name="emd_user_pass" id="emd_user_pass" class="password required emd-input-md emd-form-control" type="password"/>
</div>
</div>
</div>
<div class="emd-form-group emd-login-remember">
<label><input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php esc_html_e( 'Remember Me', 'youtube-showcase' ); ?></label>
</div>
<div>
<input type="hidden" name="redirect_to" value="<?php echo esc_url(get_permalink($post->ID)); ?>"/>
<input type="hidden" name="emd_login_nonce" value="<?php echo wp_create_nonce( 'emd-login-nonce' ); ?>"/>
<input type="hidden" name="emd_action" value="login"/>

<input type="submit" class="emd_submit button" id="emd-login-submit" value="<?php esc_html_e( 'Log In', 'youtube-showcase' ); ?>"/>
</div>
<div style="clear:both">
<p class="emd-lost-password" style="float:left">
<a href="<?php echo wp_lostpassword_url(); ?>">
<?php esc_html_e( 'Lost Password?', 'youtube-showcase' ); ?>
</a>
</p>
<p class="emd-register-link" style="float:right">
<a href="">
<?php esc_html_e( 'Register', 'youtube-showcase' ); ?>
</a>
</p>
</div>
</fieldset><!--end #emd_login_fields-->
</form>
</div>

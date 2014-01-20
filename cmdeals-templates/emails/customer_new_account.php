<?php if (!defined('ABSPATH')) exit; ?>

<?php global $user_login, $user_pass, $blogname; ?>

<?php do_action('cmdeals_email_header'); ?>

<p><?php echo sprintf(__("Thanks for registering on %s. Your login details are below:", 'cmdeals'), $blogname); ?></p>

<ul>
	<li><?php echo sprintf(__('Username: %s', 'cmdeals'), $user_login); ?></li>
	<li><?php echo sprintf(__('Password: %s', 'cmdeals'), $user_pass); ?></li>
</ul>

<p><?php echo sprintf(__("You can login to your account area here: %s.", 'cmdeals'), get_permalink(get_option('cmdeals_myaccount_page_id'))); ?></p>

<div style="clear:both;"></div>

<?php do_action('cmdeals_email_footer'); ?>
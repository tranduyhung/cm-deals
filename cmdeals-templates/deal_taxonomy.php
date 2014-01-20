<?php get_header('store'); ?>

<?php do_action('cmdeals_before_main_content'); // <div id="container"><div id="content" role="main"> ?>

	<?php $term = get_term_by( 'slug', get_query_var($wp_query->query_vars['taxonomy']), $wp_query->query_vars['taxonomy']); ?>
			
	<h1 class="page-title"><?php echo wptexturize($term->name); ?></h1>
		
	<?php if ($term->description) : ?><div class="term_description"><?php echo wpautop(wptexturize($term->description)); ?></div><?php endif; ?>
	
	<?php cmdeals_get_template_part( 'loop', 'store' ); ?>
	
	<?php do_action('cmdeals_pagination'); ?>

<?php do_action('cmdeals_after_main_content'); // </div></div> ?>

<?php do_action('cmdeals_sidebar'); ?>

<?php get_footer('store'); ?>
<?php

global $cmdeals_loop;

$cmdeals_loop['loop'] = 0;
$cmdeals_loop['show_deals'] = true;

if (!isset($cmdeals_loop['columns']) || !$cmdeals_loop['columns']) $cmdeals_loop['columns'] = apply_filters('loop_store_columns', 3);

?>

<?php do_action('cmdeals_before_store_loop'); ?>

<ul class="daily-deals">

	<?php 
	
	do_action('cmdeals_before_store_loop_deals');
	
	if ($cmdeals_loop['show_deals'] && have_posts()) : while (have_posts()) : the_post(); 
	
		$_deals = new cmdeals_deals( $post->ID );
                
                if ($_deals->is_visible()) continue; 
		
		$cmdeals_loop['loop']++;
		
		?>
		<li class="daily-deal <?php if ($cmdeals_loop['loop']%$cmdeals_loop['columns']==0) echo 'last'; if (($cmdeals_loop['loop']-1)%$cmdeals_loop['columns']==0) echo 'first'; ?>">
			
			<?php do_action('cmdeals_before_store_loop_item'); ?>
			
			<a href="<?php the_permalink(); ?>">
				
				<?php do_action('cmdeals_before_store_loop_item_title', $post, $_deals); ?>
				
				<h3><?php the_title(); ?></h3>
				
				<?php do_action('cmdeals_after_store_loop_item_title', $post, $_deals); ?>
			
			</a>
	
			<?php do_action('cmdeals_after_store_loop_item', $post, $_deals); ?>
			
		</li><?php 
		
	endwhile; endif;
	
	if ($cmdeals_loop['loop']==0) echo '<li class="info">'.__('No deals found which match your selection.', 'cmdeals').'</li>'; 

	?>

</ul>

<div class="clear"></div>

<?php do_action('cmdeals_after_store_loop'); ?>
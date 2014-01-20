<?php global $cmdeals; ?>
<div id="order_review">
	
	<div id="payment">
		<?php if ($cmdeals->cart->needs_payment()) : ?>
		<ul class="payment_methods methods">
			<?php 
				$available_gateways = $cmdeals->payment_gateways->get_available_payment_gateways();
				if ($available_gateways) : 
					// Chosen Method
					if (sizeof($available_gateways)) :
						$default_gateway = get_option('cmdeals_default_gateway');
						if (isset($available_gateways[$default_gateway])) :
							$available_gateways[$default_gateway]->set_current();
						else :
							current($available_gateways)->set_current();
						endif;
					endif;
					foreach ($available_gateways as $gateway ) :
						?>
						<li>
						<input type="radio" id="payment_method_<?php echo $gateway->id; ?>" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php if ($gateway->chosen) echo 'checked="checked"'; ?> />
						<label for="payment_method_<?php echo $gateway->id; ?>"><?php echo $gateway->title; ?> <?php echo apply_filters('cmdeals_gateway_icon', $gateway->icon(), $gateway->id); ?></label> 
							<?php
								if ($gateway->has_fields || $gateway->description) : 
									echo '<div class="payment_box payment_method_'.$gateway->id.'" style="display:none;">';
									$gateway->payment_fields();
									echo '</div>';
								endif;
							?>
						</li>
						<?php
					endforeach;
				else :
				
                                        echo '<p>'.__('Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'cmdeals').'</p>';
					
				endif;
			?>
		</ul>
		<?php endif; ?>

		<div class="form-row">
		
			<noscript><?php _e('Since your browser does not support JavaScript, or it is disabled, please ensure you click the <em>Update Totals</em> button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'cmdeals'); ?><br/><input type="submit" class="button-alt" name="update_totals" value="<?php _e('Update totals', 'cmdeals'); ?>" /></noscript>
		
			<?php $cmdeals->nonce_field('process_checkout')?>
			
			<?php do_action( 'cmdeals_review_order_before_submit' ); ?>
			
			<input type="submit" class="button alt" name="place_order" id="place_order" value="<?php echo apply_filters('cmdeals_order_button_text', __('Checkout', 'cmdeals')); ?>" />
			
			<?php if (get_option('cmdeals_terms_page_id')>0) : ?>
			<p class="form-row terms">
				<label for="terms" class="checkbox"><?php _e('I accept the', 'cmdeals'); ?> <a href="<?php echo esc_url( get_permalink(get_option('cmdeals_terms_page_id')) ); ?>" target="_blank"><?php _e('terms &amp; conditions', 'cmdeals'); ?></a></label>
				<input type="checkbox" class="input-checkbox" name="terms" <?php if (isset($_POST['terms'])) echo 'checked="checked"'; ?> id="terms" />
			</p>
			<?php endif; ?>
			
			<?php do_action( 'cmdeals_review_order_after_submit' ); ?>
			
		</div>
		
		<div class="clear"></div>

	</div>
	
</div>
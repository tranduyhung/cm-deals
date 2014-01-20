<?php
/**
 * Functions used for displaying reports in admin
 *
 * @package WordPress
 * @subpackage CM Deals
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

function cmdeals_reports() {

	$current_tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'sales';
	$current_chart = (isset($_GET['chart'])) ? $_GET['chart'] : 0;
	
	$charts = array(
		__('sales', 'cmdeals') => array(
			array(
				'title' => __('Overview', 'cmdeals'),
				'description' => '',
				'hide_title' => true,
				'function' => 'cmdeals_sales_overview'
			),
			array(
				'title' => __('Sales by day', 'cmdeals'),
				'description' => '',
				'function' => 'cmdeals_daily_sales'
			),
			array(
				'title' => __('Sales by month', 'cmdeals'),
				'description' => '',
				'function' => 'cmdeals_monthly_sales'
			),
			array(
				'title' => __('Deal Sales', 'cmdeals'),
				'description' => '',
				'function' => 'cmdeals_deals_sales'
			),
			array(
				'title' => __('Top sellers', 'cmdeals'),
				'description' => '',
				'function' => 'cmdeals_top_sellers'
			),
			array(
				'title' => __('Top earners', 'cmdeals'),
				'description' => '',
				'function' => 'cmdeals_top_earners'
			),
			array(
				'title' => __('Export to CSV (this month)', 'cmdeals'),
				'description' => '',
				'function' => 'cmdeals_export_to_csv_this_month'
			)
		),
		__('customers', 'cmdeals') => array(
			array(
				'title' => __('Overview', 'cmdeals'),
				'description' => '',
				'hide_title' => true,
				'function' => 'cmdeals_customer_overview'
			),
		)
	);
    ?>
	<div class="wrap cmdeals">
		<div class="icon32 icon32-cmdeals-reports" id="icon-cmdeals"><br></div><h2 class="nav-tab-wrapper cmdeals-nav-tab-wrapper">
			<?php
				foreach ($charts as $name => $value) :
					echo '<a href="'.admin_url('admin.php?page=cmdeals_reports&tab='.$name).'" class="nav-tab ';
					if($current_tab==$name) echo 'nav-tab-active';
					echo '">'.ucfirst($name).'</a>';
				endforeach;
			?>
			<?php do_action('cmdeals_reports_tabs'); ?>
		</h2>
		
		<?php if (sizeof($charts[$current_tab])>1) : ?><ul class="subsubsub"><li><?php
			$links = array();
			foreach ($charts[$current_tab] as $key => $chart) :
				$link = '<a href="admin.php?page=cmdeals_reports&tab='.$current_tab.'&amp;chart='.$key.'" class="';
				if ($key==$current_chart) $link .= 'current';
				$link .= '">'.$chart['title'].'</a>';
				$links[] = $link;
			endforeach;
			echo implode(' | </li><li>', $links);
		?></li></ul><br class="clear" /><?php endif; ?>
		
		<?php if (isset($charts[$current_tab][$current_chart])) : ?> 
			<?php if (!isset($charts[$current_tab][$current_chart]['hide_title']) || $charts[$current_tab][$current_chart]['hide_title']!=true) : ?><h3><?php echo $charts[$current_tab][$current_chart]['title']; ?></h3>
			<?php if ($charts[$current_tab][$current_chart]['description']) : ?><p><?php echo $charts[$current_tab][$current_chart]['description']; ?></p><?php endif; ?>
			<?php endif; ?>
			<?php
				$func = $charts[$current_tab][$current_chart]['function'];
				if ($func && function_exists($func)) $func();
			?>
		<?php endif; ?>

	</div>
	<?php
}

/**
 * Javascript for highlighting weekends
 */
function cmdeals_weekend_area_js() {
	?>
	function weekendAreas(axes) {
        var markings = [];
        var d = new Date(axes.xaxis.min);
        // go to the first Saturday
        d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
        d.setUTCSeconds(0);
        d.setUTCMinutes(0);
        d.setUTCHours(0);
        var i = d.getTime();
        do {
            markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
            i += 7 * 24 * 60 * 60 * 1000;
        } while (i < axes.xaxis.max);
 
        return markings;
    }
    <?php
}

/**
 * Javascript for chart tooltips
 */
function cmdeals_tooltip_js() {
	?>
	function showTooltip(x, y, contents) {
        jQuery('<div id="tooltip">' + contents + '</div>').css( {
            position: 'absolute',
            display: 'none',
            top: y + 5,
            left: x + 5,
		    padding: '5px 10px',  
			border: '3px solid #3da5d5',  
			background: '#288ab7'
        }).appendTo("body").fadeIn(200);
    }
 
    var previousPoint = null;
    jQuery("#placeholder").bind("plothover", function (event, pos, item) {
        if (item) {
            if (previousPoint != item.dataIndex) {
                previousPoint = item.dataIndex;
                
                jQuery("#tooltip").remove();
                
                if (item.series.label=="Sales amount") {
                	
                	var y = item.datapoint[1].toFixed(2);
                	showTooltip(item.pageX, item.pageY, item.series.label + ": " + "<?php echo get_cmdeals_currency_symbol(); ?>" + y);
                	
                } else if (item.series.label=="Number of sales") {
                	
                	var y = item.datapoint[1];
                	showTooltip(item.pageX, item.pageY, item.series.label + ": " + y);
                
                } else {
                	
                	var y = item.datapoint[1];
                	showTooltip(item.pageX, item.pageY, y);
                	
                }
            }
        }
        else {
            jQuery("#tooltip").remove();
            previousPoint = null;            
        }
    });
    <?php
}

/**
 * Javascript for date range
 */
function cmdeals_datepicker_js() {
	global $cmdeals;
	?>
	var dates = jQuery( "#from, #to" ).datepicker({
		defaultDate: "",
		dateFormat: "yy-mm-dd",
		//changeMonth: true,
		//changeYear: true,
		numberOfMonths: 1,
		minDate: "-12M",
		maxDate: "+0D",
		showButtonPanel: true,
		showOn: "button",
		buttonImage: "<?php echo $cmdeals->plugin_url(); ?>/cmdeals-assets/images/calendar.png",
		buttonImageOnly: true,
		onSelect: function( selectedDate ) {
			var option = this.id == "from" ? "minDate" : "maxDate",
				instance = jQuery( this ).data( "datepicker" ),
				date = jQuery.datepicker.parseDate(
					instance.settings.dateFormat ||
					jQuery.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates.not( this ).datepicker( "option", option, date );
		}
	});
	<?php
}

/**
 * Orders for range filter function
 */
function sales_within_range( $where = '' ) {
	global $start_date, $end_date;
	
	$after = date('Y-m-d', $start_date);
	$before = date('Y-m-d', strtotime('+1 day', $end_date));
	
	$where .= " AND post_date > '$after'";
	$where .= " AND post_date < '$before'";
	
	return $where;
}

/**
 * Sales overview
 */
function cmdeals_sales_overview() {

	global $start_date, $end_date, $cmdeals, $wpdb;
	
	$total_sales = 0;
	$total_sales = 0;
	$order_items = 0;
	
	$args = array(
	    'numberposts'     => -1,
	    'orderby'         => 'post_date',
	    'order'           => 'DESC',
	    'post_type'       => 'deals-sales',
	    'post_status'     => 'publish',
	    'tax_query' => array(
	    	array(
		    	'taxonomy' => 'deals_sales_status',
				'terms' => array('completed', 'processing', 'on-hold'),
				'field' => 'slug',
				'operator' => 'IN'
			)
	    )
	);
	$sales = get_posts( $args );
	foreach ($sales as $order) :
		$order_items_array = (array) get_post_meta($order->ID, '_order_items', true);
		foreach ($order_items_array as $item) $order_items += (int) $item['qty'];
		$total_sales += get_post_meta($order->ID, '_order_total', true);
		$total_sales++;
	endforeach;
	
	?>
	<div id="poststuff" class="cmdeals-reports-wrap">
		<div class="cmdeals-reports-sidebar">
			<div class="postbox">
				<h3><span><?php _e('Total sales', 'cmdeals'); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_sales>0) echo cmdeals_price($total_sales); else _e('n/a', 'cmdeals'); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e('Total sales', 'cmdeals'); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_sales>0) echo $total_sales. ' ('.$order_items.__(' items', 'cmdeals').')'; else _e('n/a', 'cmdeals'); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e('Average order total', 'cmdeals'); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_sales>0) echo cmdeals_price($total_sales/$total_sales); else _e('n/a', 'cmdeals'); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e('Average order items', 'cmdeals'); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_sales>0) echo number_format($order_items/$total_sales, 2); else _e('n/a', 'cmdeals'); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e('Last 5 sales', 'cmdeals'); ?></span></h3>
				<div class="inside">
					<?php
					if ($sales) :
						$count = 0;
						echo '<ul class="recent-sales">';
						foreach ($sales as $order) :
							
							$this_order = new cmdeals_order( $order->ID );
							
							if ($this_order->user_id > 0) :
								$customer = get_user_by('id', $this_order->user_id);
								$customer = $customer->user_login;
							else :
								$customer = __('Guest', 'cmdeals');
							endif;
							
							echo '
							<li>
								<span class="order-status '.sanitize_title($this_order->status).'">'.ucwords($this_order->status).'</span> <a href="'.admin_url('post.php?post='.$order->ID).'&action=edit">'.date_i18n('jS M Y (h:i A)', strtotime($this_order->order_date)).'</a><br />
								<small>'.sizeof($this_order->items).' '._n('item', 'items', sizeof($this_order->items), 'cmdeals').' <span class="order-cost">'.__('Total:', 'cmdeals') . ' ' . cmdeals_price($this_order->order_total).'</span> <span class="order-customer">'.$customer.'</span></small>
							</li>';
							
							$count++;
							if ($count==5) break;
						endforeach;
						echo '</ul>';
					endif;
					?>
				</div>
			</div>
		</div>
		<div class="cmdeals-reports-main">
			<div class="postbox">
				<h3><span><?php _e('This months sales', 'cmdeals'); ?></span></h3>
				<div class="inside chart">
					<div id="placeholder" style="width:100%; overflow:hidden; height:568px; position:relative;"></div>
				</div>
			</div>
		</div>
	</div>
	<?php
	
	$start_date = strtotime(date('Ymd', strtotime( date('Ym', current_time('timestamp')).'01' )));
	$end_date = strtotime(date('Ymd', current_time('timestamp')));
	
	// Get sales to display in widget
	add_filter( 'posts_where', 'sales_within_range' );

	$args = array(
	    'numberposts'     => -1,
	    'orderby'         => 'post_date',
	    'order'           => 'ASC',
	    'post_type'       => 'deals-sales',
	    'post_status'     => 'publish' ,
	    'suppress_filters'=> 0,
	    'tax_query' => array(
	    	array(
		    	'taxonomy' => 'deals_sales_status',
				'terms' => array('completed', 'processing', 'on-hold'),
				'field' => 'slug',
				'operator' => 'IN'
			)
	    )
	);
	$sales = get_posts( $args );
	
	$order_counts = array();
	$order_amounts = array();

	// Blank date ranges to begin
	$count = 0;
	$days = ($end_date - $start_date) / (60 * 60 * 24);
	if ($days==0) $days = 1;

	while ($count < $days) :
		$time = strtotime(date('Ymd', strtotime('+ '.$count.' DAY', $start_date))).'000';
		
		$order_counts[$time] = 0;
		$order_amounts[$time] = 0;

		$count++;
	endwhile;
	
	if ($sales) :
		foreach ($sales as $order) :
			
			$order_total = get_post_meta($order->ID, '_order_total', true);			
			$time = strtotime(date('Ymd', strtotime($order->post_date))).'000';
			
			if (isset($order_counts[$time])) :
				$order_counts[$time]++;
			else :
				$order_counts[$time] = 1;
			endif;
			
			if (isset($order_amounts[$time])) :
				$order_amounts[$time] = $order_amounts[$time] + $order_total;
			else :
				$order_amounts[$time] = (float) $order_total;
			endif;
			
		endforeach;
	endif;
	
	remove_filter( 'posts_where', 'sales_within_range' );

	$order_counts_array = array();
	foreach ($order_counts as $key => $count) :
		$order_counts_array[] = array($key, $count);
	endforeach;
	
	$order_amounts_array = array();
	foreach ($order_amounts as $key => $amount) :
		$order_amounts_array[] = array($key, $amount);
	endforeach;
	
	$order_data = array( 'order_counts' => $order_counts_array, 'order_amounts' => $order_amounts_array );

	$chart_data = json_encode($order_data);
	?>
	<script type="text/javascript">
		jQuery(function(){
			var order_data = jQuery.parseJSON( '<?php echo $chart_data; ?>' );
		
			var d = order_data.order_counts;
		    var d2 = order_data.order_amounts;
			
			for (var i = 0; i < d.length; ++i) d[i][0] += 60 * 60 * 1000;
		    for (var i = 0; i < d2.length; ++i) d2[i][0] += 60 * 60 * 1000;
			
			var placeholder = jQuery("#placeholder");
			 
			var plot = jQuery.plot(placeholder, [ { label: "Number of sales", data: d }, { label: "Sales amount", data: d2, yaxis: 2 } ], {
				series: {
					lines: { show: true },
					points: { show: true }
				},
				grid: {
					show: true,
					aboveData: false,
					color: '#ccc',
					backgroundColor: '#fff',
					borderWidth: 2,
					borderColor: '#ccc',
					clickable: false,
					hoverable: true,
					markings: weekendAreas
				},
				xaxis: { 
					mode: "time",
					timeformat: "%d %b", 
					tickLength: 1,
					minTickSize: [1, "day"]
				},
				yaxes: [ { min: 0, tickSize: 1, tickDecimals: 0 }, { position: "right", min: 0, tickDecimals: 2 } ],
		   		colors: ["#8a4b75", "#47a03e"]
		 	});
		 	
		 	placeholder.resize();
	 	
			<?php cmdeals_weekend_area_js(); ?>
			<?php cmdeals_tooltip_js(); ?>
		});
	</script>
	<?php
}

/**
 * Daily sales chart
 */
function cmdeals_daily_sales() {
	
	global $start_date, $end_date, $cmdeals, $wpdb;
	
	$start_date = (isset($_POST['start_date'])) ? $_POST['start_date'] : '';
	$end_date	= (isset($_POST['end_date'])) ? $_POST['end_date'] : '';
	
	if (!$start_date) $start_date = date('Ymd', strtotime( date('Ym', current_time('timestamp')).'01' ));
	if (!$end_date) $end_date = date('Ymd', current_time('timestamp'));
	
	$start_date = strtotime($start_date);
	$end_date = strtotime($end_date);
	
	$total_sales = 0;
	$total_sales = 0;
	$order_items = 0;
	
	// Get sales to display in widget
	add_filter( 'posts_where', 'sales_within_range' );

	$args = array(
	    'numberposts'     => -1,
	    'orderby'         => 'post_date',
	    'order'           => 'ASC',
	    'post_type'       => 'deals-sales',
	    'post_status'     => 'publish' ,
	    'suppress_filters'=> 0,
	    'tax_query' => array(
	    	array(
		    	'taxonomy' => 'deals_sales_status',
				'terms' => array('completed', 'processing', 'on-hold'),
				'field' => 'slug',
				'operator' => 'IN'
			)
	    )
	);
	$sales = get_posts( $args );
	
	$order_counts = array();
	$order_amounts = array();

	// Blank date ranges to begin
	$count = 0;
	$days = ($end_date - $start_date) / (60 * 60 * 24);
	if ($days==0) $days = 1;

	while ($count < $days) :
		$time = strtotime(date('Ymd', strtotime('+ '.$count.' DAY', $start_date))).'000';
		
		$order_counts[$time] = 0;
		$order_amounts[$time] = 0;

		$count++;
	endwhile;
	
	if ($sales) :
		foreach ($sales as $order) :
			
			$order_total = get_post_meta($order->ID, '_order_total', true);			
			$time = strtotime(date('Ymd', strtotime($order->post_date))).'000';
			
			$order_items_array = (array) get_post_meta($order->ID, '_order_items', true);
			foreach ($order_items_array as $item) $order_items += (int) $item['qty'];
			$total_sales += $order_total;
			$total_sales++;
		
			if (isset($order_counts[$time])) :
				$order_counts[$time]++;
			else :
				$order_counts[$time] = 1;
			endif;
			
			if (isset($order_amounts[$time])) :
				$order_amounts[$time] = $order_amounts[$time] + $order_total;
			else :
				$order_amounts[$time] = (float) $order_total;
			endif;
			
		endforeach;
	endif;
	
	remove_filter( 'posts_where', 'sales_within_range' );
	
	?>
	<form method="post" action="">
		<p><label for="from"><?php _e('From:', 'cmdeals'); ?></label> <input type="text" name="start_date" id="from" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $start_date) ); ?>" /> <label for="to"><?php _e('To:', 'cmdeals'); ?></label> <input type="text" name="end_date" id="to" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $end_date) ); ?>" /> <input type="submit" class="button" value="<?php _e('Show', 'cmdeals'); ?>" /></p>
	</form>
	
	<div id="poststuff" class="cmdeals-reports-wrap">
		<div class="cmdeals-reports-sidebar">
			<div class="postbox">
				<h3><span><?php _e('Total sales in range', 'cmdeals'); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_sales>0) echo cmdeals_price($total_sales); else _e('n/a', 'cmdeals'); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e('Total sales in range', 'cmdeals'); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_sales>0) echo $total_sales. ' ('.$order_items.__(' items', 'cmdeals').')'; else _e('n/a', 'cmdeals'); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e('Average order total in range', 'cmdeals'); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_sales>0) echo cmdeals_price($total_sales/$total_sales); else _e('n/a', 'cmdeals'); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e('Average order items in range', 'cmdeals'); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_sales>0) echo number_format($order_items/$total_sales, 2); else _e('n/a', 'cmdeals'); ?></p>
				</div>
			</div>
		</div>
		<div class="cmdeals-reports-main">
			<div class="postbox">
				<h3><span><?php _e('Sales in range', 'cmdeals'); ?></span></h3>
				<div class="inside chart">
					<div id="placeholder" style="width:100%; overflow:hidden; height:568px; position:relative;"></div>
				</div>
			</div>
		</div>
	</div>
	<?php

	$order_counts_array = array();
	foreach ($order_counts as $key => $count) :
		$order_counts_array[] = array($key, $count);
	endforeach;
	
	$order_amounts_array = array();
	foreach ($order_amounts as $key => $amount) :
		$order_amounts_array[] = array($key, $amount);
	endforeach;
	
	$order_data = array( 'order_counts' => $order_counts_array, 'order_amounts' => $order_amounts_array );

	$chart_data = json_encode($order_data);
	?>
	<script type="text/javascript">
		jQuery(function(){
			var order_data = jQuery.parseJSON( '<?php echo $chart_data; ?>' );
		
			var d = order_data.order_counts;
		    var d2 = order_data.order_amounts;
			
			for (var i = 0; i < d.length; ++i) d[i][0] += 60 * 60 * 1000;
		    for (var i = 0; i < d2.length; ++i) d2[i][0] += 60 * 60 * 1000;
			
			var placeholder = jQuery("#placeholder");
			 
			var plot = jQuery.plot(placeholder, [ { label: "Number of sales", data: d }, { label: "Sales amount", data: d2, yaxis: 2 } ], {
				series: {
					lines: { show: true },
					points: { show: true }
				},
				grid: {
					show: true,
					aboveData: false,
					color: '#ccc',
					backgroundColor: '#fff',
					borderWidth: 2,
					borderColor: '#ccc',
					clickable: false,
					hoverable: true,
					markings: weekendAreas
				},
				xaxis: { 
					mode: "time",
					timeformat: "%d %b", 
					tickLength: 1,
					minTickSize: [1, "day"]
				},
				yaxes: [ { min: 0, tickSize: 1, tickDecimals: 0 }, { position: "right", min: 0, tickDecimals: 2 } ],
		   		colors: ["#8a4b75", "#47a03e"]
		 	});
		 	
		 	placeholder.resize();
	 	
			<?php cmdeals_weekend_area_js(); ?>
			<?php cmdeals_tooltip_js(); ?>
			<?php cmdeals_datepicker_js(); ?>
		});
	</script>
	<?php
}


/**
 * Monthly sales chart
 */
function cmdeals_monthly_sales() {
	
	global $start_date, $end_date, $cmdeals, $wpdb;
	
	$first_year = $wpdb->get_var("SELECT post_date FROM $wpdb->posts ORDER BY post_date ASC LIMIT 1;");
	if ($first_year) $first_year = date('Y', strtotime($first_year)); else $first_year = date('Y');
	
	$current_year = (isset($_POST['show_year'])) ? $_POST['show_year'] : date('Y', current_time('timestamp'));
	
	$start_date = (isset($_POST['start_date'])) ? $_POST['start_date'] : '';
	$end_date	= (isset($_POST['end_date'])) ? $_POST['end_date'] : '';
	
	if (!$start_date) $start_date = $current_year.'0101';
	if (!$end_date) $end_date = date('Ym', current_time('timestamp')).'31';
	
	$start_date = strtotime($start_date);
	$end_date = strtotime($end_date);
	
	$total_sales = 0;
	$total_sales = 0;
	$order_items = 0;
	
	// Get sales to display in widget
	add_filter( 'posts_where', 'sales_within_range' );

	$args = array(
	    'numberposts'     => -1,
	    'orderby'         => 'post_date',
	    'order'           => 'ASC',
	    'post_type'       => 'deals-sales',
	    'post_status'     => 'publish' ,
	    'suppress_filters'=> 0,
	    'tax_query' => array(
	    	array(
		    	'taxonomy' => 'deals_sales_status',
				'terms' => array('completed', 'processing', 'on-hold'),
				'field' => 'slug',
				'operator' => 'IN'
			)
	    )
	);
	$sales = get_posts( $args );
	
	$order_counts = array();
	$order_amounts = array();

	// Blank date ranges to begin
	$count = 0;
	$months = ($end_date - $start_date) / (60 * 60 * 24 * 7 * 4);

	while ($count < $months) :
		$time = strtotime(date('Ym', strtotime('+ '.$count.' MONTH', $start_date)).'01').'000';

		$order_counts[$time] = 0;
		$order_amounts[$time] = 0;

		$count++;
	endwhile;
	
	if ($sales) :
		foreach ($sales as $order) :
			
			$order_total = get_post_meta($order->ID, '_order_total', true);			
			$time = strtotime(date('Ym', strtotime($order->post_date)).'01').'000';
			
			$order_items_array = (array) get_post_meta($order->ID, '_order_items', true);
			foreach ($order_items_array as $item) $order_items += (int) $item['qty'];
			$total_sales += $order_total;
			$total_sales++;
			
			if (isset($order_counts[$time])) :
				$order_counts[$time]++;
			else :
				$order_counts[$time] = 1;
			endif;
			
			if (isset($order_amounts[$time])) :
				$order_amounts[$time] = $order_amounts[$time] + $order_total;
			else :
				$order_amounts[$time] = (float) $order_total;
			endif;
			
		endforeach;
	endif;
	
	remove_filter( 'posts_where', 'sales_within_range' );
	
	?>
	<form method="post" action="">
		<p><label for="show_year"><?php _e('Year:', 'cmdeals'); ?></label> 
		<select name="show_year" id="show_year">
			<?php
				for ($i = $first_year; $i <= date('Y'); $i++) printf('<option value="%u" %u>%u</option>', $i, selected($current_year, $i, false), $i);
			?>
		</select> <input type="submit" class="button" value="<?php _e('Show', 'cmdeals'); ?>" /></p>
	</form>
	<div id="poststuff" class="cmdeals-reports-wrap">
		<div class="cmdeals-reports-sidebar">
			<div class="postbox">
				<h3><span><?php _e('Total sales for year', 'cmdeals'); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_sales>0) echo cmdeals_price($total_sales); else _e('n/a', 'cmdeals'); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e('Total sales for year', 'cmdeals'); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_sales>0) echo $total_sales. ' ('.$order_items.__(' items', 'cmdeals').')'; else _e('n/a', 'cmdeals'); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e('Average order total for year', 'cmdeals'); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_sales>0) echo cmdeals_price($total_sales/$total_sales); else _e('n/a', 'cmdeals'); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e('Average order items for year', 'cmdeals'); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_sales>0) echo number_format($order_items/$total_sales, 2); else _e('n/a', 'cmdeals'); ?></p>
				</div>
			</div>
		</div>
		<div class="cmdeals-reports-main">
			<div class="postbox">
				<h3><span><?php _e('Monthly sales for year', 'cmdeals'); ?></span></h3>
				<div class="inside chart">
					<div id="placeholder" style="width:100%; overflow:hidden; height:568px; position:relative;"></div>
				</div>
			</div>
		</div>
	</div>
	<?php

	$order_counts_array = array();
	foreach ($order_counts as $key => $count) :
		$order_counts_array[] = array($key, $count);
	endforeach;
	
	$order_amounts_array = array();
	foreach ($order_amounts as $key => $amount) :
		$order_amounts_array[] = array($key, $amount);
	endforeach;
	
	$order_data = array( 'order_counts' => $order_counts_array, 'order_amounts' => $order_amounts_array );

	$chart_data = json_encode($order_data);
	?>
	<script type="text/javascript">
		jQuery(function(){
			var order_data = jQuery.parseJSON( '<?php echo $chart_data; ?>' );
		
			var d = order_data.order_counts;
			var d2 = order_data.order_amounts;
			
			var placeholder = jQuery("#placeholder");
			 
			var plot = jQuery.plot(placeholder, [ { label: "Number of sales", data: d }, { label: "Sales amount", data: d2, yaxis: 2 } ], {
				series: {
					lines: { show: true },
					points: { show: true, align: "left" }
				},
				grid: {
					show: true,
					aboveData: false,
					color: '#ccc',
					backgroundColor: '#fff',
					borderWidth: 2,
					borderColor: '#ccc',
					clickable: false,
					hoverable: true
				},
				xaxis: { 
					mode: "time",
					timeformat: "%b %y", 
					tickLength: 1,
					minTickSize: [1, "month"]
				},
				yaxes: [ { min: 0, tickSize: 1, tickDecimals: 0 }, { position: "right", min: 0, tickDecimals: 2 } ],
		   		colors: ["#8a4b75", "#47a03e"]
		 	});
		 	
		 	placeholder.resize();
	 	
			<?php cmdeals_tooltip_js(); ?>
		});
	</script>
	<?php
}


/**
 * Top sellers chart
 */
function cmdeals_top_sellers() {
	
	global $start_date, $end_date, $cmdeals;
	
	$start_date = (isset($_POST['start_date'])) ? $_POST['start_date'] : '';
	$end_date	= (isset($_POST['end_date'])) ? $_POST['end_date'] : '';
	
	if (!$start_date) $start_date = date('Ymd', strtotime( date('Ym', current_time('timestamp')).'01' ));
	if (!$end_date) $end_date = date('Ymd', current_time('timestamp'));
	
	$start_date = strtotime($start_date);
	$end_date = strtotime($end_date);
	
	// Get sales to display in widget
	add_filter( 'posts_where', 'sales_within_range' );

	$args = array(
	    'numberposts'     => -1,
	    'orderby'         => 'post_date',
	    'order'           => 'ASC',
	    'post_type'       => 'deals-sales',
	    'post_status'     => 'publish' ,
	    'suppress_filters'=> 0,
	    'tax_query' => array(
	    	array(
		    	'taxonomy' => 'deals_sales_status',
				'terms' => array('completed', 'processing', 'on-hold'),
				'field' => 'slug',
				'operator' => 'IN'
			)
	    )
	);
	$sales = get_posts( $args );
	
	$found_deals = array();
	
	if ($sales) :
		foreach ($sales as $order) :
			$order_items = (array) get_post_meta( $order->ID, '_order_items', true );
			foreach ($order_items as $item) :
				$found_deals[$item['id']] = isset($found_deals[$item['id']]) ? $found_deals[$item['id']] + $item['qty'] : $item['qty'];
			endforeach;
		endforeach;
	endif;

	asort($found_deals);
	$found_deals = array_reverse($found_deals, true);
	$found_deals = array_slice($found_deals, 0, 25, true);
	reset($found_deals);
	
	remove_filter( 'posts_where', 'sales_within_range' );
	?>
	<form method="post" action="">
		<p><label for="from"><?php _e('From:', 'cmdeals'); ?></label> <input type="text" name="start_date" id="from" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $start_date) ); ?>" /> <label for="to"><?php _e('To:', 'cmdeals'); ?></label> <input type="text" name="end_date" id="to" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $end_date) ); ?>" /> <input type="submit" class="button" value="<?php _e('Show', 'cmdeals'); ?>" /></p>
	</form>
	<table class="bar_chart">
		<thead>
			<tr>
				<th><?php _e('Deal', 'cmdeals'); ?></th>
				<th><?php _e('Sales', 'cmdeals'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$max_sales = current($found_deals);
				foreach ($found_deals as $deal_id => $sales) :
					$width = ($sales>0) ? ($sales / $max_sales) * 100 : 0;
					
					$deal = get_post($deal_id);
					if ($deal) :
						$deal_name = '<a href="'.get_permalink($deal->ID).'">'.$deal->post_title.'</a>';
					else :
						$deal_name = __('Deal does not exist', 'cmdeals');
					endif;
					
					$sales_link = admin_url('edit.php?s&post_status=all&post_type=deals-sales&action=-1&s=' . urlencode($deal->post_title) . '&deals_sales_status=completed,processing,on-hold');
					
					echo '<tr><th>'.$deal_name.'</th><td width="1%"><span>'.$sales.'</span></td><td class="bars"><a href="'.$sales_link.'" style="width:'.$width.'%">&nbsp;</a></td></tr>';
				endforeach; 
			?>
		</tbody>
	</table>
	<script type="text/javascript">
		jQuery(function(){
			<?php cmdeals_datepicker_js(); ?>
		});
	</script>
	<?php
}


/**
 * Top earners chart
 */
function cmdeals_top_earners() {
	
	global $start_date, $end_date, $cmdeals;
	
	$start_date = (isset($_POST['start_date'])) ? $_POST['start_date'] : '';
	$end_date	= (isset($_POST['end_date'])) ? $_POST['end_date'] : '';
	
	if (!$start_date) $start_date = date('Ymd', strtotime( date('Ym', current_time('timestamp')).'01' ));
	if (!$end_date) $end_date = date('Ymd', current_time('timestamp'));
	
	$start_date = strtotime($start_date);
	$end_date = strtotime($end_date);
	
	// Get sales to display in widget
	add_filter( 'posts_where', 'sales_within_range' );

	$args = array(
	    'numberposts'     => -1,
	    'orderby'         => 'post_date',
	    'order'           => 'ASC',
	    'post_type'       => 'deals-sales',
	    'post_status'     => 'publish' ,
	    'suppress_filters'=> 0,
	    'tax_query' => array(
	    	array(
		    	'taxonomy' => 'deals_sales_status',
				'terms' => array('completed', 'processing', 'on-hold'),
				'field' => 'slug',
				'operator' => 'IN'
			)
	    )
	);
	$sales = get_posts( $args );
	
	$found_deals = array();
	
	if ($sales) :
		foreach ($sales as $order) :
			$order_items = (array) get_post_meta( $order->ID, '_order_items', true );
			foreach ($order_items as $item) :
				$found_deals[$item['id']] = isset($found_deals[$item['id']]) ? $found_deals[$item['id']] + ($item['qty'] + $item['cost']) : ($item['qty'] + $item['cost']);
			endforeach;
		endforeach;
	endif;

	asort($found_deals);
	$found_deals = array_reverse($found_deals, true);
	$found_deals = array_slice($found_deals, 0, 25, true);
	reset($found_deals);
	
	remove_filter( 'posts_where', 'sales_within_range' );
	?>
	<form method="post" action="">
		<p><label for="from"><?php _e('From:', 'cmdeals'); ?></label> <input type="text" name="start_date" id="from" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $start_date) ); ?>" /> <label for="to"><?php _e('To:', 'cmdeals'); ?></label> <input type="text" name="end_date" id="to" readonly="readonly" value="<?php echo esc_attr( date('Y-m-d', $end_date) ); ?>" /> <input type="submit" class="button" value="<?php _e('Show', 'cmdeals'); ?>" /></p>
	</form>
	<table class="bar_chart">
		<thead>
			<tr>
				<th><?php _e('Deal', 'cmdeals'); ?></th>
				<th colspan="2"><?php _e('Sales', 'cmdeals'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$max_sales = current($found_deals);
				foreach ($found_deals as $deal_id => $sales) :
					$width = ($sales>0) ? (round($sales) / round($max_sales)) * 100 : 0;
					
					$deal = get_post($deal_id);
					if ($deal) :
						$deal_name = '<a href="'.get_permalink($deal->ID).'">'.$deal->post_title.'</a>';
					else :
						$deal_name = __('Deal no longer exists', 'cmdeals');
					endif;
					
					$sales_link = admin_url('edit.php?s&post_status=all&post_type=deals-sales&action=-1&s=' . urlencode($deal->post_title) . '&deals_sales_status=completed,processing,on-hold');
					
					echo '<tr><th>'.$deal_name.'</th><td width="1%"><span>'.cmdeals_price($sales).'</span></td><td class="bars"><a href="'.$sales_link.'" style="width:'.$width.'%">&nbsp;</a></td></tr>';
				endforeach; 
			?>
		</tbody>
	</table>
	<script type="text/javascript">
		jQuery(function(){
			<?php cmdeals_datepicker_js(); ?>
		});
	</script>
	<?php
}


/**
 * Individual deals sales chart
 */
function cmdeals_deals_sales() {
	
	global $start_date, $end_date, $cmdeals;
	
	$chosen_deals_id = (isset($_POST['deal_id'])) ? $_POST['deal_id'] : '';
	
	if ($chosen_deals_id) :
		$start_date = date('Ym', strtotime( '-12 MONTHS', current_time('timestamp') )).'01';
		$end_date = date('Ymd', current_time('timestamp'));
		
		$start_date = strtotime($start_date);
		$end_date = strtotime($end_date);
		
		// Get sales to display in widget
		add_filter( 'posts_where', 'sales_within_range' );
	
		$args = array(
		    'numberposts'     => -1,
		    'orderby'         => 'post_date',
		    'order'           => 'ASC',
		    'post_type'       => 'deals-sales',
		    'post_status'     => 'publish' ,
		    'suppress_filters'=> 0,
		    'tax_query' => array(
		    	array(
			    	'taxonomy' => 'deals_sales_status',
					'terms' => array('completed', 'processing', 'on-hold'),
					'field' => 'slug',
					'operator' => 'IN'
				)
		    )
		);
		$sales = get_posts( $args );
		
		$max_sales = 0;
		$max_totals = 0;
		$deals_sales = array();
		$deal_totals = array();
		
		// Get ID's related to deals
		$chosen_deals = new cmdeals_deals( $chosen_deals_id );
		$child_ids = $chosen_deals->get_children();
		
		if ($sales) :
			foreach ($sales as $order) :
				$date = date('Ym', strtotime( $order->post_date ));
				$order_items = (array) get_post_meta( $order->ID, '_order_items', true );
				foreach ($order_items as $item) :
					if ($item['id']!=$chosen_deals_id && !in_array($item['id'], $child_ids)) continue;
					$deals_sales[$date] = isset($deals_sales[$date]) ? $deals_sales[$date] + $item['qty'] : $item['qty'];
					$deal_totals[$date] = isset($deal_totals[$date]) ? $deal_totals[$date] + ($item['qty'] * $item['cost']) : ($item['qty'] * $item['cost']);
					
					if ($deals_sales[$date] > $max_sales) $max_sales = $deals_sales[$date];
					if ($deal_totals[$date] > $max_totals) $max_totals = $deal_totals[$date];
				endforeach;
			endforeach;
		endif;
		
		remove_filter( 'posts_where', 'sales_within_range' );
	endif;
	?>
	<form method="post" action="">
		<p><label for="from"><?php _e('Deal:', 'cmdeals'); ?></label>
		<select name="deal_id" id="deal_id">
			<?php
				echo '<option value="">'.__('Choose an deals&hellip;', 'cmdeals').'</option>';
				
				$args = array(
					'post_type' 		=> 'daily-deals',
					'posts_per_page' 	=> -1,
					'post_status'		=> 'publish',
					'post_parent'		=> 0,
					'order'				=> 'ASC',
					'orderby'			=> 'title'
				);
				$deals = get_posts( $args );
				
				if ($deals) foreach ($deals as $deal) :
					
					echo '<option value="'.$deal->ID.'" '.selected($chosen_deals_id, $deal->ID, false).'>'.$deal->post_title.' (#'.$deal->ID.')</option>';
					
				endforeach;
			?>
		</select> <input type="submit" class="button" value="<?php _e('Show', 'cmdeals'); ?>" /></p>
	</form>
	<?php if ($chosen_deals_id) : ?>
	<table class="bar_chart">
		<thead>
			<tr>
				<th><?php _e('Month', 'cmdeals'); ?></th>
				<th colspan="2"><?php _e('Sales', 'cmdeals'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
				if (sizeof($deals_sales)>0) foreach ($deals_sales as $date => $sales) :
					$width = ($sales>0) ? (round($sales) / round($max_sales)) * 100 : 0;
					$width2 = ($deal_totals[$date]>0) ? (round($deal_totals[$date]) / round($max_totals)) * 100 : 0;
	
					$sales_link = admin_url('edit.php?s&post_status=all&post_type=deals-sales&action=-1&s=' . urlencode(get_the_title($chosen_deals_id)) . '&m=' . date('Ym', strtotime($date.'01')) . '&deals_sales_status=completed,processing,on-hold');
	
					echo '<tr><th><a href="'.$sales_link.'">'.date('F', strtotime($date.'01')).'</a></th>
					<td width="1%"><span>'.$sales.'</span><span class="alt">'.cmdeals_price($deal_totals[$date]).'</span></td>
					<td class="bars">
						<span style="width:'.$width.'%">&nbsp;</span>
						<span class="alt" style="width:'.$width2.'%">&nbsp;</span>
					</td></tr>';
				endforeach; else echo '<tr><td colspan="3">'.__('No sales :(', 'cmdeals').'</td></tr>';
			?>
		</tbody>
	</table>
	<?php
	endif;
}


/**
 * Customer overview
 */
function cmdeals_customer_overview() {

	global $start_date, $end_date, $cmdeals, $wpdb;
	
	$total_customers = 0;
	$total_customer_sales = 0;
	$total_guest_sales = 0;
	$total_customer_sales = 0;
	$total_guest_sales = 0;
	
	$users_query = new WP_User_Query( array( 
		'fields' => array('user_registered'), 
		'role' => 'customer'
		) );
	$customers = $users_query->get_results();
	$total_customers = (int) sizeof($customers);
	
	$args = array(
	    'numberposts'     => -1,
	    'orderby'         => 'post_date',
	    'order'           => 'DESC',
	    'post_type'       => 'deals-sales',
	    'post_status'     => 'publish' ,
	    'tax_query' => array(
	    	array(
		    	'taxonomy' => 'deals_sales_status',
				'terms' => array('completed', 'processing', 'on-hold'),
				'field' => 'slug',
				'operator' => 'IN'
			)
	    )
	);
	$sales = get_posts( $args );
	foreach ($sales as $order) :
		if (get_post_meta( $order->ID, '_customer_user', true )>0) :
			$total_customer_sales += get_post_meta($order->ID, '_order_total', true);
			$total_customer_sales++;
		else :
			$total_guest_sales += get_post_meta($order->ID, '_order_total', true);
			$total_guest_sales++;
		endif;
	endforeach;
	
	?>
	<div id="poststuff" class="cmdeals-reports-wrap">
		<div class="cmdeals-reports-sidebar">
			<div class="postbox">
				<h3><span><?php _e('Total customers', 'cmdeals'); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_customers>0) echo $total_customers; else _e('n/a', 'cmdeals'); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e('Total customer sales', 'cmdeals'); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_customer_sales>0) echo cmdeals_price($total_customer_sales); else _e('n/a', 'cmdeals'); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e('Total guest sales', 'cmdeals'); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_guest_sales>0) echo cmdeals_price($total_guest_sales); else _e('n/a', 'cmdeals'); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e('Average sales per customer', 'cmdeals'); ?></span></h3>
				<div class="inside">
					<p class="stat"><?php if ($total_customer_sales>0 && $total_customers>0) echo number_format($total_customer_sales/$total_customers, 2); else _e('n/a', 'cmdeals'); ?></p>
				</div>
			</div>
		</div>
		<div class="cmdeals-reports-main">
			<div class="postbox">
				<h3><span><?php _e('Signups per day', 'cmdeals'); ?></span></h3>
				<div class="inside chart">
					<div id="placeholder" style="width:100%; overflow:hidden; height:568px; position:relative;"></div>
				</div>
			</div>
		</div>
	</div>
	<?php
	
	$start_date = strtotime('-30 days', current_time('timestamp'));
	$end_date = current_time('timestamp');
	$signups = array();
	
	// Blank date ranges to begin
	$count = 0;
	$days = ($end_date - $start_date) / (60 * 60 * 24);
	if ($days==0) $days = 1;

	while ($count < $days) :
		$time = strtotime(date('Ymd', strtotime('+ '.$count.' DAY', $start_date))).'000';
		
		$signups[$time] = 0;

		$count++;
	endwhile;
	
	
	
	foreach ($customers as $customer) :
		if (strtotime($customer->user_registered) > $start_date) :
			$time = strtotime(date('Ymd', strtotime($customer->user_registered))).'000';
			
			if (isset($signups[$time])) :
				$signups[$time]++;
			else :
				$signups[$time] = 1;
			endif;
		endif;
	endforeach;

	$signups_array = array();
	foreach ($signups as $key => $count) :
		$signups_array[] = array($key, $count);
	endforeach;
	
	$chart_data = json_encode($signups_array);
	?>
	<script type="text/javascript">
		jQuery(function(){
			var d = jQuery.parseJSON( '<?php echo $chart_data; ?>' );

			for (var i = 0; i < d.length; ++i) d[i][0] += 60 * 60 * 1000;
			
			var placeholder = jQuery("#placeholder");
			 
			var plot = jQuery.plot(placeholder, [ { data: d } ], {
				series: {
					bars: { 
						barWidth: 60 * 60 * 24 * 1000,
						align: "center",
						show: true 
					}
				},
				grid: {
					show: true,
					aboveData: false,
					color: '#ccc',
					backgroundColor: '#fff',
					borderWidth: 2,
					borderColor: '#ccc',
					clickable: false,
					hoverable: true,
					markings: weekendAreas
				},
				xaxis: { 
					mode: "time",
					timeformat: "%d %b", 
					tickLength: 1,
					minTickSize: [1, "day"]
				},
				yaxes: [ { position: "right", min: 0, tickSize: 1, tickDecimals: 0 } ],
		   		colors: ["#8a4b75"]
		 	});
		 	
		 	placeholder.resize();
	 	
			<?php cmdeals_weekend_area_js(); ?>
		});
	</script>
	<?php
}

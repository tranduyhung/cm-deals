<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        
        <title><?php echo get_bloginfo('name'); ?></title>
		<style type="text/css">
			/* Client-specific/Reset Styles */
			#outlook a{padding:0;} /* Force Outlook to provide a "view in browser" button. */
			body{
				width:100% !important; /* Force Hotmail to display emails at full width */
				-webkit-text-size-adjust:none; /* Prevent Webkit platforms from changing default text sizes. */
				margin:0; 
				padding:0;
			}
			img{border:none; font-size:14px; font-weight:bold; height:auto; line-height:100%; outline:none; text-decoration:none; text-transform:capitalize;}
			#backgroundTable{height:100% !important; margin:0; padding:0; width:100% !important;}

			/* Template Styles */

			body {
				background: <?php echo get_option('cmdeals_email_background_color'); ?> url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJCAYAAADgkQYQAAAAOklEQVQYGWP8//8/AyHAREgBIyOjMQPIJFwYaIAxSI6RbOvAViC7A90qmBXI4qRZh2EFunXYrEC2DgDc+VH0jS2AGAAAAABJRU5ErkJggg==);
			}
			
			#templateContainer{
				border: 1px solid <?php echo cmdeals_hex_darker(get_option('cmdeals_email_background_color'), 20); ?>;
				-webkit-box-shadow:0 0 0 3px rgba(0,0,0,0.1);
				-webkit-border-radius:6px;
			}

			h1, .h1,
			h2, .h2,
			h3, .h3,
			h4, .h4 {
				color:<?php echo cmdeals_hex_darker(get_option('cmdeals_email_text_color'), 50); ?>;
				display:block;
				font-family:"Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", Geneva, Verdana, sans-serif;
				font-size:34px;
				font-weight:bold;
				line-height:150%;
				margin-top:0;
				margin-right:0;
				margin-bottom:10px;
				margin-left:0;
				text-align:left;
				line-height: 1.5;
			}

			h2, .h2{
				font-size:30px;
			}

			h3, .h3{
				font-size:26px;
			}

			h4, .h4{
				font-size:22px;
			}

			/* /\/\/\/\/\/\/\/\/\/\ STANDARD STYLING: HEADER /\/\/\/\/\/\/\/\/\/\ */

			#templateHeader{
				background-color: <?php echo get_option('cmdeals_email_base_color'); ?>;
				background: -webkit-linear-gradient(<?php echo cmdeals_hex_lighter(get_option('cmdeals_email_base_color'), 20); ?>, <?php echo get_option('cmdeals_email_base_color'); ?>);
				border-bottom:0;
				-webkit-border-top-left-radius:6px;
				-webkit-border-top-right-radius:6px;
			}

			.headerContent{
				padding:24px;
				vertical-align:middle;
			}

			.headerContent a:link, .headerContent a:visited{
				color:<?php echo cmdeals_light_or_dark(get_option('cmdeals_email_base_color'), '#202020', '#ffffff'); ?>;
				font-weight:normal;
				text-decoration:underline;
			}

			/* /\/\/\/\/\/\/\/\/\/\ STANDARD STYLING: MAIN BODY /\/\/\/\/\/\/\/\/\/\ */

			#templateContainer, .bodyContent{
				background-color:<?php echo get_option('cmdeals_email_body_background_color'); ?>;
				-webkit-border-radius:6px;
			}

			.bodyContent div{
				color: <?php echo cmdeals_hex_lighter(get_option('cmdeals_email_text_color'), 20); ?>;
				font-family:"Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", Geneva, Verdana, sans-serif;
				font-size:14px;
				line-height:150%;
				text-align:left;
			}

			.bodyContent div a:link, .bodyContent div a:visited{
				color: <?php echo get_option('cmdeals_email_text_color'); ?>;
				font-weight:normal;
				text-decoration:underline;
			}

			.bodyContent img{
				display:inline;
				height:auto;
			}

			/* /\/\/\/\/\/\/\/\/\/\ STANDARD STYLING: FOOTER /\/\/\/\/\/\/\/\/\/\ */

			#templateFooter{
				border-top:0;
				-webkit-border-radius:6px;
			}

			.footerContent div{
				color:<?php echo cmdeals_hex_lighter(get_option('cmdeals_email_text_color'), 40); ?>;
				font-family:Arial;
				font-size:12px;
				line-height:125%;
				text-align:left;
			}

			.footerContent div a:link, .footerContent div a:visited{
				color:<?php echo cmdeals_hex_lighter(get_option('cmdeals_email_text_color'), 40); ?>;
				font-weight:normal;
				text-decoration:underline;
			}

			.footerContent img{
				display:inline;
			}

			#credit {
				border:0;
				color:<?php echo cmdeals_hex_lighter(get_option('cmdeals_email_text_color'), 40); ?>;
				font-family:Arial;
				font-size:12px;
				line-height:125%;
				text-align:center;
			}

		</style>
	</head>
    <body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" style="background: <?php echo get_option('cmdeals_email_background_color'); ?> url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAADCAYAAABS3WWCAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2RpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYxIDY0LjE0MDk0OSwgMjAxMC8xMi8wNy0xMDo1NzowMSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDpBOTNBNkU1NDQ3NjJFMDExOEY4RURFQzZEQTVEMzM1QiIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo5RDM0QjczNDE4MTkxMUUyOERGOUE0MzMyQUYyMUMzQSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo5RDM0QjczMzE4MTkxMUUyOERGOUE0MzMyQUYyMUMzQSIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IFdpbmRvd3MiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDozRDQzNEI2Rjc0NjJFMDExOEY4RURFQzZEQTVEMzM1QiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpBOTNBNkU1NDQ3NjJFMDExOEY4RURFQzZEQTVEMzM1QiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PvFdiMoAAAAUSURBVHjaYvj//z8DEwMU8AIEGAAnCQMNp2NqbgAAAABJRU5ErkJggg==');">
    	<center style="padding: 70px 0 0 0;">
        	<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="backgroundTable">
            	<tr>
                	<td align="center" valign="top">
                	
                		<?php
                			if ($img = get_option('cmdeals_email_header_image')) :
                				echo '<p style="margin-top:0;"><img src="'.$img.'" alt="'.get_bloginfo('name').'" /></p>';
                			endif;
                		?>
                        
                        <!-- // End Template Preheader \\ -->
                    	<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateContainer" style="-webkit-box-shadow:0 0 0 3px rgba(0,0,0,0.025); -webkit-border-radius:6px;background-color:<?php echo get_option('cmdeals_email_body_background_color'); ?>;font-family:'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Geneva, Verdana, sans-serif;">
                        	<tr>
                            	<td align="center" valign="top">
                                    <!-- // Begin Template Header \\ -->
                                	<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader" style="background-color:<?php echo get_option('cmdeals_email_base_color'); ?>; -webkit-border-top-left-radius:6px; -webkit-border-top-right-radius:6px; color:<?php echo cmdeals_light_or_dark(get_option('cmdeals_email_base_color'), '#202020', '#ffffff'); ?>; font-family:Arial; font-weight:bold; line-height:100%; vertical-align:middle;">
                                	
                                        <tr>
                                            <td class="headerContent" style="padding:10px 24px; ">

                                            	<!-- // Begin Module: Standard Header Image \\ -->
                                            	<h1 class="h1" style="color:<?php echo cmdeals_light_or_dark(get_option('cmdeals_email_base_color'), '#202020', '#ffffff'); ?> !important; margin:0; text-shadow:0 1px 0 <?php echo cmdeals_hex_lighter(get_option('cmdeals_email_base_color'), 20); ?>;"><?php
                                            		global $email_heading;
                                            		echo $email_heading;
                                            	?></h1>
                                            	<!-- // End Module: Standard Header Image \\ -->
                                            
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- // End Template Header \\ -->
                                </td>
                            </tr>
                        	<tr>
                            	<td align="center" valign="top">
                                    <!-- // Begin Template Body \\ -->
                                	<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateBody">
                                    	<tr>
                                            <td valign="top" class="bodyContent" style="background-color:<?php echo get_option('cmdeals_email_body_background_color'); ?>;">
                                
                                                <!-- // Begin Module: Standard Content \\ -->
                                                <table border="0" cellpadding="20" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td valign="top">
                                                            <div>
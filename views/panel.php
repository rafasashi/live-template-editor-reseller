<?php

	if(!empty($this->parent->message)){ 
	
		//output message
	
		echo $this->parent->message;
	}
	
	// get current tab
	
	$currentTab = ( !empty($_GET['tab']) ? $_GET['tab'] : 'dashboard' );
	
	// ------------- output panel --------------------
	
	echo'<div id="panel" class="wrapper">';

		echo '<div id="sidebar">';
		
			echo'<ul class="nav nav-tabs tabs-left">';
				
				echo'<li class="gallery_type_title">Reseller</li>';
				
				echo'<li'.( $currentTab == 'dashboard' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->reseller . '">Dashboard</a></li>';
				
				if ( !empty($ltple->layer->types) ){
					
					echo'<li class="gallery_type_title">Manage Templates</li>';

					foreach( $ltple->layer->types as $term ){
			
						echo'<li'.( $currentTab == $term->slug ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->reseller . '?tab=' . $term->slug . '">' . ucfirst($term->name) . '</a></li>';
					}
				}	

			echo'</ul>';
			
		echo'</div>';

		echo'<div id="content" class="library-content" style="border-left: 1px solid #ddd;background:#fff;padding-bottom:15px;;min-height:700px;">';
			
			echo'<div class="tab-content">';
			
				if( $currentTab == 'dashboard' ){
					
					echo 'Last products & offers';
				}
				elseif ( !empty($ltple->layer->types) ){
					
					foreach( $ltple->layer->types as $term ){
						
						if( $currentTab == $term->slug ){
							
							if( !empty($_GET['action']) ){

								if( $_GET['action'] == 'add' ){
									
									echo '<h2>Add Product</h2>';
												
									$ltple->reseller->get_product_form($currentTab);
								}
								elseif( !empty($_GET['id']) && is_numeric($_GET['id']) ){
							
									$product_id = intval($_GET['id']);
									
									if( $post = get_post($product_id) ){
										
										if( $ltple->user->is_admin || intval($post->post_author) == $ltple->user->ID ){
											
											if( $_GET['action'] == 'edit' ){
												
												echo '<h2>Edit Product</h2>';
												
												$ltple->reseller->get_product_form($currentTab,$post);
											}
											elseif( $_GET['action'] == 'delete' ){
												
												
											}
										}
										else{
											
											echo '<div class="alert alert-warning">You don\'t have access to this page...</div>';
										}
									}
									else{
											
										echo '<div class="alert alert-warning">This product doesn\'t exist...</div>';
									}
								}
								else{
									
									echo '<div class="alert alert-warning">This action doesn\'t exist...</div>';
								}
							}
							else{
								
								echo'<style>
									
									table {
									
										font-size:15px;
									}
									
									th {
										
										vertical-align: middle !important;
									}
									
									th input, th select {
										
										width: 95% !important;
										padding: 5px !important;
										font-size: 11px !important;
										margin: 0 auto 6px auto !important;
										height: 25px !important;
										background: #ffffff !important;
										color: #888 !important;
										border: none !important;
									}
								
									.fixed-table-toolbar {
										
										margin-top: -48px;
										margin-bottom: -6px;
										display: inline-block;
										float: right;
									}
									
									.fixed-table-container {
										
										border:none !important;
									}
									
								</style>';
								
								echo'<ul class="nav nav-pills" role="tablist">';
									
									echo'<li role="presentation" class="active"><a href="' . $ltple->urls->current . '" role="tab">' . strtoupper(str_replace('-',' ',$term->slug)) . '</a></li>';
									
									echo'<li role="presentation"><a href="' . $ltple->urls->current . '&action=add" style="background:#4caf50;color:#fff;font-size:14px;">+ Add</a></li>';
									
								echo'</ul>';

								// get table fields
								
								echo'<div class="row">';
									
									$fields = array(
										
										array(

											'field' 	=> 'preview',
											'sortable' 	=> 'false',
											'content' 	=> '',
										),
										array(

											'field' 		=> 'name',
											'sortable' 		=> 'true',
											'content' 		=> 'Name',
											'filter-control'=> 'input',
										),
										array(

											'field' 		=> 'status',
											'sortable' 		=> 'true',
											'content' 		=> 'Status',
											'filter-control'=> 'select',
										), 									
										array(

											'field' 		=> 'price',
											'sortable' 		=> 'true',
											'content' 		=> 'Price ($)',
											'filter-control'=> 'select',
										),								
										array(

											'field' 	=> 'action',
											'sortable' 	=> 'false',
											'content' 	=> '',
										)											
									);
								
									// get table of results

									$ltple->api->get_table(
									
										$ltple->urls->api . 'ltple-reseller/v1/' . $term->slug . '?' . http_build_query($_POST, '', '&amp;'), 
										$fields, 
										$trash		= false,
										$export		= false,
										$search		= true,
										$toggle		= false,
										$columns	= false,
										$header		= true,
										$pagination	= true,
										$form		= false,
										$toolbar 	= 'toolbar',
										$card		= false
									);

								echo'</div>';
							}
						}
					}
				}

			echo'</div>';
			
		echo'</div>	';

	echo'</div>';
	
	?>
	
	<script>

		;(function($){		
			
			$(document).ready(function(){

			
				
			});
			
		})(jQuery);

	</script>
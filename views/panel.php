<?php

	if(!empty($this->parent->message)){ 
	
		//output message
	
		echo $this->parent->message;
	}
	
	if( $this->parent->user->is_seller ){
		
		// get current tab
		
		$currentTab = ( !empty($_GET['tab']) ? $_GET['tab'] : 'overview' );
		
		// ------------- output panel --------------------
		
		echo'<div id="panel" class="wrapper">';

			echo '<div id="sidebar">';
			
				echo'<ul class="nav nav-tabs tabs-left">';
					
					echo'<li class="gallery_type_title">Freelancer Program</li>';
					
					echo'<li'.( $currentTab == 'overview' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->seller . '">Overview</a></li>';
					
					if( $types = $ltple->seller->templates->get_types() ){
						
						echo'<li class="gallery_type_title">Manage Templates</li>';

						foreach( $types as $type ){
				
							echo'<li'.( $currentTab == $type->slug ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->seller . '?tab=' . $type->slug . '">' . ucfirst($type->name) . '</a></li>';
						}
					}	

				echo'</ul>';
				
			echo'</div>';

			echo'<div id="content" class="library-content" style="border-left:1px solid #ddd;background:#fbfbfb;padding-bottom:15px;min-height:700px;">';
				
				echo'<div class="tab-content">';
				
					if( $currentTab == 'overview' ){
						
						echo'<div class="tab-pane active" id="overview">';

							echo'<div class="bs-callout bs-callout-primary">';
							
								echo'<h4>';
								
									echo'Overview';
									
								echo'</h4>';
							
								echo'<p>';
								
									echo 'Your earnings snapshot and seller information';
								
								echo'</p>';	

							echo'</div>';														

							echo'<div class="row">';
							echo'<div class="col-xs-12">';
							
								echo'<div class=" panel panel-default" style="margin-bottom:0;">';
								
									echo'<table class="table table-striped table-hover">';
									
									echo'<tbody>';
										
										echo'<tr style="font-size:18px;font-weight:bold;">';
											
											echo'<td>Pending balance</td>';
											
											echo'<td>' . $ltple->seller->get_seller_balance($ltple->user->ID) . '</td>';
										
										echo'</tr>';
									
									echo'</tbody>';
									
									echo'</table>';
								
								echo'</div>';
								
							echo'</div>';
							echo'</div>';
							
							echo'<div class="row">';
							echo'<div class="col-xs-12">';
							
								echo'<div class="well" style="display:inline-block;width:100%;margin-top:20px;">';
									
									echo'<div class="col-xs-12 col-sm-7">';
									
										echo'<div class="row">';
										
											echo'<div class="col-xs-6">';
											
												echo'Marketplace Fee';
												
											echo'</div>';
											
											echo'<div class="col-xs-6">';
											
												echo'<b>50%</b> on product sales';
												
											echo'</div>';

										echo'</div>';

									echo'</div>';
									
									echo'<div class="clearfix"></div>';	
									echo'<hr></hr>';								
									
									echo'<div class="col-xs-12 col-sm-7">';
									
										echo'<div class="row">';

											echo'<div class="col-xs-6">';
											
												echo'Minimum payout';
												
											echo'</div>';
											
											echo'<div class="col-xs-6">';
											
												echo'<div><b>$100.00</b> via PayPal <i style="font-size:11px;">( PayPal fee 3,4% + $0,30 )</i></div>';
												echo'<div><b>€100.00</b> via SEPA <i style="font-size:11px;">( no fee )</i></div>';
												echo'<div><b>$100.00</b> via Other <i style="font-size:11px;">( depending on your country )</i></div>';
												
											echo'</div>';											
										
										echo'</div>';

									echo'</div>';
									
									echo'<div class="clearfix"></div>';	
									echo'<hr></hr>';								
									
									echo'<div class="col-xs-12 col-sm-12">';
									
										echo'<div class="row">';

											echo'<div class="col-xs-3">';
											
												echo'Paypal Account';
												
											echo'</div>';
											
											echo'<div class="col-xs-6">';
											
												echo'<form action="' . $this->parent->urls->current . '" method="post" class="tab-content row">';
					
													echo'<div class="row">';

														echo'<div class="col-xs-6">';				
					
															$this->parent->admin->display_field( array(
										
																'type'				=> 'text',
																'id'				=> $this->parent->_base . '_paypal_email',
																'placeholder' 		=> 'myemail@example.com',
																'description'		=> ''
																
															), $this->parent->user );
								
														echo'</div>';
									
														echo'<div class="col-xs-6">';				
					
															echo'<button class="btn btn-sm btn-warning" style="width:50px;">Save</button>';
									
														echo'</div>';								
									
													echo'</div>';
									
												echo'</form>';											
												
											echo'</div>';											
										
										echo'</div>';

									echo'</div>';
									
								echo'</div>';
							
							echo'</div>';
							echo'</div>';						

						echo'</div>';
					}
					elseif( $types = $ltple->seller->templates->get_types() ){
						
						foreach( $types as $type ){
							
							if( $currentTab == $type->slug ){
								
								if( !empty($_GET['action']) ){

									if( $_GET['action'] == 'add' ){
										
										echo '<h2>Add Product</h2>';
													
										$ltple->seller->get_product_form($currentTab);
									}
									elseif( !empty($_GET['id']) && is_numeric($_GET['id']) ){
								
										$product_id = intval($_GET['id']);
										
										if( $post = get_post($product_id) ){
											
											if( $ltple->user->is_admin || intval($post->post_author) == $ltple->user->ID ){
												
												if( $_GET['action'] == 'edit' ){
													
													echo '<h2>Edit Product</h2>';
													
													$ltple->seller->get_product_form($currentTab,$post);
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
									
									echo'<ul class="nav nav-pills" role="tablist">';
										
										echo'<li role="presentation" class="active"><a href="' . $ltple->urls->current . '" role="tab">' . strtoupper(str_replace('-',' ',$type->slug)) . '</a></li>';
										
										echo'<li role="presentation"><a href="' . $ltple->urls->current . '&action=add" style="background:#4caf50;color:#fff;">+ New</a></li>';
										
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
										
											$ltple->urls->api . 'ltple-seller/v1/' . $type->slug . '?' . http_build_query($_POST, '', '&amp;'), 
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
		
	}
	else{
		
		echo '<div class="panel-body" style="min-height:300px;">';
		
			echo '<div class="alert alert-warning">';
			
				echo 'You need to be a member of the Seller Program to access this area. Please contact us.';
			
			echo '</div>';
			
		echo '</div>';
	}
	
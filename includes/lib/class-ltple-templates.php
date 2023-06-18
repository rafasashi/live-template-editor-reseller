<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Seller_Templates {

	var $parent;
	
	var $types;
	
	/**
	 * Constructor function
	 */
	 
	public function __construct ( $parent ) {

		$this->parent 	= $parent;
		
		// add profile tabs		

		add_filter( 'ltple_profile_tabs', array( $this, 'add_profile_tabs'),10,1);
		
		add_filter( 'ltple_gallery_item_title', array( $this, 'filter_gallery_item_title' ),10,2);		
		
		// default layer fields
		
		add_filter( 'ltple_default_layer_fields', array( $this, 'add_default_layer_fields'),10);
	
		// layer type fields
	
		//add_action('layer-type_add_form_fields', array( $this, 'add_layer_fields' ) );
		add_action('layer-type_edit_form_fields', array( $this, 'add_edit_layer_fields' ) );
	
		//add_filter('manage_edit-layer-type_columns', array( $this, 'set_layer_type_columns' ) );

		//add_action('create_layer-type', array( $this, 'save_layer_taxonomy_fields' ) );
		add_action('edit_layer-type', array( $this, 'save_layer_taxonomy_fields' ) );	
			
	}
	
	public function get_types(){
		
		if( is_null($this->types) ){
			
			$this->types = get_terms( array(
				
				'taxonomy'  	=> 'layer-type',
				'hide_empty' 	=> false,
				'meta_query' 	=> array(
				
					array(
					
					   'key'       => 'can_sell',
					   'value'     => 'on',
					   'compare'   => '='
					)
				),
				
								
			));			
		}
		
		return $this->types;
	}
	
	public function add_profile_tabs($tabs){
		
		/*
		if( $tab_content = $this->get_profile_tab_content($this->parent->profile->id) ){
		
			$tabs['addon']['position'] 	= 3;
			$tabs['addon']['name'] 		= 'Templates';
			
			if( $this->parent->profile->tab == 'templates' ){
				
				add_action( 'wp_enqueue_scripts',function(){

					wp_register_style( $this->parent->_token . '-templates', false, array());
					wp_enqueue_style( $this->parent->_token . '-templates' );
				
					wp_add_inline_style( $this->parent->_token . '-templates', '

						#templates {
							
							margin-top:20px;
						}
						
					');

				},10 );				
				
				$tabs['addon']['content'] 	= $tab_content;
			}
		}
		*/
		
		return $tabs;
	}		
	
	public function filter_gallery_item_title($item_title,$post){
		
		return $item_title;
	}
	
	public function add_default_layer_fields($layer_type=null){
		
		/*
		if( !empty($layer_type->term_id) ){
		
			$can_sell = get_term_meta( $layer_type->term_id, 'can_sell', true);
			
			if( $can_sell == 'on' ){
			
				$this->parent->layer->defaultFields[] = array(
				 
					"metabox" =>
					
						array(
					
							'name' 		=> 'seller-settings',
							'title' 	=> __( 'Seller settings', 'live-template-editor-client' ), 
							'screen'	=> array('cb-default-layer'),
							'context' 	=> 'side',
						),	
						
						'id'			=> "layerPrice",
						'label'			=> "Price",
						'type'			=> 'number',
						'default'		=> '0',
						'placeholder'	=> '0',
						'description'	=> ''
				);
			}
		}
		*/
	}
	
	public function add_edit_layer_fields($term){
		
		//output our additional fields

		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Seller Program</label>';
			
			echo'</th>';
			
			echo'<td>';
				
				$this->parent->admin->display_field( array(			
					
					'name'			=> 'can_sell',
					'id'			=> 'can_sell',
					'label'			=> "",
					'type'			=> 'switch',
					'default'		=> '',
					'description'	=> 'Open to sellers',
					
				), $term );
				
			echo'</td>';	
			
		echo'</tr>';		

	}
	
	public function get_panel_items($layer_type) {
	
		$seller_items = array();
				
		// set query arguments
		
		$args = array(
			
			'post_type'			=> 'cb-default-layer',
			'post_status'		=> array('publish','draft','pending'),
			'author'			=> $this->parent->user->ID,
			'posts_per_page' 	=> -1,
		);			
		
		/*
		$mq = 0;
		
		// filter price
		
		$args['meta_query'][$mq][] = array(

			'key' 		=> 'layerPrice',
			'value' 	=> 0,
			'compare' 	=> '>',
			'type' 		=> 'NUMERIC'			
		);
		*/
		
		// filter layer type
		
		$args['tax_query'] = array('relation'=>'AND');
		
		$args['tax_query'][] = array(
		
			'taxonomy' 			=> 'layer-type',
			'field' 			=> 'slug',
			'terms' 			=> $layer_type->slug,
			'include_children' 	=> false,
			'operator'			=> 'IN'
		);

		$q = new WP_Query( $args );		
		
		if( !empty($q->posts) ){
			
			foreach( $q->posts as $item ){
				
				$item->layer_type 	= $layer_type->slug;
				
				$item->price = 0;
				
				if( $item_meta = get_post_meta($item->ID) ){
					
					if( !empty($item_meta['layerPrice']) ){
					
						$item->price = intval($item_meta['layerPrice'][0]);
					}
				}
				
				$seller_items[] = $item;
			}
		}

		return $seller_items;
	}
	
	public function save_layer_taxonomy_fields($term_id){

		if( $this->parent->user->is_admin ){
			
			if(isset($_POST['can_sell'])){

				update_term_meta( $term_id, 'can_sell', $_POST['can_sell']);			
			}			
		}
	}
	
	public function get_profile_tab_content($author_id){
		
		$tab_content = false;
		
		if( $author_id > 0 ){

			if( $categories = $this->get_user_product_categories($author_id) ){
				
				$product_cat = ( !empty($_GET['cat']) ? $_GET['cat'] : $categories[0]->slug );
				
				$tab_content = '';
				
				// get product categories
				
				$tab_content .= '<div class="col-xs-12" style="margin-bottom: 15px;">';
				
					foreach( $categories as $term ){
						
						$type_url = $this->parent->profile->url . '/templates/';
						
						$type_url = remove_query_arg(array('paged','cat'),$type_url);
						
						if( $product_cat != $term->slug ){
							
							$type_url = add_query_arg('cat',$term->slug,$type_url);
						}

						$tab_content .= '<a style="margin-right:5px;'.( $product_cat == $term->slug ? 'color:#fff;background-color:' . $this->parent->settings->mainColor . ';' : 'background-color:#fff;color:' . $this->parent->settings->mainColor . ';' ).'" href="'.$type_url.'" class="btn btn-md">';
						
							$tab_content .= $term->name . ' <span class="badge">' . $term->count . '</span>';
						
						$tab_content .= '</a>';
					}
				
				$tab_content .= '</div>';
				
				// get product items
				
				foreach( $categories as $term ){
										
					if( $product_cat == $term->slug){
						
						$items = $this->get_profile_items($term->slug,$author_id);
					
						foreach( $items as $item ){
							
							$tab_content .= $item;
						}
						
						$tab_content .='<div class="pagination" style="display: inline-block;width: 100%;padding: 0px 15px;">';
							
							$tab_content .= paginate_links( array(
								'base'         => '%_%',
								'total'        => $this->max_num_pages,
								'current'      => max( 1, get_query_var( 'paged' ) ),
								'format'       => '?paged=%#%',
								'show_all'     => false,
								'type'         => 'plain',
								'end_size'     => 2,
								'mid_size'     => 1,
								'prev_next'    => true,
								'prev_text'    => sprintf( '<i></i> %1$s', __( 'Prev', 'live-template-editor-client' ) ),
								'next_text'    => sprintf( '%1$s <i></i>', __( 'Next', 'live-template-editor-client' ) ),
								'add_args'     => false,
								'add_fragment' => '',
							) );
							
						$tab_content .='</div>	';						
					}
				}
			}
		}
		
		return $tab_content;
	}	
	
	public function get_user_product_categories($author_id){
		
		if( !isset($this->categories[$author_id]) ){
			
			// get all layer types
			
			$meta_query = array();
			
			if( !empty($_REQUEST['layer']) && is_array($_REQUEST['layer']) ){

				$meta = $_REQUEST['layer'];
				
				foreach( $meta as $key => $value ){
					
					$meta_query[] = array(
								
						array(
						
							'key' 		=> $key,
							'value' 	=> $value,
							'compare' 	=> '='
						),
					);			
				}
			}
				
			if( $categories = get_terms( array(
					
				'taxonomy' 		=> 'layer-type',
				'orderby' 		=> 'count',
				'order' 		=> 'DESC',
				'hide_empty' 	=> true,
				'meta_query' 	=> $meta_query,
			))){
				
				foreach( $categories as $term ){
				
					$term->visibility = get_option('visibility_'.$term->slug,'anyone');
					
					// meta query
					
					$meta_query = array('relation'=>'AND');
					
					$meta_query[] = array(
								
						array(
						
							'key' 		=> 'layerPrice',
							'value' 	=> 0,
							'compare' 	=> '>'
						),
					);							
					
					// count posts in term
					
					$q = new WP_Query([
					
						'posts_per_page' 	=> 0,
						'post_type' 		=> 'cb-default-layer',
						'author' 			=> $author_id,
						'meta_query' 		=> $meta_query,
						'tax_query' 		=> array(
							
							array(
							
								'taxonomy' 	=> $term->taxonomy,
								'terms' 	=> $term,
								'field' 	=> 'slug'
							)
						),
						
					]);
					
					$term->count = $q->found_posts; // replace term count by real post type count
				}
			}
			
			if( !empty($categories) ){
			
				// order by count
				
				$counts = array();
				
				foreach( $categories as $key => $type ){
					
					if( $type->count > 0 ){
					
						$counts[$key] = $type->count;
					}
					else{
						
						unset($categories[$key]);
					}
				}
				
				array_multisort($counts, SORT_DESC, $categories);
			}
			
			$this->categories[$author_id] = $categories;
		}
		
		return $this->categories[$author_id];
	}
	
	public function get_profile_items($layer_type,$author=0){
		
		$items =[];

		if( !empty($layer_type) ){
			
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
			
			// meta query
			
			$meta_query = array('relation'=>'AND');
			
			$meta_query[] = array(
						
				array(
				
					'key' 		=> 'layerPrice',
					'value' 	=> 0,
					'compare' 	=> '>'
				),
			);		
			
			// taxonomy query
			
			$tax_query = array('relation'=>'AND');
			
			$tax_query[] = array(
			
				'taxonomy' 			=> 'layer-type',
				'field' 			=> 'slug',
				'terms' 			=> $layer_type,
				'include_children' 	=> false,
				'operator'			=> 'IN'
			);				
			
			if( $query = new WP_Query(array( 
			
				'post_type' 	=> 'cb-default-layer',
				'author' 		=> $author,
				'posts_per_page'=> 15,
				'paged' 		=> $paged,
				'tax_query' 	=> $tax_query,
				'meta_query' 	=> $meta_query,
				
			))){
				
				$this->max_num_pages = $query->max_num_pages;
				
				$this->totalItems = $query->found_posts;
				
				while ( $query->have_posts() ) : $query->the_post(); 
					
					global $post;			
					
					//get item
					
					$item = $this->get_item($post,$layer_type);
					
					//merge item
					
					$items[]=$item;					
					
				endwhile; wp_reset_query();
			}
		}

		return $items;		
	}
	
	public function get_item($post,$layer_type=null){
							
		$item='';
		
		if( !empty($post) ){
			
			$currency = '$';
			
			// get info url
			
			$info_url = get_permalink($post);
			
			// get preview url
			
			$preview_url = $this->parent->urls->home . '/preview/' . $post->post_name . '/';

			// get editor_url

			$editor_url = $this->parent->urls->edit . '?uri='.$post->ID;
									
			//get post_title
			
			$post_title = the_title('','',false);

			$price = get_post_meta($post->ID,'layerPrice',true);
			
			// get item

			$item.='<div class="' . implode( ' ', get_post_class("col-xs-12 col-sm-6 col-md-4",$post->ID) ) . '" id="post-' . $post->ID . '">';
				
				$item.='<div class="panel panel-default">';
					
					$item.='<div class="thumb_wrapper" style="background:url(' . $this->parent->layer->get_thumbnail_url($post) . ');background-size:cover;background-repeat:no-repeat;background-position:top center;"></div>';					

					$item.='<div class="panel-body" style="position:relative;">';

						$item.='<div class="pull-right">';
						
							$item.='<span class="badge" style="';
							
								$item.='font-size:18px;';
								$item.='background:#ffffff;';
								$item.='color:' . $this->parent->settings->mainColor . ';';
								$item.='border-radius:4px;';
								$item.='border: 1px solid ' . $this->parent->settings->mainColor . ';';
								
							$item.='">' . $currency . $price . '</span>';
							
						$item.='</div>';
						
						$item.= apply_filters('ltple_gallery_item_title','<b>' . $post_title . '</b>',$post);
						
					$item.='</div>';
					
					$item.='<div style="background:#fff;border:none;" class="panel-footer text-right">';

						if( $this->parent->inWidget === true ){
							
							if($this->parent->plan->user_has_layer( $post->ID ) === true){
								
								$item.='<a target="_blank" class="btn btn-sm btn-success" href="'. $editor_url .'" target="_self" title="Edit layer">Edit</a>';
							}												
						}
						else{
							
							// info button
							
							$item.='<a class="btn btn-sm btn-info" style="margin-right:4px;" href="'. $info_url . '/" title="More info about '. $post_title .' template">Info</a>';
							
							// preview button

							$modal_id='modal_'.md5($preview_url);
							
							$item.='<button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#'.$modal_id.'">'.PHP_EOL;
								
								$item.='Preview'.PHP_EOL;
							
							$item.='</button>'.PHP_EOL;

							$item.='<div class="modal fade" id="'.$modal_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="z-index:9999999;">'.PHP_EOL;
								
								$item.='<div class="modal-dialog modal-full" role="document">'.PHP_EOL;
									
									$item.='<div class="modal-content">'.PHP_EOL;
									
										$item.='<div class="modal-header">'.PHP_EOL;
											
											$item.='<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
											
											$item.='<h4 class="modal-title text-left" id="myModalLabel">Preview</h4>'.PHP_EOL;
										
										$item.='</div>'.PHP_EOL;
									  
										$item.='<div class="modal-body">'.PHP_EOL;
											
											if( $this->parent->user->loggedin && $this->parent->plan->user_has_layer( $post->ID ) === true ){
												
												$item.= '<div class="loadingIframe" style="position:absolute;height:50px;width:100%;background-position:50% center;background-repeat: no-repeat;background-image:url(\'' . $this->parent->server->url . '/c/p/live-template-editor-server/assets/loader.gif\');"></div>';

												$item.= '<iframe data-src="'.$preview_url.'" style="width: 100%;position:relative;bottom: 0;border:0;height: 450px;overflow: hidden;"></iframe>';											
											}
											else{
												
												$item.= get_the_post_thumbnail($post->ID, 'recentprojects-thumb');
											}

										$item.='</div>'.PHP_EOL;

										$item.='<div class="modal-footer">'.PHP_EOL;
										
											if($this->parent->user->loggedin){

												$item.='<a class="btn btn-sm btn-success" href="'. $editor_url .'" target="_self" title="Edit layer">Edit</a>';
											}
											else{
												
												$item.='<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#login_first">'.PHP_EOL;
												
													$item.='<span class="glyphicon glyphicon-lock" aria-hidden="true"></span> Edit'.PHP_EOL;
											
												$item.='</button>'.PHP_EOL;								
											}
											
										$item.='</div>'.PHP_EOL;
									  
									$item.='</div>'.PHP_EOL;
									
								$item.='</div>'.PHP_EOL;
								
							$item.='</div>'.PHP_EOL;							
								
							// checkout button	
							
							if( !empty($layer_type) ){
							
								$item.= $this->parent->product->get_checkout_button($post,$layer_type,$price);
							}
						}
						
					$item.='</div>';
				
				$item.='</div>';
				
			$item.='</div>';
		}

		return $item;
	}
	
	/**
	 * Main LTPLE_Seller_Templates Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Stars is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Stars instance
	 */
	public static function instance ( $parent ) {
		
		if ( is_null( self::$_instance ) ) {
			
			self::$_instance = new self( $parent );
		}
		
		return self::$_instance;
		
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()
}

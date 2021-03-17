<?php
/**
 * ShMapper
 *
 * @package teplitsa
 */

	class SMC_Post
	{
		public $id;
		public $ID;
		public $body;
		public $meta;
		static $instances;
		static $all_ids;
		static $all_posts;
		function __construct($id)
		{
			if(isset($id->ID))
			{
				$this->id		= $this->ID	= $id->ID;
				$this->body		= $id;
			}
			else
			{
				$this->id		= $id;
				$this->body		= get_post($id);
			}
		}
		function is_enabled()
		{
			return isset($this->body->ID);
		}
		public static function get_instance($id)
		{
			$obj				= is_numeric($id) ?	$id :	$id->ID;
			if(!static::$instances)	static::$instances = array();
			if(!isset(static::$instances[$obj]))
				static::$instances[$obj] = new static($obj);
			return static::$instances[$obj];
		}
		static function insert($data)
		{
			$id		= wp_insert_post(
				array(
					"post_type"		=> $data['post_type'] ? $data['post_type'] : static::get_type(),
					'post_name'    	=> $data['post_name'],
					'post_title'    => $data['post_title'],
					'post_content'  => $data['post_content'],
					'post_status'   => isset($data['post_status']) ? $data['post_status'] : 'publish',
					"post_author"	=> $data['post_author'] ? $data['post_author'] : get_current_user_id()
				)
			);
			$post	= static::get_instance($id);
			$post->update_metas($data);
			return $post;
		}
		function doubled()
		{
			$metas		= array('post_title'=>$this->body->post_title, 'post_content'  => $this->body->post_content);
			require_once(SHM_REAL_PATH."class/SMC_Object_type.php");
			$SMC_Object_Type	= new SMC_Object_Type();
			$object				= $SMC_Object_Type->object;
			foreach($object[static::get_type()] as $key=>$val)
			{
				if($key 		== "t") continue;
				$metas[$key]	= $this->get_meta($key);
			}
			$metas = apply_filters("smc_before_doubled_post", $metas, $this);
			$metas['post_author'] = $this->get("post_author");
			$post =  static::insert($metas);	
			do_action("smc_after_doubled_post", $post, $this);
			return  $post;
		}
		static function delete($id)
		{
			if(is_numeric($id))
			{
				return wp_delete_post($id);
			}
			else
			{
				return wp_delete_post($id->ID);
			}
		}
		static function update($data, $id)
		{
			$cd = [];
			foreach($data as $key => $val)
			{
				if(in_array($key, ["post_type", 'post_name', 'post_title', 'post_content', 'post_status', "post_author", "thumbnail"]))
					$cd[$key]	= $val;
			}
			$cd['ID']	= $id;
			$id		= wp_update_post( $cd );
			$post	= static::get_instance($id); 		
			$post->update_metas($data);
			return $post;
		}
		function update_metas($meta_array)
		{
			$data	= array();
			foreach($meta_array as $meta=>$val)
			{
				if( $meta	== 'post_title' || $meta	== 'post_content' )
				{
					$data[$meta] = $val;
					continue;
				}
				if( $meta	== 'title' || $meta	== 'name' || $meta == 'obj_type' )
				{
					continue;
				}
				$this->update_meta($meta, $val);
			}
			if(count($data))
			{
				$data['ID'] = $this->id;
				$id			= wp_update_post($data);
			}
		}
		public function get_meta($name)
		{
			return get_post_meta($this->id, $name, true);
		}
		public function update_meta($name, $value)
		{
			update_post_meta( $this->id, $name, $value );
			return $value;
		}
		public function get($field)
		{
		    return is_object($this->body) ? $this->body->$field : NULL;
		}
		function set($field)
		{
			$this->body->$field	= $field;
			wp_update_post($this->body);
		}
		public function get_the_author()
		{
			global $authordata;
			$autor_id		= $this->body->post_author;
			$authordata		= get_userdata($autor_id);
			$author			= apply_filters("the_author", $authordata->display_name);
			return $author;
		}		
		
		/*
		
		*/
		static function get_random($count=1)
		{
			$args		= array(
								'numberposts'	=> $count,
								'offset'		=> 0,
								'orderby'		=> "rand",
								'post_status' 	=> 'publish',
								'fields'		=> 'all',
								'post_type'		=> static::get_type(),
			);
			$p			= get_posts($args);
			return static::get_instance($p[0]);
		}
		
		/*
		
		*/
		static function get_all($metas=-1, $numberposts=-1, $offset=0, $order_by='title', $order='DESC', $order_by_meta="", $fields="all", $relation="AND", $author=-1)
		{
			$args		= array(
									"numberposts"		=> $numberposts,
									"offset"			=> $offset,
									'orderby'  			=> $order_by,
									'order'     		=> $order,
									'post_type' 		=> static::get_type(),
									'post_status' 		=> 'publish',	
									'fields'			=> $fields
								);
			if($author !=-1)
			{
				$args['author']	= $author;
			}
			if($order_by == "meta_value" || $order_by == "meta_value_num") 	
				$args['meta_key']	= $order_by_meta;
			if(is_array($metas))
			{
				$arr		= array();
				foreach($metas as $key=>$val)
				{
					$ar					= array();
					$ar["value"]		= is_array($val) ? $val["value"] : $val;
					$ar["key"]			= $key;
					if(is_array($val) && !isset($val["value"]))
						$ar["operator"]	= "OR";
					else
						$ar["compare"]	= is_array($val) ? $val["compare"] : "=";
					$arr[]				= $ar;
				}
				$args['meta_query']	= array('relation'		=> $relation);
				$args['meta_query'][] 	= $arr;				
			}
			//return $args;
			self::$all_posts	=  get_posts($args);
			return self::$all_posts;
		}
		static function get_all_count( $args=-1 )
		{
			if(is_array($args ))
			{
				$args["numberposts"] = -1;
				$args['fields'] = "ids";
				$args['post_status'] = "publish";
				return count(get_posts($args));
			}
			else
			{
				global $wpdb;
				$query = "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_status='publish' AND post_type='".static::get_type()."';";
				return $wpdb->get_var( $query );
			}
			/**/
		}
		
		
		
		static function get_all_ids($metas=-1, $numberposts=-1, $offset=0, $order_by='title', $order='DESC', $is_update=false)
		{
			$args		= array(
									"numberposts"		=> $numberposts,
									"offset"			=> $offset * $numberposts,
									'orderby'  			=> $order_by,
									'order'     		=> $order,
									'fields'			=> "ids",
									'post_type' 		=> static::get_type(),
									'post_status' 		=> 'publish',									
								);
			if(is_array($metas))
			{
				$arr		= array();
				foreach($metas as $key=>$val)
				{
					$ar				= array();
					$ar["value"]	= $val;
					$ar["key"]		= $key;
					$ar["compare"]	= "=";
					$arr[]			= $ar;
				}
				$args['meta_query'][] = $arr;
				
			}
			static::$all_ids		= get_posts($args);
			return static::$all_ids;
		}
		
		/*
			
		*/
		static function amount_meta($meta_key, $post_ids=-1)
		{
			if(!is_array($post_ids))	return 0;
			global $wpdb;
			$ids					= array();
			foreach($post_ids as $post_id)
			{
				if( $post_id instanceof SMC_Post )
					$ids[]			= $post_id->id;
				else if( $post_id instanceof WP_Post )
					$ids[]			= $post_id->ID;
				else if( is_numeric($post_id ) )
					$ids[]			= $post_id;				
			}
			$query		= "SELECT SUM(meta_value) FROM " . $wpdb->prefix . "postmeta WHERE post_id IN(" . implode(",", $ids) . ") AND meta_key='count';";
			$amount		= $wpdb->get_var($query);
			return $amount;
		}
		
		/*
			
		*/
		static function wp_dropdown($params="-1")
		{
		    if( !is_array($params) ) {
				$params	= array();
		    }

		    if(isset($params["exclude_post_id"]) && !is_array($params["exclude_post_id"])) {
		        $params["exclude_post_id"] = array($params["exclude_post_id"]);
		    }
			
			$hubs = empty($params['posts']) ?
                (empty($params['args']) ? array() : self::get_all($params['args'])) :
                $params['posts'];
			
			$html		= "<select ";
			if( !empty($params['class']) )
				$html	.= "class='".$params['class']."' ";
			if( !empty($params['style']) )
				$html	.= "style='".$params['style']."' ";
			if( !empty($params['name']) )
				$html	.= "name='".$params['name']."' ";
			if( !empty($params['id']) )
				$html	.= "id='".$params['id']."' ";
			$html		.= " >";
			$zero 		= empty($params['select_none']) ? '---' : $params['select_none'];
			$html		.= "<option value='-1'>$zero</option>";

			foreach($hubs as $hub)
			{
			    if(isset($params["exclude_post_id"]) && in_array($hub->ID, $params["exclude_post_id"])) {
			        continue;
			    }
			    
				$idd 	= empty($params['display_id']) ? '' : $hub->ID.'. ';
				$html	.= "
				<option value='" . $hub->ID . "' " . selected($hub->ID, $params['selected'], 0) . ">
					$idd" . $hub->post_title .
				"</option>";
			}
			$html		.= "</select>";
			return $html;	
		}
		
		static function dropdown($data_array, $params="-1")
		{
			if(!is_array($params))
				$params	= array();
			$hubs		= $data_array;
			$html		= "<select ";
			if($params['class'])
				$html	.= "class='".$params['class']."' ";
			if($params['style'])
				$html	.= "style='".$params['style']."' ";
			if($params['name'])
				$html	.= "name='".$params['name']."' ";
			if($params['id'])
				$html	.= "id='".$params['id']."' ";
			$html		.= " >";
			$html		.= "<option value='-1'>---</option>";			
			foreach($hubs as $hub)
			{
				$html	.= "<option value='".$hub['ID']."' ".selected($hub->ID, $params['selected'], false).">".$hub['ID'].". ".$hub['post_title'] . "</option>";
			}
			$html		.= "</select>";
			return $html;	
		}
		static function get_type()
		{
			return "post";
		}
		
		
		static function init()
		{
			if(!static::$instances || !is_array( static::$instances ))	
				static::$instances = [];
			$typee	= static::get_type();
			add_action('admin_menu',							array(get_called_class(), 'my_extra_fields'));
			add_action("save_post_{$typee}",					array(get_called_class(), 'true_save_box_data'), 10);
			
			//admin table 
			add_filter("manage_edit-{$typee}_columns", 			array(get_called_class(), 'add_views_column'), 4);
			add_filter("manage_edit-{$typee}_sortable_columns", array(get_called_class(), 'add_views_sortable_column'));
			add_filter("manage_{$typee}_posts_custom_column", 	array(get_called_class(), 'fill_views_column'), 5, 2);
			add_filter("pre_get_posts",							array(get_called_class(), 'add_column_views_request'));
			
			//bulk actions
			add_filter("bulk_actions-edit-{$typee}", 			array(get_called_class(), "register_my_bulk_actions"));
			add_filter("handle_bulk_actions-edit-{$typee}",  	array(get_called_class(), 'my_bulk_action_handler'), 10, 3 );
			//add_action('admin_notices', 						array(get_called_class(), 'my_bulk_action_admin_notice' ));
			add_action("bulk_edit_custom_box", 					array(get_called_class(), 'my_bulk_edit_custom_box'), 2, 2 );
			//add_action("quick_edit_custom_box", 				array(get_called_class(), 'my_bulk_edit_custom_box'), 2, 2 );
			add_action("wp_ajax_save_bulk_edit", 				array(get_called_class(), 'save_bulk_edit_book') );
			return;	
		}
		 	
		static function add_views_column( $columns )
		{
			require_once(SHM_REAL_PATH."class/SMC_Object_type.php");
			$SMC_Object_type	= SMC_Object_Type::get_instance();
			$obj				= $SMC_Object_type->object [forward_static_call_array( array( get_called_class(),"get_type"), array()) ];
			$posts_columns = array(
				"cb" 				=> " ",
				//"IDs"	 			=> __("ID", 'smp'),
				"title" 			=> __("Title")
			);
			//insertLog("add_views_column", "----");
			foreach($obj as $key=>$value)
			{
				if($key == 't' ||$key == 'class' ) continue;
				if(isset($value['hidden']) && $value['hidden'] || (isset($value['thread']) && $value['thread'] === false))
					continue;
				$posts_columns[$key] = isset($value['name']) ? $value['name'] : $key;
			}
			return $posts_columns;				
		}
			
		static function fill_views_column($column_name, $post_id) 
		{	
			$p					= static::get_instance($post_id);
			require_once(SHM_REAL_PATH."class/SMC_Object_type.php");
				
			$SMC_Object_type	= SMC_Object_type::get_instance();
			$obj				= $SMC_Object_type->object [forward_static_call_array( array( get_called_class(),"get_type"), array()) ];
			
			switch( $column_name) 
			{		
				case 'IDs':
					$color				= $p->get_meta( "color" );
					if($post_id)
						echo "<div class='IDs'><span style='background-color:#$color;'>ID</span>".$post_id. "</div>
					<p>";
					break;	
				default:
					if(array_key_exists($column_name, $obj))
					{
						$meta			= $p->get_meta($column_name);
						switch($obj[$column_name]['type'])
						{
							case "number":
							case "string":
								echo $meta;
								break;
							case "date":
								echo $meta ? date("d.m.Y   H:i", $meta) : "";
								break;
							case "boolean":
								echo $meta 
									? "<img src='" . SHM_URLPATH . "assets/img/check_checked.png'> <span class='smc-label-782px'>" . $obj[$column_name]['name'] . "</span>" 
									: "<img src='" . SHM_URLPATH . "assets/img/check_unchecked.png'> <span class='smc-label-782px'>" . $obj[$column_name]['name'] . "</span>";
								break;
							case "media":
								echo "<img style='height:140px; width:auto;' src='".wp_get_attachment_image_url($meta, array(140, 140))."'/>";
								break;
							case "array":
								echo implode(", ", $meta);
								break;
							case "post":
								if($meta)
								{
									$p = get_post($meta);
									$post_title = is_object($p) ? $p->post_title : '';
									$color = $obj[$column_name]['color'];
									echo "
										<strong>$post_title</strong>
										<br><div class='IDs'><span style='background-color:$color;'>ID</span>$meta</div>";
								}
								break;							
							case "taxonomy":
								if($term)
								{
									$term = get_term_by("term_id", $meta, $elem);
									echo $term ? "<h6>".$term->name ."</h6> <div class='IDs'><span>ID</span>".$meta. "</div>
										<div style='background-color:#$color; width:15px;height:15px;'></div>" : $meta;
								}
								break;
							case "id":
							default:
								$elem			= $SMC_Object_type->get_object($meta, $obj[$column_name]["object"] );
								
								switch( $obj[$column_name]["object"])
								{
									case "user":
										if($meta)
										{
											$user = get_user_by("id", $meta);
											$display_name = $user ? $user->display_name : "==";
											echo  $display_name."<br><div class='IDs'><span>ID</span>".$meta. "</div>
												<div style='background-color:#$color; width:15px;height:15px;'></div>";
										}
										break;
									case "post":
										if($meta)
										{
											$p = get_post($meta);
											$post_title = $p->post_title;
											$color = get_post_meta($meta, "color", true);
											
											echo "
											<strong>$post_title</strong>
											<br>
											<div class='IDs'><span>ID</span>".$meta. "</div>
											<div style='background-color:#$color; width:15px;height:15px;'></div>";
										}
										break;
									case "taxonomy": 
										if($meta)
										{
											$p = get_term_by("term_id", $meta, $column_name);
											$post_title = $p->name; 
											$color = get_term_meta($meta, "color", true);
											
											echo "
											<strong>$post_title</strong>
											<br>
											<div style='background-color: $color' class='IDs'><span>ID</span>".$meta. "</div> ";
										}
										break;
									default:
										echo apply_filters(
											"smc_post_fill_views_column",
											"-- booboo --",
											$column_name,
											$post_id, 
											$obj, 
											$meta
										);
										
								}	 
						}
					}
					break;
			}
		}
		
		// add the ability to sort the column
		static function add_views_sortable_column($sortable_columns)
		{
			
			return $sortable_columns;
		}
		
		// change the query when sorting a column
		static function add_column_views_request( $object )
		{
			
		}	
		
		// bulk actions
		static function register_my_bulk_actions( $bulk_actions )
		{
			$bulk_actions['double'] = __("Double", SHMAPPER);
			return $bulk_actions;
		}
		
		static  function my_bulk_action_handler( $redirect_to, $doaction, $post_ids )
		{
			// do nothing if it is not our action
			if( $doaction !== 'double' )
				return $redirect_to;
			foreach( $post_ids as $post_id )
			{			
				$ppost = static::get_instance($post_id);
				$ppost->doubled();
			}
			$redirect_to = add_query_arg( 'my_bulk_action_done', count( $post_ids ), $redirect_to );
			return $redirect_to;
		}
		static  function my_bulk_action_admin_notice()
		{
			if( empty( $_GET['my_bulk_action_done'] ) )		return;
			$data = $_GET['my_bulk_action_done'];
			$msg = sprintf( 'Doubled: %s.', $data );
			echo '<div id="message" class="updated"><p>'. $msg .'</p></div>';
		}
		static function my_bulk_edit_custom_box( $column_name, $post_type ) 
		{ 
			if($post_type != forward_static_call_array( array( get_called_class(),"get_type"), array()))	return;
			/**/
			static $printNonce = TRUE;
			if ( $printNonce ) {
				$printNonce = FALSE;
				wp_nonce_field( plugin_basename( __FILE__ ), 'book_edit_nonce' );
			}
			
			$p					= static::get_instance($post_id);
			require_once(SHM_REAL_PATH."class/SMC_Object_type.php");				
			$SMC_Object_type	= SMC_Object_type::get_instance();
			$obj				= $SMC_Object_type->object [forward_static_call_array( array( get_called_class(),"get_type"), array()) ];
			
			?>
			<fieldset class="inline-edit-col-left">
			  <div class="inline-edit-col shm-column-<?php echo $column_name; ?>">
				<?php 
				 switch ( $column_name ) {
					 case 'owner_map':
						 echo "<span class='title'>".__("Usage in Maps: ", SHMAPPER)."</span>";
						 break;
					default:
						if(array_key_exists($column_name, $obj))
						{
							echo "<div class='shm-title-5'>".$obj[$column_name]['name']."</DIV>" ;
							switch($obj[$column_name]['type'])
							{
								case "number":
								case "string":
									
									break;
								case "date":
									
									break;
								case "boolean":
									echo "
									<input type='radio' name='$column_name' value='-1' class='smc_post_changer' id='__$column_name'/> 
									<label for='__$column_name' class='shm-inline'>" . __("&mdash; No Change &mdash;") . "</label>
									
									<input type='radio' name='$column_name' value='0' class='smc_post_changer' id='no$column_name'/> 
									<label for='no$column_name' class='shm-inline'>" . __("No") . "</label>
									
									<input type='radio' name='$column_name' value='1' class='smc_post_changer' id='yes$column_name'/> 
									<label for='yes$column_name' class='shm-inline'>" . __("Yes") . "</label>";
									break;
								case "media":
								
									break;
								case "array":
								
									break;
								case "post":
									
									break;
								case "taxonomy":
									
									break;
								case "id":
								default:
									break;
							}
						}
						break;
				 }
				?>
			  </div>
			</fieldset>
			<?php
		} 
		static function save_bulk_edit_book()
		{
			do_action("shmapper_bulk_before");
			$post_ids	= ( ! empty( $_POST[ 'post_ids' ] ) ) ? $_POST[ 'post_ids' ] : array();
			require_once(SHM_REAL_PATH."class/SMC_Object_type.php");				
			$SMC_Object_type	= SMC_Object_type::get_instance();
			$obj				= $SMC_Object_type->object [forward_static_call_array( array( get_called_class(),"get_type"), array()) ];		
			
			if ( ! empty( $post_ids ) && is_array( $post_ids ) )			
				foreach( $post_ids as $post_id ) 
				{
					$_obj = static::get_instance((int)$post_id);				
					foreach($obj as $key => $value)
					{
						if($key == 't' ||$key == 'class' ) continue;
						switch($obj[$key]['type'])
						{
							case "number":
							case "string":
								
								break;
							case "date":
								
								break;
							case "boolean":
								if(!isset($_POST['smc_post_changer'][ $key ])  || (int)$_POST['smc_post_changer'][ $key ] < 0 ) break;
								$val = (int)$_POST['smc_post_changer'][ $key ];
								$_obj->update_meta( $key, $val );
								break;
						}
					}
				}
			echo json_encode( $_POST );
			wp_die();
		}
		
		
		static function get_extra_fields_title()
		{
			return __('Parameters', SHMAPPER);
		}
		
		static function my_extra_fields() 
		{
			add_meta_box( 'extra_fields', __('Parameters', SHMAPPER), array(get_called_class(), 'extra_fields_box_func'), static::get_type(), 'normal', 'high'  );
			
		}
		static function extra_fields_box_func( $post )
		{	
			$lt					= static::get_instance( $post );
			//echo static::get_type();
			echo static::view_admin_edit($lt);			
			wp_nonce_field( basename( __FILE__ ), static::get_type().'_metabox_nonce' );
		}
		static function true_save_box_data ( $post_id ) 
		{
			if ( !isset( $_POST[static::get_type().'_metabox_nonce' ] )
			|| !wp_verify_nonce( $_POST[static::get_type().'_metabox_nonce' ], basename( __FILE__ ) ) )
				return $post_id;
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
				return $post_id;
			if ( !current_user_can( 'edit_post', $post_id ) )
				return $post_id;		
			$lt					= static::get_instance( $post_id );
			$metas				= static::save_admin_edit($lt);
			$lt->update_metas( $metas );
			return $post_id;
		}
		static function view_admin_edit($obj)
		{			
			require_once(SHM_REAL_PATH."class/SMC_Object_type.php");
			$SMC_Object_type	= SMC_Object_Type::get_instance();
			$bb				= $SMC_Object_type->object [forward_static_call_array( array( get_called_class(),"get_type"), array()) ];	
			foreach($bb as $key=>$value)
			{
				if($key == 't' || $key == 'class' ) continue;
				$meta = get_post_meta( $obj->id, $key, true);
				$$key = $meta;
				switch( $value['type'] )
				{
					case "number":
						$h = "<input type='number' name='$key' id='$key' value='$meta' class='sh-form'/>";
						break;
					case "boolean":
						$h = "<input type='checkbox' class='checkbox' name='$key' id='$key' value='1' " . checked(1, $meta, 0) . "/><label for='$key'></label>";
						break;
					default: 
						$h = apply_filters(
							"smc-post-admin-edit", 
							"<input type='' name='$key' id='$key' value='$meta' class='sh-form'/>", 
							$meta, 
							$obj, 
							$key, 
							$value
						);
				}
				$html .="<div class='shm-row'>
					<div class='shm-3 shm-md-12 sh-right sh-align-middle'>".$value['name'] . "</div>
					<div class='shm-9 shm-md-12 '>
						$h
					</div>
				</div>
				<div class='spacer-5'></div>";
			}
			echo $html;
		}
		static function save_admin_edit($obj)
		{
			return array();
		}
	}

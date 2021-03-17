<?php
/**
 * ShMapper
 *
 * @package teplitsa
 */

	class SMC_Object_Type
	{
		public $object;
		protected static $_instance;
		function __construct()
		{
			add_filter("smc_add_option", array($this, "init_options"), 10);
			$this->object = apply_filters("smc_add_post_types", $this->object);
			//$this->init();			
		}
		static function get_instance()
		{
			if(null === static::$_instance)
				static::$_instance = new static;
			return static::$_instance;
		}
		function get_class_by_name( $name )
		{
			return $this->object[$name]['class']['type'];
		}
		
		function init_options($array)
		{
			
		}
		function get($string)
		{
			return $this->object[$string];
		}
		function get_meta($string)
		{
			$r		= $this->get($string);
			unset($r['t']);
			return $r;
		}
		function get_type($string)
		{
			$r		= $this->get($string);
			return $r['t']['type'];
		}
		
		function is_meta_exists($string, $meta)
		{
			$obj	= $this->get($string);
			return array_key_exists($meta, $obj);
		}
		
		function get_object($id, $t)
		{
			global $new_dir;
			$d										=  $this->get($t);
			$obj									= array();
			switch($d['t']['type'])
			{
				case "post":
					$obj					= $this->get_post_elements($id, $t, $d);
					if(is_wp_error($obj))	
						insertLog("SMC_Object.get_object", array($id, $t, $d));
					break;
				case "taxonomy":				
					$obj					= $this->get_taxonomy_element($id, $t, $d);
					if(is_wp_error($obj))	
						insertLog("SMC_Object.get_object", array($id, $t, $d));
					break;
				case "db_row":
					
					break;
				default:
					return $t;
			}
			return apply_filters("smc_get_object", $obj, $obj);
		}
		
		function get_post_elements($id, $type, $data)
		{
			$el								= get_post($id);
			$obj							= array(
														'post_type'	=> $type,
														'obj_type'	=> $data['t']['type'],
														'title'		=> htmlentities(stripslashes ($el->post_title)),
														'name'		=> $el->post_name,
														//'id'		=> $id,
														'text'		=> htmlentities(stripslashes ($el->post_content)),																
													);
			$keys							= array_keys($data);
			$values							= array_values($data);
			for($i = 0; $i < count($keys); $i++)
			{						
				$meta						= get_post_meta($id, $keys[$i], true);
				if($keys[$i] == 't' ||$keys[$i] == 'class') continue;
				if($values[$i]['type'] == 'id')
				{
					$pos					= $this->get($values[$i]['object']);
					switch($pos['t']['type'])
					{
						case 'post':
							$obj[$keys[$i]]	= $this->get_post_property($meta,  $values[$i]['object']);
							break;
						case 'taxonomy':
							$obj[$keys[$i]]	= $this->get_taxonomy_property($meta, $values[$i]['object'], $i);
							break;
						case 'user':
							$obj[$keys[$i]]	= $this->get_user_property($meta, $values, $i);
							break;
						case 'array':
							$arr			= $this->get_array_property( $meta, $values ); 
							if(!is_wp_error($arr))	$obj[$keys[$i]] = $arr;
						default:
							ob_start();
							print_r($meta);
							$obj[$keys[$i]]	= ob_get_clean();
							break;
					}
				}
				else if($values[$i]['type'] == 'array')
				{
					$arr			 		= $this->get_array_property($meta, $values[$i]);
					if(!is_wp_error($arr))	$obj[$keys[$i]] = $arr;
				}
				else if($values[$i]['type'] == 'media')
				{
					$meta					= htmlentities(stripslashes (get_post_meta($id, $keys[$i], true)));
					if($values[$i]['download'])
					{	
						$imageUrl			= wp_get_attachment_url(  $meta );
						$stt				= (strrpos($imageUrl, '/'))+1;
						$fnn				= (strrpos($imageUrl, '.')) - $stt;
						$filename 			= substr($imageUrl,  $stt, $fnn);
						$thumbnail 			= substr($imageUrl,  $stt);
						$wp_check_filetype 	= wp_check_filetype($imageUrl);
						file_copy($imageUrl, $new_dir ."/".  $filename . "." . $wp_check_filetype['ext']);
						$obj[$keys[$i]]		= $imageUrl;
					}
				}
				
				else if($values[$i]['type'] == 'bool' || $values[$i]['type'] == 'number')
				{
					$obj[$keys[$i]]			= (int)$meta;
				}
				else if($values[$i]['type'] == 'string')
				{
					
					$meta					= get_post_meta($id, $keys[$i], true);
					$obj[$keys[$i]]			= ($meta);
					if($values[$i]['download'])
					{
					
					}
				}
			}
			return $obj;
		}
		
		/*	=================
		//	
		//	
		//	
			=================*/		
		function get_taxonomy_element($id, $type, $data)
		{	
			if($data['t']['type'] != "taxonomy")	return new WP_Error('no taxonomy');
			$el								= get_term_by("id", $id, $type);
			$parent							= get_term_by("id", $el->parent, $type);
			$parent		=		$parent==0	? "" : $parent->slug;				
			$obj							= array(
														'post_type'	=> $type,
														'obj_type'	=> $data['t']['type'],
														'title'		=> $el->name,
														'name'		=> $el->slug,
														'id'		=> $id,
														'text'		=> '',	
														'parent'	=> $parent
													);
			$class_name						= $data['class'];
			$option							= $this->object_property_args($class_name,"get_term_meta", $id); 
			$keys							= array_keys($data);
			$values							= array_values($data);
			
			for($i = 0; $i < count($keys); $i++)
			{
				if($keys[$i] == "class")	continue;
				if($values[$i]['type'] == 'db_row')
				{
					continue;
				}
				$meta						= $option[$keys[$i]];
				if($values[$i]['type'] == 'id')
				{
					$pos					= $this->get($values[$i]['object']);
					switch($pos['t']['type'])
					{
						case 'post':
							$obj[$keys[$i]]	= $this->get_post_property($meta);
							break;
						case 'taxonomy':
							$obj[$keys[$i]]	= $this->get_taxonomy_property($meta, $values[$i]['object']);					
							break;
						case 'user':
							$obj[$keys[$i]]	= $this->get_user_property($meta);
							break;
						case 'array':
						default:
							ob_start();
							print_r($meta);
							$obj[$keys[$i]]	= ob_get_clean();
							break;
					}
				}
				else if($values[$i]['type'] == 'array')
				{
					ob_start();
					print_r($meta);
					$obj[$keys[$i]]			= ob_get_clean();
				}
				else if($values[$i]['type'] == 'bool' || $values[$i]['type'] == 'number')
				{
					$obj[$keys[$i]]			= (int)$meta;
				}						
				else
				{
					$meta					= $option[$keys[$i]];
					$obj[$keys[$i]]			= $meta;
				}
			}
			return $obj;
		}
		function get_property($val, $value)
		{
			if($value == "number" || $value == "bool")	
				return (int)$val;
			if($value == "string")
				return $val;
			if(is_array($value))
			{
				if($value['type'] == 'id')
				{
					foreach($val as $v)
					{
						switch($value['t'])
						{
							case "post":
								$obj		= $this->get_post_property($v);
								return $obj;
							case "taxonomy":
								$obj		= @$this->get_taxonomy_property($v, $value['object']);
								insertLog( "get_property ", array( $obj, $v ) );
								return $obj;
							default:
								insertLog( "get_property error", array( $obj, $value) );
						}
					}
				}
				$obj	= $this->get_array_property($val, $value);
				if(!is_wp_error($obj))
					return $obj;
			}
		}
		function get_post_property($meta)
		{
			$pp				= get_post($meta);
			if(!$pp)		return "";
			$pos_slug		= $pp->post_name;
			return $pos_slug;
		}
		function get_user_property($meta)
		{
			return "==$meta";
		}
		function get_taxonomy_property($meta, $object)
		{
			$pp				= get_term_by("id", $meta, $object );
			$pos_slug		= $pp->slug;
			return $pos_slug;
		}
		function get_db_row_property($meta)
		{
			return $meta;
		}
		function get_array_property( $meta, $values )
		{
			if(!is_array($meta) || 	count($meta)==0)	return new WP_Error("parameter is not array");
			if(!is_array($values))	return new WP_Error("SMC_Object element is not array");
			$obj			= array( );
			foreach( $meta as $el => $val)
			{
				foreach($values as  $key => $value )
				{
					if($key	== "type")		continue;
					if($key == "object")
					{
						$obj[] = array($this->get_array_property($val, $value));
					}
					else
					{
						$xx			= $this->get_property($val, $value);
						$obj[$key]	= is_wp_error($xx) ? "" : $xx;
					}
					
				}
			}
			return $obj;
		}
		/*
		что делать, когда тип = id
		*/
		function convert_id($key, $val, $d, $id)
		{
			global $wpdb, $httml, $migration_url, $components;
			require_once(IMPORMAN_REAL_PATH."tpl/post_trumbnail.php");	
			if(	
				$key == 'title' || 
				$key == 'post_content' || 
				$key == 'name' || 
				$key == 'obj_type'  || 
				$key == 'id'  || 
				$key == 't'  || 
				$key == 'text'  || 
				$key == 'parent'  || 
				$key == 'post_type'  
				)
				return new WP_Error( $key );
			if($d[$key]['type'] == 'id')
			{
				$pos	= $this->get($d[$key]['object']);
				switch($pos['t']['type'])
				{
					case 'post':
						$p			= $wpdb->get_row("SELECT ID FROM ".$wpdb->prefix."posts WHERE post_name='" .$val. "' LIMIT 1", ARRAY_A );
						$val		= $p["ID"];
						break;
					case 'taxonomy':
						$p			= $wpdb->get_row("SELECT term_id FROM ".$wpdb->prefix."terms WHERE slug='" .$val. "' LIMIT 1", ARRAY_A );
						$val		= $p["term_id"];
						break;
					case 'user':						
						break;
					default:						
						break;
				}
			}
			if(is_array($val))
			{
				$components[] = array( "key" => $key, "value" => $val, "id" => $id );
				$val		= "";
			}
			
			if($key == 'png_url')
				$val	= IMPORMAN_URLPATH . "picto/" . $val;
			if($key == "_thumbnail_id")
			{
				$wp_upload_dir 		= wp_upload_dir();
				$filename			= (ERMAK_MIGRATION_PATH . $val);
				$httml .=  "<DIV>". $migration_url . $val ."</div>";
				if(is_wp_error($filename))
				{
					$httml .=  "<div style='color:red; font-size:20px;'>". echo_me($filename->get_error_messages())."</div>";
				}
				else
				{
					$httml .=  "<DIV>filename = $filename</div>";
					$wp_filetype 	= wp_check_filetype(basename($filename), null );
					cyfu_publish_post($id, $filename);
					return new WP_Error( $key );
				}
			}
			return $val=="" ? new WP_Error( $key ) : $val;
		}
		
		function convert_array($val)
		{
			$arr			= array();
			foreach($val as $k => $v)
			{
				if(!$this->get($k))
				{
					if(is_array( $v ))
					{
						{
							$arr[$k]	= $this->convert_array($v);
						}
					}
					else
					{
						$arr[$k]	= $v;
					}
				}
				else
				{
					$tp				= $this->get($k);
					switch($tp['t']['type'])
					{
						case "post":
							$arr[$k]		= get_postId_by_slug($v, $k);
							break;
						case "taxonomy":
							$arr[$k]		= get_termId_by_slug($v, $k);
					}
				}
			}		
			return $arr;
		}
		
		function insert_post_meta($metas, $id, $post_type)
		{
			global $wpdb;
			$d								=  $this->get($post_type);
			foreach($metas as $key=>$val)
			{				
				$val = $this->convert_id($key, $val, $d, $id);				
				if(!is_wp_error($val)) 
					update_post_meta($id, $key, $val);
			}
		}
		function object_property_args($class_name, $method, $args)
		{
			return @call_user_func(array($class_name, $method), $args);
		}
	}
	
	function file_copy($file, $distination)
	{
		if($file != "")
		{
			try {
				$adress	= str_replace(esc_url( home_url( '/' ) ), ABSPATH, $file);
				$cl		= @copy($adress, $distination) ;
			}
			catch (Exception $e) 
			{
				insertLog("file_copy", $e->getMessage());
			}
			if ($cl)	
			{
				return true;				
			}
		}
		else
			return false;
	}
	function get_postId_by_slug($slug, $post_type)
	{
		global $wpdb;
		$query	= "SELECT ID AS id FROM ".$wpdb->prefix."posts WHERE post_type='$post_type' AND post_name='$slug' LIMIT 1";
		return $wpdb->get_var($query);
	}
	function get_termId_by_slug($slug, $tax)
	{
		$term	= get_term_by("slug", $slug, $tax);
		return $term->term_id;
	}

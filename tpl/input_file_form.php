<?php
/**
 * ShMapper
 *
 * @package teplitsa
 */

function get_input_file_form()
{
	return '<div class="container"> <br />
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="input-group image-preview">
							<input placeholder="" type="text" class="form-control image-preview-filename" disabled="disabled">
							<!-- do not give a name === do is not send on POST/GET --> 
							<span class="input-group-btn"> 
							<!-- image-preview-clear button -->
							<button type="button" class="btn btn-default image-preview-clear" style="display:none;"> <span class="glyphicon glyphicon-remove"></span> Clear </button>
							<!-- image-preview-input -->
							<div class="btn btn-default image-preview-input"> <span class="glyphicon glyphicon-folder-open"></span> <span class="image-preview-input-title">' . esc_html__( 'Browse', 'shmapper-by-teplitsa' ) . '</span>
								<input type="file" accept="image/png, image/jpeg, image/gif" name="input-file-preview"/>
								<!-- rename it --> 
							</div>
							<button type="button" class="btn btn-labeled btn-primary"> <span class="btn-label"><i class="glyphicon glyphicon-upload"></i> </span>' . esc_html__( 'Upload', 'shmapper-by-teplitsa' ) . '</button>
							</span> </div>
						<!-- /input-group image-preview [TO HERE]--> 
						
						<br />
						
						<!-- Drop Zone -->
						<div class="upload-drop-zone" id="drop-zone">' . esc_html__( 'Or drag and drop files here', 'shmapper-by-teplitsa' ) . '</div>
						<br />
						<!-- Progress Bar -->
						<div class="progress">
							<div class="progress-bar" role="progressbar" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100" style="width: 10%;"> <span class="sr-only">10% ' . esc_html__( 'Complete', 'shmapper-by-teplitsa' ) . '</span> </div>
						</div>
						<br />

					</div>
				</div>
			</div>
		</div>
	</div>';
}

function get_input_file_form2($image_input_name = "image-file", $media_id='-1', $prefix='user_ava', $id='')
{
	return "
	<div class='button my_image_upload' style='padding:10px; height:80px; margin:3px 3px 3px 0; float:left;' image_id='".$id."'  prefix='$prefix'>
		<div class='pictogramm ' id='$prefix$id' style='position:relative; display:inline-block; height:68px; overflow:hidden;'>".
			_get_media($media_id, 68).
		"</div>
	</div>
	<div class='button my_image_delete' prefix='$prefix' default='" . _get_default() . "'>
		<span class='dashicons dashicons-no'></span>
	</div>
	<input type='hidden' id='".$prefix."_media_id$id' name='$prefix$id' value='$media_id'/>";
}

function get_input_file_form3($image_input_name = "image-file")
{
	return '
		<!-- bootstrap-imageupload. -->
		<div class="imageupload panel panel-default">
			<div class="panel-heading clearfix">
				<h3 class="panel-title pull-left">' . __( 'Upload Image', 'shmapper-by-teplitsa' ) . '</h3>
				<div class="btn-group pull-right">
					<button type="button" class="btn btn-default active">' . __( 'File', 'shmapper-by-teplitsa' ) . '</button>
					<button type="button" class="btn btn-default">' . esc_html__( 'URL', 'shmapper-by-teplitsa' ) . '</button>
				</div>
			</div>
			<div class="file-tab panel-body">
				<label class="btn btn-default btn-file btn-lg">
					<span>' . __( 'Browse', 'shmapper-by-teplitsa' ) . '</span>
					<!-- The file is stored here. -->
					<input type="file" name="' . $image_input_name . '">
				</label>
				<button type="button" class="btn btn-default">' . __( 'Remove', 'shmapper-by-teplitsa' ) . '</button>
				<button type="button" class="btn btn-default">' . __( 'Insert', 'shmapper-by-teplitsa' ) . '</button>
			</div>
			<div class="url-tab panel-body">
				<div class="input-group">
					<input type="text" class="form-control hasclear" placeholder="' . esc_attr( 'Image URL', 'shmapper-by-teplitsa' ) . '">
					<div class="input-group-btn">
						<button type="button" class="btn btn-default">' . __( 'Submit', 'shmapper-by-teplitsa' ) . '</button>
					</div>
				</div>
				<button type="button" class="btn btn-default ">' . __( 'Remove', 'shmapper-by-teplitsa' ).'</button>
				<!-- The URL is stored here. -->
				<input type="hidden" name="image-url">
			</div>
			<div class="panel-heading clearfix">
				<div class="btn-group pull-right">
					<button type="button" class="btn btn-default">' . __( 'Submit', 'shmapper-by-teplitsa' ) . '</button>
				</div>
			</div>
		</div>';
}

function _get_media($media_id, $size=300)
{
	$src	= $size == "full" ?  wp_get_attachment_image_src($media_id, $size): wp_get_attachment_image_src($media_id, array($size, $size));
	if($src)
	{
		return "<img style='height:auto; width:".$size."px;' src='".$src[0]."'/>";
	}
	else
	{
		return "<img style='opacity:1; height:".$size."px; width:auto;' src='"._get_default()."'/>";
	}
}
function _get_default()
{
	return SHM_URLPATH."assets/img/empty.png";
}

function recurse_copy( $src, $dst ) { 
	$dir = opendir($src); 
	@mkdir($dst); 
	while(false !== ( $file = readdir($dir)) ) { 
		if (( $file != '.' ) && ( $file != '..' )) { 
			if ( is_dir($src . '/' . $file) ) { 
				recurse_copy($src . '/' . $file, $dst . '/' . $file); 
			} 
			else { 
				copy($src . '/' . $file, $dst . '/' . $file); 
			} 
		} 
	} 
	closedir($dir); 
}

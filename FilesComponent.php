<?php
class FilesComponent extends Component {
	var $dirs = array ();
	
	/*
	 * Function for uploading files to server
	 */
	function save($data, $status = 1) {
		$file = ClassRegistry::init ( 'File' );
		if (! empty ( $data ['File'] )) {
			foreach ( $data ['File'] as $key => $value ) {
				@mkdir ( WWW_ROOT . "files/$key" );
				@chmod ( WWW_ROOT . "files/$key", 0777 );
				foreach ( $value as $valueData ) {
					$save_file_name = $this->_checkfilename ( $valueData ['name'], $key );
					if (move_uploaded_file ( $valueData ['tmp_name'], WWW_ROOT . "files/$key/$save_file_name" )) {
						@chmod ( WWW_ROOT . "files/$key/$save_file_name", 0777 );
						$file->create ();
						$file->save ( array (
								'file_name' => $save_file_name,
								'status' => $status,
								'folder' => trim ( $key ),
								'mime_type' => $valueData ['type'] 
						) );
					}
				}
			}
			return true;
		}
	}
	
	function delete($id) {
		$file = ClassRegistry::init ( 'File' );
		$files = $file->findById ( $id );
		$folder = WWW_ROOT . "files/{$files['File']['folder']}";
		$file_name = $files ['File'] ['file_name'];
		$folders = array ();
		if ($handle = opendir ( $folder )) {
			while ( false !== ($entry = readdir ( $handle )) ) {
				if ($entry != "." && $entry != "..") {
					if (is_dir ( "$folder/$entry" )) {
						$folders [] = "$folder/$entry";
					}
				}
			}
			closedir ( $handle );
		}
		if (count ( $folders )) {
			foreach ( $folders as $value ) {
				@unlink ( "$value/$file_name" );
			}
			@unlink ( "$folder/$file_name" );
		}
		$file->id = $files ['File'] ['id'];
		$file->delete ();
		return true;
	}
	
	/*
	 * Function 
	 */
	function _checkfilename($name, $folder) {
		$name = preg_replace ( "/\s/", "-", $name );
		if (file_exists ( WWW_ROOT . "files/$folder/$name" )) {
			$ext = substr ( strrchr ( $name, '.' ), 1 );
			$c = preg_replace ( "/$ext/", "", $name );
			$file_name = substr ( $c, 0, - 1 );
			$separator = '-';
			$original_name = $file_name;
			$i = 0;
			do {
				// Append an incrementing numeric suffix until we find a unique alias.
				$unique_suffix = $separator . $i;
				$file_name = $original_name . $unique_suffix;
				$i ++;
			} while ( file_exists ( WWW_ROOT . "files/$folder/$file_name.$ext" ) );
			return $file_name . "." . $ext;
		} else {
			return $name;
		}
	}
        
        
        /*watermark*/
        
        
 function watermark_text($oldimage_name, $new_image_name)
 {
 $fontbase_path = dirname(dirname(dirname(__FILE__))) . DS . 'Lib' . DS . 'Fonts';    
 $font_path = $fontbase_path."/monofont.TTF"; // Font file
 $font_size = 30; // in pixcels
 $water_mark_text_2 = "furniture"; // Watermark Text
    
        //global $font_path, $font_size, $water_mark_text_2;
        list($owidth,$oheight) = getimagesize($oldimage_name);
        $width = $height = 800; 
        $image = imagecreatetruecolor($width, $height);
        $image_src = imagecreatefromjpeg($oldimage_name);
       imagecopyresampled($image, $image_src, 0, 0, 0, 0, $width, $height, $owidth, $oheight);
        $blue = imagecolorallocate($image, 255, 255, 255);
       imagettftext($image, $font_size, 90, 30, 280, $blue, $font_path, $water_mark_text_2);
       imagejpeg($image, $new_image_name, 100);
       imagedestroy($image);
       unlink($oldimage_name);
       return true;
 }
        
}

?>
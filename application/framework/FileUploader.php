<?php
class FileUploader {
	// Error codes
	const MIME_TYPE_NOT_ACCEPTED 		= "MIME_TYPE_NOT_ACCEPTED";
	const MAXIMUM_FILE_SIZE_EXCEEDED	= "MAXIMUM_FILE_SIZE_EXCEEDED"; 
	const MINIMUM_WIDTH_VIOLATED		= "MINIMUM_WIDTH_VIOLATED";
	const MINIMUM_HEIGHT_VIOLATED		= "MINIMUM_HEIGHT_VIOLATED";
	const MAXIMUM_WIDTH_VIOLATED		= "MAXIMUM_WIDTH_VIOLATED";
	const MAXIMUM_HEIGHT_VIOLATED		= "MAXIMUM_HEIGHT_VIOLATED";	
	const CANNOT_MOVE_UPLOADED_FILE		= "CANNOT_MOVE_UPLOADED_FILE";
	const CANNOT_CREATE_THUMBNAIL		= "CANNOT_CREATE_THUMBNAIL";

	public function __construct() {	
	}

	/*$options = array(
			"userID"		=> 1,
			"title"			=> "Name of the file",
			"directoryID" 	=> 5,
			"acceptedMime" 	=> array('image/gif', 'image/png', 'image/pjpeg', 'image/jpeg', 'image/jpg'),
			"maxFileSize" 	=> 2000,
			"minImageWidth"	=> 10,
			"minImageHeight"=> 10,			
			"maxImageWidth"	=> 1000,
			"maxImageHeight"=> 1000,			
			"resizeImage"	=> true,
			"thumbnailSizes"=> array(	"small" => array("width" => 100, "height" => 100, "crop" => true),
										"big" 	=> array("width" => 200, "height" => 200, "crop" => false)
									)
	);
	*/

	public static function uploadFile($_file, $_options) {
		$result = array(
					'id' => -1,
					'error' => array()					
					);
					
		// Flash bug, always sends application/octet-stream
		$fileType = mime_content_type($_file['tmp_name']);
		$isImage = (strpos($fileType, 'image')===false?false:true);
		$hasThumbnail = false;

		// Mime check
		if (isset($_options["acceptedMime"])) {
			//if (!in_array($_file['type'], $_options["acceptedMime"])) {
			if (!in_array($fileType, $_options["acceptedMime"])) {
				array_push($result['error'], self::MIME_TYPE_NOT_ACCEPTED);
			}
		}

		// File size check
		if (isset($_options["maxFileSize"])) {
			if ($_options["maxFileSize"] <= $_file['size']) {
				array_push($result['error'], self::MAXIMUM_FILE_SIZE_EXCEEDED);				
			}
		}

		if ($isImage) {
			$imageSize = getimagesize($_file['tmp_name']);

			// Minimum size check
			if (isset($_options["minImageWidth"]) || isset($_options["minImageHeight"])) {				
				if (isset($_options["minImageWidth"]) && ($imageSize[0] < $_options["minImageWidth"])) {
					array_push($result['error'], self::MINIMUM_WIDTH_VIOLATED);
				}
				if (isset($_options["minImageHeight"]) && ($imageSize[1] < $_options["minImageHeight"])) {
					array_push($result['error'], self::MINIMUM_HEIGHT_VIOLATED);
				}
			}

			// Maximum size check
			if (isset($_options["maxImageWidth"]) || isset($_options["maxImageHeight"])) {
				if (isset($_options["maxImageWidth"]) && ($imageSize[0] > $_options["maxImageWidth"])) {
					array_push($result['error'], self::MAXIMUM_WIDTH_VIOLATED);
				}
				if (isset($_options["maxImageHeight"]) && ($imageSize[1] > $_options["maxImageHeight"])) {
					array_push($result['error'], self::MAXIMUM_HEIGHT_VIOLATED);
				}
			}
		}

		if (empty($result['error'])) {
		//if ($result['error'] == false) {
			$fileName = date("ymdHis")."_".md5($_file['name']);

			// Move to the file to the upload folder
			if (move_uploaded_file($_file['tmp_name'], '../../'.UPLOAD_DIR.'/'.$fileName)) {

				// Create thumbnail if needed
				if ($isImage && $_options["resizeImage"]) {
					foreach ($_options["thumbnailSizes"] as $key => $value) {
						if (self::resizeImage($fileName, $fileType, $key, $_options["thumbnailSizes"][$key]["width"], $_options["thumbnailSizes"][$key]["height"], $_options["thumbnailSizes"][$key]["crop"])) {
							$hasThumbnail = true;							
						} else {
							$hasThumbnail = false;
							array_push($result['error'], self::CANNOT_CREATE_THUMBNAIL);
						}
					}
				}

				// Insert into DB
				try {
					$db = Registry::get('db');
					$data = array(
						'tree_id' => $_options["directoryID"],
						'user_id' => (isset($_options["userID"])?$_options["userID"]:null),
						'title' => (isset($_options["title"])?$_options["title"]:null),
						'datetime' => date("Y-m-d H:i:s"),
						'file_name' => $_file['name'],
						'storage_name' => $fileName,
						'mime_type' => $fileType,
						'has_thumbnail' => $hasThumbnail,
						'file_size' => $_file['size']
					);								
					$db->insert(DBPREFIX.'file', $data);
					$result['id'] = $db->lastInsertId();
				} catch (Exception $e) {
					echo "Upload file exception";
				}				
			} else {
				array_push($result['error'], self::CANNOT_MOVE_UPLOADED_FILE);
			}
		}

		return $result;
	}

	private static function resizeImage($fileName, $mimeType, $subdir, $thumbWidth, $thumbHeight, $thumbCrop) {
		$size = GetImageSize('../../'.UPLOAD_DIR.'/'.$fileName);
		$originalWidth = $size[0];
		$originalHeight = $size[1];

		if ($originalHeight > $thumbHeight || $originalWidth > $thumbWidth) {
			$xRatio = $thumbWidth / $originalWidth;
			$yRatio = $thumbHeight / $originalHeight;

			if ($originalHeight > $thumbHeight && $originalWidth > $thumbWidth) {
				if ($thumbCrop) { // Crop
					if ($xRatio > $yRatio) {
						$tnHeight = $originalHeight * $yRatio;
						$tnWidth = $thumbWidth;//$originalWidth * $xRatio;
						$originalHeight = $thumbHeight/$xRatio;
					} else {
						$tnHeight = $thumbHeight;//$originalHeight * $yRatio;
						$tnWidth = $originalWidth * $xRatio;
						$originalWidth = $thumbWidth/$yRatio;
					}
				} else { // Normal
					if ($xRatio < $yRatio) {
						$tnHeight = $originalHeight * $xRatio;
						$tnWidth = $thumbWidth;//$originalWidth * $xRatio;
					} else {
						$tnHeight = $thumbHeight;//$originalHeight * $yRatio;
						$tnWidth = $originalWidth * $yRatio;
					}
				}
			} elseif ($originalWidth >$thumbWidth) {
				$tnHeight = $originalHeight * $xRatio;
				$tnWidth = $originalWidth * $xRatio;
			} elseif ($originalHeight > $thumbHeight) {
				$tnHeight = $originalHeight * $yRatio;
				$tnWidth = $originalWidth * $yRatio;
			}

			//if (isset($tnHeight) && isset($tnWidth)) {
			switch ($mimeType) {
				case 'image/jpg':
				case 'image/jpeg':
				case 'image/pjpeg':
					$src = imagecreatefromjpeg('../../'.UPLOAD_DIR.'/'.$fileName);
					$dst = imagecreatetruecolor($tnWidth, $tnHeight);
					imagecopyresampled($dst, $src, 0, 0, 0, 0, $tnWidth, $tnHeight, $originalWidth, $originalHeight);
					imagejpeg($dst, '../../'.UPLOAD_DIR.'/'.$subdir.'/'.$fileName, JPEG_QUALITY);						
					break;
				case 'image/png':
					$src = imagecreatefrompng('../../'.UPLOAD_DIR.'/'.$fileName);
					$dst = imagecreatetruecolor($tnWidth, $tnHeight);
					imagecopyresampled($dst, $src, 0, 0, 0, 0, $tnWidth, $tnHeight, $originalWidth, $originalHeight);
					imagepng($dst, '../../'.UPLOAD_DIR.'/'.$subdir.'/'.$fileName);
					break;
				case 'image/gif':
					$src = imagecreatefromgif('../../'.UPLOAD_DIR.'/'.$fileName);
					$dst = imagecreatetruecolor($tnWidth, $tnHeight);
					imagecopyresampled($dst,$src, 0, 0, 0, 0, $tnWidth, $tnHeight, $originalWidth, $originalHeight);
					imagegif($dst, '../../'.UPLOAD_DIR.'/'.$subdir.'/'.$fileName);
					break;
			}

			chmod('../../'.UPLOAD_DIR.'/'.$subdir.'/'.$fileName, 0777);
			return true;
			//}			
		} else {
			copy('../../'.UPLOAD_DIR.'/'.$fileName, '../../'.UPLOAD_DIR.'/'.$subdir.'/'.$fileName);
			return true;
		}
		return false;
	}
}
?>
<?php
function watermark($image, $string, $position)
{
    if(!isset($position['fontfile'])) $position['fontfile'] = 'simsun.ttc';
    $fontfilename = APPPATH . 'fonts' . DIRECTORY_SEPARATOR . $position['fontfile'];
    if(!isset($position['color'])) $position['color'] = 0x000000;
    $r = $position['color'] & 0xff0000 >> 16;
    $g = $position['color'] & 0x00ff00 >> 8;
    $b = $position['color'] & 0x0000ff;
    $fontcolor=ImageColorAllocateAlpha($image, $r, $g, $b, 0);
    ImageTTFText($image, $position['fontsize'], 0, $position['left'], $position['top'], $fontcolor, $fontfilename, $string);
    //$shadecolor=ImageColorAllocateAlpha($image, 0, 0, 0, 95);
    //ImageTTFText($image, $position['fontsize'], 0, $position['left']+1, $position['top']+1, $shadecolor, $fontfilename, $string);

    return $image;
}

function resizeThumbnailImage($image, $width, $height, $start_width, $start_height, $thumb_width, $thumb_height){
	list($imagewidth, $imageheight, $imageType) = getimagesize($image);
	$imageType = image_type_to_mime_type($imageType);
	
	$newImage = imagecreatetruecolor($thumb_width,$thumb_height);
	switch($imageType) {
		case "image/gif":
			$source=imagecreatefromgif($image);
			break;
	    case "image/pjpeg":
		case "image/jpeg":
		case "image/jpg":
			$source=imagecreatefromjpeg($image);
			break;
	    case "image/png":
		case "image/x-png":
			$source=imagecreatefrompng($image);
			break;
  	}
	imagecopyresampled($newImage,$source,0,0,$start_width,$start_height,$thumb_width,$thumb_height,$width,$height);
	return $newImage;
}

<?php

/*
 * @CODOLICENSE
 */
namespace CODOF\User;

class Avatar {
	
	/**
	 *
	 * @var array
	 */
	private $BACKGROUND_COLORS = array (
			"#ff4040",
			"#7f2020",
			"#cc5c33",
			"#734939",
			"#bf9c8f",
			"#995200",
			"#4c2900",
			"#f2a200",
			"#ffd580",
			"#332b1a",
			"#4c3d00",
			"#ffee00",
			"#b0b386",
			"#64664d",
			"#6c8020",
			"#c3d96c",
			"#143300",
			"#19bf00",
			"#53a669",
			"#bfffd9",
			"#40ffbf",
			"#1a332e",
			"#00b3a7",
			"#165955",
			"#00b8e6",
			"#69818c",
			"#005ce6",
			"#6086bf",
			"#000e66",
			"#202440",
			"#393973",
			"#4700b3",
			"#2b0d33",
			"#aa86b3",
			"#ee00ff",
			"#bf60b9",
			"#4d3949",
			"#ff00aa",
			"#7f0044",
			"#f20061",
			"#330007",
			"#d96c7b" 
	);
	
	/**
	 * Converts HEX to RGB
	 * 
	 * @param string $hex        	
	 * @return string
	 */
	private function hex2rgb($hex) {
		$hex = str_replace ( "#", "", $hex );
		
		if (strlen ( $hex ) == 3) {
			$r = hexdec ( substr ( $hex, 0, 1 ) . substr ( $hex, 0, 1 ) );
			$g = hexdec ( substr ( $hex, 1, 1 ) . substr ( $hex, 1, 1 ) );
			$b = hexdec ( substr ( $hex, 2, 1 ) . substr ( $hex, 2, 1 ) );
		} else {
			$r = hexdec ( substr ( $hex, 0, 2 ) );
			$g = hexdec ( substr ( $hex, 2, 2 ) );
			$b = hexdec ( substr ( $hex, 4, 2 ) );
		}
		$rgb = array (
				$r,
				$g,
				$b 
		);
		// return implode(",", $rgb); // returns the rgb values separated by commas
		return $rgb; // returns an array with the rgb values
	}
	
	/**
	 * Saves avatar to DB and outputs that as PNG
	 * 
	 * @param int $id        	
	 */
	public function generate($id, $display = true) {
		$uid = ( int ) $id;
		
		$user = User::get ( $uid );
		
		$initial = strtoupper ( $user->username [0] );
		
		$bI = rand ( 0, 41 );
		$color = $this->BACKGROUND_COLORS [$bI];
		$rgb = $this->hex2rgb ( $color );
		
		// Create a 300x100 image
		$im = imagecreatetruecolor ( 128, 128 );
		$red = imagecolorallocate ( $im, $rgb [0], $rgb [1], $rgb [2] );
		$black = imagecolorallocate ( $im, 0xFF, 0xFF, 0xFF );
		
		// Make the background red
		imagefilledrectangle ( $im, 0, 0, 128, 128, $red );
		
		// Path to our ttf font file
		$font_file = ASSET_DIR . 'img/general/SourceCodePro-Light.ttf';
		
		// Draw the text 'PHP Manual' using font size 13
		imagefttext ( $im, 80, 0, 32, 100, $black, $font_file, $initial );
		
		$name = $initial . '_' . str_replace ( '#', '', $color ) . '.png';
				
		imagepng ( $im, AVATAR_PATH . $name );
		
		$resizer = new \Ext\ImageResize ();
		
		$iconW = 36;
		$iconH = 36;
		$iconPath = AVATAR_PATH . 'icons/' . $name;
		$success = $resizer->smart_resize_image ( AVATAR_PATH . $name, NULL, $iconW, $iconH, true, $iconPath, FALSE, FALSE, 75 );
		
		if (! $success) {
			
			@copy ( AVATAR_PATH . $name, $iconPath );
		}
		
		$user->set ( array (
				
				'avatar' => $name 
		) );
		
		if ($display) {
				
			// Output image to the browser
			header ( 'Content-Type: image/png' );
			imagepng ( $im );				
		}
		
		imagedestroy ( $im );
	}
}

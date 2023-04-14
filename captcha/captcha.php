<?php
function generate_string($input, $strength = 10) {
    $input_length = strlen($input);
    $random_string = '';
    for($i = 0; $i < $strength; $i++) {
        $random_character = $input[mt_rand(0, $input_length - 1)];
        $random_string .= $random_character;
    }
    return $random_string;
}
function getImageCaptcha()  {
  $image = imagecreatetruecolor(200, 50);

  imageantialias($image, true);

  $colors = [];

  $red = rand(125, 175);
  $green = rand(125, 175);
  $blue = rand(125, 175);

  for($i = 0; $i < 5; $i++) {
    $colors[] = imagecolorallocate($image, $red - 20*$i, $green - 20*$i, $blue - 20*$i);
  }

  imagefill($image, 0, 0, -1);

  /*for($i = 0; $i < 10; $i++) {
    imagesetthickness($image, rand(2, 10));
    $line_color = $colors[rand(1, 4)];
    imagerectangle($image, rand(-10, 190), rand(-10, 10), rand(-10, 190), rand(40, 60), $line_color);
  }*/

  

  

  $black = imagecolorallocate($image, 0, 0, 0);
  $white = imagecolorallocate($image, 255, 255, 255);
  $blue = imagecolorallocate($image, 49, 68, 123);
  $textcolors = [$black, $white];

  for ($i = 0; $i < 18; $i++) {
    // Coordenadas de inicio y fin de la línea
    $x1 = rand(0, imagesx($image));
    $y1 = rand(0, imagesy($image));
    $x2 = rand(0, imagesx($image));
    $y2 = rand(0, imagesy($image));
    
    // Dibuja la línea en la imagen
    imageline($image, $x1, $y1, $x2, $y2, $blue);
}

  $fonts = [__DIR__.'/fonts/Acme.ttf', __DIR__.'/fonts/Ubuntu.ttf', __DIR__.'/fonts/Merriweather.ttf', __DIR__.'/fonts/Roboto.ttf'];

  $string_length = 6;
  $captcha_string = generate_string('ABCDEFGHJKLMNPQRSTUVWXYZ1234567890', $string_length);

  for($i = 0; $i < $string_length; $i++) {
    $letter_space = 120/$string_length;
    $initial = 50;
    
    imagettftext($image, 24, rand(-15, 15), $initial + $i*$letter_space, rand(25, 45), $blue, $fonts[array_rand($fonts)], $captcha_string[$i]);
  }

 
  //$_SESSION['captcha_text'] = $captcha_string;

  //header('Content-type: image/png');
  //var_dump($image); 
  ob_start (); 

  imagepng($image); 
  $image_data = ob_get_contents (); 

  ob_end_clean (); 

  $image_data_base64 = base64_encode ($image_data);
  imagedestroy($image);
  return [0 => $image_data_base64,
          1 => $captcha_string];

  //return imagepng($image);
  //imagepng($image);
}

?>
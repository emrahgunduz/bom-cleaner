<?php

$file = file_get_contents( dirname( __FILE__ ) . '/bootstrap.min.css' );
$comp = gzdeflate( $file, 9 );
$base = base64_encode( $comp );
addchar($base, 80, "\n");

file_put_contents( dirname( __FILE__ ) . "/bootstrap.txt", $base );

function addchar(&$str, $padding_at, $padding_char){
 $arr = str_split($str, $padding_at);
 $str=implode($padding_char, $arr);
}
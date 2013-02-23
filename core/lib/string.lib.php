<?php

class Str {

	public static function beginsWith($needle, $haystack){
		return !!(substr(self::trim($haystack), 0, strlen(self::trim($needle))) == self::trim($needle));
	}

	public static function trim($string, $left = '', $right = ''){
		return rtrim(ltrim(trim($string), $left), $right);
	}

	public static function after($needle, $haystack){
		return trim(substr($haystack, strlen($needle)));
	}

	public static function munge($string){
		$chars = array('a' => 'ä', 'b' => 'Б', 'c' => 'ċ', 'd' => 'đ', 'e' => 'ë', 'f' => 'ƒ', 'g' => 'ġ', 'h' => 'ħ', 'i' => 'í', 'j' => 'ĵ', 'k' => 'ķ', 'l' => 'ĺ', 'm' => 'ṁ', 'n' => 'ñ', 'o' => 'ö', 'p' => 'ρ', 'q' => 'ʠ', 'r' => 'ŗ', 's' => 'š', 't' => 'ţ', 'u' => 'ü', 'v' => '', 'w' => 'ω', 'x' => 'χ', 'y' => 'ÿ', 'z' => 'ź', 'A' => 'Å', 'B' => 'Β', 'C' => 'Ç', 'D' => 'Ď', 'E' => 'Ē', 'F' => 'Ḟ', 'G' => 'Ġ', 'H' => 'Ħ', 'I' => 'Í', 'J' => 'Ĵ', 'K' => 'Ķ', 'L' => 'Ĺ', 'M' => 'Μ', 'N' => 'Ν', 'O' => 'Ö', 'P' => 'Р', 'Q' => 'Ｑ', 'R' => 'Ŗ', 'S' => 'Š', 'T' => 'Ţ', 'U' => 'Ů', 'V' => 'Ṿ', 'W' => 'Ŵ', 'X' => 'Χ', 'Y' => 'Ỳ', 'Z' => 'Ż');
		return str_replace(array_keys($chars), $chars, $string);
	}
}

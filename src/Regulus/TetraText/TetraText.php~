<?php namespace Regulus\TetraText;

/*----------------------------------------------------------------------------------------------------------
	TetraText
		A small text/string formatting composer package that formats phone numbers, numeric values,
		money values and more. There are also some limited date functions available.

		created by Cody Jassman
		last updated on September 27, 2013
----------------------------------------------------------------------------------------------------------*/

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

use \HTMLPurifier;

class TetraText {

	/**
	 * Remove all non-numeric characters from a string.
	 *
	 * @param  float   $value
	 * @param  boolean $allowDecimal
	 * @param  boolean $allowNegative
	 * @return string
	 */
	public static function numeric($value, $allowDecimal = true, $allowNegative = false)
	{
		$formatted = "";
		for ($n=0; $n < strlen($value); $n++) {
			if ($allowDecimal == false) {
				if (is_numeric(substr($value, $n, 1)) || ($allowNegative && $n == 0 && substr($value, 0, 1) == '-')) {
					$formatted .= substr($value, $n, 1);
				}
			} else {
				if (is_numeric(substr($value, $n, 1)) || substr($value, $n, 1) == '.' || ($allowNegative && $n == 0 && substr($value, 0, 1) == '-')) {
					$formatted .= substr($value, $n, 1);
				}
			}
		}
		return $formatted;
	}

	/**
	 * Format a money value. This is superior to PHP's number_format() because it will but
	 * the dollar symbol to the right of the minus for a negative value ("-$33.00").
	 *
	 * @param  float   $value
	 * @param  string  $prefix
	 * @param  boolean $allowNegative
	 * @param  string  $thousandsSeparator
	 * @return string
	 */
	public static function money($value, $prefix = '$', $allowNegative = true, $thousandsSeparator = ',')
	{
		$value = static::numeric($value, true, true);
		$formatted = "";
		if ($value != "") {
			if (substr($value, 0, 1) == '-') {
				if ($allowNegative) {
					if ($allowNegative === "brackets") {
						$formatted = '('.$prefix.number_format(abs($value), 2, '.', $thousandsSeparator).')';
					} else {
						$formatted = '-'.$prefix.number_format(abs($value), 2, '.', $thousandsSeparator);
					}
				} else {
					$formatted = $prefix.'0.00';
				}
			} else {
				$formatted = $prefix.number_format($value, 2, '.', $thousandsSeparator);
			}
		} else { $formatted = $prefix.'0.00'; }
		return $formatted;
	}

	/**
	 * Calculate the percentage of a value to a total. Avoids division by zero error.
	 *
	 * @param  float   $value
	 * @param  float   $total
	 * @param  integer $decimals
	 * @param  boolean $returnNumeric
	 * @return mixed
	 */
	public static function percent($value = 0, $total = 0, $decimals = 1, $returnNumeric = false)
	{
		$value = static::numeric($value, true);
		$total = static::numeric($total, true);

		$percent = 0;
		if ($total > 0) {
			$percent = round($value / $total * 100, $decimals);
		}

		if (!$returnNumeric) $percent .= '%';
		return $percent;
	}

	/**
	 * Format a name (adjust for CAPS LOCK and uncapitalized names).
	 *
	 * @param  string  $name
	 * @return string
	 */
	public static function name($name = '')
	{
		$name = trim($name);
		if ($name == strtoupper($name)) $name = strtolower($name);
		$name = ucfirst($name);
		return $name;
	}

	/**
	 * Format a title.
	 *
	 * @param  string  $title
	 * @return string
	 */
	public static function title($title = '')
	{
		$title = ucwords(static::name($title));
		$lowercaseWords = array(
			'a',
			'an',
			'the',
			'of',
			'at',
			'in',
		);

		foreach ($lowercaseWords as $word) {
			$title = str_replace(' '.ucfirst($word).' ', ' '.$word.' ', $title); //make word lowercase
			$title = str_replace(': '.$word.' ', ': '.ucfirst($word).' ', $title); //change word back if it is preceded by colon
		}
		return $title;
	}

	/**
	 * Format a Canadian/American phone number.
	 *
	 * @param  string  $phoneNumber
	 * @param  integer $digits
	 * @param  string  $separator
	 * @param  boolean $areaCodeBrackets
	 * @return string
	 */
	public static function phone($phoneNumber, $digits = 10, $separator = '-', $areaCodeBrackets = true)
	{
		if ($phoneNumber != "") {
			if ($areaCodeBrackets) {
				$bracketL = "("; $bracketR = ") ";
				if ($digits == 11) { $firstDigit = "1 "; } else { $firstDigit = ""; }
			} else {
				$bracketL = ""; $bracketR = $separator;
				if ($digits == 11) { $firstDigit = "1".$separator; } else { $firstDigit = ""; }
			}
			$phoneNumber = static::numeric($phoneNumber, false);
			$length = strlen($phoneNumber);
			$offset = $length - $digits;
			if ($offset < 0) $offset = 0;
			if ($digits == 11) {
				$offset ++;
				if ($length == 10) $phoneNumber = '1'.$phoneNumber; //if length is too short, add 1 to make 11 digit phone number
			} else if ($digits == 10 && $length == 11) {
				$offset ++;
			}
			$phoneNumber = $firstDigit.$bracketL.substr($phoneNumber, $offset, 3).$bracketR.substr($phoneNumber, ($offset+3), 3).$separator.substr($phoneNumber, ($offset+6), 4);
		}
		return $phoneNumber;
	}

	/**
	 * Format a Canadian postal code.
	 *
	 * @param  string  $postalCode
	 * @param  boolean $separateWithSpace
	 * @return string
	 */
	public static function postalCode($postalCode = '', $separateWithSpace = true)
	{
		if ($separateWithSpace) {
			$separator = " ";
			$length    = 7;
		} else {
			$separator = "";
			$length    = 6;
		}
		$postalCode = strtoupper(str_replace(' ', '', $postalCode));
		$postalCode = substr($postalCode, 0, 3).$separator.substr($postalCode, 3, 3);
		$postalCode = substr($postalCode, 0, 7);
		if (strlen($postalCode) != $length) $postalCode = "";
		return $postalCode;
	}

	/**
	 * Turn a boolean value into a string. Some examples of types are "Yes/No", "Yes", "On/Off", and "Active/Inactive".
	 *
	 * @param  boolean $value
	 * @param  string  $type
	 * @return string
	 */
	public static function boolToStr($value, $type = 'Yes/No')
	{
		if (is_string($type)) $type = explode('/', $type);
		if (!isset($type[1])) $type[1] = "";
		if ($value)
			return $type[0];
		else
			return $type[1];
	}

	/**
	 * Turn a list of items into a string.
	 *
	 * @param  array   $list
	 * @param  string  $delimiter
	 * @return string
	 */
	public static function listToStr($list, $delimiter = ', ')
	{
		$str = "";
		foreach ($list as $key => $value) {
			if (!is_numeric($key) && (is_bool($value) || is_numeric($value))) {
				if ($value) {
					if ($str == "") {
						$str = $key;
					} else {
						$str .= $delimiter.$key;
					}
				}
			} else {
				if ($str == "") {
					$str = $value;
				} else {
					$str .= $delimiter.$value;
				}
			}
		}
		return $str;
	}

	/**
	 * Turn a collection of objects into a string list of items based on a given attribute or method.
	 *
	 * @param  object  $obj
	 * @param  string  $item
	 * @param  string  $delimiter
	 * @return string
	 */
	public static function objListToStr($obj, $attribute = null, $delimiter = ', ')
	{
		$str = "";
		foreach ($obj as $objListed) {
			if (is_null($attribute)) {
				$item = $objListed;
			} else {
				preg_match('/\(\)/', $attribute, $functionMatch);
				if (!empty($functionMatch)) { //attribute is a method of object; call it
					$function = str_replace('()', '', $attribute);
					$item = $objListed->$function();
				} else {
					$item = $objListed->{$attribute};
				}
			}
			if ($str == "") {
				$str = $item;
			} else {
				$str .= ', '.$item;
			}
		}
		return $str;
	}

	/**
	 * Pluralize a string containing ":item" and ":number" which will automatically be replaced.
	 *
	 * @param  string  $singular
	 * @param  integer $number
	 * @param  string  $plural
	 * @return string
	 */
	public static function pluralize($singular = 'result', $number = 1, $plural = false)
	{
		if ($number == 1) {
			return $singular;
		} else {
			if (!$plural) $plural = Str::plural($singular);

			return $plural;
		}
	}

	/**
	 * Pluralize a string containing ":item" and ":number" which will automatically be replaced.
	 *
	 * @param  string  $message
	 * @param  string  $singular
	 * @param  integer $number
	 * @param  string  $plural
	 * @return string
	 */
	public static function pluralizeMessage($message, $singular = 'result', $number = 1, $plural = false)
	{
		$item = static::pluralize($singular, $number, $plural);
		$message = str_replace(':number', $number, str_replace(':item', $item, $message));
	}

	/**
	 * Add "a" or "an" to prefix to word based on whether it begins with a vowel.
	 *
	 * @param  string  $item
	 * @return string
	 */
	public static function a($item)
	{
		$itemFormatted = strtolower($item);
		$prefix = 'a';

		//use "an" if item begins with a vowel
		if (in_array(substr($itemFormatted, 0, 1), array('a', 'e', 'i', 'o', 'u')))
			$prefix .= 'n';

		//use "an" if item is an acronym and starts with a letter that has a vowel sound
		if (substr($item, 0, 2) == substr(strtoupper($item), 0, 2)
		&& in_array(substr($itemFormatted, 0, 1), array('a', 'e', 'f', 'h', 'i', 'l', 'm', 'n', 'o', 'r', 's', 'x')))
			$prefix .= 'n';

		return $prefix.' '.$item;
	}

	/**
	 * Convert HTML characters to entities.
	 *
	 * The encoding specified in the application configuration file will be used.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function entities($value)
	{
		return htmlentities($value, ENT_QUOTES, Config::get('tetra-text::encoding'), false);
	}

	/**
	 * Create a URI slug from a string.
	 *
	 * @param  string  $string
	 * @param  mixed   $charLimit
	 * @return string
	 */
	public static function slug($string, $charLimit = false)
	{
		$slug = Str::slug(strtr(
			trim($string),
			'`!@#$%^&*()-_=+[]{}<>,.?/|:;\\\'"',
			'                               '
		));

		if ($charLimit)
			$slug = substr($slug, 0, $charLimit);

		if (substr($slug, -1) == "-")
			$slug = substr($slug, 0, (strlen($slug) - 1));

		return $slug;
	}

	/**
	 * Create a unique URI slug from a string. You may optionally limit the number of characters.
	 *
	 * @param  string  $string
	 * @param  string  $table
	 * @param  mixed   $ignoreID
	 * @param  mixed   $charLimit
	 * @param  string  $fieldName
	 * @return mixed
	 */
	public static function uniqueSlug($string, $table, $ignoreID = false, $charLimit = false, $fieldName = 'slug')
	{
		$slug = static::slug($string, $charLimit);

		if ($ignoreID) {
			$exists = DB::table($table)->where($fieldName, '=', $slug)->where('id', '!=', $ignoreID)->count();
		} else {
			$exists = DB::table($table)->where($fieldName, '=', $slug)->count();
		}
		if ((int) $exists) {
			$uniqueFound = false;
			if ($charLimit) $slug = substr($slug, 0, ($charLimit - 2));
			$originalSlug = $slug;
			for ($s = 2; $s <= 99; $s++) {
				if (!$uniqueFound) {
					$slug  = $originalSlug;
					$slug .= '-'.$s;
					if ($ignoreID) {
						$exists = DB::table($table)->where($fieldName, '=', $slug)->where('id', '!=', $ignoreID)->count();
					} else {
						$exists = DB::table($table)->where($fieldName, '=', $slug)->count();
					}
					if (!$exists) $uniqueFound = true;
				}
			}
			if (!$uniqueFound) return false;
		}

		return $slug;
	}

	/**
	 * Get the first day of the week starting with any day you want. Sunday is defaulted as the first day of the week.
	 *
	 * @param  string  $date
	 * @param  string  $firstDay
	 * @return string
	 */
	public static function firstDayOfWeek($date = 'current', $firstDay = 'Sunday')
	{
		$firstDay = date('w', strtotime(ucfirst($firstDay)));
		if ($date == "current") { $date = date('Y-m-d'); } else { $date = date('Y-m-d', strtotime($date)); }
		$difference = date('w', strtotime($date)) - $firstDay;
		if ($difference < 0) $difference += 7;
		$newDate = date('Y-m-d', (strtotime($date)-($difference*86400))); //subtract days
		return $newDate;
	}

	/**
	 * Get the last day of the week starting with any day you want. Sunday is defaulted as the first day of the week.
	 *
	 * @param  string  $date
	 * @param  string  $firstDay
	 * @return string
	 */
	public static function lastDayOfWeek($date = 'current', $firstDay = 'Sunday')
	{
		$firstDay = date('w', strtotime(ucfirst($firstDay)));
		$lastDay = $firstDay + 6;
		if ($date == "current") { $date = date('Y-m-d'); } else { $date = date('Y-m-d', strtotime($date)); }
		$difference = $lastDay - date('w', strtotime($date));
		if ($difference >= 7) $difference -= 7;
		$newDate = date('Y-m-d', (strtotime($date)+($difference*86400))); //subtract days
		return $newDate;
	}

	/**
	 * Get the first day of the month. This function is just here for the sake of completeness.
	 *
	 * @param  string  $date
	 * @param  string  $format
	 * @return string
	 */
	public static function firstDayOfMonth($date = 'current', $format = false)
	{
		if ($date == "current") {
			$result = date('Y-m-01');
		} else {
			$result = date('Y-m-01', strtotime($date));
		}

		if ($format) $result = static::date($result, $format);
		return $result;
	}

	/**
	 * Get the last day of the month. You can use the second argument to format the date (example: "F j, Y").
	 *
	 * @param  string  $date
	 * @param  mixed   $format
	 * @return string
	 */
	public static function lastDayOfMonth($date = 'current', $format = false)
	{
		if ($date == "current") {
			$date = date('Y-m-d');
		} else {
			$date = date('Y-m-d', strtotime($date));
			$originalMonth = substr($date, 5, 2);
		}
		$year = substr($date, 0, 4); $month = substr($date, 5, 2); $day = substr($date, 8, 2); $result = "";
		if (isset($originalMonth) && $month != $originalMonth) $month = $originalMonth; //prevent invalid dates having wrong month assigned (June 31 = July, etc...)
		if ($month == "01" || $month == "03" || $month == "05" || $month == "07" || $month == "08" || $month == "10" || $month == "12") {
			$result = $year.'-'.$month.'-31';
		} else if ($month == "04" || $month == "06" || $month == "09" || $month == "11") {
			$result = $year.'-'.$month.'-30';
		} else if ($month == "02") {
			if (($year/4) == round($year/4)) {
				if (($year/100) == round($year/100)) {
					if (($year/400) == round($year/400)) {
						$result = $year.'-'.$month.'-29';
					} else {
						$result = $year.'-'.$month.'-28';
					}
				} else {
					$result = $year.'-'.$month.'-29';
				}
			} else {
				$result = $year.'-'.$month.'-28';
			}
		}

		if ($format) $result = static::date($result, $format);
		return $result;
	}

	/**
	 * Get the time between two dates.
	 *
	 * @param  string  $dateStart
	 * @param  string  $dateEnd
	 * @param  boolean $interval
	 * @param  boolean $includePartialInterval
	 * @return string
	 */
	public static function timeBetweenDates($dateStart, $dateEnd, $interval = false, $includePartialInterval = false)
	{
		$time = strtotime($dateEnd) - strtotime($dateStart);
		if ($interval) {
			if (strtolower(substr($interval, 0, 1)) == "m") {
				$time = $time / 2629743.83;
				if ($includePartialInterval) { return ceil($time); } else { return floor($time); }
			}
		}
		return $time;
	}

	/**
	 * An easy way for basic use of PHP's date() method.
	 *
	 * @param  string  $date
	 * @param  string  $format
	 * @param  string  $adjust
	 * @return string
	 */
	public static function date($date, $format = 'F j, Y', $adjust = '')
	{
		if (trim($date) != "" && strtolower(trim($date)) != "date" && $date != "0000-00-00") return date($format, strtotime($date.' '.$adjust));
		return "";
	}

	/**
	 * Convert a date to a time interval. Used in conjuction with dateToIntervalStr() to get dates like "33 minutes ago"
	 *
	 * @param  string  $dateStart
	 * @param  mixed   $dateEnd
	 * @return string
	 */
	public static function dateToInterval($dateStart, $dateEnd = false)
	{
		if (!is_int($dateStart))	$dateStart = strtotime($dateStart);
		if (!$dateEnd) {
			$dateEnd = time();
		} else {
			if (!is_int($dateEnd))	$dateEnd = strtotime($dateEnd);
		}
		$date = array('number'   => 0,
					  'interval' => '',
			   		  'past'     => false);

		$seconds = $dateEnd - $dateStart;
		if ($seconds < 0) {
			$seconds = abs($seconds);
		} else {
			$date['past'] = true;
		}

		$intervals = array(
			'year'   => 31536000,
			'month'  => 2628000,
			'week'   => 604800,
			'day'    => 86400,
			'hour'   => 3600,
			'minute' => 60,
			'second' => 1,
		);

		foreach ($intervals as $interval => $intervalSeconds) {
			$number = floor($seconds / $intervalSeconds);
			if ($number > 0 && $date['interval'] == "") {
				$date['number'] = $number;
				$date['interval'] = $interval;
			}
		}
		return $date;
	}

	/**
	 * Convert a date to a time interval. Used in conjuction with dateToIntervalStr() to get dates like "33 minutes ago"
	 *
	 * @param  string  $dateStart
	 * @param  mixed   $dateEnd
	 * @return string
	 */
	public static function dateToIntervalStr($dateStart, $dateEnd = false)
	{
		$date = static::dateToInterval($dateStart, $dateEnd);
		if (!$date['past']) {
			if ($date['number'] == 1 && !in_array($date['interval'], array('minute', 'second'))) {
				if ($date['interval'] == "day") {
					return 'until tomorrow';
				} else {
					return 'until next '.$date['interval'];
				}
			} else {
				return 'for '.$date['number'].' more '.$date['interval'].'s';
			}
		} else {
			return static::pluralize('[number] [word] ago', $date['number'], $date['interval']);
		}
	}

	/**
	 * Separate a string with new line characters into paragraphs.
	 *
	 * @param  string  $string
	 * @return string
	 */
	public static function nl2p($string)
	{
		return str_replace('<p></p>', '', str_replace("\r\n", '</p><p>', str_replace("\r\n\r\n", '</p><p>', trim($string))));
	}

	/**
	 * Separate a string into paragraphs.
	 *
	 * @param  string  $string
	 * @param  integer $characters
	 * @return string
	 */
	public static function paragraphs($string, $charLimit = 0)
	{
		if ($charLimit) $string = static::charLimit($string, $charLimit);
		return '<p>'.static::nl2p($string).'</p>';
	}

	/**
	 * Limit a string to a number of characters.
	 *
	 * @param  string  $string
	 * @param  integer $characters
	 * @param  string  $end
	 * @param  mixed   $endLink
	 * @param  boolean $paragraphs
	 * @return string
	 */
	public static function charLimit($string = '', $characters = 140, $end = true, $endLink = false, $paragraphs = false)
	{
		//if end is set to null or false, set it to an empty string
		if (is_null($end) || (is_bool($end) && !$end))
			$end = "";

		//if end is set to true, use "..." as a default
		if (is_bool($end) && $end)
			$end = "...";

		//convert HTML special characters if end string is not HTML
		if ($end == strip_tags($end))
			$end = static::entities($end);

		//if end link is not a full URL, convert it into one
		if ($endLink && substr($end, 0, 4) != "http")
			$endLink = URL::to($endLink);

		$formattedString = substr($string, 0, $characters);
		if ($formattedString != $string) {
			if ($endLink) $end = ' <a href="'.$endLink.'" class="read-more">'.$end.'</a>';
			$formattedString .= $end;
		}
		if ($paragraphs) $formattedString = static::paragraphs($formattedString);
		return $formattedString;
	}

	/**
	 * Get a random MD5-hashed string at a specified length.
	 *
	 * @param  integer $length
	 * @return string
	 */
	public static function strRandom($length = 32)
	{
		$md5 = md5(rand(100000, 99999999).rand(100000, 99999999));
		if ($length > 32) $md5 .= md5(rand(100000, 99999999).rand(100000, 99999999)); //double length if length to return exceeds 32 characters
		return substr($md5, 0, $length);
	}

	/**
	 * Use HTMLPurifier to sanitize HTML data.
	 *
	 * @param  string  $html
	 */
	public static function purifyHTML($html) {
		$purifier = new HTMLPurifier();
		$html = trim($html);
		$html = $purifier->purify($html);

		if (substr($html, -4) == "<br>") $html = substr($html, 0, (strlen($html) - 4));
		$html = str_replace('<br>', '<br />', $html);

		return $html;
	}

}
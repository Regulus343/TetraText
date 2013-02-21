<?php namespace Regulus\TetraText;

/*----------------------------------------------------------------------------------------------------------
	TetraText
		A small text/string formatting composer package that formats phone numbers, numeric values,
		money values and more. There are also some limited date functions available.

		created by Cody Jassman
		last updated on February 20, 2013
----------------------------------------------------------------------------------------------------------*/

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class TetraText {

	/**
	 * Remove all non-numeric characters from a string
	 * the dollar symbol to the right of the minus for a negative value ("-$33.00").
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
			$phoneNumber = static::numeric($phoneNumber);
			$length = strlen($phoneNumber);
			$offset = $length - $digits;
			if ($offset < 0) $offset = 0;
			if ($digits == 11) {
				$offset ++;
				if ($length == 10) $phoneNumber = '1'.$phoneNumber; //if length is too short, add 1 to make 11 digit phone number
			} else if ($digits == 10 && $length == 11) {
				$offset ++;
			}
			$variable = $firstDigit.$bracketL.substr($phoneNumber, $offset, 3).$bracketR.substr($phoneNumber, ($offset+3), 3).$separator.substr($phoneNumber, ($offset+6), 4);
			return $phoneNumber;
		}
	}

	/**
	 * Format a Canadian postal code.
	 *
	 * @param  string  $postalCode
	 * @return string
	 */
	public static function postalCode($postalCode = '')
	{
		$postalCode = strtoupper(str_replace(' ', '', $postalCode));
		$postalCode = substr($postalCode, 0, 3).' '.substr($postalCode, 3, 3);
		$postalCode = substr($postalCode, 0, 7);
		if (strlen($postalCode) != 7) $postalCode = "";
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
		$type = explode('/', $type);
		if (!isset($type[1])) $type[1] = "";
		if ($value)
			return $type[0];
		else
			return $type[1];
	}

	/**
	 * Pluralize a string containing "[word]" and "[number]" which will automatically be replaced.
	 *
	 * @param  string  $message
	 * @param  integer $number
	 * @param  string  $string
	 * @param  string  $plural
	 * @return string
	 */
	public static function pluralize($message, $number, $singular = 'result', $plural = false)
	{
		if (!$plural) $plural = Str::plural($singular);
		$message = str_replace('[number]', $number, $message);
		if ($number == 1) {
			return str_replace('[word]', $singular, $message);
		} else {
			return str_replace('[word]', $plural, $message);
		}
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
	 * @param  string  $firstDay
	 * @return string
	 */
	public static function firstDayOfMonth($date = 'current')
	{
		if ($date == "current") {
			return date('Y-m-01');
		} else {
			return date('Y-m-01', strtotime($date));
		}
	}

	/**
	 * Get the last day of the month.
	 *
	 * @param  string  $date
	 * @param  string  $firstDay
	 * @return string
	 */
	public static function lastDayOfMonth($date = 'current')
	{
		if ($date == "current") { $date = date('Y-m-d'); } else { $originalMonth = substr($date, 5, 2); $date = date('Y-m-d', strtotime($date)); }
		$year = substr($date, 0, 4); $month = substr($date, 5, 2); $day = substr($date, 8, 2); $result = "";
		if (isset($originalMonth) && $month != $originalMonth) $month = $originalMonth; //prevent invalid dates having wrong month assigned (June 31 = July, etc...)
		if ($month == "01" || $month == "03" || $month == "05" || $month == "07" || $month == "08" || $month == "10" || $month == "12") { $result = $year.'-'.$month.'-31'; } 
		else if ($month == "04" || $month == "06" || $month == "09" || $month == "11") { $result = $year.'-'.$month.'-30'; } 
		else if ($month == "02") {
			if (($year/4) == round($year/4)) {
				if (($year/100) == round($year/100)) {
					if (($year/400) == round($year/400)) { $result = $year.'-'.$month.'-29'; } 
					else { $result = $year.'-'.$month.'-28'; }
				} else { $result = $year.'-'.$month.'-29'; }
			} else { $result = $year.'-'.$month.'-28'; }
		}
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
		$date = array('number'=>	0,
					  'interval'=>	'',
			   		  'past'=>		false);

		$seconds = $dateEnd - $dateStart;
		if ($seconds < 0) {
			$seconds = abs($seconds);
		} else {
			$date['past'] = true;
		}

		$intervals = array('year'=>		31536000,
						   'month'=>	2628000,
						   'week'=>		604800,
						   'day'=>		86400,
						   'hour'=>		3600,
						   'minute'=>	60,
						   'second'=>	1);

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
		return trim(str_replace('<p></p>', '', str_replace("\n", '</p><p>', str_replace("\n\n", '</p><p>', $string))));
	}

	/**
	 * Get a random MD5 hash.
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

}
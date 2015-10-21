<?php namespace Regulus\TetraText;

/*----------------------------------------------------------------------------------------------------------
	TetraText
		A small text/string formatting composer package that formats phone numbers, numeric values,
		money values and more. There are also some limited date functions available.

		created by Cody Jassman
		v0.5.1
		last updated on October 20, 2014
----------------------------------------------------------------------------------------------------------*/

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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
	public function numeric($value, $allowDecimal = true, $allowNegative = false)
	{
		$formatted = "";

		for ($n = 0; $n < strlen($value); $n++)
		{
			if ($allowDecimal == false)
			{
				if (is_numeric(substr($value, $n, 1)) || ($allowNegative && $n == 0 && substr($value, 0, 1) == '-'))
					$formatted .= substr($value, $n, 1);
			} else
				if (is_numeric(substr($value, $n, 1)) || substr($value, $n, 1) == '.' || ($allowNegative && $n == 0 && substr($value, 0, 1) == '-')) {
					$formatted .= substr($value, $n, 1);
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
	public function money($value, $prefix = '$', $allowNegative = true, $thousandsSeparator = ',')
	{
		$value     = $this->numeric($value, true, true);
		$formatted = "";

		if ($value != "")
		{
			if (substr($value, 0, 1) == '-') {
				if ($allowNegative) {
					if ($allowNegative === "brackets")
						$formatted = '('.$prefix.number_format(abs($value), 2, '.', $thousandsSeparator).')';
					else
						$formatted = '-'.$prefix.number_format(abs($value), 2, '.', $thousandsSeparator);
				} else {
					$formatted = $prefix.'0.00';
				}
			} else {
				$formatted = $prefix.number_format($value, 2, '.', $thousandsSeparator);
			}
		} else {
			$formatted = $prefix.'0.00';
		}

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
	public function percent($value = 0, $total = 0, $decimals = 1, $returnNumeric = false)
	{
		$value = $this->numeric($value, true);
		$total = $this->numeric($total, true);

		$percent = 0;
		if ($total > 0)
			$percent = round($value / $total * 100, $decimals);

		if (!$returnNumeric)
			$percent .= '%';

		return $percent;
	}

	/**
	 * Format a name (adjust for CAPS LOCK and uncapitalized names).
	 *
	 * @param  string  $name
	 * @return string
	 */
	public function name($name = '')
	{
		$name = trim($name);

		//if name is in all caps, make it lowercase so only first letter is capitalized
		if ($name == strtoupper($name))
			$name = strtolower($name);

		$name = ucfirst($name);

		return $name;
	}

	/**
	 * Format a title.
	 *
	 * @param  string  $title
	 * @return string
	 */
	public function title($title = '')
	{
		$title = ucwords($this->name($title));

		$lowercaseWords = array(
			'a',
			'an',
			'at',
			'by',
			'in',
			'of',
			'on',
			'the',
		);

		foreach ($lowercaseWords as $word)
		{
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
	public function phone($phoneNumber, $digits = null, $separator = null, $areaCodeBrackets = null)
	{
		if (is_null($phoneNumber) || $phoneNumber == "")
			return null;

		$phoneNumber = $this->numeric($phoneNumber, false);

		if (is_null($digits))
			$digits = config('format.defaults.phone.digits');

		if (is_null($separator))
			$separator = config('format.defaults.phone.separator');

		if (is_null($areaCodeBrackets))
			$areaCodeBrackets = config('format.defaults.phone.area_code_brackets');

		if ($areaCodeBrackets)
		{
			$bracketL   = "(";
			$bracketR   = ") ";
			$firstDigit = $digits == 11 ? '1 ' : '';
		} else {
			$bracketL   = "";
			$bracketR   = $separator;
			$firstDigit = $digits == 11 ? '1'.$separator : '';
		}

		$length = strlen($phoneNumber);
		$offset = $length - $digits;

		if ($length < 7)
			return null;

		if ($offset < 0)
			$offset = 0;

		if ($digits == 11)
		{
			$offset ++;

			//if length is too short, add 1 to make 11 digit phone number
			if ($length == 10)
				$phoneNumber = '1'.$phoneNumber;
		}

		$phoneNumber = $firstDigit.$bracketL.substr($phoneNumber, $offset, 3).$bracketR.substr($phoneNumber, ($offset + 3), 3).$separator.substr($phoneNumber, ($offset + 6), 4);

		return $phoneNumber;
	}

	/**
	 * Format a Canadian postal code.
	 *
	 * @param  string  $postalCode
	 * @param  boolean $separateWithSpace
	 * @return string
	 */
	public function postalCode($postalCode, $separateWithSpace = true)
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

		if (strlen($postalCode) != $length)
			return null;

		return $postalCode;
	}

	/**
	 * Format an email address as a mailto: link.
	 *
	 * @param  mixed   $email
	 * @param  mixed   $subject
	 * @return mixed
	 */
	public function email($email = null, $subject = null)
	{
		if (is_null($email) || $email == "" || !strpos($email, '@') || !strpos($email, '.'))
			return null;

		$email = trim($email);
		$href  = 'mailto:'.$email;

		if (!is_null($subject) && trim($subject) != "")
			$href .= '?subject='.str_replace(' ', '%20', trim($subject));

		return '<a href="'.$href.'" class="email">'.$email.'</a>';
	}

	/**
	 * Add a letter suffix to an integer ("1st", "2nd", "3rd", "4th").
	 *
	 * @param  integer $number
	 * @param  boolean $superscriptTag
	 * @return string
	 */
	public function numberSuffix($number = 1, $superscriptTag = true)
	{
		$number = $this->numeric($number, false);

		$suffix = "th";

		if ((int) substr($number, -1) == 1 && $number != 11)
			$suffix = "st";

		if ((int) substr($number, -1) == 2 && $number != 12)
			$suffix = "nd";

		if ((int) substr($number, -1) == 3 && $number != 13)
			$suffix = "rd";

		if ($superscriptTag)
			$suffix = '<sup>'.$suffix.'</sup>';

		return $number.$suffix;
	}

	/**
	 * Turn a boolean value into a string. Some examples of types are "Yes/No", "Yes", "On/Off", and "Active/Inactive".
	 *
	 * @param  boolean $value
	 * @param  mixed   $options
	 * @return string
	 */
	public function boolToStr($value, $options = null)
	{
		if (is_null($options))
			$options = config('format.defaults.bool_to_str_options');

		if (is_string($options))
			$options = explode('/', $options);

		if (!isset($options[1]))
			$options[1] = "";

		if ($value)
			return $options[0];
		else
			return $options[1];
	}

	/**
	 * Get only items (keys) from an array if their values are set to true. If a simple array is used, the function will simply remove duplicate values.
	 *
	 * @param  array   $array
	 * @return array
	 */
	public function getSelectedFromArray($array)
	{
		$newArray = [];
		foreach ($array as $key => $value)
		{
			if (!is_numeric($key) && (is_bool($value) || is_numeric($value)))
			{
				if ($value && !in_array($key, $newArray))
					$newArray[] = $key;

			} else {
				if ($value && !in_array($value, $newArray))
					$newArray[] = $value;
			}
		}

		return $newArray;
	}

	/**
	 * Turn an array of items into a string.
	 *
	 * @param  array   $array
	 * @param  string  $delimiter
	 * @return string
	 */
	public function arrayToStringList($array, $delimiter = ', ')
	{
		$array = $this->getSelectedFromArray($array);

		return implode(', ', $array);
	}

	/**
	 * Turn an array of items into an HTML list.
	 *
	 * @param  array   $array
	 * @param  boolean $ordered
	 * @return string
	 */
	public function arrayToHtmlList($array, $ordered = false)
	{
		$array = $this->getSelectedFromArray($array);

		if (empty($array))
			return "";

		$html  = $ordered ? '<ol>' : '<ul>';
		$html .= "\n";

		foreach ($array as $item)
		{
			$html .= '<li>'.$item.'</li>' . "\n";
		}

		$html .= $ordered ? '</ol>' : '</ul>';
		$html .= "\n";

		return $html;
	}

	/**
	 * Get the values of a specific attribute or method from an array of objects and place them in an array.
	 *
	 * @param  array   $objects
	 * @param  mixed   $attribute
	 * @return array
	 */
	public function objectItemsToArray($objects, $attribute = null)
	{
		$array = [];

		foreach ($objects as $object)
		{
			if (is_null($attribute)) {
				$item = $object;
			} else {
				$method = $this->getMethodFromString($attribute);

				if (!is_null($method)) //attribute is a method of object; call it
				{
					$item = call_user_func_array([$object, $method['name']], $method['parameters']);
				} else {
					$item = $object->{$attribute};
				}
			}

			if (!in_array($item, $array))
				$array[] = $item;
		}

		return $array;
	}

	/**
	 * Get a method's name and parameters from a string.
	 *
	 * @param  string  $method
	 * @return mixed
	 */
	public function getMethodFromString($method)
	{
		preg_match('/\(([A-Za-z0-9\ \'\"\,]*)\)/', $method, $methodMatch);

		if (empty($methodMatch))
			return null;

		$parameters = $methodMatch[1];
		if ($parameters != "")
		{
			$parameters = explode(',', $parameters);
			for ($p = 0; $p < count($parameters); $p++)
			{
				$parameters[$p] = trim($parameters[$p]);

				if ((substr($parameters[$p], 0, 1) == "'" && substr($parameters[$p], -1) == "'") || (substr($parameters[$p], 0, 1) == '"' && substr($parameters[$p], -1) == '"'))
				{
					$parameters[$p] = (string) substr($parameters[$p], 1, (strlen($parameters[$p]) - 2));
				} else {
					if (strtolower($parameters[$p]) == "true")
						$parameters[$p] = true;
					else if (strtolower($parameters[$p]) == "false")
						$parameters[$p] = false;
					else if (strtolower($parameters[$p]) == "null")
						$parameters[$p] = null;
					else
						$parameters[$p] = (int) $parameters[$p];
				}
			}
		} else {
			$parameters = [];
		}

		return [
			'name'       => str_replace($methodMatch[0], '', $method),
			'parameters' => $parameters,
		];
	}

	/**
	 * Get a method's name from a string.
	 *
	 * @param  string  $method
	 * @return mixed
	 */
	public function getMethodNameFromString($method)
	{
		$method = $this->getMethodFromString($method);
		if (is_null($method))
			return $method;

		return $method['name'];
	}

	/**
	 * Get a method's parameters from a string.
	 *
	 * @param  string  $method
	 * @return mixed
	 */
	public function getMethodParametersFromString($method)
	{
		$method = $this->getMethodFromString($method);
		if (is_null($method))
			return [];

		return $method['parameters'];
	}

	/**
	 * Pluralize a string containing ":item" and ":number" which will automatically be replaced.
	 *
	 * @param  string  $singular
	 * @param  integer $number
	 * @param  string  $plural
	 * @return string
	 */
	public function pluralize($singular = 'result', $number = 1, $plural = false)
	{
		if ($number == 1)
		{
			return $singular;
		} else {
			if (!$plural)
				$plural = Str::plural($singular);

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
	public function pluralizeMessage($message, $singular = 'result', $number = 1, $plural = false)
	{
		$item    = $this->pluralize($singular, $number, $plural);
		$message = str_replace(':number', $number, str_replace(':item', $item, $message));
	}

	/**
	 * Add "a" or "an" to prefix to word based on whether it begins with a vowel.
	 *
	 * @param  string  $item
	 * @return string
	 */
	public function a($item)
	{
		$itemFormatted = strtolower($item);
		$prefix        = 'a';

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
	public function entities($value)
	{
		return htmlentities($value, ENT_QUOTES, config('format.encoding'), false);
	}

	/**
	 * Create a URI slug from a string.
	 *
	 * @param  string  $string
	 * @param  mixed   $charLimit
	 * @return string
	 */
	public function slug($string, $charLimit = false)
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
	 * Create a unique URI slug from a string.
	 *
	 * @param  string  $string
	 * @param  string  $table
	 * @param  string  $fieldName
	 * @param  mixed   $ignoreId
	 * @param  mixed   $charLimit
	 * @return mixed
	 */
	public function uniqueSlug($string, $table, $fieldName = 'slug', $ignoreId = false, $charLimit = false)
	{
		$slug = $this->slug($string, $charLimit);

		return $this->unique($slug, $table, $fieldName, $ignoreId, false, $charLimit);
	}

	/**
	 * Create a unique string for a table field. You may optionally limit the number of characters.
	 *
	 * @param  string  $string
	 * @param  string  $table
	 * @param  string  $fieldName
	 * @param  mixed   $ignoreId
	 * @param  boolean $filename
	 * @param  mixed   $charLimit
	 * @return mixed
	 */
	public function unique($string, $table, $fieldName = 'name', $ignoreId = false, $filename = false, $charLimit = false)
	{
		if ($ignoreId)
			$exists = DB::table($table)->where($fieldName, '=', $string)->where('id', '!=', $ignoreId)->count();
		else
			$exists = DB::table($table)->where($fieldName, '=', $string)->count();

		$extension = $filename ? File::extension($string) : '';

		if ((int) $exists)
		{
			$uniqueFound = false;

			if ($charLimit)
				$string = substr($string, 0, ($charLimit - 2));

			$originalString = $string;

			for ($s = 2; $s <= 99; $s++) {
				if (!$uniqueFound)
				{
					$string = $originalString;
					$suffix = '-'.$s;

					if ($filename)
						$string = str_replace('.'.$extension, '', $string).$suffix.'.'.$extension;
					else
						$string .= $suffix;

					if ($ignoreId)
						$exists = DB::table($table)->where($fieldName, '=', $string)->where('id', '!=', $ignoreId)->count();
					else
						$exists = DB::table($table)->where($fieldName, '=', $string)->count();

					if (!$exists)
						$uniqueFound = true;
				}
			}

			if (!$uniqueFound)
				return false;
		}

		return $string;
	}

	/**
	 * Get the first day of the week starting with any day you want. Sunday is defaulted as the first day of the week.
	 *
	 * @param  mixed   $date
	 * @param  string  $firstDay
	 * @return string
	 */
	public function firstDayOfWeek($date = null, $firstDay = 'Sunday')
	{
		$firstDay = date('w', strtotime(ucfirst($firstDay)));

		$date = is_null($date) ? date('Y-m-d') : date('Y-m-d', strtotime($date));

		$difference = date('w', strtotime($date)) - $firstDay;

		if ($difference < 0)
			$difference += 7;

		$newDate = date('Y-m-d', (strtotime($date)-($difference * 86400))); //subtract days

		return $newDate;
	}

	/**
	 * Get the last day of the week starting with any day you want. Sunday is defaulted as the first day of the week.
	 *
	 * @param  mixed   $date
	 * @param  string  $firstDay
	 * @return string
	 */
	public function lastDayOfWeek($date = null, $firstDay = 'Sunday')
	{
		$firstDay = date('w', strtotime(ucfirst($firstDay)));

		$lastDay = $firstDay + 6;

		$date = is_null($date) ? date('Y-m-d') : date('Y-m-d', strtotime($date));

		$difference = $lastDay - date('w', strtotime($date));

		if ($difference >= 7)
			$difference -= 7;

		$newDate = date('Y-m-d', (strtotime($date)+($difference * 86400))); //subtract days

		return $newDate;
	}

	/**
	 * Get the first day of the month. This function is just here for the sake of completeness.
	 *
	 * @param  mixed   $date
	 * @param  string  $format
	 * @return string
	 */
	public function firstDayOfMonth($date = null, $format = false)
	{
		$date = is_null($date) ? date('Y-m-d') : date('Y-m-d', strtotime($date));

		if ($format)
			$result = $this->date($result, $format);

		return $result;
	}

	/**
	 * Get the last day of the month. You can use the second argument to format the date (example: "F j, Y").
	 *
	 * @param  mixed   $date
	 * @param  mixed   $format
	 * @return string
	 */
	public function lastDayOfMonth($date = null, $format = false)
	{
		if (is_null($date)) {
			$date = date('Y-m-d');
		} else {
			$date = date('Y-m-d', strtotime($date));

			$originalMonth = substr($date, 5, 2);
		}

		$year   = substr($date, 0, 4);
		$month  = substr($date, 5, 2);
		$day    = substr($date, 8, 2);
		$result = "";

		//prevent invalid dates having wrong month assigned (June 31 = July, etc...)
		if (isset($originalMonth) && $month != $originalMonth)
			$month = $originalMonth;

		if (in_array($month, ['01', '03', '05', '07', '08', '10', '12'])) {
			$lastDay = 31;
		} else if (in_array($month, ['04', '06', '09', '11'])) {
			$lastDay = 30;
		} else if ($month == "02") {
			if (($year/4) == round($year/4)) {
				if (($year/100) == round($year/100))
				{
					if (($year/400) == round($year/400))
						$lastDay = 29;
					else
						$lastDay = 28;
				} else {
					$lastDay = 29;
				}
			} else {
				$lastDay = 28;
			}
		}

		$result = $year.'-'.$month.'-'.$lastDay;

		if ($format)
			$result = $this->date($result, $format);

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
	public function timeBetweenDates($dateStart, $dateEnd, $interval = false, $includePartialInterval = false)
	{
		$time = strtotime($dateEnd) - strtotime($dateStart);

		if ($interval) {
			if (strtolower(substr($interval, 0, 1)) == "m")
			{
				$time = $time / 2629743.83;

				if ($includePartialInterval)
					return ceil($time);
				else
					return floor($time);
			}
		}

		return $time;
	}

	/**
	 * Format a date.
	 *
	 * @param  mixed   $date
	 * @param  mixed   $format
	 * @param  string  $adjust
	 * @return string
	 */
	public function date($date = null, $format = null, $adjust = '')
	{
		if (is_null($date))
			$date = date('Y-m-d H:i:s');

		if (is_null($date) || trim($date) == "" || $date == "0000-00-00" || $date == "0000-00-00 00:00:00")
			return null;

		if (is_null($format))
			$format = config('format.defaults.date');

		return date($format, strtotime($date.' '.$adjust));
	}

	/**
	 * Format a date-time.
	 *
	 * @param  mixed   $date
	 * @param  string  $format
	 * @param  string  $adjust
	 * @return string
	 */
	public function dateTime($date = false, $format = null, $adjust = '')
	{
		if (is_null($format))
			$format = config('format.defaults.datetime');

		return $this->date($date, $format, $adjust);
	}

	/**
	 * Convert a date to a time interval. Used in conjuction with dateToIntervalStr() to get dates like "33 minutes ago"
	 *
	 * @param  string  $dateStart
	 * @param  mixed   $dateEnd
	 * @return string
	 */
	public function dateToInterval($dateStart, $dateEnd = false)
	{
		if (!is_int($dateStart))
			$dateStart = strtotime($dateStart);

		if (!$dateEnd) {
			$dateEnd = time();
		} else {
			if (!is_int($dateEnd))
				$dateEnd = strtotime($dateEnd);
		}
		$date = array(
			'number'   => 0,
			'interval' => '',
			'past'     => false,
		);

		$seconds = $dateEnd - $dateStart;

		if ($seconds < 0)
			$seconds = abs($seconds);
		else
			$date['past'] = true;

		$intervals = array(
			'year'   => 31536000,
			'month'  => 2628000,
			'week'   => 604800,
			'day'    => 86400,
			'hour'   => 3600,
			'minute' => 60,
			'second' => 1,
		);

		foreach ($intervals as $interval => $intervalSeconds)
		{
			$number = floor($seconds / $intervalSeconds);

			if ($number > 0 && $date['interval'] == "")
			{
				$date['number']   = $number;
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
	public function dateToIntervalStr($dateStart, $dateEnd = false)
	{
		$date = $this->dateToInterval($dateStart, $dateEnd);

		if (!$date['past'])
		{
			if ($date['number'] == 1 && !in_array($date['interval'], array('minute', 'second')))
			{
				if ($date['interval'] == "day")
					return 'until tomorrow';
				else
					return 'until next '.$date['interval'];

			} else {
				return 'for '.$date['number'].' more '.$date['interval'].'s';
			}
		} else {
			return $this->pluralize('[number] [word] ago', $date['number'], $date['interval']);
		}
	}

	/**
	 * Separate a string with new line characters into paragraphs.
	 *
	 * @param  string  $string
	 * @return string
	 */
	public function nl2p($string)
	{
		return str_replace('<p></p>', '', str_replace("\r\n", '</p><p>', str_replace("\r\n\r\n", '</p><p>', trim($string))));
	}

	/**
	 * Separate a string into paragraphs.
	 *
	 * @param  string  $string
	 * @param  integer $charLimit
	 * @return string
	 */
	public function paragraphs($string, $charLimit = 0)
	{
		if ($charLimit)
			$string = $this->charLimit($string, $charLimit);

		return '<p>'.$this->nl2p($string).'</p>';
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
	public function charLimit($string = '', $characters = 140, $end = true, $endLink = false, $paragraphs = false)
	{
		//if end is set to null or false, set it to an empty string
		if (is_null($end) || (is_bool($end) && !$end))
			$end = "";

		//if end is set to true, use "..." as a default
		if (is_bool($end) && $end)
			$end = "...";

		//convert HTML special characters if end string is not HTML
		if ($end == strip_tags($end))
			$end = $this->entities($end);

		//if end link is not a full URL, convert it into one
		if ($endLink && substr($end, 0, 4) != "http")
			$endLink = URL::to($endLink);

		$formattedString = substr($string, 0, $characters);
		if ($formattedString != $string) {
			if ($endLink)
				$end = ' <a href="'.$endLink.'" class="read-more">'.$end.'</a>';

			$formattedString .= $end;
		}

		if ($paragraphs)
			$formattedString = $this->paragraphs($formattedString);

		return $formattedString;
	}

	/**
	 * Get a random string at a specified length.
	 *
	 * @param  integer $length
	 * @return string
	 */
	public function getRandomString($length = 32)
	{
		$characters       = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$charactersLength = strlen($characters);
		$string           = "";

		for ($i = 0; $i < $length; $i++) {
			$string .= $characters[rand(0, $charactersLength - 1)];
		}

		return $string;
	}

	/**
	 * Use HTMLPurifier to sanitize HTML data.
	 *
	 * @param  string  $html
	 */
	public function purifyHtml($html)
	{
		$purifier = new HTMLPurifier();

		$html = trim($html);
		$html = $purifier->purify($html);

		if (substr($html, -4) == "<br>")
			$html = substr($html, 0, (strlen($html) - 4));

		$html = str_replace('<br>', '<br />', $html);

		return $html;
	}

}
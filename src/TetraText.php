<?php namespace Regulus\TetraText;

/*----------------------------------------------------------------------------------------------------------
	TetraText
		A small text/string formatting composer package that formats phone numbers, numeric values,
		money values and more. There are also some limited date functions available.

		created by Cody Jassman
		v0.6.9
		last updated on April 24, 2017
----------------------------------------------------------------------------------------------------------*/

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

use DateTime;
use DateTimeZone;
use Lang;

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
	 * Format a money value. This is superior to PHP's number_format() for monetary values because
	 * it will put the dollar symbol to the right of the minus for a negative value ("-$343.00").
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
			if (substr($value, 0, 1) == '-')
			{
				if ($allowNegative)
				{
					if ($allowNegative === "brackets")
						$formatted = '('.$prefix.number_format(abs($value), 2, '.', $thousandsSeparator).')';
					else
						$formatted = '-'.$prefix.number_format(abs($value), 2, '.', $thousandsSeparator);
				}
				else
				{
					$formatted = $prefix.'0.00';
				}
			}
			else
			{
				$formatted = $prefix.number_format($value, 2, '.', $thousandsSeparator);
			}
		}
		else
		{
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
	 * @param  string  $number
	 * @param  array   $config
	 * @return string
	 */
	public function phone($number, $config = [])
	{
		if (is_null($number) || $number == "")
			return null;

		$number = strtolower($number);

		// check for extension
		$extension = false;
		if (strpos($number, 'x') !== false)
		{
			$number      = str_replace('ext', 'x', $number);
			$numberArray = explode('x', $number);
			if (count($numberArray) == 2)
			{
				$number    = $numberArray[0];
				$extension = $this->numeric(str_replace('.', '', $numberArray[1]), false);
			}
		}

		$number = $this->numeric($number, false);
		$config = array_merge($this->camelizeKeys(config('format.defaults.phone')), $config);

		if (!in_array($config['digits'], [10, 11]))
			$config['digits'] = 10;

		if ($config['areaCodeBrackets'])
		{
			$bracketL   = "(";
			$bracketR   = ") ";
			$firstDigit = $config['digits'] == 11 ? '1 ' : '';
		} else {
			$bracketL   = "";
			$bracketR   = $config['separator'];
			$firstDigit = $config['digits'] == 11 ? '1'.$config['separator'] : '';
		}

		$length = strlen($number);
		$offset = $length - $config['digits'];

		if ($length < 7)
			return null;

		if ($offset < 0)
			$offset = 0;

		if ($config['digits'] == 11)
		{
			$offset ++;

			//if length is too short, add 1 to make 11 digit phone number
			if ($length == 10)
				$number = '1'.$number;
		}

		$formattedNumber  = $firstDigit.$bracketL.substr($number, $offset, 3).$bracketR.substr($number, ($offset + 3), 3);
		$formattedNumber .= $config['separator'].substr($number, ($offset + 6), 4);

		if (!$config['stripExtension'] && $extension !== false)
		{
			$formattedNumber .= $config['extensionSeparator'].$extension;
		}

		return $formattedNumber;
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
	public function emailLink($email = null, $subject = null)
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
	 * @param  mixed   $tag
	 * @return string
	 */
	public function numberSuffix($number = 1, $tag = 'sup', $tagClass = 'number-suffix')
	{
		$number = (int) $this->numeric($number, false);

		$suffix = "th";

		if ((int) substr($number, -1) == 1 && $number != 11)
			$suffix = "st";

		if ((int) substr($number, -1) == 2 && $number != 12)
			$suffix = "nd";

		if ((int) substr($number, -1) == 3 && $number != 13)
			$suffix = "rd";

		if (!is_null($tag) && $tag !== false)
		{
			$class = !is_null($tagClass) && $tagClass !== false ? ' class="'.$tagClass.'"' : "";

			$suffix = '<'.$tag.''.$class.'>'.$suffix.'</'.$tag.'>';
		}

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
	 * Make the keys of an array or object camel case.
	 *
	 * @param  array   $array
	 * @return array
	 */
	public function camelizeKeys($array)
	{
		$formattedArray = [];

		$object = is_object($array);

		foreach ($array as $key => $value)
		{
			$key = camel_case($key);

			if (is_array($value) || is_object($value))
			{
				$formattedArray[$key] = $this->camelizeKeys($value);
			}
			else
			{
				$formattedArray[$key] = $value;
			}
		}

		if ($object)
			$formattedArray = (object) $formattedArray;

		return $formattedArray;
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
			if (is_null($attribute))
			{
				$item = $object;
			}
			else
			{
				$method = $this->getMethodFromString($attribute);

				if (!is_null($method)) //attribute is a method of object; call it
				{
					$item = call_user_func_array([$object, $method['name']], $method['parameters']);
				}
				else
				{
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
		preg_match('/\((.*)\)/', $method, $methodMatch);

		if (empty($methodMatch))
			return null;

		if ($methodMatch[1] != "")
			$parameters = $this->getParametersFromString($methodMatch[1]);
		else
			$parameters = [];

		return [
			'name'       => str_replace($methodMatch[0], '', $method),
			'parameters' => $parameters,
		];
	}

	/**
	 * Get an array of parameters from a string.
	 *
	 * @param  string  $parametersString
	 * @return array
	 */
	public function getParametersFromString($parametersString)
	{
		$parameters          = explode(',', $parametersString);
		$parametersFormatted = [];
		$skip                = [];

		for ($p = 0; $p < count($parameters); $p++)
		{
			$parameters[$p] = ltrim($parameters[$p]);

			if (substr($parameters[$p], 0, 1) == "'" || substr($parameters[$p], 0, 1) == '"')
			{
				$parameter   = substr($parameters[$p], 1, strlen($parameters[$p]));
				$singleQuote = substr($parameters[$p], 0, 1) == "'";

				// if parameter doesn't end with quotation mark, there is a comma inside the full parameter string
				if (($singleQuote && substr($parameters[$p], -1) != "'") || (!$singleQuote && substr($parameters[$p], -1) != '"'))
				{
					$closingQuoteFound = false;
					while (!$closingQuoteFound) // loop through subsequent array items until closing quotation mark is found
					{
						$p ++;

						$positionQuoteFound = strpos($parameters[$p], ($singleQuote ? "'" : '"'));

						if ($positionQuoteFound)
						{
							$parameter .= ','.substr($parameters[$p], 0, $positionQuoteFound);

							$closingQuoteFound = true;
						}
						else
						{
							$parameter .= ','.$parameters[$p];
						}
					}
				}
				else
				{
					//trim trailing quote off of parameter
					$parameter = substr($parameter, 0, (strlen($parameter) - 1));
				}

				$parametersFormatted[] = $parameter;
			}
			else
			{
				$parameter = $parameters[$p];

				if (strtolower($parameter) == "true")
				{
					$parametersFormatted[] = true;
				}
				else if (strtolower($parameter) == "false")
				{
					$parametersFormatted[] = false;
				}
				else if (strtolower($parameter) == "null")
				{
					$parametersFormatted[] = null;
				}
				else
				{
					if (strpos($parameter, '.'))
						$parametersFormatted[] = (float) $parameter;
					else
						$parametersFormatted[] = (int) $parameter;
				}
			}
		}

		return $parametersFormatted;
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
	 * @param  mixed   $plural
	 * @return string
	 */
	public function pluralize($singular = 'item', $number = 1, $plural = null)
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
	 * @param  mixed   $plural
	 * @return string
	 */
	public function pluralizeMessage($message, $singular = 'item', $number = 1, $plural = null)
	{
		$item = $this->pluralize($singular, $number, $plural);

		return str_replace(':number', $number, str_replace(':item', $item, $message));
	}

	/**
	 * Add "a" or "an" to prefix to a word based on whether it begins with a vowel.
	 *
	 * @param  string  $item
	 * @param  boolean $addItem
	 * @return string
	 */
	public function a($item, $addItem = true)
	{
		$acronym     = $item == strtoupper($item);
		$firstLetter = substr(strtolower($item), 0, 1);
		$prefix      = 'a';

		if ($acronym) // use "an" if item is an acronym and starts with a letter that has a vowel sound
			$letters = ['a', 'e', 'f', 'h', 'i', 'l', 'm', 'n', 'o', 'r', 's', 'x'];
		else // use "an" if item begins with a vowel
			$letters = ['a', 'e', 'i', 'o', 'u'];

		if (in_array($firstLetter, $letters))
			$prefix .= 'n';

		$formatted = $prefix;

		if ($addItem)
			$formatted .= ' '.$item;

		return $formatted;
	}

	/**
	 * Convert HTML characters to entities.
	 *
	 * The encoding specified in the application config file will be used.
	 *
	 * @param  string  $string
	 * @return string
	 */
	public function entities($string)
	{
		return htmlentities($string, ENT_QUOTES, config('format.encoding'), false);
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
	 * @param  mixed   $tableModel
	 * @param  array   $config
	 * @return string
	 */
	public function uniqueSlug($string, $tableModel, $config = [])
	{
		$defaultConfig = [
			'field'          => 'slug',
			'model'          => config('format.defaults.unique.model'),
			'ignoreId'       => null,
			'charLimit'      => config('format.defaults.unique.char_limit'),
			'softDelete'     => config('format.defaults.unique.soft_delete'),
			'matchingValues' => [],
		];

		$config = array_merge($defaultConfig, $config);

		$slug = $this->slug($string, $config['charLimit']);

		return $this->unique($slug, $tableModel, $config);
	}

	/**
	 * Create a unique string for a table field. You may optionally limit the number of characters.
	 *
	 * @param  string  $string
	 * @param  mixed   $tableModel
	 * @param  array   $config
	 * @return string
	 */
	public function unique($string, $tableModel, $config = [])
	{
		$defaultConfig = [
			'field'          => 'name',
			'model'          => config('format.defaults.unique.model'),
			'ignoreId'       => null,
			'filename'       => false,
			'charLimit'      => config('format.defaults.unique.char_limit'),
			'softDelete'     => config('format.defaults.unique.soft_delete'),
			'matchingValues' => [],
		];

		$config = array_merge($defaultConfig, $config);

		$uniqueFound = false;

		while (!$uniqueFound)
		{
			$result = $this->stringIsUnique($string, $tableModel, $config);

			if ($result->unique)
			{
				$string = $result->string;

				$uniqueFound = true;
			}

			if (!isset($config['suffix']))
				$config['suffix'] = 2;
			else
				$config['suffix'] ++;
		}

		return $string;
	}

	/**
	 * Check if a particular string is unique for the specified table or model.
	 *
	 * @param  string  $string
	 * @param  mixed   $tableModel
	 * @param  array   $config
	 * @return object
	 */
	public function stringIsUnique($string, $tableModel, $config = [])
	{
		$defaultConfig = [
			'field'          => 'name',
			'model'          => config('format.defaults.unique.model'),
			'ignoreId'       => null,
			'filename'       => false,
			'charLimit'      => config('format.defaults.unique.char_limit'),
			'softDelete'     => config('format.defaults.unique.soft_delete'),
			'suffix'         => null,
			'matchingValues' => [],
		];

		$config = array_merge($defaultConfig, $config);

		if ($config['model'])
			$existingRecord = $tableModel::query();
		else
			$existingRecord = DB::table($tableModel);

		$suffix = !is_null($config['suffix']) ? '-'.$config['suffix'] : "";

		// enforce character limit with fewer characters to make room for suffix
		if ($suffix != "" && $config['charLimit'])
		{
			$suffix = (int) $suffix;

			if ($suffix < 10)
			{
				$config['charLimit'] -= 2;
			}
			else if ($suffix >= 10 && $suffix < 100)
			{
				$config['charLimit'] -= 2;
			}
			else if ($suffix >= 100 && $suffix < 1000)
			{
				$config['charLimit'] -= 3;
			}
			else if ($suffix >= 1000)
			{
				$config['charLimit'] -= 4;
			}
		}

		if ($config['charLimit'])
			$string = substr($string, 0, $config['charLimit']);

		if ($config['filename'])
		{
			$extension = File::extension($string);

			$string = str_replace('.'.File::extension($string), '', $string).$suffix.'.'.$extension;
		}
		else
		{
			$string .= $suffix;
		}

		$existingRecord->where($config['field'], $string);

		if ($config['ignoreId'])
			$existingRecord->where('id', '!=', $config['ignoreId']);

		if ($config['softDelete'])
		{
			$config['matchingValues']['deleted_at'] = null;
		}

		if (!empty($config['matchingValues']))
		{
			$operators = [
				'!=',
				'>',
				'<',
				'>=',
				'<=',
			];

			foreach ($config['matchingValues'] as $matchingField => $matchingValue)
			{
				if (is_array($matchingValue))
				{
					$existingRecord->whereIn($matchingField, $matchingValue);
				}
				else
				{
					$operator = "=";

					foreach ($operators as $operatorChecked)
					{
						if (substr($matchingValue, 0, strlen($operatorChecked)) == $operatorChecked)
						{
							$operator = $operatorChecked;

							$matchingValue = ltrim(substr($matchingValue, strlen($operator)));
						}
					}

					$existingRecord->where($matchingField, $operator, $matchingValue);
				}
			}
		}

		$count = $existingRecord->count();

		return (object) [
			'string' => $string,
			'unique' => !$count,
		];
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
		$date = is_null($date) ? date('Y-m-01') : date('Y-m-01', strtotime($date));

		if ($format)
			$date = $this->date($date, $format);

		return $date;
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
		if (is_null($date))
		{
			$date = date('Y-m-d');
		}
		else
		{
			$date = date('Y-m-d', strtotime($date));

			$originalMonth = substr($date, 5, 2);
		}

		$year   = substr($date, 0, 4);
		$month  = substr($date, 5, 2);
		$day    = substr($date, 8, 2);
		$result = "";

		// prevent invalid dates having wrong month assigned (June 31 = July, etc...)
		if (isset($originalMonth) && $month != $originalMonth)
			$month = $originalMonth;

		if (in_array($month, ['01', '03', '05', '07', '08', '10', '12']))
		{
			$lastDay = 31;
		}
		else if (in_array($month, ['04', '06', '09', '11']))
		{
			$lastDay = 30;
		}
		else if ($month == "02")
		{
			if (($year/4) == round($year/4))
			{
				if (($year/100) == round($year/100))
				{
					if (($year/400) == round($year/400))
						$lastDay = 29;
					else
						$lastDay = 28;
				}
				else
				{
					$lastDay = 29;
				}
			}
			else
			{
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
	 * @param  mixed   $timezone
	 * @return string
	 */
	public function date($date = null, $format = null, $adjust = null, $timezone = null)
	{
		// allow parameters to be passed as associative array
		if (is_array($date))
		{
			$data = $date;

			if (isset($data['date']))
				$date = $data['date'];
			else
				$date = null;

			if (isset($data['format']))
				$format = $data['format'];

			if (isset($data['adjust']))
				$adjust = $data['adjust'];

			if (isset($data['timezone']))
				$timezone = $data['timezone'];
		}
		else
		{
			// allow parameters to be passed as associative array for second parameter as well
			if (is_array($format))
			{
				$data = $format;

				if (isset($data['format']))
					$format = $data['format'];
				else
					$format = null;

				if (isset($data['adjust']))
					$adjust = $data['adjust'];

				if (isset($data['timezone']))
					$timezone = $data['timezone'];
			}
		}

		if (is_null($date))
			$date = date('Y-m-d H:i:s');

		if (is_null($date) || trim($date) == "" || $date == "0000-00-00" || $date == "0000-00-00 00:00:00")
			return null;

		if (is_null($format))
			$format = config('format.defaults.date');

		if (!is_null($adjust))
			$date .= ' '.$adjust;

		$time = strtotime($date.' '.$adjust);

		if (is_null($timezone))
			$timezone = config('format.defaults.timezone');

		if (!is_null($timezone) && $timezone !== false)
		{
			$date = new DateTime;

			$date->setTimestamp($time);
			$date->setTimezone(new DateTimeZone($timezone));

			return $date->format($format);
		}
		else
		{
			return date($format, $time);
		}
	}

	/**
	 * Format a date-time.
	 *
	 * @param  mixed   $date
	 * @param  string  $format
	 * @param  string  $adjust
	 * @param  mixed   $timezone
	 * @return string
	 */
	public function dateTime($date = null, $format = null, $adjust = null, $timezone = null)
	{
		if (is_null($format))
			$format = config('format.defaults.datetime');

		return $this->date($date, $format, $adjust, $timezone);
	}

	/**
	 * Format a time.
	 *
	 * @param  mixed   $date
	 * @param  string  $format
	 * @param  string  $adjust
	 * @param  mixed   $timezone
	 * @return string
	 */
	public function time($date = null, $format = null, $adjust = null, $timezone = null)
	{
		if (is_null($format))
			$format = config('format.defaults.time');

		return $this->date($date, $format, $adjust, $timezone);
	}

	/**
	 * Convert a date to a time interval. Used in conjuction with dateToIntervalStr() to get dates like "33 minutes ago"
	 *
	 * @param  string  $dateStart
	 * @param  mixed   $dateEnd
	 * @return string
	 */
	public function dateToInterval($dateStart, $dateEnd = null)
	{
		if (!is_int($dateStart))
			$dateStart = strtotime($dateStart);

		if (is_null($dateEnd)) {
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
	 * Convert a date to a time interval. Used in conjuction with dateToIntervalStr() to get dates like "33 minutes ago".
	 *
	 * @param  string  $dateStart
	 * @param  mixed   $dateEnd
	 * @return string
	 */
	public function dateToIntervalStr($dateStart, $dateEnd = null)
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
		$string = trim($string);

		$paragraphCloseOpen = "</p><p>";
		$replacementStrings = [
			"\r\n\r\n",
			"\n\n",
			"\r\n",
			"\n",
		];

		foreach ($replacementStrings as $replacementString)
		{
			$string = str_replace($replacementString, $paragraphCloseOpen, $string);
		}

		return str_replace('<p></p>', '', $string);
	}

	/**
	 * Separate a string with new line characters into paragraphs.
	 *
	 * @param  string  $string
	 * @param  mixed   $charLimit
	 * @return string
	 */
	public function paragraphs($string, $charLimit = null)
	{
		if ($charLimit)
			$string = $this->charLimit($string, $charLimit);

		return '<p>'.$this->nl2p($string).'</p>';
	}

	/**
	 * Limit a string to a number of characters.
	 *
	 * @param  string  $string
	 * @param  mixed   $config
	 * @param  array   $alternateConfig
	 * @return string
	 */
	public function charLimit($string, $config = [], $alternateConfig = [])
	{
		if (is_integer($config))
			$alternateConfig['charLimit'] = true;
		else
			$config['charLimit'] = true;

		return $this->stringLimit($string, $config, $alternateConfig);
	}

	/**
	 * Limit a string to a number of words.
	 *
	 * @param  string  $string
	 * @param  mixed   $config
	 * @param  array   $alternateConfig
	 * @return string
	 */
	public function wordLimit($string, $config = [], $alternateConfig = [])
	{
		if (is_integer($config))
			$alternateConfig['wordLimit'] = true;
		else
			$config['wordLimit'] = true;

		return $this->stringLimit($string, $config, $alternateConfig);
	}

	/**
	 * Limit a string.
	 *
	 * @param  string  $string
	 * @param  mixed   $config
	 * @param  array   $alternateConfig
	 * @return string
	 */
	public function stringLimit($string, $config = [], $alternateConfig = [])
	{
		if (is_integer($config))
		{
			$limitConfig = $config;
			$config      = $alternateConfig;

			if ((!isset($config['wordLimit']) || !$config['wordLimit']) && (!isset($config['charLimit']) || !$config['charLimit']))
				$config['wordLimit'] = true;

			if (!isset($config['wordLimit']))
				$config['wordLimit'] = false;

			if ($config['wordLimit'])
				$config['words'] = $limitConfig;
			else
				$config['chars'] = $limitConfig;
		}
		else
		{
			if ((!isset($config['wordLimit']) || !$config['wordLimit']) && (!isset($config['charLimit']) || !$config['charLimit']))
				$config['wordLimit'] = true;
			else
				$config['wordLimit'] = false;
		}

		$config = array_merge($this->camelizeKeys(config('format.defaults.string_limit')), $config);

		if ($config['trim'])
			$string = trim($string);

		$stripTags = true;

		if ($config['html'])
		{
			$regExp = '/<([\w]+)[^>]*\>(.*?)<\/\1>|<([\w]+)[^>]*\/\>/';

			if ($config['wordLimit'])
			{
				$wordsReal       = explode(' ', $string);
				$exceededHtmlMax = count($wordsReal) > $config['maxWordsHtml'];

				if (!$exceededHtmlMax)
				{
					$stripTags = false;

					$spaceReplacer = "[[]]";

					// replace spaces in tags to make them behave as a single word for the word count
					preg_match_all($regExp, $string, $tags);

					if (isset($tags[0]) && !empty($tags[0]))
					{
						for ($t = 0; $t < count($tags[0]); $t++)
						{
							$string = str_replace($tags[0][$t], str_replace(' ', $spaceReplacer, $tags[0][$t]), $string);
						}
					}
				}
			}
			else
			{
				$charactersReal  = strlen($string);
				$exceededHtmlMax = count($charactersReal) > $config['maxCharsHtml'];

				if (!$exceededHtmlMax)
				{
					$stripTags = false;

					$tagsArray = [];

					// replace tags with placeholders
					preg_match_all($regExp, $string, $tags);

					if (isset($tags[0]) && !empty($tags[0]))
					{
						for ($t = 0; $t < count($tags[0]); $t++)
						{
							$tagsArray[] = $tags[0][$t];

							$string = str_replace($tags[0][$t], '[[T'.sprintf('%03d', count($tagsArray)).']]', $string);
						}
					}
				}
			}
		}

		if ($stripTags)
			$string = strip_tags($string);

		$formattedString = "";

		if ($config['wordLimit'])
		{
			$wordsAdded = 0;

			$words = explode(' ', $string);

			foreach ($words as $word)
			{
				if ($wordsAdded < $config['words'])
				{
					if ($formattedString != "")
						$formattedString .= " ";

					$formattedString .= $word;

					$wordsAdded ++;
				}
			}

			if ($config['html'] && !$exceededHtmlMax)
				$formattedString = str_replace($spaceReplacer, ' ', $formattedString);

			$exceededLimit = count($words) > $config['words'];
		}
		else
		{
			$charsAdded = 0;

			$exceededLimit = false;

			$s = $string;
			for ($c = 0; $c < strlen($s); $c++)
			{
				if (!$exceededLimit)
				{
					$possibleTagPlaceholderStart = isset($s[$c]) && $s[$c] == "[" && isset($s[$c+7]);

					if (isset($s[$c]) && ($charsAdded < $config['chars'] || $possibleTagPlaceholderStart))
					{
						$char = $s[$c];

						if ($possibleTagPlaceholderStart)
						{
							$n = $s[$c+3] . $s[$c+4] . $s[$c+5];

							if ($s[$c+1] == "[" && $s[$c+2] == "T" && is_numeric($n) && $s[$c+6] == "]" && $s[$c+7] == "]")
							{
								$n   = (int) $n;
								$tag = $tagsArray[$n-1];

								$charsInTag = strlen(strip_tags($tag));

								if (strlen($formattedString) + $charsInTag <= $config['chars'])
								{
									$formattedString .= $tag;

									$charsAdded += $charsInTag;
								}
								else
								{
									$exceededLimit = true;
								}

								$c += 7;
							}
						}
						else
						{
							$formattedString .= $char;

							$charsAdded ++;
						}
					}
					else
					{
						if (isset($s[$c]) && $charsAdded >= $config['chars'])
							$exceededLimit = true;
					}
				}
			}
		}

		if ($exceededLimit && !is_null($config['exceededText']) && $config['exceededText'] !== false)
		{
			$formattedString = trim($formattedString);

			$exceededText = $config['exceededText'];

			if (!is_null($config['exceededLinkUrl']) && $config['exceededLinkUrl'] !== false)
			{
				$exceededLinkClass = "";
				if ($config['exceededLinkClass'] != "" && !is_null($config['exceededLinkClass']) && $config['exceededLinkClass'] !== false)
				{
					$exceededLinkClass = ' class="'.$config['exceededLinkClass'].'"';
				}

				$exceededText = '<a href="'.$config['exceededLinkUrl'].'"'.$exceededLinkClass.'>'.$exceededText.'</a>';
			}
			else
			{
				$exceededText = '<span class="exceeded-limit">'.$exceededText.'</span>';
			}

			$formattedString .= $exceededText;
		}

		return $formattedString;
	}

	/**
	 * Translate the given message and make it lowercase (unless it appears to be an acronym).
	 *
	 * @param  string  $id
	 * @param  array   $replace
	 * @param  string  $locale
	 * @return \Symfony\Component\Translation\TranslatorInterface|string
	 */
	public function transL($id = null, array $replace = [], $locale = null)
	{
		$trans = trans($id, $replace, $locale);

		if ($trans != strtoupper($trans))
			$trans = strtolower($trans);

		return $trans;
	}

	/**
	 * Translates the given message based on a count and make it lowercase (unless it appears to be an acronym).
	 *
	 * @param  string  $id
	 * @param  int|array|\Countable  $number
	 * @param  array   $replace
	 * @param  string  $locale
	 * @return string
	 */
	public function transChoiceL($id, $number = 1, array $replace = [], $locale = null)
	{
		$trans = trans_choice($id, $number, $replace, $locale);

		if ($trans != strtoupper($trans))
			$trans = strtolower($trans);

		return $trans;
	}

	/**
	 * Translate the given message and prepend with "a" or "an" (if language is English or exceeds 2 letter language code).
	 *
	 * @param  string  $id
	 * @param  mixed   $replace
	 * @param  boolean $lower
	 * @param  string  $locale
	 * @return \Symfony\Component\Translation\TranslatorInterface|string
	 */
	public function transA($id = null, $replace = [], $lower = false, $locale = null)
	{
		if (is_bool($replace)) // allow "replace" to be skipped in function arguments
		{
			$lower   = $replace;
			$replace = [];
		}

		$trans = trans($id, $replace, $locale);

		$locale = !is_null($locale) ? $locale : Lang::locale();

		$acronym = $trans == strtoupper($trans);

		if (($locale == "en" || strlen($locale) > 2))
			$trans = $this->a($trans);

		if ($lower && !$acronym)
			$trans = strtolower($trans);

		return $trans;
	}

	/**
	 * Translates the given message based on a count and prepend with "a" or "an" (if language is English or exceeds 2 letter language code).
	 *
	 * @param  string  $id
	 * @param  int|array|\Countable  $number
	 * @param  mixed   $replace
	 * @param  boolean $lower
	 * @param  string  $locale
	 * @return string
	 */
	public function transChoiceA($id, $number = 1, $replace = [], $lower = false, $locale = null)
	{
		if (is_bool($replace)) // allow "replace" to be skipped in function arguments
		{
			$lower   = $replace;
			$replace = [];
		}

		$trans = trans_choice($id, $number, $replace, $locale);

		$locale = !is_null($locale) ? $locale : Lang::locale();

		$acronym = $trans == strtoupper($trans);

		if ($number == 1)
		{
			if (($locale == "en" || strlen($locale) > 2))
				$trans = $this->a($trans);
		}
		else // if we're not prepending "a" or "an", prepend the number
		{
			$trans = $number.' '.$trans;
		}

		if ($lower && !$acronym)
			$trans = strtolower($trans);

		return $trans;
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
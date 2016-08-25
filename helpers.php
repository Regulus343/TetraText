<?php

if (!function_exists('numeric'))
{
	/**
	 * Remove all non-numeric characters from a string.
	 *
	 * @param  float   $value
	 * @param  boolean $allowDecimal
	 * @param  boolean $allowNegative
	 * @return string
	 */
	function numeric($value, $allowDecimal = true, $allowNegative = false)
	{
		return \Regulus\TetraText\Facade::numeric($value, $allowDecimal, $allowNegative);
	}
}

if (!function_exists('money'))
{
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
	function money($value, $prefix = '$', $allowNegative = true, $thousandsSeparator = ',')
	{
		return \Regulus\TetraText\Facade::money($value, $prefix, $allowNegative, $thousandsSeparator);
	}
}

if (!function_exists('percent'))
{
	/**
	 * Calculate the percentage of a value to a total. Avoids division by zero error.
	 *
	 * @param  float   $value
	 * @param  float   $total
	 * @param  integer $decimals
	 * @param  boolean $returnNumeric
	 * @return mixed
	 */
	function percent($value = 0, $total = 0, $decimals = 1, $returnNumeric = false)
	{
		return \Regulus\TetraText\Facade::percent($value, $total, $decimals, $returnNumeric);
	}
}

if (!function_exists('phone'))
{
	/**
	 * Format a Canadian/American phone number.
	 *
	 * @param  string  $number
	 * @param  array   $config
	 * @return string
	 */
	function phone($number, $config = [])
	{
		return \Regulus\TetraText\Facade::phone($number, $config);
	}
}

if (!function_exists('postal_code'))
{
	/**
	 * Format a Canadian postal code.
	 *
	 * @param  string  $postalCode
	 * @param  boolean $separateWithSpace
	 * @return string
	 */
	function postal_code($postalCode, $separateWithSpace = true)
	{
		return \Regulus\TetraText\Facade::postalCode($postalCode, $separateWithSpace);
	}
}

if (!function_exists('bool_to_str'))
{
	/**
	 * Turn a boolean value into a string. Some examples of types are "Yes/No", "Yes", "On/Off", and "Active/Inactive".
	 *
	 * @param  boolean $value
	 * @param  mixed   $options
	 * @return string
	 */
	function bool_to_str($value, $options = null)
	{
		return \Regulus\TetraText\Facade::boolToStr($value, $options);
	}
}

if (!function_exists('get_selected_from_array'))
{
	/**
	 * Get only items (keys) from an array if their values are set to true. If a simple array is used, the function will simply remove duplicate values.
	 *
	 * @param  array   $array
	 * @return array
	 */
	function get_selected_from_array($array)
	{
		return \Regulus\TetraText\Facade::getSelectedFromArray($array);
	}
}

if (!function_exists('camelize_keys'))
{
	/**
	 * Make the keys of an array or object camel case.
	 *
	 * @param  array   $array
	 * @return array
	 */
	function camelize_keys($array)
	{
		return \Regulus\TetraText\Facade::camelizeKeys($array);
	}
}

if (!function_exists('entities'))
{
	/**
	 * Convert HTML characters to entities.
	 *
	 * The encoding specified in the config file will be used.
	 *
	 * @param  string  $string
	 * @return string
	 */
	function entities($string)
	{
		return \Regulus\TetraText\Facade::entities($string);
	}
}

if (!function_exists('slug'))
{
	/**
	 * Create a URI slug from a string.
	 *
	 * @param  string  $string
	 * @param  mixed   $charLimit
	 * @return string
	 */
	function slug($string, $charLimit = false)
	{
		return \Regulus\TetraText\Facade::slug($string, $charLimit);
	}
}

if (!function_exists('nl2p'))
{
	/**
	 * Separate a string with new line characters into paragraphs.
	 *
	 * @param  string  $string
	 * @return string
	 */
	function nl2p($string)
	{
		return \Regulus\TetraText\Facade::nl2p($string);
	}
}

if (!function_exists('paragraphs'))
{
	/**
	 * Separate a string with new line characters into paragraphs.
	 *
	 * @param  string  $string
	 * @param  mixed   $charLimit
	 * @return string
	 */
	function paragraphs($string, $charLimit = null)
	{
		return \Regulus\TetraText\Facade::paragraphs($string, $charLimit);
	}
}

if (!function_exists('char_limit'))
{
	/**
	 * Limit a string to a number of characters.
	 *
	 * @param  string  $string
	 * @param  mixed   $config
	 * @param  array   $alternateConfig
	 * @return string
	 */
	function char_limit($string, $config = [], $alternateConfig = [])
	{
		return \Regulus\TetraText\Facade::charLimit($string, $config, $alternateConfig);
	}
}

if (!function_exists('word_limit'))
{
	/**
	 * Limit a string to a number of words.
	 *
	 * @param  string  $string
	 * @param  mixed   $config
	 * @param  array   $alternateConfig
	 * @return string
	 */
	function word_limit($string, $config = [], $alternateConfig = [])
	{
		return \Regulus\TetraText\Facade::wordLimit($string, $config, $alternateConfig);
	}
}

if (!function_exists('trans_l'))
{
	/**
	 * Translate the given message and make it lowercase (unless it appears to be an acronym).
	 *
	 * @param  string  $id
	 * @param  array   $parameters
	 * @param  string  $domain
	 * @param  string  $locale
	 * @return \Symfony\Component\Translation\TranslatorInterface|string
	 */
	function trans_l($id = null, array $parameters = [], $domain = 'messages', $locale = null)
	{
		return \Regulus\TetraText\Facade::transL($id, $parameters, $domain, $locale);
	}
}

if (!function_exists('trans_choice_l'))
{
	/**
	 * Translates the given message based on a count and make it lowercase (unless it appears to be an acronym).
	 *
	 * @param  string  $id
	 * @param  int|array|\Countable  $number
	 * @param  array   $parameters
	 * @param  string  $domain
	 * @param  string  $locale
	 * @return string
	 */
	function trans_choice_l($id, $number = 1, array $parameters = [], $domain = 'messages', $locale = null)
	{
		return \Regulus\TetraText\Facade::transChoiceL($id, $number, $parameters, $domain, $locale);
	}
}

if (!function_exists('trans_a'))
{
	/**
	 * Translate the given message and prepend with "a" or "an".
	 *
	 * @param  string  $id
	 * @param  mixed   $parameters
	 * @param  boolean $lower
	 * @param  string  $domain
	 * @param  string  $locale
	 * @return \Symfony\Component\Translation\TranslatorInterface|string
	 */
	function trans_a($id = null, $parameters = [], $lower = false, $domain = 'messages', $locale = null)
	{
		return \Regulus\TetraText\Facade::transA($id, $parameters, $lower, $domain, $locale);
	}
}

if (!function_exists('trans_choice_a'))
{
	/**
	 * Translates the given message based on a count and prepend with "a" or "an".
	 *
	 * @param  string  $id
	 * @param  int|array|\Countable  $number
	 * @param  mixed   $parameters
	 * @param  boolean $lower
	 * @param  string  $domain
	 * @param  string  $locale
	 * @return string
	 */
	function trans_choice_a($id, $number = 1, $parameters = [], $lower = false, $domain = 'messages', $locale = null)
	{
		return \Regulus\TetraText\Facade::transChoiceA($id, $number, $parameters, $lower, $domain, $locale);
	}
}
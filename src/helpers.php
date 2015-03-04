<?php

use Regulus\TetraText\Facade as Format;

if ( ! function_exists('numeric'))
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
		return Format::numeric($value, $allowDecimal, $allowNegative);
	}
}

if ( ! function_exists('money'))
{
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
	function money($value, $prefix = '$', $allowNegative = true, $thousandsSeparator = ',')
	{
		return Format::money($value, $prefix, $allowNegative, $thousandsSeparator);
	}
}

if ( ! function_exists('percent'))
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
		return Format::percent($value, $total, $decimals, $returnNumeric);
	}
}

if ( ! function_exists('phone'))
{
	/**
	 * Format a Canadian/American phone number.
	 *
	 * @param  string  $phoneNumber
	 * @param  integer $digits
	 * @param  string  $separator
	 * @param  boolean $areaCodeBrackets
	 * @return string
	 */
	function phone($phoneNumber, $digits = null, $separator = null, $areaCodeBrackets = null)
	{
		return Format::phone($phoneNumber, $digits, $separator, $areaCodeBrackets);
	}
}

if ( ! function_exists('postal_code'))
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
		return Format::postalCode($postalCode, $separateWithSpace);
	}
}

if ( ! function_exists('bool_to_str'))
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
		return Format::boolToStr($value, $options);
	}
}

if ( ! function_exists('nl2p'))
{
	/**
	 * Separate a string with new line characters into paragraphs.
	 *
	 * @param  string  $string
	 * @return string
	 */
	function nl2p($string)
	{
		return Format::nl2p($string);
	}
}
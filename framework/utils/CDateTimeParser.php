<?php
/**
 * CDateTimeParser class file
 *
 * @author Wei Zhuo <weizhuo[at]gamil[dot]com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Tomasz Suchanek <tomasz[dot]suchanek[at]gmail[dot]com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDateTimeParser converts a date/time string to a UNIX timestamp according to the specified pattern.
 *
 * The following pattern characters are recognized:
 * <pre>
 * Pattern |      Description
 * ----------------------------------------------------
 * d       | Day of month 1 to 31, no padding
 * dd      | Day of month 01 to 31, zero leading
 * M       | Month digit 1 to 12, no padding
 * MM      | Month digit 01 to 12, zero leading
 * MMM     | Short textual representation of month (available since 1.1.11; locale aware since 1.1.13)
 * MMMM    | Textual representation of month (available since 1.1.13; locale aware)
 * yy      | 2 year digit, e.g., 96, 05
 * yyyy    | 4 year digit, e.g., 2005
 * h       | Hour in 0 to 23, no padding
 * hh      | Hour in 00 to 23, zero leading
 * H       | Hour in 0 to 23, no padding
 * HH      | Hour in 00 to 23, zero leading
 * m       | Minutes in 0 to 59, no padding
 * mm      | Minutes in 00 to 59, zero leading
 * s       | Seconds in 0 to 59, no padding
 * ss      | Seconds in 00 to 59, zero leading
 * a       | AM or PM, case-insensitive (since version 1.1.5)
 * ?       | matches any character (wildcard) (since version 1.1.11)
 * ----------------------------------------------------
 * </pre>
 * All other characters must appear in the date string at the corresponding positions.
 *
 * For example, to parse a date string '21/10/2008', use the following:
 * <pre>
 * $timestamp=CDateTimeParser::parse('21/10/2008','dd/MM/yyyy');
 * </pre>
 *
 * To format a timestamp to a date string, please use {@link CDateFormatter}.
 *
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.utils
 * @since 1.0
 */
class CDateTimeParser
{
	/**
	 * Converts a date string to a timestamp.
	 * @param string $value the date string to be parsed
	 * @param string $pattern the pattern that the date string is following
	 * @param array $defaults the default values for year, month, day, hour, minute and second.
	 * The default values will be used in case when the pattern doesn't specify the
	 * corresponding fields. For example, if the pattern is 'MM/dd/yyyy' and this
	 * parameter is array('minute'=>0, 'second'=>0), then the actual minute and second
	 * for the parsing result will take value 0, while the actual hour value will be
	 * the current hour obtained by date('H'). This parameter has been available since version 1.1.5.
	 * @return integer timestamp for the date string. False if parsing fails.
	 */
	public static function parse($value,$pattern='MM/dd/yyyy',$defaults=array())
	{
		$tokens=self::tokenize($pattern);
		$i=0;
		$n=mb_strlen($value);
		foreach($tokens as $token)
		{
			switch($token)
			{
				case 'yyyy':
				{
					if(($year=self::parseInteger($value,$i,4,4))===false)
						return false;
					$i+=4;
					break;
				}
				case 'yy':
				{
					if(($year=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=mb_strlen($year);
					break;
				}
				case 'MMMM':
				{
					$monthName='';
					if(($month=self::parseMonth($value,$i,'wide',$monthName))===false)
						return false;
					$i+=mb_strlen($monthName);
					break;
				}
				case 'MMM':
				{
					$monthName='';
					if(($month=self::parseMonth($value,$i,'abbreviated',$monthName))===false)
						return false;
					$i+=mb_strlen($monthName);
					break;
				}
				case 'MM':
				{
					if(($month=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 'M':
				{
					if(($month=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=mb_strlen($month);
					break;
				}
				case 'dd':
				{
					if(($day=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 'd':
				{
					if(($day=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=mb_strlen($day);
					break;
				}
				case 'h':
				case 'H':
				{
					if(($hour=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=mb_strlen($hour);
					break;
				}
				case 'hh':
				case 'HH':
				{
					if(($hour=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 'm':
				{
					if(($minute=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=mb_strlen($minute);
					break;
				}
				case 'mm':
				{
					if(($minute=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 's':
				{
					if(($second=self::parseInteger($value,$i,1,2))===false)
						return false;
					$i+=mb_strlen($second);
					break;
				}
				case 'ss':
				{
					if(($second=self::parseInteger($value,$i,2,2))===false)
						return false;
					$i+=2;
					break;
				}
				case 'a':
				{
				    if(($ampm=self::parseAmPm($value,$i))===false)
				        return false;
				    if(isset($hour))
				    {
				    	if($hour==12 && $ampm==='am')
				    		$hour=0;
				    	else if($hour<12 && $ampm==='pm')
				    		$hour+=12;
				    }
					$i+=2;
					break;
				}
				default:
				{
					$tn=mb_strlen($token);
					if($i>=$n || ($token{0}!='?' && mb_substr($value,$i,$tn)!==$token))
						return false;
					$i+=$tn;
					break;
				}
			}
		}
		if($i<$n)
			return false;

		if(!isset($year))
			$year=isset($defaults['year']) ? $defaults['year'] : date('Y');
		if(!isset($month))
			$month=isset($defaults['month']) ? $defaults['month'] : date('n');
		if(!isset($day))
			$day=isset($defaults['day']) ? $defaults['day'] : date('j');

		if(mb_strlen($year)===2)
		{
			if($year>=70)
				$year+=1900;
			else
				$year+=2000;
		}
		$year=(int)$year;
		$month=(int)$month;
		$day=(int)$day;

		if(
			!isset($hour) && !isset($minute) && !isset($second)
			&& !isset($defaults['hour']) && !isset($defaults['minute']) && !isset($defaults['second'])
		)
			$hour=$minute=$second=0;
		else
		{
			if(!isset($hour))
				$hour=isset($defaults['hour']) ? $defaults['hour'] : date('H');
			if(!isset($minute))
				$minute=isset($defaults['minute']) ? $defaults['minute'] : date('i');
			if(!isset($second))
				$second=isset($defaults['second']) ? $defaults['second'] : date('s');
			$hour=(int)$hour;
			$minute=(int)$minute;
			$second=(int)$second;
		}

		if(CTimestamp::isValidDate($year,$month,$day) && CTimestamp::isValidTime($hour,$minute,$second))
			return CTimestamp::getTimestamp($hour,$minute,$second,$month,$day,$year);
		else
			return false;
	}

	/*
	 * @param string $pattern the pattern that the date string is following
	 */
	private static function tokenize($pattern)
	{
		if(!($n=mb_strlen($pattern)))
			return array();
		$tokens=array();
		for($c0=$pattern[0],$start=0,$i=1;$i<$n;++$i)
		{
			if(($c=$pattern[$i])!==$c0)
			{
				$tokens[]=mb_substr($pattern,$start,$i-$start);
				$c0=$c;
				$start=$i;
			}
		}
		$tokens[]=mb_substr($pattern,$start,$n-$start);
		return $tokens;
	}

	/**
	 * @param string $value the date string to be parsed
	 * @param integer $offset starting offset
	 * @param integer $minLength minimum length
	 * @param integer $maxLength maximum length
	 */
	protected static function parseInteger($value,$offset,$minLength,$maxLength)
	{
		for($len=$maxLength;$len>=$minLength;--$len)
		{
			$v=mb_substr($value,$offset,$len);
			if(ctype_digit($v) && mb_strlen($v)>=$minLength)
				return $v;
		}
		return false;
	}

	/**
	 * @param string $value the date string to be parsed
	 * @param integer $offset starting offset
	 */
	protected static function parseAmPm($value, $offset)
	{
		$v=mb_strtolower(mb_substr($value,$offset,2));
		return $v==='am' || $v==='pm' ? $v : false;
	}

	/**
	 * @param string $value the date string to be parsed.
	 * @param integer $offset starting offset.
	 * @param string $width month name width. It can be 'wide', 'abbreviated' or 'narrow'.
	 * @param string $monthName extracted month name. Passed by reference.
	 * @since 1.1.13
	 */
	protected static function parseMonth($value, $offset, $width, &$monthName)
	{
		$valueLength=mb_strlen($value);
		for($len=1; ; $len++)
		{
			$monthName=mb_substr($value, $offset, $len);
			if(!preg_match('/^\p{L}+$/u', $monthName)) // unicode aware replacement for ctype_alpha($monthName)
			{
				$monthName=mb_substr($monthName, 0, -1);
				break;
			}
			if($offset+$len==$valueLength)
				break;
		}
		$monthName=mb_strtolower($monthName);

		$monthNames=Yii::app()->getLocale()->getMonthNames($width, false);
		foreach($monthNames as $k=>$v)
			$monthNames[$k]=trim(mb_strtolower($v), '.');

		$monthNamesStandAlone=Yii::app()->getLocale()->getMonthNames($width, true);
		foreach($monthNamesStandAlone as $k=>$v)
			$monthNamesStandAlone[$k]=trim(mb_strtolower($v), '.');

		if(($v=array_search($monthName, $monthNames))===false && ($v=array_search($monthName, $monthNamesStandAlone))===false)
			return false;
		return $v;
	}
}

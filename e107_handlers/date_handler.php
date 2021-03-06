<?php
/*
 * e107 website system
 * 
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 * 
 * $Source: /cvs_backup/e107_0.8/e107_handlers/date_handler.php,v $
 * $Revision$
 * $Date$
 * $Author$
 * 
*/
if (!defined('e107_INIT')) { exit; }

include_lan(e_LANGUAGEDIR.e_LANGUAGE."/lan_date.php");

class convert
{
	
	function __construct()
	{
		
		
	}
	/**
	 * Convert datestamp to human readable date.
	 * System time offset is considered.
	 * 
	 * @param integer $datestamp unix stamp
	 * @param string $mask [optional] long|short|forum or any strftime() valid string
	 * 
	 * @return string parsed date
	 */
	function convert_date($datestamp, $mask = '') {
		if(empty($mask))
		{
			$mask = 'long';
		}
		
		switch($mask)
		{
			case 'long':
				$mask = e107::getPref('longdate');
			//	$datestamp += TIMEOFFSET;
			break;
			
			case 'short':
				$mask = e107::getPref('shortdate');
			//	$datestamp += TIMEOFFSET;
			break;
			
			case 'input': 
			case 'inputdate': 
				$mask = e107::getPref('inputdate', '%d/%m/%Y %H:%M');
				// $mask .= " ".e107::getPref('inputtime', '%H:%M');
			break;
			
			case 'inputdatetime': 
				$mask = e107::getPref('inputdate', '%d/%m/%Y %H:%M');
				$mask .= " ".e107::getPref('inputtime', '%H:%M');
			break;
			
			case 'inputtime': 
				$mask .= e107::getPref('inputtime', '%H:%M');
			break;

			case 'forum': // DEPRECATED - temporary here from BC reasons only
		//	default: 
				//BC - old 'forum' call
				if(strpos($mask, '%') === FALSE)
				{
					$mask = e107::getPref('forumdate');
				}
			//	$datestamp += TIMEOFFSET;
			break;
			
			default:
				if(strpos($mask, '%') === FALSE)
				{
					$mask = $this->toMask($mask,true);
				}				
			break;
		}
		
		// 

		return strftime($mask, $datestamp);
	}



	/**
	 * Converts between unix timestamp and human-readable date-time OR vice-versa. (auto-detected)
	 * @param string $string unix timestamp OR human-readable date-time. 
	 * @param string $mask (optional) long | short | input
	 */
	function convert($string=null, $mask = 'inputdate')
	{
		if($string == null) return false;
		return is_integer($string) ? $this->convert_date($string, $mask) : $this->toTime($string, $mask);	
	}
	
	
	
	/** 
	 * Converts to new date-mask format or vice-versa when $legacy is TRUE 
	 */
	function toMask($mask, $legacy = false)
	{
		$convert = array(
			'%Y'	=> 'yyyy',	// jquery-ui docs say 'yy' but yy produces '13' instead of '2013'
			'%d'	=> 'dd',
			'%m'	=> 'mm',		
			'%B'	=> 'MM', 	// Full month name, based on the locale
			'%A'	=> 'DD', 	// A full textual representation of the day
	
			'%y'	=> 'y',
			'%a'	=> 'D', 	// An abbreviated textual representation of the day
			'%b'	=> 'M', 	// Abbreviated month name, based on the locale
			'%h'	=> 'M', 	// Abbreviated month name, based on the locale (an alias of %b)
			
			'%l'	=> 'h',		// 12 hour format - no leading zero
			'%I'	=> 'hh',	// 12 hour format - leading zero
			'%H'	=> 'hh',	// 24 hour format - leading zero
			'%M'	=> 'mm',
			'%S'	=> 'ss',
			'%p'	=> 'TT',	//	%p	UPPER-CASE 'AM' or 'PM' based on the given time
			'%P'	=> 'tt',		// %P	lower-case 'am' or 'pm' based on the given time
			'%T' 	=> 'hh:mm:ss',
			'%r' 	=> "hh:mmm:ss TT" // 12 hour format
		
		);
			
		$s = array_keys($convert);
		$r = array_values($convert);	
		
		if(strpos($mask, '%') === FALSE && $legacy == TRUE)
		{
			return str_replace($r, $s,$mask);
		}
		
		
		return str_replace($s,$r, $mask);
		
		// Keep this info here: 
		/*
				 * $options allowed keys:
	
		 * 
		 *   d - day of month (no leading zero)
		    dd - day of month (two digit)
		    o - day of the year (no leading zeros)
		    oo - day of the year (three digit)
		    D - day name short
		    DD - day name long
		    m - month of year (no leading zero)
		    mm - month of year (two digit)
		    M - month name short
		    MM - month name long
		    y - year (two digit)
		    yy - year (four digit)
		    @ - Unix timestamp (ms since 01/01/1970)
		     ! - Windows ticks (100ns since 01/01/0001)
		    '...' - literal text
		    '' - single quote
		    anything else - literal text 
		
		    ATOM - 'yy-mm-dd' (Same as RFC 3339/ISO 8601)
		    COOKIE - 'D, dd M yy'
		    ISO_8601 - 'yy-mm-dd'
		    RFC_822 - 'D, d M y' (See RFC 822)
		    RFC_850 - 'DD, dd-M-y' (See RFC 850)
		    RFC_1036 - 'D, d M y' (See RFC 1036)
		    RFC_1123 - 'D, d M yy' (See RFC 1123)
		    RFC_2822 - 'D, d M yy' (See RFC 2822)
		    RSS - 'D, d M y' (Same as RFC 822)
		    TICKS - '!'
		    TIMESTAMP - '@'
		    W3C - 'yy-mm-dd' (Same as ISO 8601)
		 * 
		 * h    Hour with no leading 0
		 * hh    Hour with leading 0
		 * m    Minute with no leading 0
		 * mm    Minute with leading 0
		 * s    Second with no leading 0
		 * ss    Second with leading 0
		 * l    Milliseconds always with leading 0
		 * t    a or p for AM/PM
		 * T    A or P for AM/PM
		 * tt    am or pm for AM/PM
		 * TT    AM or PM for AM/PM 
			
			*/
	}


	
	/**
	 * Convert date string back to integer (unix timestamp)
	 * NOTE: after some tests, strptime (compat mode) is adding +1 sec. after parsing to time, investigate!
	 * 
	 * @param object $date_string
	 * @param object $mask [optional]
	 * @return integer
	 */
	function toTime($date_string, $mask = 'input')
	{
		switch($mask)
		{
			case 'long':
				$mask = e107::getPref('longdate');
			break;
			
			case 'short':
				$mask = e107::getPref('shortdate');
			break;

			case 'input': 
			case 'inputdate': 
				$mask = e107::getPref('inputdate', '%Y/%m/%d');
			break;
			
			case 'inputdatetime': 
				$mask = e107::getPref('inputdate', '%Y/%m/%d');
				$mask .= " ".e107::getPref('inputtime', '%H:%M');
			break;
			
			case 'inputtime': 
				$mask = e107::getPref('inputtime', '%H:%M');
			break;
		}
		
		// also in php compat handler for plugins that might use it. 
		$tdata = $this->strptime($date_string, $mask);
		

	//	print_a($tdata);
		
		if(empty($tdata))
		{
			return null;
		}
		$unxTimestamp = mktime( 
			$tdata['tm_hour'], 
			$tdata['tm_min'], 
			$tdata['tm_sec'], 
			$tdata['tm_mon'], 
			$tdata['tm_mday'], 
			($tdata['tm_year'] + 1900) 
		); 
		
		
	//	echo "<br />UNIX=".$unxTimestamp - TIMEOFFSET;
	//	echo "<br />".date("l, d M Y g:i A",$unxTimestamp);
	
		// var_dump($tdata, $date_string, $this->convert_date($unxTimestamp, $mask), $unxTimestamp);

		return $unxTimestamp;
	}

// -----------------------

	/**
	 * Tolerant date/time input routine - doesn't force use of specific delimiters, and can sometimes allow no delimiters at all
	 * The format string defines the critical day/month/year order.
	 * As examples, with suitable format specifiers all of the following will be interpreted into valid (and sensible) dates/times:
	 *  09122003 153045		-> 9-12-03 at 15:30:45 (requires dmy or mdy format specifier)
	 *	20031209 12:30:32	-> 9-12-03 at 12:30:32 (requires ymd specifier)
	 *	091203 1530			-> 9-12-09 at 15:30:00
	 *  9/12/3 12			-> 9-12-09 at 12:00:00
	 *	6-3/4 15-45:27		-> 6-3-04 at 15:45:27
	 *
	 * @param string $input - usually date/time string with numeric values for relevant fields, and almost any separator. e.g. dd-mm-yy hh:mm
	 *							Date and time must be separated by one or more spaces. In times, minutes and seconds are optional, and default to zero
	 *							One special value is allowed - 'now'
	 * @param string $decode - one of 'date', 'time', 'datetime', 'timedate'
	 * @param string $format - sets field order for dates. Valid strings are dmy, mdy, ymd. Add suffix 'z' to return UTC/GMT
	 * @param boolean $endDay - if TRUE, and no time entered, includes a time of 23:59:59 in the entered date
	 *
	 * @return integer time stamp.  returns zero on any error
	 */
	public function decodeDateTime($input, $decode = 'date', $format = 'dmy', $endDay = FALSE)
	{
		if ($input == 'now') return time();		// Current time   TODO: option to return UTC or local time here
		$useLocale = TRUE;
		if (substr($format,-1,1) == 'z')
		{
			$useLocale = FALSE;
			$format = substr($format,0,-1);		// Remove local disable string
		}
		switch ($decode)
		{
			case 'date' :
				$timeString = '';
				$dateString = $input;
				break;
			case 'time' :
				$timeString = $input;
				$dateString = '';
				break;
			case 'datetime' :		// Date then time, separated by space
				$input = str_replace('  ',' ', $input);
				list($dateString, $timeString) = explode(' ',$input,2);
				break;
			case 'timedate' :		// Time then date, separated by space
				$input = str_replace('  ',' ', $input);
				list($timeString, $dateString) = explode(' ',$input,2);
				break;
			default :
				return 0;
		}
		$timeString = trim($timeString);
		$dateString = trim($dateString);
		$dateVals = array (1 => 0, 2 => 0, 3 => 0);		// Preset date in case 
		$timeVals = array (1 => 0, 2 => 0, 3 => 0);		// Preset time in case 
		if ($dateString)
		{
			if (is_numeric($dateString))
			{
				if (strlen($dateString) == 6)
				{	// Probably fixed format numeric without separators
					$dateVals = array(1 => substr($dateString,0,2), 2 => substr($dateString,2,2), 3 => substr($dateString,-2));
				}
				elseif (strlen($dateString) == 8)
				{	// Trickier - year may be first or last!
					if ($format == 'ymd')
					{
						$dateVals = array(1 => substr($dateString,0,4), 2 => substr($dateString,4,2), 3 => substr($dateString,-2));
					}
					else
					{
						$dateVals = array(1 => substr($dateString,0,2), 2 => substr($dateString,2,2), 3 => substr($dateString,-4));
					}
				}
			}
			else
			{  // Assume standard 'nn-nn-nn', 'nnnn-nn-nn' or 'nn-nn-nnnn' type format
				if (!preg_match('#(\d{1,4})\D(\d{1,2})\D(\d{1,4})#', $dateString, $dateVals))
				{
					return 0;			// Can't decode date
				}
			}
		}
		if ($timeString)
		{
			if (is_numeric($timeString))
			{
				if (strlen($timeString) == 6)
				{	// Assume hhmmss
					$timeVals = array(1 => substr($timeString,0,2), 2 => substr($timeString,2,2), 3 => substr($timeString,-2));
				}
				elseif (strlen($timeString) == 4)
				{	// Assume hhmm
					$timeVals = array(1 => substr($timeString,0,2), 2 => substr($timeString,-2), 3 => 0);
				}
				else
				{	// Hope its just hours!
					if ($timeString < 24)
					{
						$timeVals[1] = $timeString;
					}
				}
			}
			else
			{
				preg_match('#(\d{1,2})(?:\D(\d{1,2})){0,1}(?:\D(\d{1,2})){0,1}#', $timeString, $timeVals);
			}
		}
		elseif ($endDay)
		{
			$timeVals = array (1 => 23, 2 => 59, 3 => 59);		// Last second of day
		}
		// Got all the values now - the rest is simple!
		switch ($format)
		{
			case 'dmy' :
				$month = $dateVals[2]; $day = $dateVals[1]; $year = $dateVals[3]; break;
			case 'mdy' :
				$month = $dateVals[1]; $day = $dateVals[2]; $year = $dateVals[3]; break;
			case 'ymd' :
				$month = $dateVals[2]; $day = $dateVals[3]; $year = $dateVals[1]; break;
			default :
				echo "Unsupported format string: {$format}<br />";
				return 0;
		}
		if ($useLocale)
		{
			return mktime($timeVals[1], $timeVals[2], $timeVals[3], $month, $day, $year);
		}
		return gmmktime($timeVals[1], $timeVals[2], $timeVals[3], $month, $day, $year);
	}



	/**
	 * Calculate difference between two dates for display in terms of years/months/weeks....
	 * 
	 * @param integer $older_date - time stamp
	 * @param integer|boolean $newer_date - time stamp.  Defaults to current time if FALSE
	 * @param boolean $mode -if TRUE, return value is an array. Otherwise return value is a string
	 * @param boolean $show_secs
	 * @param string $format - controls display format. 'short' misses off year. 'long' includes everything
	 * @return array|string according to $mode, array or string detailing the time difference
	 */
	function computeLapse($older_date, $newer_date = FALSE, $mode = FALSE, $show_secs = TRUE, $format = 'long') 
	{	/*
		$mode = TRUE :: return array
		$mode = FALSE :: return string
		*/

		if($format == 'short')
		{
			$sec = LANDT_09;
			$secs = LANDT_09s;
			$min = LANDT_08;
			$mins = LANDT_08s;
		}
		else
		{
			$sec = LANDT_07;
			$secs = LANDT_07s;
			$min = LANDT_06;
			$mins = LANDT_06s;
		}
/*
  If we want an absolutely accurate result, main problems arise from the varying numbers of days in a month.
  If we go over a month boundary, then we need to add days to end of start month, plus days in 'end' month
  If start day > end day, we cross a month boundary. Calculate last day of start date. Otherwise we can just do a simple difference.
*/
		$newer_date = ($newer_date === FALSE ? (time()) : $newer_date);
		if($older_date>$newer_date)
		{  // Just in case the wrong way round
		  $tmp=$newer_date; 
		  $newer_date=$older_date; 
		  $older_date=$tmp; 
		}
		$new_date = getdate($newer_date);
		$old_date = getdate($older_date);
		$result   = array();
		$outputArray = array();

		$params   = array(
					  6 => array('seconds',60, $sec, $secs),
					  5 => array('minutes',60, $min, $mins),
					  4 => array('hours',24, LANDT_05, LANDT_05s),
					  3 => array('mday', -1, LANDT_04, LANDT_04s),
					  2 => array('',-3, LANDT_03, LANDT_03s),
					  1 => array('mon',12, LANDT_02, LANDT_02s),
					  0 => array('year', -2, LANDT_01,LANDT_01s)
					);

		$cy = 0;
		foreach ($params as $parkey => $parval)
		{
		  if ($parkey == 2)
		  {
		    $result['2'] = floor($result['3']/7);
			$result['3'] = fmod($result['3'],7);
		  }
		  else
		  {
		    $tmp = $new_date[$parval[0]] - $old_date[$parval[0]] - $cy;
			$scy = $cy;
		    $cy = 0;
		    if ($tmp < 0)
		    {
		      switch ($parval[1])
			  {
			    case -1 :    // Wrapround on months - special treatment
			      $tempdate = getdate(mktime(0,0,0,$old_date['mon']+1,1,$old_date['year']) - 1);  // Last day of month
				  $tmp = $tempdate['mday'] - $old_date['mday'] + $new_date['mday'] - $scy;
				  $cy = 1;
			      break;
			    case -2 :		// Year wraparound - shouldn't happen
				case -3 : 		// Week processing - this shouldn't happen either
				  echo "Code bug!<br />";
			      break;
			    default :
		          $cy = 1;
				  $tmp += $parval[1];
			}
		  }
		  $result[$parkey] = $tmp;
		  }
		}

		// Generate output array, add text
		for ($i = 0; $i < ($show_secs ? 7 : 6); $i++)
		{
		  if (($i > 4) || ($result[$i] != 0))
		  {  // Only show non-zero values, except always show minutes/seconds
		    $outputArray[] = $result[$i]." ".($result[$i] == 1 ? $params[$i][2] : $params[$i][3]);
		  }
		}
		return ($mode ? $outputArray : implode(", ", $outputArray));
	}




	/**
	 *  This work of Lionel SAURON (http://sauron.lionel.free.fr:80) is licensed under the
	 *  Creative Commons Attribution-Noncommercial-Share Alike 2.0 France License.
	 *  To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/2.0/fr/
	 *  or send a letter to Creative Commons, 171 Second Street, Suite 300, San Francisco, California, 94105, USA.
	 * 
	 * http://snipplr.com/view/4964/emulate-php-5-for-backwards-compatibility/
	 * 
	 * Parse a date generated with strftime().
	 * 
	 * @author Lionel SAURON and reworked by e107 Inc. for month names. 
	 * @version 1.0
	 * @public
	 *
	 * @param string $str date string to parse (e.g. returned from strftime()).
	 * @param string $sFormat strftime format used to create the date
	 * @return array Returns an array with the <code>$str</code> parsed, or <code>false</code> on error.
	 */
	public function strptime($str, $format)
	{
		
		static $expand = array('%D'=>'%m/%d/%y', '%T'=>'%H:%M:%S', );
		
		$ampm	= (preg_match("/%l|%I|%p|%P/",$format)) ? 'true' : 'false';
		
		static $map_r = array(
			'%S'	=>'tm_sec',
			'%M'	=>'tm_min',
			'%H'	=>'tm_hour', 
			'%I'	=>'tm_hour',
			'%d'	=>'tm_mday', 
			'%m'	=>'tm_mon', 
			'%Y'	=>'tm_year', 
			'%y'	=>'tm_year', 
			'%W'	=>'tm_wday', 
			'%D'	=>'tm_yday', 
			'%B'	=>'tm_fmon', // full month-name
			'%b'	=>'tm_amon', // abrev. month-name
			'%p'	=>'tm_AMPM', // AM/PM	
			'%P'	=>'tm_ampm', // am/pm				
			'%u'	=>'unparsed',
			 
		);
				
		$fullmonth = array();
		$abrevmonth = array();
		
		for ($i = 1; $i <= 12; $i++)
		{
			$k = strftime('%B',mktime(0,0,0,$i));
    		$fullmonth[$k] = $i;
			
			$j = strftime('%b',mktime(0,0,0,$i));
    		$abrevmonth[$j] = $i;
		}

		
		
		#-- transform $format into extraction regex
		$format = str_replace(array_keys($expand), array_values($expand), $format);
		$preg = preg_replace('/(%\w)/', '(\w+)', preg_quote($format));
		
		#-- record the positions of all STRFCMD-placeholders
		preg_match_all('/(%\w)/', $format, $positions);
		$positions = $positions[1];
		
		#-- get individual values
		if (preg_match("#$preg#", $str, $extracted))
		{
			#-- get values
			foreach ($positions as $pos => $strfc)
			{
				$v = $extracted[$pos + 1];
				#-- add
				if (isset($map_r[$strfc]))
				{
					$n = $map_r[$strfc];
					$vals[$n] = ($v > 0) ? (int) $v : $v;
				}
				else
				{
					$vals['unparsed'] .= $v.' ';
				}
			}
			
			#-- fixup some entries
			//$vals["tm_wday"] = $names[ substr($vals["tm_wday"], 0, 3) ];
			if ($vals['tm_year'] >= 1900)
			{
				$vals['tm_year'] -= 1900;
			}
			elseif ($vals['tm_year'] > 0)
			{
				$vals['tm_year'] += 100;
			}
			
			if ($vals['tm_mon'])
			{
				$vals['tm_mon'] -= 1;			
			}
			else
			{

				if(isset($fullmonth[$vals['tm_fmon']]))
				{
					$vals['tm_mon'] = $fullmonth[$vals['tm_fmon']];	
				}
				elseif(isset($abrevmonth[$vals['tm_amon']]))
				{
					$vals['tm_mon'] = $abrevmonth[$vals['tm_amon']];	
				}
	
			}
			
			if($ampm)
			{
				if($vals['tm_hour'] == 12 && ($vals['tm_AMPM'] == 'AM' || $vals['tm_ampm'] == 'am'))
				{
					$vals['tm_hour'] = 0;			
				}
				
				if($vals['tm_hour'] < 12 && ($vals['tm_AMPM'] == 'PM' || $vals['tm_ampm'] == 'pm'))
				{
					$vals['tm_hour'] = intval($vals['tm_hour']) + 12;			
				}
							
			}
			
			//$vals['tm_sec'] -= 1; always increasing tm_sec + 1 ??????
			
			#-- calculate wday/yday
			$unxTimestamp = mktime($vals['tm_hour'], $vals['tm_min'], $vals['tm_sec'], ($vals['tm_mon'] + 1), $vals['tm_mday'], ($vals['tm_year'] + 1900));
			$vals['tm_wday'] = (int) strftime('%w', $unxTimestamp); // Days since Sunday (0-6)
			$vals['tm_yday'] = (strftime('%j', $unxTimestamp) - 1); // Days since January 1 (0-365)
			//var_dump($vals, $str, strftime($format, $unxTimestamp), $unxTimestamp);
		}
		
		return isset($vals) ? $vals : false;
		
	} 





	function supported($mode = FALSE)
	{
		$strftimeFormats = array(
		    'A' => 'A full textual representation of the day',
		    'B' => 'Full month name, based on the locale',
		    'C' => 'Two digit representation of the century (year divided by 100, truncated to an integer)',
		    'D' => 'Same as "%m/%d/%y"',
		    'E' => '',
		    'F' => 'Same as "%Y-%m-%d"',
		    'G' => 'The full four-digit version of %g',
		    'H' => 'Two digit representation of the hour in 24-hour format',
		    'I' => 'Two digit representation of the hour in 12-hour format',
		    'J' => '',
		    'K' => '',
		    'L' => '',
		    'M' => 'Two digit representation of the minute',
		    'N' => '',
		    'O' => '',
		    'P' => 'lower-case "am" or "pm" based on the given time',
		    'Q' => '',
		    'R' => 'Same as "%H:%M"',
		    'S' => 'Two digit representation of the second',
		    'T' => 'Same as "%H:%M:%S"',
		    'U' => 'Week number of the given year, starting with the first Sunday as the first week',
		    'V' => 'ISO-8601:1988 week number of the given year, starting with the first week of the year with at least 4 weekdays, with Monday being the start of the week',
		    'W' => 'A numeric representation of the week of the year, starting with the first Monday as the first week',
		    'X' => 'Preferred time representation based on locale, without the date',
		    'Y' => 'Four digit representation for the year',
		    'Z' => 'The time zone offset/abbreviation option NOT given by %z (depends on operating system)',
		    'a' => 'An abbreviated textual representation of the day',
		    'b' => 'Abbreviated month name, based on the locale',
		    'c' => 'Preferred date and time stamp based on local',
		    'd' => 'Two-digit day of the month (with leading zeros)',
		    'e' => 'Day of the month, with a space preceding single digits',
		    'f' => '',
		    'g' => 'Two digit representation of the year going by ISO-8601:1988 standards (see %V)',
		    'h' => 'Abbreviated month name, based on the locale (an alias of %b)',
		    'i' => '',
		    'j' => 'Day of the year, 3 digits with leading zeros',
		    'k' => '',
		    'l' => 'Hour in 12-hour format, with a space preceeding single digits',
		    'm' => 'Two digit representation of the month',
		    'n' => 'A newline character ("\n")',
		    'o' => '',
		    'p' => 'UPPER-CASE "AM" or "PM" based on the given time',
		    'q' => '',
		    'r' => 'Same as "%I:%M:%S %p"',
		    's' => 'Unix Epoch Time timestamp',
		    't' => 'A Tab character ("\t")',
		    'u' => 'ISO-8601 numeric representation of the day of the week',
		    'v' => '',
		    'w' => 'Numeric representation of the day of the week',
		    'x' => 'Preferred date representation based on locale, without the time',
		    'y' => 'Two digit representation of the year',
		    'z' => 'Either the time zone offset from UTC or the abbreviation (depends on operating system)',
		    '%' => 'A literal percentage character ("%")',
		);
		
		// Results.
		$strftimeValues = array();
		
		// Evaluate the formats whilst suppressing any errors.
		foreach($strftimeFormats as $format => $description)
		{
		    if (False !== ($value = @strftime("%{$format}")))
		    {
		        $strftimeValues[$format] = $value;
		    }
		}
		
		// Find the longest value.
		$maxValueLength = 2 + max(array_map('strlen', $strftimeValues));
		
		$ret = array(
			'enabled' 	=> array(),
			'disabled' 	=> array()
		);
		
		// Report known formats.
		foreach($strftimeValues as $format => $value)
		{
			$ret['enabled'][] = $format;
		    echo ($mode =='list') ? "Known format   : '{$format}' = ". str_pad("'{$value}'", $maxValueLength). " ( {$strftimeFormats[$format]} )<br />" : "";
		}
		
		// Report unknown formats.
		foreach(array_diff_key($strftimeFormats, $strftimeValues) as $format => $description)
		{
			$ret['disabled'][] = $format;
		    echo ($mode =='list') ? "Unknown format : '{$format}'   ". str_pad(' ', $maxValueLength). ($description ? " ( {$description} )" : ''). "<br />" : "";
		}	
		
		return in_array($mode,$ret['enabled']); 
		
		
	}


}
?>
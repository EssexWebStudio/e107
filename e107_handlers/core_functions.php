<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Core functions
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/core_functions.php,v $
 * $Revision$
 * $Date$
 * $Author$
*/

//

//

/**
 * Use these to combine isset() and use of the set value. or defined and use of a constant
 * i.e. to fix  if($pref['foo']) ==> if ( varset($pref['foo']) ) will use the pref, or ''.
 * Can set 2nd param to any other default value you like (e.g. false, 0, or whatever)
 * $testvalue adds additional test of the value (not just isset())
 * Examples:
 * <code>
 * $something = pref;  Bug if pref not set         ==> $something = varset(pref);
 * $something = isset(pref) ? pref : "";              ==> $something = varset(pref);
 * $something = isset(pref) ? pref : default;         ==> $something = varset(pref,default);
 * $something = isset(pref) && pref ? pref : default; ==> use varsettrue(pref,default)
 * </code>
 * 
 * @param mixed $val
 * @param mixed $default [optional]
 * @return mixed
 */
function varset(&$val, $default='')
{
	if (isset($val)) { return $val; }
	return $default;
}

/**
 * Check if the given string is defined (constant)
 * 
 * @param string $str
 * @param mixed $default [optional]
 * @return mixed 
 */
function defset($str, $default='')
{
	if (defined($str)) { return constant($str); }
	return $default;
}

/**
 * Variant of {@link varset()}, but only return the value if both set AND 'true'
 * 
 * @param mixed $val
 * @param mixed $default [optional]
 * @return mixed
 */
function varsettrue(&$val, $default='')
{
	if (isset($val) && $val) { return $val; }
	return $default;
}

/**
 * Alias of {@link varsettrue()}
 * 
 * @param mixed $val
 * @param mixed $default [optional]
 * @return mixed
 */
function vartrue(&$val, $default='')
{
	return varsettrue($val, $default);
}

/**
 * Variant of {@link defset()}, but only return the value if both defined AND 'true'
 * 
 * @param string $str
 * @param mixed $default [optional]
 * @return mixed
 */
function defsettrue($str, $default='')
{
	if (defined($str) && constant($str)) { return constant($str); }
	return $default;
}

/**
 * Alias of {@link defsettrue()}
 * 
 * @param string $str
 * @param mixed $default [optional]
 * @return mixed
 */
function deftrue($str, $default='')
{
	if (defined($str) && constant($str)) { return constant($str); }
	return $default;
}

function e107_include($fname)
{
	global $e107_debug, $_E107;
	$ret = (($e107_debug || isset($_E107['debug'])) ? include($fname) : @include($fname));
	return $ret;
}

function e107_include_once($fname)
{
	global $e107_debug, $_E107;
	if(is_readable($fname))
	{
		$ret = ($e107_debug || isset($_E107['debug'])) ? include_once($fname) : @include_once($fname);
	}
	return (isset($ret)) ? $ret : '';
}

function e107_require_once($fname)
{
	global $e107_debug, $_E107;
	
	$ret = (($e107_debug || isset($_E107['debug'])) ? require_once($fname) : @require_once($fname));
	
	return $ret;
}

function e107_require($fname)
{
	global $e107_debug, $_E107;
	$ret = (($e107_debug || isset($_E107['debug'])) ? require($fname) : @require($fname));
	return $ret;
}


function print_a($var, $return = FALSE)
{
	if( ! $return)
	{
		echo '<pre>'.htmlspecialchars(print_r($var, TRUE), ENT_QUOTES, 'utf-8').'</pre>';
		return TRUE;
	}
	else
	{
		return '<pre>'.htmlspecialchars(print_r($var, true), ENT_QUOTES, 'utf-8').'</pre>';
	}
}

function e_print($expr = null)
{
	$args = func_get_args();
	if(!$args) return;
	foreach ($args as $arg) 
	{
		print_a($arg);
	}
}

function e_dump($expr = null)
{
	$args = func_get_args();
	if(!$args) return;
	
	echo '<pre>';
	call_user_func_array('var_dump', $args);
	echo '</pre>';
}

/**
 * Strips slashes from a var if magic_quotes_gqc is enabled
 *
 * @param mixed $data
 * @return mixed
 */
function strip_if_magic($data)
{
	if (MAGIC_QUOTES_GPC == true)
	{
		return array_stripslashes($data);
	}
	else
	{
		return $data;
	}
}

/**
 * Strips slashes from a string or an array
 *
 * @param mixed $value
 * @return mixed
 */
function array_stripslashes($data)
{
	return is_array($data) ? array_map('array_stripslashes', $data) : stripslashes($data);
}

function echo_gzipped_page()
{

    if(headers_sent())
	{
        $encoding = false;
    }
	elseif( strpos($_SERVER["HTTP_ACCEPT_ENCODING"], 'x-gzip') !== false )
	{
        $encoding = 'x-gzip';
    }
	elseif( strpos($_SERVER["HTTP_ACCEPT_ENCODING"],'gzip') !== false )
	{
        $encoding = 'gzip';
    }
	else
	{
        $encoding = false;
    }

    if($encoding)
	{
        $contents = ob_get_contents();
        ob_end_clean();
        header('Content-Encoding: '.$encoding);
        print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
        $size = strlen($contents);
        $contents = gzcompress($contents, 9);
        $contents = substr($contents, 0, $size);
        print($contents);
        exit();
    }
	else
	{
        ob_end_flush();
        exit();
    }
}


// Better Array-sort by key function by acecream (22-Apr-2003 11:02) http://php.net/manual/en/function.asort.php
if (!function_exists('asortbyindex')) 
{
    function asortbyindex($array, $key)
    {
       foreach ($array as $i => $k)
       {
            $sort_values[$i] = $array[$i][$key];
       }
       asort ($sort_values);
       reset ($sort_values);
       while (list ($arr_key, $arr_val) = each ($sort_values))
       {
              $sorted_arr[] = $array[$arr_key];
       }
       return $sorted_arr;
    }
}

if (!function_exists('multiarray_sort')) {
    function multiarray_sort(&$array, $key, $order = 'asc', $natsort = true, $case = true)
    {
        if(!is_array($array)) return $array;

        $order = strtolower($order);
        foreach ($array as $i => $arr)
        {
           $sort_values[$i] = $arr[$key];
        }

        if(!$natsort) 
        {
            ($order=='asc')? asort($sort_values) : arsort($sort_values);
        }
        elseif(isset($sort_values))
        {
             $case ? natsort($sort_values) : natcasesort($sort_values);
             if($order != 'asc') $sort_values = array_reverse($sort_values, true);
        }
        
        if(!isset($sort_values))
        {
            return;             
        }
            
        reset ($sort_values);

        while (list ($arr_key, $arr_val) = each ($sort_values))
        {
             $sorted_arr[] = $array[$arr_key];
        }
        return $sorted_arr;
    }
}

/**
 * Array Storage Class. 
 */
class e_array {

    /**
    * Returns an array from stored array data.
    *
    * @param string $ArrayData
    * @return array stored data
    */
    public function read($ArrayData) 
    {
        if ($ArrayData == ""){
            return false;
        }
        
        // Saftety mechanism for 0.7 -> 0.8 transition. 
        if(substr($ArrayData,0,2)=='a:' || substr($ArrayData,0,2)=='s:')
        {
            $dat = unserialize($ArrayData);
            $ArrayData = $this->WriteArray($dat,FALSE);
        }
        
        
        $data = "";
        $ArrayData = '$data = '.trim($ArrayData).';';
        @eval($ArrayData);
        if (!isset($data) || !is_array($data)) {
            trigger_error("Bad stored array data - <br /><br />".htmlentities($ArrayData), E_USER_ERROR);
            return false;
        }
        return $data;        
    }
    
    
    /**
    * Return a string containg exported array data.
    *
    * @param array $ArrayData array to be stored
    * @param bool $AddSlashes default true, add slashes for db storage, else false
    * @return string
    */
    public function write($ArrayData, $AddSlashes = true) 
    {       
        if (!is_array($ArrayData)) {
            return false;
        }
        $Array = var_export($ArrayData, true);
        if ($AddSlashes == true) {
            $Array = addslashes($Array);
        }
        return $Array;        
    }


    /**
     * DEPRECATED - Backwards Compatible. Use write() instead; 
    * @param array $ArrayData array to be stored
    * @param bool $AddSlashes default true, add slashes for db storage, else false
    * @returnReturn a string containg exported array data.
     */
    function WriteArray($ArrayData, $AddSlashes = true) {
        
        return  $this->write($ArrayData, $AddSlashes);   

    }

    /**
    * DEPRECATED: Use read(); instead. 
    * Returns an array from stored array data.
    *
    * @param string $ArrayData
    * @return array stored data
    */
    function ReadArray($ArrayData) 
    {
        return $this->read($ArrayData);
    }
}


?>
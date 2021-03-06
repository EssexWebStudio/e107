<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/import_classes.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

/*
Root classes for import and saving of data. Application-specific classes build on these
*/

class base_import_class
{
	var $ourDB = NULL;
	var $DBPrefix = '';
	var $currentTask = '';
	var $copyUserInfo = TRUE;
	var $arrayData = array();

	/**
	 * Connect to the external DB if not already connected
	 */
	function db_Connect($server, $user, $password, $database, $prefix)
	{
		if ($this->ourDB == NULL)
		{
	  		$this->ourDB = new db;
	  		$result = $this->ourDB->db_Connect($server, $user, $password, $database);
	  		$this->DBPrefix = $prefix;
	  		if ($result)
	  		{
	  	 		return $result;
	  		}
		}
		
		return TRUE;
	}

	/**
	 * Set up a query for the specified task.  If $blank_user is TRUE, user ID Data in source data is ignored
	 * @return boolean TRUE on success. FALSE on error
	*/
	function setupQuery($task, $blank_user=FALSE)
	{
		return FALSE;
	}


	function saveData($dataRecord)
	{
		switch($this->currentTask)
		{
	  		case 'users' :
	    		return $this->saveUserData($dataRecord);
	    	break;
			
			case 'news' :
				return $this->saveNewsData($dataRecord);
			break;
			
			case 'page' :
				return $this->savePageData($dataRecord);
			break;

			case 'links' :
				return $this->saveLinksData($dataRecord);
			break;
			
			case 'media' :
				return $this->saveMediaData($dataRecord);
			break;
			
	  		case 'forumdefs' :
	    		return $this->saveForumData($dataRecord);
	    	break;
			
	  		case 'forumposts' :
	    		return $this->savePostData($dataRecord);
	    	break;
			
	  		case 'polls' :
	    	break;
		}
		
		return FALSE;
  }


  // Return the next record as an array. All data has been converted to the appropriate E107 formats
  // Return FALSE if no more data
  // Its passed a record initialised with the default values
	function getNext($initial,$mode='db')
	{
		if($mode == 'db')
		{
			$result = $this->ourDB->db_Fetch();	
		}
		else
		{
			$result = current($this->arrayData);
			next($this->arrayData);
		}
		
		
		if (!$result) return FALSE;
		switch($this->currentTask)
		{
	  		case 'users' :
				return $this->copyUserData($initial, $result);
			break;
			
			case 'news' :
				return $this->copyNewsData($initial, $result);
	  		break;
			
			case 'page' :
				return $this->copyPageData($initial, $result);
	  		break;

			case 'links' :
				return $this->copyLinksData($initial, $result);
	  		break;

			case 'media' :
				return $this->copyMediaData($initial, $result);
	  		break;
						
	  		case 'forumdefs' :
	  		break; 
				
	  		case 'forumposts' :
	  		break;
		  
	  		case 'polls' :
	  		break;
		  
	  		
		}

    	return FALSE;
	}


	// Called to signal that current task is complete; tidy up as required
	function endQuery()
	{
		$this->currentTask = '';
	}


	// Empty functions which descendants can inherit from

	function init()
	{
		return;
	}
	
		
	function copyUserData(&$target, &$source)
	{
		return $target;
	}
	
	function copyNewsData(&$target, &$source)
	{
		return $target;
	}
	
	function copyPageData(&$target, &$source)
	{
		return $target;
	}
	
	function copyLinksData(&$target, &$source)
	{
		return $target;
	}
	
	function copyMediaData(&$target, &$source)
	{
		return $target;
	}
}


//===========================================================
//				UTILITY ROUTINES
//===========================================================

// Process all bbcodes in the passed value; return the processed string.
// Works recursively
// Start by assembling matched pairs. Then map and otherwise process as required.
// Divide the value into five bits:
//      Preamble - up to the identified bbcode (won't contain bbcode)
//		BBCode start code
//		Inner - text between the two bbcodes (may contain another bbcode)
//		BBCode end code
//		Trailer - remaining unprocessed text (may contain more bbcodes)
// (Note: preg_split might seem obvious, but doesn't pick out the actual codes
function proc_bb($value, $options = "", $maptable = null)
{
  $bblower = (strpos($options,'bblower') !== FALSE) ? TRUE : FALSE;		// Convert bbcode to lower case
  $bbphpbb = (strpos($options,'phpbb') !== FALSE) ? TRUE : FALSE;		// Strip values as phpbb
  $nextchar = 0;
  $loopcount = 0;
 
  while ($nextchar < strlen($value))
  {
    $firstbit = '';
    $middlebit = '';
    $lastbit = '';
    $loopcount++;
	if ($loopcount > 10) return 'Max depth exceeded';
    unset($bbword);
    $firstcode = strpos($value,'[',$nextchar);
    if ($firstcode === FALSE) return $value;   	// Done if no square brackets
    $firstend = strpos($value,']',$firstcode);
    if ($firstend === FALSE) return $value;		// Done if no closing bracket
    $bbword = substr($value,$firstcode+1,$firstend - $firstcode - 1);	// May need to process this more if parameter follows
	$bbparam = '';
	$temp = strpos($bbword,'=');
	if ($temp !== FALSE)
	{
	  $bbparam = substr($bbword,$temp);
	  $bbword  = substr($bbword,0,-strlen($bbparam));
	}
    if (($bbword) && ($bbword == trim($bbword)))
    {
      $laststart = strpos($value,'[/'.$bbword,$firstend);    // Find matching end
	  $lastend   = strpos($value,']',$laststart);
	  if (($laststart === FALSE) || ($lastend === FALSE))
	  {   //  No matching end character
	    $nextchar = $firstend;	// Just move scan pointer along 
	  }
	  else
	  {  // Got a valid bbcode pair here
	    $firstbit = '';
	    if ($firstcode > 0) $firstbit = substr($value,0,$firstcode);
	    $middlebit = substr($value,$firstend+1,$laststart - $firstend-1);
	    $lastbit = substr($value,$lastend+1,strlen($value) - $lastend);
	    // Process bbcodes here
		if ($bblower) $bbword = strtolower($bbword);
		if ($bbphpbb && (strpos($bbword,':') !== FALSE)) $bbword = substr($bbword,0,strpos($bbword,':'));
		if ($maptable)
		{   // Do mapping
		  if (array_key_exists($bbword,$maptable)) $bbword = $maptable[$bbword];
		}
	    $bbbegin = '['.$bbword.$bbparam.']';
	    $bbend   = '[/'.$bbword.']';
	    return $firstbit.$bbbegin.proc_bb($middlebit,$options,$maptable).$bbend.proc_bb($lastbit,$options,$maptable);
	  }
    }
	else
	{
	  $nextchar = $firstend+1;
	}
  }  //endwhile;
  
}




?>

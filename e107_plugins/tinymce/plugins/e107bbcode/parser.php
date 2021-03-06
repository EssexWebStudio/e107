<?php
/*
 * e107 website system
 *
 * Copyright (C) e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.8/e107_handlers/bbcode_handler.php $
 * $Id: bbcode_handler.php 12778 2012-06-02 08:12:16Z e107coders $
 */
require_once("../../../../class2.php");

/**
 * Two Modes supported below going to and from the Tinymce wysiwyg editor.  
 * 1) When the post_html pref is active - raw html is used in the editor and wrapped in [html] [/html] bbcodes in the background. 
 * 2) When the post_html pref is disabled - bbcodes are used in the background and converted to html for the editor. 
 * Tested extensively over 24 hours with Images - check with Cameron first if issues arise. 
 * TODO Test Lines breaks and out html tags. 
 * TODO Check with html5 tags active. 
 */

if($_POST['mode'] == 'tohtml')
{
   
    
	// XXX @Cam possible fix - convert to BB first, see news admin AJAX request/response values for reference why
	$content = stripslashes($_POST['content']);
        
//	$content = e107::getBB()->htmltoBBcode($content);	//XXX This breaks inserted images from media-manager. :/
    e107::getBB()->setClass($_SESSION['media_category']);
     
    if(check_class($pref['post_html'])) // raw HTML within [html] tags. 
    {
        $content = str_replace("{e_BASE}","",$content); // We want {e_BASE} in the final data going to the DB, but not the editor. 
   
        $srch = array("<!-- bbcode-html-start -->","<!-- bbcode-html-end -->","[html]","[/html]");
        $content = str_replace($srch,"",$content);
        echo $content;
    }
    else  // bbcode Mode. 
    {   
        
        // XXX @Cam this breaks new lines, currently we use \n instead [br]
        //echo $tp->toHtml(str_replace("\n","",$content), true); 
        
        $content = str_replace("{e_BASE}",e_HTTP, $content); // We want {e_BASE} in the final data going to the DB, but not the editor. 
        $content = $tp->toHtml($content, true);
        $content = str_replace(e_MEDIA_IMAGE,"{e_MEDIA_IMAGE}",$content);
        
        echo $content;
    }
	
	e107::getBB()->clearClass();	
}

if($_POST['mode'] == 'tobbcode')
{
	// echo $_POST['content'];    
	$content = stripslashes($_POST['content']);
    
	if(check_class($pref['post_html'])) // Plain HTML mode. 
    {
        $srch = array('src="'.e_HTTP.'thumb.php?');
        $repl = array('src="{e_BASE}thumb.php?');
    
        $content = str_replace($srch, $repl, $content);
    
    // resize the thumbnail to match wysiwyg width/height. 
        $psrch = '/<img src="{e_BASE}thumb.php\?src=([\S]*)w=([\d]*)&amp;h=([\d]*)"(.*)width="([\d]*)" height="([\d]*)"/i';
        $prepl = '<img src="{e_BASE}thumb.php?src=$1w=$5&amp;h=$6"$4width="$5" height="$6" ';
    
         $content = preg_replace($psrch, $prepl, $content);
        
        echo ($content) ? "[html]".$content."[/html]" : ""; // Add the tags before saving to DB. 
    }
    else  // bbcode Mode. 
    {
     //   [img width=400]/e107_2.0/thumb.php?src={e_MEDIA_IMAGE}2012-12/e107org_white_stripe.png&w=400&h=0[/img]
       // $content = str_replace("{e_BASE}","", $content); // We want {e_BASE} in the final data going to the DB, but not the editor. 
   
        echo e107::getBB()->htmltoBBcode($content);     
    }
		
}



?>
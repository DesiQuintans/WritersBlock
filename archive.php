<?php
# Writer's Block v3.8a: http://www.desiquintans.com/writersblock
# Writer's Block is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('admin/control.php');
require ('template/'.LANGUAGE_FILE);

// Entry summarisation function.
// $terminations lists all the punctuation that a sentence can end with, including the length of the terminator. Placed outside the function so it isn't
// recreated with every iteration.
$terminations = array(
	'. ',
	'! ',
	'? ',
	'.<p>',
	'.</p>',
	'!<p>',
	'!</p>',
	'?<p>',
	'?</p>',
	'.',
	'?',
	'!');
function SummariseEntry($longentry) {
	global $terminations;
	$strippedentry = strip_tags($longentry, '<p>');
	$term_pos = NULL;
	foreach($terminations as $needle) {
	    	// Check each punctuation character to find where it occurs.
		$pos = strpos($strippedentry, $needle);
		if($pos !== FALSE) {
			// Record the only the earliest terminator position.
			if(!isset($term_pos) || $pos < $term_pos) $term_pos = $pos;
		}
	}
	if(isset($term_pos)) {
		// If terminator found, grab first sentence with terminator included.
		$item_summary = substr($strippedentry, 0, $term_pos+1);
	} else {
		// If terminator not found, return first 100 characters of tag-stripped string. 100 chars is the average length of a decent sentence.
		$item_summary = substr($strippedentry, 0, 100).'&#8230;';
	}
	return strip_tags($item_summary);
}

if(isset($_GET['cat'])) {
## Check if category exists
    $check = strpos($_GET['cat'], '+');
    $check .= strpos($_GET['cat'], '%20');
    if(!$check) {
        $getinfo = mysql_query("SELECT PCatID, PostCatName, PostDesc FROM ".POSTCAT_TBL." WHERE PostCatUrl='".$_GET['cat']."'");
    }
    if (mysql_num_rows($getinfo) == 0 or $check) {
        $pagetitle =& $language['404'];
        include ('template/header.htm');
        include ('template/404.htm');
        include ('template/footer.htm');
        exit();
    }
## If script didn't EXIT(), show the Category Directory
    $dir = mysql_fetch_array($getinfo);
    $pagetitle =& $dir['PostCatName'];
    include ('template/header.htm');

    if (!isset($_GET['pg'])) $page = 1; else $page =& $_GET['pg'];
    if(!is_numeric($page)) $page = 1;
    $offset = ($page*$archive_is)-$archive_is;
    $getrows = mysql_query("SELECT PostID FROM ".POSTS_TBL." WHERE (PostCat1='".$dir['PCatID']."') OR (PostCat2='".$dir['PCatID']."') OR
    (PostCat3='".$dir['PCatID']."') OR (PostCat4='".$dir['PCatID']."') AND (Draft=0)");
    $pagecount = ceil(mysql_num_rows($getrows)/$archive_is);

    $paginate = NULL;
    
    if($page>1) $paginate .= '<a href="archive.php?cat='.$_GET['cat'].'&amp;pg='.($page-1).'">'.$language['back'].'</a> | ';
        else $paginate .= $language['backlimit'];
    for($pg = 1; $pg <= $pagecount; ++$pg) {
    if ($pg == $page) $paginate .= '<strong>'.$pg.'</strong> ';
        else $paginate .= '<a href="archive.php?cat='.$_GET['cat'].'&amp;pg='.$pg.'">'.$pg.'</a> ';
    }
    if($page<$pagecount) $paginate .= '| <a href="archive.php?cat='.$_GET['cat'].'&amp;pg='.($page+1).'">'.$language['next'].'</a>';
        else $paginate .= $language['nextlimit'];

    $get_cat_names = mysql_query("SELECT PCatID, PostCatUrl, PostCatName FROM ".POSTCAT_TBL);
    while($get_cat_names_result = mysql_fetch_array($get_cat_names)) {
     $cname[$get_cat_names_result['PCatID']] = $get_cat_names_result['PostCatName'];
     $curl[$get_cat_names_result['PCatID']] = $get_cat_names_result['PostCatUrl'];
    }

    $getcat = mysql_query("SELECT PostID, Title, Timestamp, Body, PostCat1, PostCat2, PostCat3, PostCat4 FROM ".POSTS_TBL." WHERE (PostCat1='".$dir['PCatID']."') OR
    (PostCat2='".$dir['PCatID']."') OR (PostCat3='".$dir['PCatID']."') OR (PostCat4='".$dir['PCatID']."') AND (Draft=0) ORDER BY
    Timestamp DESC LIMIT $offset,$archive_is");
    if(mysql_num_rows($getcat) == 0) {
        $links =& $language['noblog'];
    } else {
        $links = NULL;
        while($posts = mysql_fetch_array($getcat)) {
            $category = NULL;
            for ($i = 1; $i <= 4; ++$i) {
                if(empty($posts['PostCat'.$i])) {
                    $category .= '';
                } elseif($curl[$posts['PostCat'.$i]] == $_GET['cat']) {
                    $category .= $cname[$posts['PostCat'.$i]].$delimiterlist_is;
                } else {
                    $category .= '<a href="archive.php?cat='.$curl[$posts['PostCat'.$i]].'">'.$cname[$posts['PostCat'.$i]].'</a>'.$delimiterlist_is;
                }
            } 
            
            $category = substr($category, 0, -strlen($delimiterlist_is));
            if(empty($category)) $category =& $language['nocategory'];
        
            $postcatdirlink = $language['postcatdirlink'];
            $use_template = array('{DATE}' => date($listdatepattern_is, $posts['Timestamp']), '{TITLE}' => $posts['Title'],
                '{POST_ID}' => $posts['PostID'], '{CATEGORIES}' => $category, '{SUMMARY}' => SummariseEntry($posts['Body']));
            foreach($use_template as $key => $value) {
                $postcatdirlink = @str_replace($key, $value, $postcatdirlink);
            }
            $links .= $postcatdirlink;
        }
    }

    $use_template = array('{TITLE}' => $pagetitle, '{DESCRIPTION}' => $dir['PostDesc'], '{PAGINATE}' => $paginate, '{LINKS}' => $links);
    if ($file = file_get_contents('template/catdir.htm')) {
        foreach($use_template as $key => $value) {
            $file = @str_replace($key, $value, $file);
        }
        echo $file;
    }

    include ('template/footer.htm');
    exit();
} elseif(isset($_GET['miniblog'])) {
## Check if Mini-Blog exists
    $check = strpos($_GET['miniblog'], '+');
    $check .= strpos($_GET['miniblog'], '%20');
    if(!$check) {
        // $getinfo must be error-suppressed in case the user tries to draw from a Mini-Blog that doesn't exist.
        $getinfo = @mysql_query("SELECT MiniID FROM ".$_GET['miniblog']." LIMIT 1");
    }
    if (!$getinfo or $check) {
        $pagetitle =& $language['404'];
        include ('template/header.htm');
        include ('template/404.htm');
        include ('template/footer.htm');
        exit();
    }
## If script didn't EXIT(), show the Mini-Blog archive
    $mini_info = mysql_query("SELECT MiniBlogUrl, MiniBlogTitle FROM ".MINIBLOG_TBL." WHERE MiniBlogUrl='".$_GET['miniblog']."' LIMIT 1");
    $mini = mysql_fetch_array($mini_info);
    
    $pagetitle = str_replace('{MINIBLOG_URL}', $mini['MiniBlogUrl'], $language['miniblogarchive']);
    $pagetitle = str_replace('{MINIBLOG_TITLE}', $mini['MiniBlogTitle'], $pagetitle);
    include('template/header.htm');

    if (!isset($_GET['pg'])) $page = 1; else $page =& $_GET['pg'];
    if(!is_numeric($page)) $page = 1;
    $offset = ($page*$archive_is)-$archive_is;
    $getrows = mysql_query("SELECT MiniID FROM ".$mini['MiniBlogUrl']." WHERE MiniDraft=0");
    $pagecount = ceil(mysql_num_rows($getrows)/$archive_is);

    $paginate = NULL;
    
    if($page>1) $paginate .= '<a href="archive.php?miniblog='.$mini['MiniBlogUrl'].'&amp;pg='.($page-1).'">'.$language['back'].'</a> | ';
        else $paginate .= $language['backlimit'];
    for($pg = 1; $pg <= $pagecount; ++$pg) {
    if ($pg == $page) $paginate .= '<strong>'.$pg.'</strong> ';
        else $paginate .= '<a href="archive.php?miniblog='.$mini['MiniBlogUrl'].'&amp;pg='.$pg.'">'.$pg.'</a> ';
    }
    if($page<$pagecount) $paginate .= '| <a href="archive.php?miniblog='.$mini['MiniBlogUrl'].'&amp;pg='.($page+1).'">'.$language['next'].'</a>';
        else $paginate .= $language['nextlimit'];

    $links = NULL;
    $getlinks = mysql_query("SELECT MiniID, MiniTitle, MiniBody, MiniTimestamp FROM ".$mini['MiniBlogUrl']." WHERE MiniDraft=0 ORDER BY MiniTimestamp
        DESC LIMIT $offset,$archive_is");
    while ($posts = mysql_fetch_array($getlinks)) {
        $minibloglink = $language['minibloglink'];
        $use_template = array('{DATE}' => date($listdatepattern_is, $posts['MiniTimestamp']), '{URLSTRING}' => $mini['MiniBlogUrl'],
            '{MINI_ID}' => $posts['MiniID'], '{TITLE}' => $posts['MiniTitle'], '{SUMMARY}' => SummariseEntry($posts[MiniBody]));
        foreach($use_template as $key => $value) {
            $minibloglink = @str_replace($key, $value, $minibloglink);
        }
        $links .= $minibloglink;
    }

    $use_template = array('{TITLE}' => $pagetitle, '{PAGINATE}' => $paginate, '{LINKS}' => $links);
    if ($file = file_get_contents('template/archive.htm')) {
        foreach($use_template as $key => $value) {
            $file = @str_replace($key, $value, $file);
        }
        echo $file;
    }

    include ('template/footer.htm');
    exit();
} else {
## Normal post archive
    if (!isset($_GET['pg'])) $page = 1; else $page =& $_GET['pg'];
    if(!is_numeric($page)) $page = 1;
    $offset = ($page*$archive_is)-$archive_is;
    $getrows = mysql_query("SELECT PostID FROM ".POSTS_TBL." WHERE Draft=0");
    $pagecount = ceil(mysql_num_rows($getrows)/$archive_is);

    $get_cat_names = mysql_query("SELECT PCatID, PostCatUrl, PostCatName FROM ".POSTCAT_TBL);
    while($get_cat_names_result = mysql_fetch_array($get_cat_names)) {
     $cname[$get_cat_names_result['PCatID']] = $get_cat_names_result['PostCatName'];
     $curl[$get_cat_names_result['PCatID']] = $get_cat_names_result['PostCatUrl'];
    }

    $links = NULL;
    $getlinks = mysql_query("SELECT * FROM ".POSTS_TBL." WHERE Draft=0 ORDER BY Timestamp DESC LIMIT $offset,$archive_is");
    while ($posts = mysql_fetch_array($getlinks)) {
        $category = NULL;
        for ($i = 1; $i <= 4; ++$i) {
            if(empty($posts['PostCat'.$i])) $category .= '';
                else $category .= '<a href="archive.php?cat='.$curl[$posts['PostCat'.$i]].'">'.$cname[$posts['PostCat'.$i]].'</a>'.$delimiterlist_is;
        }
        $category = substr($category, 0, -strlen($delimiterlist_is));
        if(empty($category)) $category =& $language['nocategory'];

        $bloglink = $language['bloglink'];
        $use_template = array('{DATE}' => date($listdatepattern_is, $posts['Timestamp']), '{POST_ID}' => $posts['PostID'],
            '{TITLE}' => $posts['Title'], '{CATEGORIES}' => $category, '{SUMMARY}' => SummariseEntry($posts['Body']));
        foreach($use_template as $key => $value) {
            $bloglink = @str_replace($key, $value, $bloglink);
        }
        $links .= $bloglink;
    }

    $pagetitle =& $language['archive'];
    include ('template/header.htm');

    $paginate = NULL;
    if($page>1) $paginate .= '<a href="archive.php?pg='.($page-1).'">'.$language['back'].'</a> | ';
        else $paginate .= $language['backlimit'];
    for($pg = 1; $pg <= $pagecount; ++$pg) {
    if ($pg == $page) $paginate .= '<strong>'.$pg.'</strong> ';
        else $paginate .= '<a href="archive.php?pg='.$pg.'">'.$pg.'</a> ';
    }
    if($page<$pagecount) $paginate .= '| <a href="archive.php?pg='.($page+1).'">'.$language['next'].'</a>';
        else $paginate .= $language['nextlimit'];

    $use_template = array('{TITLE}' => $pagetitle, '{PAGINATE}' => $paginate, '{LINKS}' => $links);
    if ($file = file_get_contents('template/archive.htm')) {
        foreach($use_template as $key => $value) {
            $file = @str_replace($key, $value, $file);
        }
        echo $file;
    }

    include ('template/footer.htm');
    exit();
}
?>

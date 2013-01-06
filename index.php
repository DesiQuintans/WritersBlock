<?php
# Writer's Block v3.8a: http://www.desiquintans.com/writersblock
# Writer's Block is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('admin/control.php');
require ('template/'.LANGUAGE_FILE);

$pagetitle =& $defaulttitle_is;
include ('template/header.htm');

$get_cat_names = mysql_query("SELECT PCatID, PostCatUrl, PostCatName FROM ".POSTCAT_TBL);
while($get_cat_names_result = mysql_fetch_array($get_cat_names)) {
    $cname[$get_cat_names_result['PCatID']] = $get_cat_names_result['PostCatName'];
    $curl[$get_cat_names_result['PCatID']] = $get_cat_names_result['PostCatUrl'];
}

$get_user_names = mysql_query("SELECT UserID, DispName FROM ".USERS_TBL);
while($get_user_names_result = mysql_fetch_array($get_user_names)) {
    $users[$get_user_names_result['UserID']] = $get_user_names_result['DispName'];
}

$getposts = mysql_query("SELECT * FROM ".POSTS_TBL." WHERE Draft=0 ORDER BY Timestamp DESC LIMIT $showposts_is");
$blog_entry_template = file_get_contents('template/blog.htm');
while ($post = mysql_fetch_array($getposts)) {
    $comms = mysql_query("SELECT CommID FROM ".COMMENTS_TBL." WHERE PostID='".$post['PostID']."'");
    $countcomms = mysql_num_rows($comms);
    
    $category = NULL;
    for ($i = 1; $i <= 4; ++$i) {
        if(empty($post['PostCat'.$i])) {$category .= '';} else {
            $category .= '<a href="archive.php?cat='.$curl[$post['PostCat'.$i]].'">'.$cname[$post['PostCat'.$i]].'</a>'.$delimitermain_is;
        }
    }
    $category = substr($category, 0, -strlen($delimitermain_is));
    if(empty($category)) $category =& $language['nocategory'];

    $prevlink = mysql_query("SELECT PostID FROM ".POSTS_TBL." WHERE Timestamp<".$post['Timestamp']." AND Draft=0 ORDER BY Timestamp DESC LIMIT 1");
    $row = mysql_fetch_array($prevlink); $prevvalue =& $row['PostID'];
    if($prevvalue) {$prev = '<a href="permalink.php?PostID='.$prevvalue.'">'.$language['prevpost'].'</a>';
    } else {$prev =& $language['prevpostlimit'];}
    
    $nextlink = mysql_query("SELECT PostID FROM ".POSTS_TBL." WHERE Timestamp>".$post['Timestamp']." AND Draft=0 ORDER BY Timestamp ASC LIMIT 1");
    $row = mysql_fetch_array($nextlink); $nextvalue =& $row['PostID'];
    if($nextvalue) {$next = '<a href="permalink.php?PostID='.$nextvalue.'">'.$language['nextpost'].'</a>';
    } else {$next =& $language['nextpostlimit'];}

    $use_template = array('{TITLE}' => $post['Title'], '{POST_ID}' => $post['PostID'],
        '{DATE}' => date($postdatepattern_is, $post['Timestamp']), '{BODY}' => $post['Body'], '{COMMENT_COUNT}' => $countcomms,
        '{CATEGORY}' => $category, '{PREV}' => $prev, '{NEXT}' => $next, '{AUTHOR}' => $users[$post['Author']]);
    if ($file = $blog_entry_template) {
        foreach($use_template as $key => $value) {
            $file = @str_replace($key, $value, $file);
        }
        echo $file;
    }
}

include ('template/footer.htm');
?>
<?
# Writer's Block v3.8a: http://www.desiquintans.com/writersblock
# Writer's Block is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('admin/control.php');
require ('template/'.LANGUAGE_FILE);

if(isset($_GET['blog'])) {
    $check = strpos($_GET['blog'], '+');
    $check .= strpos($_GET['blog'], '%20');
    if(!$check) {
        // $exist must be error-suppressed in case the user tries to draw from a Mini-Blog that doesn't exist.
        $exist = @mysql_query("SELECT MiniID FROM ".$_GET['blog']." WHERE MiniDraft=0 LIMIT 1");
    }
}

if(!isset($_GET['blog']) or $check or !$exist) {
## If a user accesses this without defining a Mini-Blog table, if the query string contains spaces or if the table doesn't exist.
    $pagetitle =& $language['404'];
    include ('template/header.htm');
    include ('template/404.htm');
    include ('template/footer.htm');
    exit();
} else {
    $mini_info = mysql_query("SELECT MiniBlogTitle, MiniBlogDesc, MiniBlogDate, MiniBlogShow, MiniBlogAuthor FROM ".MINIBLOG_TBL."
        WHERE MiniBlogUrl='".$_GET['blog']."' LIMIT 1");
    $mini_inf = mysql_fetch_array($mini_info);
    $get_author = mysql_query("SELECT DispName FROM ".USERS_TBL." WHERE UserID='".$mini_inf['MiniBlogAuthor']."' LIMIT 1");
    $author = mysql_fetch_array($get_author);
}

## If the script didn't exit and a permalink isn't requested, display the Mini-Blog.
if(isset($_GET['entry'])) {
    $check = strpos($_GET['entry'], '+');
    $check .= strpos($_GET['entry'], '%20');
    if(!$check) {
        $checkentry = @mysql_query("SELECT MiniID, MiniTitle, MiniBody, MiniTimestamp FROM ".$_GET['blog']." WHERE MiniDraft=0
            AND MiniID='".$_GET['entry']."' LIMIT 1");
    } elseif(mysql_num_rows($checkentry) == 0 or $check) {
    ## If a user tries to pull the permalink for an invalid MiniPost.
        $pagetitle =& $language['404'];
        include ('template/header.htm');
        include ('template/404.htm');
        include ('template/footer.htm');
        exit();
    }
## If script didn't EXIT, show the single entry.
	$entry = mysql_fetch_array($checkentry);
    $pagetitle =& $entry['MiniTitle'];
	$sitedesc_is = $mini_inf['MiniBlogDesc'];
    $prevlink = mysql_query("SELECT MiniID FROM ".$_GET['blog']." WHERE MiniID<".$_GET['entry']." AND MiniDraft=0 ORDER BY MiniTimestamp DESC LIMIT 1");
    $row = mysql_fetch_array($prevlink); $prevvalue =& $row['MiniID'];
    if($prevvalue) {
    $prev = '<a href="miniblog.php?blog='.$_GET['blog'].'&amp;entry='.$prevvalue.'">'.$language['prevpost'].'</a>';
    } else {$prev =& $language['prevpostlimit'];}

    $nextlink = mysql_query("SELECT MiniID FROM ".$_GET['blog']." WHERE MiniID>".$_GET['entry']." AND MiniDraft=0 ORDER BY MiniTimestamp ASC LIMIT 1");
    $row = mysql_fetch_array($nextlink); $nextvalue =& $row['MiniID'];
    if($nextvalue) {
    $next = '<a href="miniblog.php?blog='.$_GET['blog'].'&amp;entry='.$nextvalue.'">'.$language['nextpost'].'</a>';
    } else {$next =& $language['nextpostlimit'];}

    include('template/header.htm');

    $use_template = array('{TITLE}' => $entry['MiniTitle'], '{BODY}' => $entry['MiniBody'], '{AUTHOR}' => $author['DispName'],
    '{DATE}' => date($mini_inf['MiniBlogDate'], $entry['MiniTimestamp']), '{MINI_ID}' => $entry['MiniID'], '{BLOG_URL}' => $_GET['blog'],
    '{NEXT}' => $next, '{PREV}' => $prev);
    if ($contentfile = file_get_contents('template/minipost.htm')) {
        foreach($use_template as $key => $value) {
            $contentfile = @str_replace($key, $value, $contentfile);
        }
    $content =& $contentfile;
    }
    
    $sandwich_template = array('{MINIBLOG_TITLE}' => $mini_inf['MiniBlogTitle'], '{MINIBLOG_DESC}' => $mini_inf['MiniBlogDesc'],
        '{MINIBLOG_CONTENT}' => $content, '{BLOG_URL}' => $_GET['blog'], '{AUTHOR}' => $author['DispName']);
    if ($file = file_get_contents('template/miniblog.htm')) {
        foreach($sandwich_template as $key => $value) {
            $file = @str_replace($key, $value, $file);
        }
    echo $file;
    }

    include ('template/footer.htm');
    exit();
} else {
## Show the normal Mini-Blog
    $get_posts = mysql_query("SELECT MiniID, MiniTitle, MiniBody, MiniTimestamp FROM ".$_GET['blog']." WHERE MiniDraft=0 ORDER BY
        MiniTimestamp DESC LIMIT ".$mini_inf['MiniBlogShow']);
    $content = NULL;
    $miniblog_entry_template = file_get_contents('template/minipost.htm');
    while($mini = mysql_fetch_array($get_posts)) {
        $prevlink = mysql_query("SELECT MiniID FROM ".$_GET['blog']." WHERE MiniID<".$mini['MiniID']." AND MiniDraft=0 ORDER BY MiniTimestamp DESC LIMIT 1");
        $row = mysql_fetch_array($prevlink); $prevvalue =& $row['MiniID'];
        if($prevvalue) {
        $prev = '<a href="miniblog.php?blog='.$_GET['blog'].'&amp;entry='.$prevvalue.'">'.$language['prevpost'].'</a>';
        } else {$prev =& $language['prevpostlimit'];}

        $nextlink = mysql_query("SELECT MiniID FROM ".$_GET['blog']." WHERE MiniID>".$mini['MiniID']." AND MiniDraft=0 ORDER BY MiniTimestamp ASC LIMIT 1");
        $row = mysql_fetch_array($nextlink); $nextvalue =& $row['MiniID'];
        if($nextvalue) {
        $next = '<a href="miniblog.php?blog='.$_GET[blog].'&amp;entry='.$nextvalue.'">'.$language['nextpost'].'</a>';
        } else {$next =& $language['nextpostlimit'];}

        $use_template = array('{TITLE}' => $mini['MiniTitle'], '{BODY}' => $mini['MiniBody'], '{AUTHOR}' => $author['DispName'],
        '{DATE}' => date($mini_inf['MiniBlogDate'], $mini['MiniTimestamp']), '{MINI_ID}' => $mini['MiniID'], '{BLOG_URL}' => $_GET['blog'],
        '{PREV}' => $prev, '{NEXT}' => $next);
        if ($contentfile = $miniblog_entry_template) {
            foreach($use_template as $key => $value) {
                $contentfile = @str_replace($key, $value, $contentfile);
            }
        $content .= $contentfile;
        }
    }

    $pagetitle =& $mini_inf['MiniBlogTitle'];
    $sitedesc_is = $mini_inf['MiniBlogDesc'];
    include ('template/header.htm');
    
    $sandwich_template = array('{MINIBLOG_TITLE}' => $mini_inf['MiniBlogTitle'], '{MINIBLOG_DESC}' => $mini_inf['MiniBlogDesc'],
        '{MINIBLOG_CONTENT}' => $content, '{BLOG_URL}' => $_GET['blog'], '{AUTHOR}' => $author['DispName']);
    if ($file = file_get_contents('template/miniblog.htm')) {
        foreach($sandwich_template as $key => $value) {
            $file = @str_replace($key, $value, $file);
        }
    echo $file;
    }

    include ('template/footer.htm');
    exit();
}
?>
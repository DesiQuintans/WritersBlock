<?php
# Writer's Block v3.8a: http://www.desiquintans.com/writersblock
# Writer's Block is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('admin/control.php');
require ('template/'.LANGUAGE_FILE);

if(isset($_GET['PostID'])) {
    $check = strpos($_GET['PostID'], '+');
    $check .= strpos($_GET['PostID'], '%20');
    $check .= strpos($_GET['PostID'], '(');
    $check .= strpos($_GET['PostID'], ')');
    $check .= strpos($_GET['PostID'], '\'');
}
if(isset($_POST['PostID'])) {
    $check = strpos($_GET['PostID'], '+');
    $check .= strpos($_GET['PostID'], '%20');
    $check .= strpos($_GET['PostID'], '(');
    $check .= strpos($_GET['PostID'], ')');
    $check .= strpos($_GET['PostID'], '\'');
}

if((!isset($_GET['PostID']) and !isset($_POST['PostID'])) or $check) {
## If a user accesses this without defining a post or if it has URL encoded spaces in it
    $pagetitle =& $language['404'];
    include ('template/header.htm');
    include ('template/404.htm');
    include ('template/footer.htm');
    exit();
}

if($_POST['submit']) {
    // Test open status of thread before proper processing.
    $threadsettings = mysql_query("SELECT Timestamp, Thread FROM ".POSTS_TBL." WHERE PostID=".$_POST['PostID']);
    $threadsettings = @mysql_fetch_array($threadsettings);
        $threadtimestamp =& $threadsettings['Timestamp'];
        $threadstatus =& $threadsettings['Thread'];
    switch ($threadstatus) {
        case 1:
        // Closed. End processing.
            $pagetitle =& $language['closedtitle'];
            include ('template/header.htm');

            $use_template = array('{TITLE}' => $pagetitle, '{BODY}' => $language['closedmessage']);
            if ($file = file_get_contents('template/message.htm')) {
                foreach($use_template as $key => $value) {
                    $file = @str_replace($key, $value, $file);
                }
            echo $file;
            }

            include ('template/footer.htm');
            exit();
        break;
        case 2:
        // Auto. Check if comment is old.
            $threadage = time() - $threadtimestamp;
            if($threadage >= $threadlife_is) {
            // Old thread. Change status to closed and end processing.
            mysql_query("UPDATE ".POSTS_TBL." SET Thread=1 WHERE PostID=".$_POST['PostID']);
            $pagetitle =& $language['closedtitle'];
            include ('template/header.htm');

            $use_template = array('{TITLE}' => $pagetitle, '{BODY}' => $language['closedmessage']);
            if ($file = file_get_contents('template/message.htm')) {
                foreach($use_template as $key => $value) {
                    $file = @str_replace($key, $value, $file);
                }
            echo $file;
            }

            include ('template/footer.htm');
            exit();
            } // If thread unexpired, process comment.
        break;
        case 3:
        default:
        // Open. Process comment.
        break;
    }
        
    if(empty($_POST['name']) or empty($_POST['comment'])) {
        $pagetitle =& $language['badcommenttitle'];
        if(isset($_POST['url'])) $urlfield =& $_POST['url']; else $urlfield = 'http://';
        if(isset($_POST['name'])) $namefield =& $_POST['name']; else $namefield = '';
        if(isset($_POST['email'])) $emailfield =& $_POST['email']; else $emailfield = '';
        if(isset($_POST['comment'])) $commentfield =& $_POST['comment']; else $commentfield = '';
        include ('template/header.htm');
        
        $replace_comment = array('{SELF}' => $_SERVER['PHP_SELF'],'{POST_ID}' => $_POST['PostID'],'{NAME}' => $namefield, '{EMAIL}' => $emailfield,
            '{URL}' => $urlfield,'{COMMENT}' => $commentfield); $error_commentform =& $language['badcommentform'];
        foreach($replace_comment as $tag => $real) {
            $error_commentform = str_replace($tag, $real, $error_commentform);
        }
        
        $use_template = array('{TITLE}' => $pagetitle, '{BODY}' => $error_commentform);
        if ($file = file_get_contents('template/message.htm')) {
            foreach($use_template as $key => $value) {
                $file = @str_replace($key, $value, $file);
            }
        echo $file;
        }
                
        include ('template/footer.htm');
        exit();
    }
    ## Spamguard: URL string, email and body scanning based on blacklists.
    // This function clears comments from the three blacklists and turns the list into regex strings.
    function ClearComments($targetblacklist) {
        // Remove comments.
        $clearedlist = preg_replace('/(##).+(##)/', '', file_get_contents($targetblacklist));
        // Turn spaces into vertical bars.
        $compiledlist = preg_replace('/\s+/', '|', trim($clearedlist));
        return $compiledlist;
    };

## Email scanning
    $BlockedEmails = ClearComments('admin/include/spamguard-email.txt');
    if(eregi($BlockedEmails, $_POST['email'])) {
        $pagetitle =& $language['spamtitle'];
        include ('template/header.htm');

        $use_template = array('{TITLE}' => $pagetitle, '{BODY}' => $language['spammessage']);
        if ($file = file_get_contents('template/message.htm')) {
            foreach($use_template as $key => $value) {
                $file = @str_replace($key, $value, $file);
            }
        echo $file;
        }

        include ('template/footer.htm');
        exit();
    } // Email not blacklisted? Continue executing.

## Body text scanning
    $BlockedText = ClearComments('admin/include/spamguard-text.txt');
    if(eregi($BlockedText, $_POST['comment'])) {
        $pagetitle =& $language['spamtitle'];
        include ('template/header.htm');

        $use_template = array('{TITLE}' => $pagetitle, '{BODY}' => $language['spammessage']);
        if ($file = file_get_contents('template/message.htm')) {
            foreach($use_template as $key => $value) {
                $file = @str_replace($key, $value, $file);
            }
        echo $file;
        }

        include ('template/footer.htm');
        exit();
    } // Comment body not blacklisted? Continue executing.

## URL string scanning
    $BlockedUrls = ClearComments('admin/include/spamguard.txt');
    // Get all hrefs from comment body and put them into array
    preg_match_all('/href\s*=\s*.?\s*[^>]+/i', $_POST['comment'], $urls_in_text, PREG_PATTERN_ORDER);
    // Turn array from above into string (sans 'href=') and append URL field to the end.
    $given_urls = implode(' % ', $urls_in_text[0]);
    $given_urls = preg_replace('/href\s*=\s*.?\s*/i', '', $given_urls, -1).$_POST['url'];
    // Search string for blocked URL fragments and kill script if found.
    if(eregi($BlockedUrls, $given_urls)) {
        $pagetitle =& $language['spamtitle'];
        include ('template/header.htm');
        
        $use_template = array('{TITLE}' => $pagetitle, '{BODY}' => $language['spammessage']);
        if ($file = file_get_contents('template/message.htm')) {
            foreach($use_template as $key => $value) {
                $file = @str_replace($key, $value, $file);
            }
        echo $file;
        }
            echo '<!-- URLS IN TEXT: '.implode(' % ', $urls_in_text[0]).' -->';
echo "<!-- GIVEN URLS: $given_urls -->";
        include ('template/footer.htm');
        exit();
    } else { // URL scanning is the last check. If a comment passes all three checks, it is put in the database at this point.
        if(strlen($_POST['url']) <= 7) $url = ''; else $url = strip_tags($_POST['url']);
        if($_POST['remember']) {
            $expiry = time()+60*60*24*60;
            setcookie('commenter_name', strip_tags($_POST['name']), $expiry, '', '', 0);
            setcookie('commenter_email', strip_tags($_POST['email']), $expiry, '', '', 0);
            setcookie('commenter_url', $url, $expiry, '', '', 0);
        }
        $comment = trim($_POST['comment']);
        $comment = strip_tags($comment, '<i><em><b><strong><u><a><br><br /><span>');
        include ('admin/include/xmlfriendly.inc');
        foreach($ItemArray as $key => $value) {
          $comment = str_replace($key, $value, $comment);
        }
        $comment_sent = mysql_query("INSERT INTO ".COMMENTS_TBL." (PostID, Name, Email, Url, Body, Timestamp) VALUES
        ('".$_POST['PostID']."', '".strip_tags($_POST['name'])."', '".strip_tags($_POST['email'])."', '$url', '".nl2br($comment)."', 
            '".time()."')");
        $get_commid = mysql_query("SELECT CommID FROM ".COMMENTS_TBL." ORDER BY CommID DESC LIMIT 1");
        $ident = mysql_fetch_array($get_commid);
    }
} // This ends the block for handling comments.

if($comment_sent) {
    $pagetitle =& $language['cleantitle'];
    include ('template/header.htm');
    
    $use_template = array('{TITLE}' => $pagetitle, '{BODY}' => str_replace('{COMMENT_LINK}', $_SERVER['PHP_SELF'].'?PostID='.$_POST['PostID'].'#C'.$ident['CommID'],$language['cleanmessage']));
    if ($file = file_get_contents('template/message.htm')) {
        foreach($use_template as $key => $value) {
            $file = @str_replace($key, $value, $file);
        }
    echo $file;
    }
    include ('template/footer.htm');
    exit();
}

if (isset($_GET['PostID'])) {
## Check if permalink exists
    $getpost = @mysql_query("SELECT Title, Timestamp, Body, PostCat1, PostCat2, PostCat3, PostCat4, Author FROM ".POSTS_TBL." WHERE
        PostID='".$_GET['PostID']."' AND Draft=0");
    if (mysql_num_rows($getpost) == 0) {
        $pagetitle =& $language['404'];
        include ('template/header.htm');
        include ('template/404.htm');
        include ('template/footer.htm');
        exit();
    }
## If the script didn't EXIT(), show the permalink for the post
    $postcontents = mysql_fetch_array($getpost);
    $pagetitle =& $postcontents['Title'];
    $get_author = mysql_query("SELECT DispName, Bio FROM ".USERS_TBL." WHERE UserID='".$postcontents['Author']."'");
    $author = mysql_fetch_array($get_author);
    
    $get_cat_names = mysql_query("SELECT PCatID, PostCatUrl, PostCatName FROM ".POSTCAT_TBL);
    while($get_cat_names_result = mysql_fetch_array($get_cat_names)) {
        $cname[$get_cat_names_result['PCatID']] = $get_cat_names_result['PostCatName'];
        $curl[$get_cat_names_result['PCatID']] = $get_cat_names_result['PostCatUrl'];
    }
    
    $category = NULL;
    for ($i = 1; $i <= 4; ++$i) {
        if(empty($postcontents['PostCat'.$i])) {$category .= '';} else {
            $category .= '<a href="archive.php?cat='.$curl[$postcontents['PostCat'.$i]].'">'.$cname[$postcontents['PostCat'.$i]].'</a>'.$delimitermain_is;
        }
    }
    $category = substr($category, 0, -strlen($delimitermain_is));
    if(empty($category)) $category =& $language['nocategory'];
    
    include ('template/header.htm');
    
    // Check thread status and hide form if thread is closed.
    $threadsettings = mysql_query("SELECT Timestamp, Thread FROM ".POSTS_TBL." WHERE PostID=".$_GET['PostID']);
        $row = @mysql_fetch_array($threadsettings);
        $threadtimestamp =& $row['Timestamp'];
        $threadstatus =& $row['Thread'];
    $threadage = time() - $threadtimestamp;
    if(($threadage >= $threadlife_is) or $threadstatus == 1) {
	mysql_query("UPDATE ".POSTS_TBL." SET Thread=1 WHERE PostID=".$_GET['PostID']);
        $normal_commentform =& $language['closedthread'];
    } else {
        // Get commenter cookies
        if (isset($_COOKIE['commenter_name'])) $your_name =& $_COOKIE['commenter_name']; else $your_name = '';
        if (isset($_COOKIE['commenter_email'])) $your_email =& $_COOKIE['commenter_email']; else $your_email = '';
        if (isset($_COOKIE['commenter_url'])) $your_url =& $_COOKIE['commenter_url']; else $your_url = 'http://';

        $replace_comment = array('{SELF}' => $_SERVER['PHP_SELF'], '{POST_ID}' => $_GET['PostID'], '{COOKIE_NAME}' => $your_name,
            '{COOKIE_EMAIL}' => $your_email, '{COOKIE_URL}' => $your_url);
        $normal_commentform = $language['commentform'];
        foreach($replace_comment as $tag => $real) {
            $normal_commentform = @str_replace($tag, $real, $normal_commentform);
        }
    }

    $comments = NULL;
    $getcomm = mysql_query("SELECT CommID, Name, Url, Body, Timestamp FROM ".COMMENTS_TBL." WHERE PostID='".$_GET['PostID']."' ORDER BY
    Timestamp $commentorder_is");
    
    switch(mysql_num_rows($getcomm)) {
    case 0:
    // Show a 'no comments' message based on open status of thread.
        if($postcontents['Thread'] == 1) {
            $comments =& $language['emptythread'];
        } else {
            $comments =& $language['nocomments'];
        }
        break;
    default:
        $each_comment_template = file_get_contents('template/comment.htm');
        while ($comm = mysql_fetch_array($getcomm)) {
            if(empty($comm['Url'])) $name =& $comm['Name']; else $name = '<a href="'.$comm['Url'].'">'.$comm['Name'].'</a>';
            
            $use_template = array('{COMMENT}' => '<a name="C'.$comm['CommID'].'"></a>'.$comm['Body'], '{NAME}' => $name,
                '{DATE}' => date($commdatepattern_is, $comm['Timestamp']),
                '{P_URL}' => 'permalink.php?PostID='.$_POST['PostID'].'#C'.$comm['CommID']);
            if ($file = $each_comment_template) {
                foreach($use_template as $key => $value) {
                    $file = @str_replace($key, $value, $file);
                }
            $comments .= $file;
            }
        }
        break;
    }
    
    $prevlink = mysql_query("SELECT PostID FROM ".POSTS_TBL." WHERE PostID<".$_GET['PostID']." AND Draft=0 ORDER BY Timestamp DESC LIMIT 1");
    $prevarray = mysql_fetch_array($prevlink); $prevvalue =& $prevarray['PostID'];
    if($prevvalue) {
    $prev = '<a href="permalink.php?PostID='.$prevvalue.'">'.$language['prevpost'].'</a>';
    } else {$prev =& $language['prevpostlimit'];}
    
    $nextlink = mysql_query("SELECT PostID FROM ".POSTS_TBL." WHERE PostID>".$_GET['PostID']." AND Draft=0 ORDER BY Timestamp ASC LIMIT 1");
    $nextarray = mysql_fetch_array($nextlink); $nextvalue =& $nextarray['PostID'];
    if($nextvalue) {
    $next = '<a href="permalink.php?PostID='.$nextvalue.'">'.$language['nextpost'].'</a>';
    } else {$next =& $language['nextpostlimit'];}
    
    $use_template = array('{TITLE}' => $pagetitle, '{BODY}' => $postcontents['Body'], '{DATE}' => date($postdatepattern_is, $postcontents['Timestamp']),
        '{CATEGORY}' => $category, '{PREV}' => $prev, '{NEXT}' => $next, '{COMMENT_FORM}' => $normal_commentform,
        '{COMMENTS}' => $comments, '{AUTHOR}' => $author['DispName'], '{BIO}' => $author['Bio'], '{POST_ID}' => $_GET['PostID']);
    if ($permalink = file_get_contents('template/permalink.htm')) {
        foreach($use_template as $key => $value) {
            $permalink = @str_replace($key, $value, $permalink);
        }
    echo $permalink;
    }

    include ('template/footer.htm');
    exit();
}
?>

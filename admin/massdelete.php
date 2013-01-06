<?php
# Writer's Block v3.8: http://www.desiquintans.com/writersblock
# Writer's Block is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('control.php');

// Check for authentication
if (!isset($_COOKIE['auth_id'], $_COOKIE['auth_username'], $_COOKIE['auth_hash'])) {
    include('login.php');
    exit();
} else {
    $authenticate = mysql_query("SELECT Permission FROM ".USERS_TBL." WHERE UserID='".$_COOKIE['auth_id']."' AND
    UserName='".$_COOKIE['auth_username']."' AND Password='".$_COOKIE['auth_hash']."'");
    if(mysql_num_rows($authenticate) != 1) {
        include('login.php');
        exit();
    } else {
        $extend_expiry = time()+7200;
        setcookie('auth_id', $_COOKIE['auth_id'], $extend_expiry, '', '', 0);
        setcookie('auth_username', $_COOKIE['auth_username'], $extend_expiry, '', '', 0);
        setcookie('auth_hash', $_COOKIE['auth_hash'], $extend_expiry, '', '', 0);
    }
    $p = mysql_fetch_array($authenticate);
    if ($p['Permission'] == 3) {
        $pagetitle = 'Not authorised';
        include ('include/head.htm');
        print '<p align="center"><img src="include/img/unauthorised.png" width="16" height="16" alt="" /> You are not authorised to moderate comments.</p>';
        include ('include/foot.htm');
        exit();
    }
}

$pagetitle = 'Mass-delete comments';
include ('include/head.htm');

if($_POST['submitted']) {
    // Check input for dangerous queries.
    if(empty($_POST['delete_query']) || $_POST['delete_query'] == '/' || $_POST['delete_query'] == '.' || $_POST['delete_query'] == ':') {
        echo '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> <strong>An invalid keyword was supplied.</strong> ';
    }
        // Search URL and Body.
            $searchboth = mysql_query("SELECT CommID, Url, Body FROM ".COMMENTS_TBL);
            // Get all URLs from all comments and put them into $delete_array.
            $delete_array = NULL;
            while($geturls = mysql_fetch_array($searchboth)) {
                // Get URLs from Body of each comment.
                $recent_string = preg_match_all('/href\s*=\s*.?\s*[^>]+/i', $geturls['Body'], $urls_in_text, PREG_PATTERN_ORDER);
                $given_urls = implode('', $urls_in_text[0]);
                $given_urls = preg_replace('/href\s*=\s*.?\s*/i', '', $given_urls, -1);
                // Get URL from URL field.
                if (empty($geturls['Url'])) $urlfield = '|'; else $urlfield = '|'.$geturls['Url'].'|';
                // Commit all URLs to array.
                $delete_array[$geturls['CommID']] = $given_urls.$urlfield;
            }
            // Loop through array entries and delete clean entries.
            foreach($delete_array as $id => $urls) {
                $urls = substr($urls, 0, -1);
                if(!@eregi($_POST['delete_query'], $urls)) {
                    unset($delete_array[$id]);
                }
            }
            // Stringify $delete_array and use in mySQL delete query.
            $del_list = NULL;
            foreach($delete_array as $id => $urls) {
                $del_list .= '(CommID='.$id.') OR ';
            }
            $del_list = substr($del_list, 0, -4);
	        $bulk_delete = mysql_query("DELETE FROM ".COMMENTS_TBL." WHERE $del_list");
            $number_deleted = mysql_affected_rows();

            echo '<p align="center"><img src="include/img/massdelete.png" width="16" height="16" alt="" /> <strong>'.$number_deleted.'</strong> ';
            if($number_deleted == 1) {
                echo 'comment was ';
            } else {
                echo 'comments were ';
            }
            echo 'deleted.</p>';
        
}

?>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="text-align: center;" name="keyword">
    <fieldset>
    <legend>Delete comments using URL query (<a href="http://www.desiquintans.com/writersblock/manual/index.php?page=usingmassdelete">[how?]</a>)</legend>
    URL fragment:<br />
    <input type="text" size="60" class="noformat" name="delete_query" />
    <p>
    <input type="submit" name="submitted" value="Delete offending comments" />
    </p>
    </fieldset>
    </form>
    <strong>Most recent URLs</strong>
    <?php

        # List URLs from thirty most recent comments.
            $recent = mysql_query("SELECT Url, Body FROM ".COMMENTS_TBL." ORDER BY Timestamp DESC LIMIT 30");
            // Get all hrefs from comment body and put them into array
            $urls_in_text = '';
            while($recent_array = mysql_fetch_array($recent)) {
                $recent_string = preg_match_all('/href\s*=\s*.?\s*[^>]+/i', $recent_array['Body'], $urls_in_text, PREG_PATTERN_ORDER);
                $given_urls = implode('', $urls_in_text[0]);
                if(empty($recent_array['Url'])) {
                    $url_field = '';
                } else {
                    $url_field = '<br />'.$recent_array['Url'];
                }
                $given_urls = preg_replace('/href\s*=\s*/i', '<br />', $given_urls, -1).$url_field;
                $given_urls = str_replace('"', '', $given_urls);
                echo $given_urls;
            }
            
            while($recent_array = mysql_fetch_array($recent)) {
                if(empty($recent_array['Url'])) {
                    echo '';
                } else {
                    echo '<br />'.$recent_array['Url'];
                }
            }
            
    ?>

<!-- Automatically brings the input field into focus. -->
<script language="JavaScript">
<!--
if (document.keyword) {
document.keyword.delete_query.focus();
}
// -->
</script>
    
<?php
    include ('include/foot.htm');
    exit();
?>

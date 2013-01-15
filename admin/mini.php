<?
# Writer's Block v3.8a: http://www.desiquintans.com/writersblock
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
}

if($_POST['submitted']) {
    ## Parse the input for any oddness.
    include ('include/xmlfriendly.inc');
    if (empty($_POST['title'])) $title = 'Untitled'; else $title = strip_tags($_POST['title']);
    if($_POST['autobreak']) $body = nl2br($_POST['body']); else $body =& $_POST['body'];
    foreach($ItemArray as $key => $value) {
        $body=@str_replace($key, $value, $body);
    }
    $body = str_replace("'", "&#146;", $body);
    if($_POST['draft']) $draft = 1; else $draft = 0;

    if($_POST['editentry']) {
        if($_POST['delete']) {
            $deleted = mysql_query("DELETE FROM ".$_POST['blog']." WHERE MiniID=".$_POST['MiniID']);
            if($deleted) {
                $pagetitle = 'Done';
                include ('include/head.htm');
                print '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Entry deleted.</strong>
                <br />Do you want to <a href="mini.php?item=entry&amp;action=new">make a new one</a>?
                </p>';
                include ('include/foot.htm');
                exit();
            }
        }
        $timestamp = strtotime($_POST['month']." ".$_POST['date']." ".$_POST['year']." ".$_POST['time']);
        $post_edited = mysql_query("UPDATE ".$_POST['blog']." SET MiniTitle='".$_POST['title']."', MiniBody='$body', MiniDraft='$draft',
            MiniTimestamp='$timestamp' WHERE MiniID=".$_POST['MiniID']);
        if ($post_edited) {
            $pagetitle = 'Done';
            include ('include/head.htm');
            print '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Entry updated.</strong>
            <br />Do you want to <a href="../miniblog.php?blog='.$_POST['blog'].'&amp;entry='.$_POST['MiniID'].'">view it</a> or
            <a href="mini.php">make a new one</a>?</p>';
            include ('include/foot.htm');
            exit();
        }
    } elseif($_POST['newentry']) {
        $timestamp = strtotime($_POST['month']." ".$_POST['date']." ".$_POST['year']." ".$_POST['time']);
        $post_made = mysql_query("INSERT INTO ".$_POST['blog']." (MiniID, MiniTitle, MiniBody, MiniTimestamp, MiniAuthor, MiniDraft) VALUES
            (NULL, '$title', '$body', '$timestamp', '".$_COOKIE['auth_id']."', $draft)");
        if($post_made) {
            $pagetitle = 'Done';
            include ('include/head.htm');
            print '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>New entry published.</strong>
            <br />Do you want to <a href="../miniblog.php?blog='.$_POST['blog'].'">view it</a>?</p>';
            include ('include/foot.htm');
            exit();
        }
    } elseif($_POST['editminiblog']) {
        if($_POST['delete']) {
            $delete_table = mysql_query("DROP TABLE ".$_POST['MiniBlogUrl']);
            $delete_entry = mysql_query("DELETE FROM ".MINIBLOG_TBL." WHERE MiniBlogID=".$_POST['MiniBlogID']);
            if($delete_table and $delete_entry) {
                $pagetitle = 'Done';
                include ('include/head.htm');
                print '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Mini-Blog deleted.</strong>
                <br />Do you want to <a href="mini.php">make a new one</a>?</p>';
                include ('include/foot.htm');
                exit();
            }
        }
        $blog_edited = mysql_query("UPDATE ".MINIBLOG_TBL." SET MiniBlogTitle='$title', MiniBlogDesc='$body',
            MiniBlogDate='".$_POST['miniblogdate']."', MiniBlogShow='".$_POST['miniblogshow']."' WHERE MiniBlogID='".$_POST['MiniBlogID']."' AND
            MiniBlogAuthor=".$_COOKIE['auth_id']);
        if($blog_edited) {
            $pagetitle = 'Done';
            include ('include/head.htm');
            print '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Mini-Blog\'s settings updated.</strong>
            <br />Do you want to <a href="../miniblog.php?blog='.$_POST['MiniBlogUrl'].'">view it</a>?</p>';
            include ('include/foot.htm');
            exit();
        }
    } elseif($_POST['makeminiblog']) {
        if (empty($_POST['urlname'])) $urlname = '!!_UNDEFINED_URL_STRING_!!'; else $urlname = strip_tags($_POST['urlname']);
        $check = strstr($urlname, ' ');
        $check .= strstr($urlname, '+');
        $check .= strstr($urlname, '%20');
        $check .= strstr($urlname, '&');
        $check .= strstr($urlname, '?');
        $check .= strstr($urlname, '*');
        if($check) {
            $pagetitle = 'Invalid URL string';
            include ('include/head.htm');
            echo '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> <strong>You submitted an invalid URL string.</strong>
            <br />The <a href="http://www.desiquintans.com/writersblock/manual/index.php?page=urlstringguide">URL string guide</a> can tell you
            what characters are not allowed.
            <br />Please go back and redo the URL string field.</p>';
            include('include/foot.htm');
            exit();
        }
        $make_entry = mysql_query("INSERT INTO ".MINIBLOG_TBL." (MiniBlogID, MiniBlogUrl, MiniBlogTitle, MiniBlogDesc, MiniBlogDate,
            MiniBlogShow, MiniBlogAuthor) VALUES (NULL, '$urlname', '$title', '$body', '".$_POST['miniblogdate']."', '".$_POST['miniblogshow']."',
            '".$_COOKIE['auth_id']."')");
        $make_blog = mysql_query("CREATE TABLE $urlname (
            MiniID INT NOT NULL AUTO_INCREMENT,
            MiniTitle TINYTEXT NOT NULL,
            MiniBody LONGTEXT NOT NULL,
            MiniTimestamp INT(20) NOT NULL,
            MiniAuthor INT(2) NOT NULL,
            MiniDraft INT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (MiniID),
            UNIQUE KEY (MiniID),
            INDEX (MiniTimestamp)
        )");
        if($make_entry and $make_blog) {
            $pagetitle = 'Done';
            include ('include/head.htm');
            print '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Mini-Blog created.</strong>
            <br />Do you want to <a href="mini.php">make a new post</a> in it?</p>';
            include ('include/foot.htm');
            exit();
        }
    }
}

if($_GET['item'] == 'blog') {
## If the user wants to administer to a Mini-Blog's settings.
    if($_GET['action'] == 'new') {
        $pagetitle = 'Make a new Mini-Blog';
        include('include/head.htm');
?>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="submitted" value="submitted" />
        <fieldset>
        <legend>Details of the Mini-Blog (plain text only)</legend>
        URL string (cannot be changed once set): <input type="text" size="15" name="urlname" />
        <p>
        Title: <input type="text" size="60" name="title" />
        </p><p>
        Date format <a href="http://www.desiquintans.com/writersblock/manual/index.php?page=formattingdates">[how?]</a>: <input type="text" size="15" name="miniblogdate" />
        Posts to show on Mini-Blog: <input type="text" size="2" maxlength="2" name="miniblogshow" />
        </p>
        </fieldset>
        <fieldset>
        <legend>Describe the Mini-Blog's content (HTML allowed)</legend>
        <script type="text/javascript">edToolbar();</script>
        <textarea id="canvas" cols="58" rows="10" name="body"></textarea>
        <script type="text/javascript">var edCanvas = document.getElementById('canvas');</script>
        <p>
        <?php include ('include/autobreak.inc'); ?>
        </p><p>
        <input type="submit" name="makeminiblog" value="Create this Mini-Blog" />
        </p>
        </fieldset>
        </form>
<?php
        include('include/foot.htm');
        exit();
    } elseif($_GET['action'] == 'edit') {
        $get_old = mysql_query("SELECT MiniBlogUrl, MiniBlogTitle, MiniBlogDesc, MiniBlogDate, MiniBlogShow, MiniBlogAuthor
            FROM ".MINIBLOG_TBL." WHERE MiniBlogID=".$_GET['ID']);
        $old = mysql_fetch_array($get_old);
        $pagetitle = 'Edit a Mini-Blog';
        include('include/head.htm');

        $users_returned = mysql_fetch_array($authenticate);
        if($users_returned['Permission'] != 1 and ($old['MiniBlogAuthor'] != $_COOKIE['auth_id'])) {
            echo '<strong>You are not authorised to edit this page.</strong>';
            include('include/foot.htm');
            exit();
        }
?>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="submitted" value="submitted" />
        <input type="hidden" name="MiniBlogID" value="<?php echo $_GET['ID']; ?>" />
        <input type="hidden" name="MiniBlogUrl" value="<?php echo $old['MiniBlogUrl']; ?>" />
        <fieldset>
        <legend>Details of the Mini-Blog (plain text only)</legend>
        Title:<input type="text" size="60" name="title" value="<?php echo $old['MiniBlogTitle']; ?>" />
        <p>
        Date format <a href="http://www.desiquintans.com/writersblock/manual/index.php?page=formattingdates">[how?]</a>: <input type="text" size="15" name="miniblogdate" value="<?php echo $old['MiniBlogDate']; ?>" />
        Posts to show on Mini-Blog: <input type="text" size="2" maxlength="2" name="miniblogshow" value="<?php echo $old['MiniBlogShow']; ?>" />
        </p>
        </fieldset>
        <fieldset>
        <legend>Describe the Mini-Blog's content (HTML allowed)</legend>
        <script type="text/javascript">edToolbar();</script>
        <textarea id="canvas" cols="58" rows="10" name="body"><?php echo htmlspecialchars($old['MiniBlogDesc']); ?></textarea>
        <script type="text/javascript">var edCanvas = document.getElementById('canvas');</script>
        <p>
        <?php include ('include/autobreak.inc'); ?>
        </p><p>
        <input type="checkbox" name="delete" /><strong>Delete</strong> this entire Mini-Blog?
        </p><p>
        <input type="submit" name="editminiblog" value="Edit this Mini-Blog" />
        </p>
        </fieldset>
        </form>
<?php
        include('include/foot.htm');
        exit();
    }
} elseif($_GET['item'] == 'entry') {
## If the user wants to administer to a Mini-Blog entry.
    if($_GET['action'] == 'new') {
        $pagetitle = 'Make a Mini-Blog post';
        include('include/head.htm');

        $get_blog_author = mysql_query("SELECT MiniBlogAuthor FROM ".MINIBLOG_TBL." WHERE MiniBlogUrl='".$_GET['blog']."' LIMIT 1");
        $blog_author = mysql_fetch_array($get_blog_author);
        if($blog_author['MiniBlogAuthor'] != $_COOKIE['auth_id']) {
            echo '<strong>You are not authorised to post to this Mini-Blog.</strong>';
            include('include/foot.htm');
            exit();
        }
        $date_now = date('F');
?>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="submitted" value="submitted" />
        <input type="hidden" name="blog" value="<?php echo $_GET['blog']; ?>" />
        <fieldset>
        <legend>Details of the post (plain text only)</legend>
        Title:<input type="text" size="60" name="title" />
        </p><p>
        Date/Time:<select name="month">
        <option value="<?php echo $date_now; ?>"><?php echo $date_now; ?></option>
        <option value="January">January</option>
        <option value="February">February</option>
        <option value="March">March</option>
        <option value="April">April</option>
        <option value="May">May</option>
        <option value="June">June</option>
        <option value="July">July</option>
        <option value="August">August</option>
        <option value="September">September</option>
        <option value="October">October</option>
        <option value="November">November</option>
        <option value="December">December</option>
        </select>
        <input name="date" type="text" size="2" class="noformat" value="<?php echo date('d'); ?>" />
        <input name="year" type="text" size="4" class="noformat" value="<?php echo date('Y'); ?>" />
        <input name="time" type="text" size="5" class="noformat" value="<?php echo date('H:i'); ?>" />
        <input type="checkbox" style="margin-left: 2em;" name="draft" />Is this a <strong>Draft</strong>?
        </p>
        </fieldset>
        <fieldset>
        <legend>Text of Mini-Blog entry (HTML allowed)</legend>
        <script type="text/javascript">edToolbar();</script>
        <textarea id="canvas" cols="80" rows="20" name="body"></textarea>
        <script type="text/javascript">var edCanvas = document.getElementById('canvas');</script>
        <p>
        <?php include ('include/autobreak.inc'); ?>
        </p><p>
        <input type="submit" name="newentry" value="Create this entry" />
        </p>
        </fieldset>
        </form>
<?php
        include ('include/foot.htm');
        exit();
    } elseif($_GET['action'] == 'select') {
        $users_returned = mysql_fetch_array($authenticate);
        switch ($users_returned['Permission']) {
            case 1:
                $permission = '';
                break;
            default:
                $permission = " WHERE MiniAuthor='".$_COOKIE['auth_id']."'";
                break;
        }
        if (!isset($_GET['pg'])) $page = 1; else $page =& $_GET['pg'];
        if(!is_numeric($page)) $page = 1;
        $offset = ($page*15)-15;

        $pagetitle = 'Edit a Mini-Blog entry';
        include ('include/head.htm');

        $getrows = mysql_query("SELECT MiniID FROM ".$_GET['blog'].$permission);
        $pagecount = ceil(mysql_num_rows($getrows)/15);

        $paginate = NULL;
        if($page>1) $paginate .= '<a href="mini.php?item=entry&amp;blog='.$_GET['blog'].'&amp;action=select&amp;pg='.($page-1).'">Back</a> | ';
        else $paginate .= '';
        for($pg = 1; $pg <= $pagecount; ++$pg) {
            if ($pg == $page) $paginate .= '<strong>'.$pg.'</strong> ';
            else $paginate .= '<a href="mini.php?item=entry&amp;blog='.$_GET['blog'].'&amp;action=select&amp;pg='.$pg.'">'.$pg.'</a> ';
        }
        if($page<$pagecount) $paginate .= '| <a href="mini.php?item=entry&amp;blog='.$_GET['blog'].'&amp;action=select&amp;pg='.($page+1).'">Next</a>';
        else $paginate .= '';

        echo $paginate;

        echo '
        <table border="0" cellpadding="5" cellspacing="2">
        <tr bgcolor="#FFFFFF">
        <th>Date</th><th>Title</th><th>Status</th>
        </tr>';
        $get_mini = mysql_query("SELECT MiniID, MiniTitle, MiniTimestamp, MiniDraft FROM ".$_GET['blog'].$permission." ORDER BY
            MiniTimestamp DESC LIMIT $offset,15");
        $bg = '#EEEEEE';
        while($show = mysql_fetch_array($get_mini)) {
            $bg = ($bg == '#FFFFFF' ? '#EEEEEE' : '#FFFFFF');
            echo '
            <tr bgcolor="'.$bg.'">
            <td>'.date('Y M d', $show['MiniTimestamp']).'</td>
            <td><a href="mini.php?item=entry&amp;blog='.$_GET['blog'].'&amp;MiniID='.$show['MiniID'].'&amp;action=edit">
                '.$show['MiniTitle'].'</a></td>
            <td align="center">';
            switch($show['MiniDraft']) {
                case 0:
                    echo '<img src="include/img/live.png" width="16" height="16" alt="Live" title="This is live" />';
                    break;
                case 1:
                    echo '<img src="include/img/draft.png" width="16" height="16" alt="Draft" title="This is a draft" />';
                    break;
            }
            echo '</td></tr>';
        }
        echo '</table><p>'.$paginate.'</p>';

        include ('include/foot.htm');
        exit();
    } elseif($_GET['action'] == 'edit') {
        $get_old = mysql_query("SELECT MiniTitle, MiniBody, MiniTimestamp, MiniDraft, MiniAuthor FROM ".$_GET['blog']." WHERE
            MiniID=".$_GET['MiniID']." LIMIT 1");
        $old = mysql_fetch_array($get_old);

        $users_returned = mysql_fetch_array($authenticate);
        if($users_returned['Permission'] != 1 and ($old['MiniAuthor'] != $_COOKIE['auth_id'])) {
            echo '<strong>You are not authorised to edit this page.</strong>';
            include('include/foot.htm');
            exit();
        }

        if($old['MiniDraft'] == 1) $is_draft = ' checked="checked"'; else $is_draft = '';
        $old_month = date('F', $old['MiniTimestamp']);

        $pagetitle = 'Edit a Mini-Blog post';
        include('include/head.htm');
?>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="submitted" value="submitted" />
        <input type="hidden" name="blog" value="<?php echo $_GET['blog']; ?>" />
        <input type="hidden" name="MiniID" value="<?php echo $_GET['MiniID']; ?>" />
        <fieldset>
        <legend>Details of the post (plain-text only)</legend>
        Title:<input type="text" size="60" name="title" value="<?php echo $old['MiniTitle']; ?>" />
        </p><p>
        Date/Time:<select name="month">
        <option value="<?php echo $old_month; ?>"><?php echo $old_month; ?></option>
        <option value="January">January</option>
        <option value="February">February</option>
        <option value="March">March</option>
        <option value="April">April</option>
        <option value="May">May</option>
        <option value="June">June</option>
        <option value="July">July</option>
        <option value="August">August</option>
        <option value="September">September</option>
        <option value="October">October</option>
        <option value="November">November</option>
        <option value="December">December</option>
        </select>
        <input type="text" size="2" class="noformat" value="<?php echo date('d', $old['MiniTimestamp']); ?>" name="date" />
        <input type="text" size="4" class="noformat" value="<?php echo date('Y', $old['MiniTimestamp']); ?>" name="year" />
        <input type="text" size="5" class="noformat" value="<?php echo date('H:i', $old['MiniTimestamp']); ?>" name="time" />
        </p><p>
        <input type="checkbox" style="margin-left: 2em;" name="delete" /><strong>Delete</strong> this post?
        <input type="checkbox" style="margin-left: 2em;" name="draft"<?php echo $is_draft; ?> />Is this a <strong>Draft</strong>?
        <input type="submit" name="editentry" value="Update this post" style="margin-left: 4em;" />
        </p>
        </fieldset>
        <fieldset>
        <legend>Text of Mini-Blog entry (HTML allowed)</legend>
        <script type="text/javascript">edToolbar();</script>
        <textarea id="canvas" cols="80" rows="20" name="body"><?php echo htmlspecialchars($old['MiniBody']); ?></textarea>
        <script type="text/javascript">var edCanvas = document.getElementById('canvas');</script>
        <p>
        <?php include ('include/autobreak.inc'); ?>
        </p><p>
        <input type="submit" name="editentry" value="Update this post" />
        </p>
        </fieldset>
        </form>
<?php
        include ('include/foot.htm');
        exit();
    }
} else {
    $users_returned = mysql_fetch_array($authenticate);
    switch ($users_returned['Permission']) {
        case 1:
            $permission = '';
            break;
        default:
            $permission = "WHERE MiniBlogAuthor='".$_COOKIE['auth_id']."' ";
            break;
    }
    $pagetitle = 'Edit a Mini-Blog';
    include ('include/head.htm');
    $get_mini = mysql_query("SELECT MiniBlogID, MiniBlogUrl, MiniBlogTitle FROM ".MINIBLOG_TBL." ".$permission."ORDER BY
        MiniBlogTitle ASC");

    echo '
    <table border="0" align="center" cellpadding="8" cellspacing="5">
    <tr bgcolor="#FFFFFF">
    <th>Mini-Blog</th><th colspan="3">Actions</th>
    </tr>';
    $bg = '#EEEEEE';
    while($show = mysql_fetch_array($get_mini)) {
        $bg = ($bg == '#FFFFFF' ? '#EEEEEE' : '#FFFFFF');
        echo '
        <tr bgcolor="'.$bg.'">
        <td>'.$show['MiniBlogTitle'].'</td>
        <td align="center"><a href="mini.php?item=entry&amp;blog='.$show['MiniBlogUrl'].'&amp;action=new" title="New entry"><span class="button"><img src="include/img/new.png" width="16" height="16" alt="New entry" /></span></a>
                           <a href="mini.php?item=entry&amp;blog='.$show['MiniBlogUrl'].'&amp;action=select" title="Edit entry"><span class="offset"><img src="include/img/edit.png" width="16" height="16" alt="Edit entry" /></span></a>
                           <a href="mini.php?item=blog&amp;action=edit&amp;ID='.$show['MiniBlogID'].'" title="Change settings"><span class="offset"><img src="include/img/prefs.png" width="16" height="16" alt="Change settings" /></span></a></td>
        </tr>';
    }
    echo '</table>';
    
    include ('include/foot.htm');
    exit();
}
?>
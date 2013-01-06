<?php
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
    $authenticate = mysql_query("SELECT UserID, UserName, Password, Permission FROM ".USERS_TBL." WHERE UserID='".$_COOKIE['auth_id']."' AND
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

if ($_POST['submitted']) {
    ## Parse the input for any oddness.
    include ('include/xmlfriendly.inc');
    if (empty($_POST['title'])) $title = 'Untitled'; else $title = strip_tags($_POST['title']);
    if($_POST['autobreak']) $body = nl2br($_POST['body']); else $body =& $_POST['body'];
    foreach($ItemArray as $key => $value) {
        $body= str_replace($key, $value, $body);
    }
    $body = str_replace("'", "&#8217;", $body);
    $timestamp = strtotime($_POST['month'].' '.$_POST['date'].' '.$_POST['year'].' '.$_POST['time']);
    if($_POST['draft']) $draft = 1; else $draft = 0;

    if($_POST['submitnew']) {
        $post_made = mysql_query("INSERT INTO ".POSTS_TBL." (PostID, Title, Body, Timestamp, PostCat1, PostCat2, PostCat3, PostCat4,
        Author, Draft, Thread) VALUES (NULL,'$title','$body','$timestamp','".$_POST['category1']."','".$_POST['category2']."','".$_POST['category3']."',
        '".$_POST['category4']."', '".$_COOKIE['auth_id']."', '$draft', '".$_POST['thread']."')");
        if ($post_made) {
            $pagetitle = 'Done';
            include ('include/head.htm');
            print '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>New post published.</strong>
            <br />Do you want to <a href="../index.php">view it</a> or <a href="post.php?action=new">make another</a>?</p>';
            include ('include/foot.htm');
            exit();
        }
    } elseif ($_POST['submitedit']) {
        if ($_POST['delete']) {
            $deleted = mysql_query("DELETE FROM ".POSTS_TBL." WHERE PostID=".$_POST['PostID']);
            if($deleted) {
                $pagetitle = 'Done';
                include ('include/head.htm');
                print '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Post deleted.</strong>
                <br />Do you want to <a href="post.php?action=new">make a new one</a>?</p>';
                include ('include/foot.htm');
                exit();
            }
        }
        $post_edited = mysql_query("UPDATE ".POSTS_TBL." SET Title='".$_POST['title']."', Body='$body', Timestamp='$timestamp',
        PostCat1='".$_POST['category1']."', PostCat2='".$_POST['category2']."', PostCat3='".$_POST['category3']."', PostCat4='".$_POST['category4']."',
        Draft='$draft', Thread='".$_POST['thread']."' WHERE PostID=".$_POST['PostID']);
        if ($post_edited) {
            $pagetitle = 'Done';
            include ('include/head.htm');
            print '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Blog post updated.</strong>
            <br />Do you want to <a href="../permalink.php?PostID='.$_POST['PostID'].'">view it</a> or <a href="post.php?action=new">make a new one</a>?</p>';
            include ('include/foot.htm');
            exit();
        }
    }
}

if($_GET[action] == 'new') {
    ## Make new blog post
    $pagetitle = 'Add post';
    include ('include/head.htm');
    $date_now = date('F');
    ?>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="submitted" value="submitted" />
    <fieldset>
    <legend>Details of the blog post (plain text only)</legend>
    Post's title:<br />
    <input type="text" size="60" name="title" />
    <p>
    Categories (blank for none):
    <select name="category1">
    <option></option>
<?php
    $getcat = mysql_query("SELECT PCatID, PostCatName FROM ".POSTCAT_TBL);
    $category_names = NULL;
    while ($row = mysql_fetch_array($getcat)) {
        $category_names .= '<option value="'.$row['PCatID'].'">'.$row['PostCatName'].'</option>';
    }
    echo $category_names;
?>
    </select>
    <select name="category2">
    <option></option>
<?php echo $category_names; ?>
    </select>
    <select name="category3">
    <option></option>
<?php echo $category_names; ?>
    </select>
    <select name="category4">
    <option></option>
<?php echo $category_names; ?>
    </select>
    </p><p>
    Date/Time:
    <select name="month">
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
    </p>
    </fieldset>
    <fieldset>
    <legend>Blog post settings</legend>
    Allow comments?
    <input type="radio" name="thread" value="2" id="threadauto" checked="checked" /><label for"threadauto">Before expiry</label>
    <input type="radio" name="thread" value="3" id="threadon" /><label for"threadon">Always</label>
    <input type="radio" name="thread" value="1" id="threadoff" /><label for"threadoff">Never</label>
    <p>
    <input type="checkbox" style="margin-left: 2em;" name="draft" />Is this a <strong>Draft</strong>?
    </p>
    </fieldset>
    <fieldset>
    <legend>Text of blog post (HTML allowed)</legend>
    <script type="text/javascript">edToolbar();</script>
    <textarea id="canvas" cols="80" rows="20" name="body"></textarea>
    <script type="text/javascript">var edCanvas = document.getElementById('canvas');</script>
    <p>
    <?php include ('include/autobreak.inc'); ?>
    </p><p>
    <input type="submit" name="submitnew" value="Publish Post" />
    </p>
    </fieldset>
    </form>
<?php
    include ('include/foot.htm');
    exit();
} elseif($_GET['action'] == 'edit') {
    ## Edit an existing post
    $pagetitle = 'Edit post';
    include ('include/head.htm');

    $retrieve = mysql_query("SELECT Title, Body, Timestamp, PostCat1, PostCat2, PostCat3, PostCat4, Author, Draft, Thread FROM ".POSTS_TBL." WHERE PostID=".$_GET['PostID']);
    $row = mysql_fetch_array($retrieve);
    $users_returned = mysql_fetch_array($authenticate);
    if($users_returned['Permission'] == 3) {
        if($row['Author'] != $_COOKIE['auth_id']) {
            echo '<strong>You are not authorised to edit this page.</strong>';
            include('include/foot.htm');
            exit();
        }
    }
    $old_month = date('F', $row['Timestamp']);
?>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="submitted" value="submitted" />
    <input type="hidden" name="PostID" value="<?php echo $_GET['PostID']; ?>" />
    <fieldset>
    <legend>Details of the blog post (plain text only)</legend>
    Post's title:<input type="text" size="60" name="title" value="<?php echo $row['Title']; ?>" />
    <p>
    Categories (blank for none):
<?php
    $get_cat_names = mysql_query("SELECT PCatID, PostCatName FROM ".POSTCAT_TBL);
    while($get_cat_names_result = mysql_fetch_array($get_cat_names)) {
        $cname[$get_cat_names_result['PCatID']] = $get_cat_names_result['PostCatName'];
    }

    for ($i = 1; $i <= 4; ++$i) {
        echo '
        <select name="category'.$i.'">
        <option value="'.$row['PostCat'.$i].'">'.$cname[$row['PostCat'.$i]].'</option>
        <option> </option>
        ';
        foreach($cname as $id => $name) {
            echo '<option value="'.$id.'">'.$name.'</option>';
        }
        echo '</select>';
    }
?>
    </p><p>
    Date/Time:
    <select name="month">
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
    <input type="text" size="2" class="noformat" value="<?php echo date('d', $row['Timestamp']); ?>" name="date" />
    <input type="text" size="4" class="noformat" value="<?php echo date('Y', $row['Timestamp']); ?>" name="year" />
    <input type="text" size="5" class="noformat" value="<?php echo date('H:i', $row['Timestamp']); ?>" name="time" />
    </p>
    </fieldset>
    <fieldset>
    <legend>Blog post settings</legend>
    Allow comments?
    <input type="radio" name="thread" value="2" id="threadauto"<?php if($row['Thread'] == 2) echo ' checked="checked"'; ?> /><label for"threadauto">Before expiry</label>
    <input type="radio" name="thread" value="3" id="threadon"<?php if($row['Thread'] == 3) echo ' checked="checked"'; ?> /><label for"threadon">Always</label>
    <input type="radio" name="thread" value="1" id="threadoff"<?php if($row['Thread'] == 1) echo ' checked="checked"'; ?> /><label for"threadoff">Never</label>
    <p>
    <input type="checkbox" style="margin-left: 2em;" name="delete" /><strong>Delete</strong> this post?
    <input type="checkbox" style="margin-left: 4em;" name="draft"<?php if($row['Draft'] == 1) echo ' checked="checked"'; ?> />Is this a <strong>Draft</strong>?
    <input type="submit" name="submitedit" value="Update Post" style="margin-left: 4em;" />
    </p>
    </fieldset>
    <fieldset>
    <legend>Text of blog post (HTML allowed)</legend>
    <script type="text/javascript">edToolbar();</script>
    <textarea id="canvas" cols="80" rows="20" name="body"><?php echo htmlspecialchars($row['Body']); ?></textarea>
    <script type="text/javascript">var edCanvas = document.getElementById('canvas');</script>
    <p>
    <?php include ('include/autobreak.inc'); ?>
    </p><p>
    <input type="submit" name="submitedit" value="Update Post" />
    </p>
    </fieldset>
    </form>
<?php
    include ('include/foot.htm');
    exit();
} else {
    ## List old posts
    $pagetitle = 'Edit blog post';
    include ('include/head.htm');

    if (!isset($_GET['pg'])) $page = 1; else $page =& $_GET['pg'];
    if(!is_numeric($page)) $page = 1;
    $offset = ($page*15)-15;

    $get_auth = mysql_query("SELECT Permission FROM ".USERS_TBL." WHERE UserID='".$_COOKIE['auth_id']."' AND UserName='".$_COOKIE['auth_username']."'");
    $p = mysql_fetch_array($get_auth);
    switch ($p['Permission']) {
        case 1:
        case 2:
            $permission = "ORDER BY Timestamp DESC";
            break;
        default:
            $permission = "WHERE Author='".$_COOKIE['auth_id']."' ORDER BY Timestamp DESC";
            break;
    }

    $getrows = mysql_query("SELECT PostID FROM ".POSTS_TBL." ".$permission);
    $pagecount = ceil(mysql_num_rows($getrows)/15);

    $paginate = NULL;
    if($page>1) $paginate .= '<a href="post.php?pg='.($page-1).'">Back</a> | ';
    else $paginate .= '';
    for($pg = 1; $pg <= $pagecount; ++$pg) {
        if ($pg == $page) $paginate .= '<strong>'.$pg.'</strong> ';
        else $paginate .= '<a href="post.php?pg='.$pg.'">'.$pg.'</a> ';
    }
    if($page<$pagecount) $paginate .= '| <a href="post.php?pg='.($page+1).'">Next</a>';
    else $paginate .= '';

    $get_cat_names = mysql_query("SELECT PCatID, PostCatName FROM ".POSTCAT_TBL);
    while($get_cat_names_result = mysql_fetch_array($get_cat_names)) {
        $cname[$get_cat_names_result['PCatID']] = $get_cat_names_result['PostCatName'];
    }
    $list = mysql_query("SELECT PostID, Title, Timestamp, PostCat1, PostCat2, PostCat3, PostCat4, Draft FROM ".POSTS_TBL." $permission LIMIT $offset,15");
    
    echo $paginate;
    echo '
    <table border="0" cellpadding="5" cellspacing="2">
    <tr bgcolor="#FFFFFF">
    <th>Date</th><th>Title</th><th>Categories</th><th>Status</th>
    </tr>';
    $bg = '#EEEEEE';
    while ($row = mysql_fetch_array($list)) {
        $bg = ($bg == '#FFFFFF' ? '#EEEEEE' : '#FFFFFF');
        switch($row['Draft']) {
            case 0:
                $status = '<img src="include/img/live.png" width="16" height="16" alt="Live" title="This is live" />';
                break;
            case 1:
                $status = '<img src="include/img/draft.png" width="16" height="16" alt="Draft" title="This is a draft" />';
                break;
        }
        $categories = NULL;
        for ($i = 1; $i <= 4; ++$i) {
            if(empty($row['PostCat'.$i])) $categories .= ''; else $categories .= $cname[$row['PostCat'.$i]].', ';
        }
        $categories = substr($categories, 0, -2);
        
        echo '
        <tr bgcolor="'.$bg.'">
        <td>'.date('Y M d',$row['Timestamp']).'</td>
        <td><a href="post.php?action=edit&amp;PostID='.$row['PostID'].'">'.$row['Title'].'</a></td>
        <td>'.$categories.'</td>
        <td align="center">'.$status.'</td>
        </tr>';
    }
    echo '</table><p>'.$paginate.'</p>';
    include ('include/foot.htm');
    exit();
}
?>

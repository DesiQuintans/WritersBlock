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
    $authenticate = mysql_query("SELECT UserID FROM ".USERS_TBL." WHERE UserID='".$_COOKIE['auth_id']."' AND
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

include ('include/xmlfriendly.inc');

if ($_POST['submitted']) {
    ## Parse the input for any oddness.
    if (empty($_POST['caturl'])) $caturl = '!!_UNDEFINED_URL_STRING_!!'; else $caturl = strip_tags($_POST['caturl']);
    $check = strstr($caturl, ' ');
    $check .= strstr($caturl, '+');
    $check .= strstr($caturl, '%20');
    $check .= strstr($caturl, '&');
    $check .= strstr($caturl, '?');
    $check .= strstr($caturl, '*');
    if($check) {
        $pagetitle = 'Invalid URL string';
        include ('include/head.htm');
        echo '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> <strong>You submitted an invalid URL string.</strong>
        <br />The <a href="http://www.desiquintans.com/writersblock/manual/index.php?page=urlstringguide">URL string guide</a> can tell you
        what characters are not allowed.
        <br />Please go back and redo the URL string field.</p>';
    }
    if (empty($_POST['catname'])) $catname = 'NO NAME ENTERED'; else $catname = strip_tags($_POST['catname']);
    if (empty($_POST['catdesc'])) $catdesc = 'NO DESCRIPTION ENTERED'; else $catdesc =& $_POST['catdesc'];
    foreach($ItemArray as $key => $value) {
        $catdesc = str_replace($key, $value, $catdesc);
    }
    if ($_POST['submitnew'] and !$check) {
        ## Create new Category
        switch ($_POST['catscope']) {
        case 'any':
            $catscope = ANYCAT_TBL;
            $column = 'AnyCatUrl, AnyCatName, AnyDesc, Author';
            break;
        case 'post':
            $catscope = POSTCAT_TBL;
            $column = 'PostCatUrl, PostCatName, PostDesc, Author';
            break;
        }
        $made_new = mysql_query("INSERT INTO $catscope ($column) VALUES ('$caturl', '$catname', '$catdesc', '".$_COOKIE['auth_id']."')");
        if ($made_new) {
            $pagetitle = 'Done';
            include ('include/head.htm');
            echo '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Category created.</strong>
            <br />Do you want to <a href="cat.php?action=new">make another one</a>?</p>';
            include ('include/foot.htm');
            exit();
        }
    } elseif ($_POST['editpcat'] and !$check) {
        ## Edit a Post Category
        if($_POST['delete']) {
            $deleted = mysql_query("DELETE FROM ".POSTCAT_TBL." WHERE PCatID='".$_POST['PCatID']."' LIMIT 1");
            if($deleted) {
                $pagetitle = 'Done';
                include ('include/head.htm');
                echo '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Category deleted.</strong>
                <br />Do you want to <a href="cat.php?action=new">make a new one</a>?</p>';
                include ('include/foot.htm');
                exit();
            }
        }
        $cat_edited = mysql_query("UPDATE ".POSTCAT_TBL." SET PostCatUrl='".$_POST['caturl']."', PostCatName='".$_POST['catname']."',
        PostDesc='".$_POST['catdesc']."' WHERE PCatID=".$_POST['PCatID']);
        
        if ($cat_edited) {
            $pagetitle = 'Done';
            include ('include/head.htm');
            echo '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Category updated.</strong>
            <br />Do you want to <a href="../archive.php?cat='.$_POST['caturl'].'">view it</a>?</p>';
            include ('include/foot.htm');
            exit();
        }
    } elseif ($_POST['editacat'] and !$check) {
        ## Edit an Anypage Category
        if($_POST['delete']) {
            $deleted = mysql_query("DELETE FROM ".ANYCAT_TBL." WHERE ACatID='".$_POST['ACatID']."' LIMIT 1");
            if($deleted) {
                $pagetitle = 'Done';
                include ('include/head.htm');
                echo '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Category deleted.</strong>
                <br />Do you want to <a href="cat.php?action=new">make a new one</a>?</p>';
                include ('include/foot.htm');
                exit();
            }
        }
        $cat_edited = mysql_query("UPDATE ".ANYCAT_TBL." SET AnyCatUrl='".$_POST['caturl']."', AnyCatName='".$_POST['catname']."',
        AnyDesc='".$_POST['catdesc']."' WHERE ACatID=".$_POST['ACatID']);
        if ($cat_edited) {
            $pagetitle = 'Done';
            include ('include/head.htm');
            echo '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Category updated.</strong>
            <br />Do you want to <a href="../articles.php?cat='.$_POST['caturl'].'">view it</a>?</p>';
            include ('include/foot.htm');
            exit();
        }
    }
}

if($_GET['action'] == 'new') {
    ## Make a new category
    $pagetitle = 'New Category';
    include ('include/head.htm');
    ?>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="submitted" value="submitted" />
    <fieldset>
    <legend>What goes under this category?</legend>
    <input type="radio" name="catscope" id="pcat_scope" value="post" />
    <label for="pcat_scope">Posts</label>&nbsp;&nbsp;&nbsp;
    <input type="radio" name="catscope" id="acat_scope" value="any" checked="checked" />
    <label for="acat_scope">Anypages</label>
    </fieldset>
    <fieldset>
    <legend>Name the category (plain text only)</legend>
    URL string:
    <input type="text" size="15" name="caturl" maxlength="20" />
    Display name:
    <input type="text" size="35" name="catname" />
    </fieldset>
    <fieldset>
    <legend>Describe the contents of the category (HTML allowed)</legend>
    <script type="text/javascript">edToolbar();</script>
    <textarea id="canvas" cols="80" rows="5" name="catdesc"></textarea>
    <script type="text/javascript">var edCanvas = document.getElementById('canvas');</script>
    <p>
    <input type="submit" name="submitnew" value="Create New Category" />
    </p>
    </fieldset>
    </form>
<?php
    include ('include/foot.htm');
    exit();
} elseif ($_GET['action'] == 'edit') {
    if (isset($_GET['PCatID'])) {
        ## Edit post category
        $pagetitle = 'Edit Post Category';
        include ('include/head.htm');

        $retrieve = mysql_query ("SELECT * FROM ".POSTCAT_TBL." WHERE PCatID=".$_GET['PCatID']);
        $old = mysql_fetch_array($retrieve);
        $users_returned = mysql_fetch_array($authenticate);
        if($users_returned['Permission'] == 3) {
            if($old['Author'] != $_COOKIE['auth_id']) {
                echo '<strong>You are not authorised to edit this page.</strong>';
                exit();
            }
        }
?>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="submitted" value="submitted" />
        <input type="hidden" name="PCatID" value="<?php echo $_GET['PCatID']; ?>" />
        <fieldset>
        <legend>Name the category (plain text only)</legend>
        URL string: <input type="text" size="15" name="caturl" value="<?php echo $old['PostCatUrl']; ?>" />
        Display name:<input type="text" size="30" name="catname" value="<?php echo $old['PostCatName']; ?>" />
        </fieldset>
        <fieldset>
        <legend>Describe the contents of the category (HTML allowed)</legend>
        <script type="text/javascript">edToolbar();</script>
        <textarea id="canvas" cols="80" rows="5" name="catdesc"><?php echo htmlspecialchars($old['PostDesc']); ?></textarea>
        <script type="text/javascript">var edCanvas = document.getElementById('canvas');</script>
        </fieldset>
        <fieldset>
        <legend>Update options</legend>
        <input type="checkbox" name="delete" />Delete this category.
        <p>If you delete this category, all posts in it will have invalid categories until you refile them.</p>
        <p>
        <input type="submit" name="editpcat" value="Update Post Category" />
        </p>
        </fieldset>
        </form>
<?php
        include ('include/foot.htm');
        exit();
} elseif (isset($_GET['ACatID'])) {
        ## Edit Anypage category
        $pagetitle = 'Edit Anypage Category';
        include ('include/head.htm');

        $retrieve = mysql_query("SELECT * FROM ".ANYCAT_TBL." WHERE ACatID=".$_GET['ACatID']);
        $old = mysql_fetch_array($retrieve);
        $users_returned = mysql_fetch_array($authenticate);
        if($users_returned['Permission'] == 3) {
            if($old['Author'] != $_COOKIE['auth_id']) {
                echo '<strong>You are not authorised to edit this page.</strong>';
                exit();
            }
        }
?>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="submitted" value="submitted" />
        <input type="hidden" name="ACatID" value="<?php echo $_GET['ACatID']; ?>" />
        <fieldset>
        <legend>Name the category (plain text only)</legend>
        URL string:<input type="text" size="15" name="caturl" maxlength="25" value="<?php echo $old['AnyCatUrl']; ?>" />
        Display name:<input type="text" size="30" name="catname" value="<?php echo $old['AnyCatName']; ?>" />
        </fieldset>
        <fieldset>
        <legend>Describe the contents of the category (HTML allowed)</legend>
        <script type="text/javascript">edToolbar();</script>
        <textarea id="canvas" cols="80" rows="5" name="catdesc"><?php echo htmlspecialchars($old['AnyDesc']); ?></textarea>
        <script type="text/javascript">var edCanvas = document.getElementById('canvas');</script>
        </fieldset>
        <fieldset>
        <legend>Update options</legend>
        <input type="checkbox" name="delete" />Delete this category.
        <p>If you delete this category, all posts in it will have invalid categories until you refile them.</p>
        <p>
        <input type="submit" name="editacat" value="Update Anypage Category" />
        </p>
        </fieldset>
        </form>
<?php
        include ('include/foot.htm');
        exit();
    }
} else {
    ## List existing categories
    $pagetitle = 'Edit Category';
    include ('include/head.htm');

    $p = mysql_fetch_array($authenticate);
    switch ($p['Permission']) {
        case 1:
        case 2:
            $permission = "";
            break;
        default:
            $permission = "WHERE Author='".$_COOKIE['auth_id']."' ";
            break;
    }

    $pcats = mysql_query("SELECT PCatID, PostCatName, PostDesc FROM ".POSTCAT_TBL." ".$permission."ORDER BY PostCatName ASC");
    $acats = mysql_query("SELECT ACatID, AnyCatName, AnyDesc FROM ".ANYCAT_TBL." ".$permission."ORDER BY AnyCatName ASC");
    
    echo '<h2>Categories for blog posts</h2>';
    echo '
    <table border="0" cellpadding="5" cellspacing="2">
    <tr bgcolor="#FFFFFF">
    <th>Title</th><th>Description</th>
    </tr>';
    $bg = '#EEEEEE';
    while ($row = mysql_fetch_array($pcats)) {
        $bg = ($bg == '#FFFFFF' ? '#EEEEEE' : '#FFFFFF');
        echo '
        <tr bgcolor="'.$bg.'">
        <td><a href="cat.php?action=edit&amp;PCatID='.$row['PCatID'].'">'.$row['PostCatName'].'</a></td>
        <td>'.$row['PostDesc'].'</td>
        </tr>';
    }
    echo '</table>';
    
    echo '<h2>Categories for Anypages</h2>';
    echo '
    <table border="0" cellpadding="5" cellspacing="2">
    <tr bgcolor="#FFFFFF">
    <th>Title</th><th>Description</th>
    </tr>';
    $bg = '#EEEEEE';
    while ($row = mysql_fetch_array($acats)) {
        $bg = ($bg == '#FFFFFF' ? '#EEEEEE' : '#FFFFFF');
        echo '
        <tr bgcolor="'.$bg.'">
        <td><a href="cat.php?action=edit&amp;ACatID='.$row['ACatID'].'">'.$row['AnyCatName'].'</a></td>
        <td>'.$row['AnyDesc'].'</td>
        </tr>';
    }
    echo '</table>';

    include ('include/foot.htm');
    exit();
}
?>
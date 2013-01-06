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

include ('include/xmlfriendly.inc');

if ($_POST['submitted']) {
    ## Parse the input for any oddness.
    if (empty($_POST[urlname])) $urlname = '!!_UNDEFINED_URL_STRING_!!'; else $urlname = strip_tags($_POST['urlname']);
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
    if (empty($_POST['title'])) $title = '[UNTITLED]'; else $title = strip_tags($_POST['title']);
    if($_POST['autobreak']) $body = nl2br($_POST['body']); else $body =& $_POST['body'];
//  if($_POST['subheading'] == "") $subheader = ''; else $subheader =& $_POST['subheading']; str_replace("'", "&#146;", $_POST['subheading'])
    if($_POST['subheading'] == "") $subheader = ''; else $subheader = str_replace("'", "&#146;", $_POST['subheading']);
    $body = str_replace("'", "&#146;", $body);
    $postdesc = str_replace("'", "&#146;", $_POST['desc']);
    foreach($ItemArray as $key => $value) {
        $body = str_replace($key, $value, $body);
    }

    $timestamp = strtotime($_POST['month'].' '.$_POST['date'].' '.$_POST['year'].' '.$_POST['time']);

    if ($_POST['submitnew'] and !$check) {
        $make_page = mysql_query("INSERT INTO ".ANYPAGE_TBL." (AnyID, UrlName, Title, PageDesc, Subheading, PageBody, AnyCat1, AnyCat2,
        AnyCat3, AnyCat4, Author, Timestamp) VALUES (NULL,'$urlname','$title','$postdesc','$subheader','$body','$_POST[category1]',
        '$_POST[category2]','$_POST[category3]','$_POST[category4]', '$_COOKIE[auth_id]', '$timestamp')");
        if ($make_page) {
            $pagetitle = 'Done';
            include ('include/head.htm');
            echo '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Anypage created.</strong><br />
            Do you want to <a href="../articles.php?page='.$urlname.'">view it</a> or <a href="any.php?action=new">make another one</a>?</p>';
            include ('include/foot.htm');
            exit();
        }
    } elseif ($_POST[submitedit] and !$check) {
        if ($_POST[delete]) {
            $deleted = mysql_query("DELETE FROM ".ANYPAGE_TBL." WHERE AnyID=$_POST[AnyID] LIMIT 1");
            if ($deleted) {
                $pagetitle = 'Done';
                include ('include/head.htm');
                echo '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Anypage deleted.</strong>
                <br />Do you want to <a href="any.php?action=new">make a new one</a>?
                </p>';
                include ('include/foot.htm');
                exit();
            }
        }
//	$edit_page = mysql_query("UPDATE ".ANYPAGE_TBL." SET UrlName='$urlname' WHERE AnyID='$_POST[AnyID]'");
//		if (!$edit_page) {print ("Updating field UrlName failed. Exiting."); exit();}
//		
//	$edit_page = mysql_query("UPDATE ".ANYPAGE_TBL." SET Title='$title' WHERE AnyID='$_POST[AnyID]'");
//		if (!$edit_page) {print ("Updating field Title failed. Exiting."); exit();}
//		
//	$edit_page = mysql_query("UPDATE ".ANYPAGE_TBL." SET PageDesc='$postdesc' WHERE AnyID='$_POST[AnyID]'");
//		if (!$edit_page) {print ("Updating field PageDesc failed. Exiting."); exit();}
//		
//	$edit_page = mysql_query("UPDATE ".ANYPAGE_TBL." SET Subheading='$subheader' WHERE AnyID='$_POST[AnyID]'");
//		if (!$edit_page) {print ("Updating field Subheading failed. Exiting.");}
//		
//	$edit_page = mysql_query("UPDATE ".ANYPAGE_TBL." SET PageBody='$body' WHERE AnyID='$_POST[AnyID]'");
//		if (!$edit_page) {print ("Updating field PageBody failed. Exiting."); exit();}
//		
//	$edit_page = mysql_query("UPDATE ".ANYPAGE_TBL." SET AnyCat1='$_POST[category1]' WHERE AnyID='$_POST[AnyID]'");
//		if (!$edit_page) {print ("Updating field AnyCat1 failed. Exiting."); exit();}
//		
//	$edit_page = mysql_query("UPDATE ".ANYPAGE_TBL." SET AnyCat2='$_POST[category2]' WHERE AnyID='$_POST[AnyID]'");
//		if (!$edit_page) {print ("Updating field AnyCat2 failed. Exiting."); exit();}
//		
//	$edit_page = mysql_query("UPDATE ".ANYPAGE_TBL." SET AnyCat3='$_POST[category3]' WHERE AnyID='$_POST[AnyID]'");
//		if (!$edit_page) {print ("Updating field AnyCat3 failed. Exiting."); exit();}
//		
//	$edit_page = mysql_query("UPDATE ".ANYPAGE_TBL." SET AnyCat4='$_POST[category4]' WHERE AnyID='$_POST[AnyID]'");
//		if (!$edit_page) {print ("Updating field AnyCat4 failed. Exiting."); exit();}
//		
//	$edit_page = mysql_query("UPDATE ".ANYPAGE_TBL." SET Timestamp='$timestamp' WHERE AnyID='$_POST[AnyID]'");
//		if (!$edit_page) {print ("Updating field Timestamp failed. Exiting."); exit();}
		
        $edit_page = mysql_query("UPDATE ".ANYPAGE_TBL." SET UrlName='$urlname',Title='$title', PageDesc='$postdesc',
        Subheading='$subheader',PageBody='$body', AnyCat1='$_POST[category1]', AnyCat2='$_POST[category2]',
        AnyCat3='$_POST[category3]', AnyCat4='$_POST[category4]', Timestamp='$timestamp' WHERE AnyID='$_POST[AnyID]'");
        if ($edit_page) {
            $pagetitle = 'Done';
            include ('include/head.htm');
            echo '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Anypage updated.</strong>
            <br />Do you want to <a href="../articles.php?page='.$urlname.'">view it</a> or <a href="any.php?action=new">make a new one</a>?</p>';
            include ('include/foot.htm');
            exit();
        }
        else
	{
            $pagetitle = 'Error';
            include ('include/head.htm');
            echo '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> <strong>Update failed.</strong>
            <br />I wonder why?</p><p><ul><li>UrlName: '.$urlname.'</li><li>Title: '.$title.'</li><li>PageDesc: '.$_POST[desc].'</li><li>Subheading: '.$subheader.'</li><li>AnyCat1: '.$_POST[category1].'</li><li>AnyCat2: '.$_POST[category2].'</li><li>AnyCat3: '.$_POST[category3].'</li><li>AnyCat4: '.$_POST[category4].'</li><li>Timestamp: '.$timestamp.'</li><li>AnyID: '.$_POST[AnyID].'</li></ul>';
            include ('include/foot.htm');
            exit();
	}
    }
}

if($_GET[action] == 'new') {
    ## Create new Anypage
    $pagetitle = 'New Anypage';
    include ('include/head.htm');
    
    $date_now = date('F');
    ?>

    <form method="post" action="<?php echo $_SERVER[PHP_SELF]; ?>">
    <input type="hidden" name="submitted" value="submitted" />
    <fieldset>
    <legend>Required details (plain text only)</legend>
    URL string: <input type="text" size="15" name="urlname" />
    Title:<input type="text" size="60" name="title" />
    <p>
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
    <legend>Optional details (HTML allowed)</legend>
    Page description (only shown for categorised pages):<br />
    <textarea cols="60" rows="2" name="desc"></textarea>
    </p><p>
    Subheading (for secondary titles or pagination):<br />
    <textarea cols="60" rows="2" name="subheading"></textarea>
    </p><p>
    Categories (leave blank for none):<br />
    <select name="category1">
    <option></option>
<?php
    $category_names = NULL;
    $get_cats = mysql_query("SELECT ACatID, AnyCatName FROM ".ANYCAT_TBL);
    while ($cats = mysql_fetch_array($get_cats)) {
        $category_names .= '<option value="'.$cats[ACatID].'">'.$cats[AnyCatName].'</option>';
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
    </p>
    </fieldset>
    <fieldset>
    <legend>Text of Anypage (HTML allowed)</legend>
    <script type="text/javascript">edToolbar();</script>
    <textarea id="canvas" cols="80" rows="20" name="body"></textarea>
    <script type="text/javascript">var edCanvas = document.getElementById('canvas');</script>
    <p>
    <?php include ('include/autobreak.inc'); ?>
    </p>
    <input type="submit" name="submitnew" value="Create Anypage" />
    </fieldset>
    </form>

<?php
    include ('include/foot.htm');
    exit();
} elseif($_GET[action] == 'edit') {
    // Edit an existing Anypage
    $pagetitle = 'Edit Anypage #'.$_GET[AnyID];
    include ('include/head.htm');

    $get_old_data = mysql_query("SELECT * FROM ".ANYPAGE_TBL." WHERE AnyID=$_GET[AnyID]");
    $old = mysql_fetch_array($get_old_data);
    $old_desc = htmlspecialchars($old[PageDesc]);
    $old_subheading = htmlspecialchars($old[Subheading]);
    $old_body = htmlspecialchars($old[PageBody]);
    if($users_returned[Permission] == 3) {
        if($old[Author] != $_COOKIE[auth_id]) {
            echo '<strong>You are not authorised to edit this page.</strong>';
            exit();
        }
    }
    $get_cat_names = mysql_query("SELECT ACatID, AnyCatName FROM ".ANYCAT_TBL);
    while($get_cat_names_result = mysql_fetch_array($get_cat_names)) {
        $cname[$get_cat_names_result[ACatID]] = $get_cat_names_result[AnyCatName];
    }
    
    $old_month = date('F', $old['Timestamp']);
    ?>
    <form method="post" action="<?php echo $_SERVER[PHP_SELF]; ?>">
    <input type="hidden" name="submitted" value="submitted" />
    <input type="hidden" name="AnyID" value="<?php echo $_GET[AnyID]; ?>" />
    <fieldset>
    <legend>Required details (plain text only)</legend>
    URL string: <input type="text" size="15" name="urlname" value="<?php echo $old[UrlName]; ?>" />
    Title:<input type="text" size="60" name="title" value="<?php echo $old[Title]; ?>" />
    <p>
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
    <input type="text" size="2" class="noformat" value="<?php echo date('d', $old['Timestamp']); ?>" name="date" />
    <input type="text" size="4" class="noformat" value="<?php echo date('Y', $old['Timestamp']); ?>" name="year" />
    <input type="text" size="5" class="noformat" value="<?php echo date('H:i', $old['Timestamp']); ?>" name="time" />
    </p>
    </fieldset>
    <fieldset>
    <legend>Optional details (HTML allowed)</legend>
    Page description (only shown for categorised pages):<br />
    <textarea cols="60" rows="2" name="desc"><?php echo $old_desc; ?></textarea>
    <input type="checkbox" style="margin-left: 4em;" name="delete" /><strong>Delete</strong> this Anypage?
    <p>
    Subheading (for secondary titles or pagination):<br />
    <textarea cols="60" rows="2" name="subheading"><?php echo $old_subheading; ?></textarea>
    <input type="submit" style="margin-left: 4em; vertical-align: top;" name="submitedit" value="Update Anypage" />
    </p><p>
    Categories (leave blank for none):<br />
<?php
    for ($i = 1; $i <= 4; ++$i) {
        $code = 'AnyCat'.$i;
        echo '
        <select name="category'.$i.'">
        <option value="'.$old[$code].'">'.$cname[$old[$code]].'</option>
        <option value="0">&nbsp;</option>
        ';
        foreach($cname as $id => $name) {
            echo '<option value="'.$id.'">'.$name.'</option>';
        }
        echo '</select>';
    }
?>
    </fieldset>
    <fieldset>
    <legend>Text of Anypage (HTML allowed)</legend>
    <script type="text/javascript">edToolbar();</script>
    <textarea id="canvas" cols="80" rows="20" name="body"><?php echo $old_body; ?></textarea>
    <script type="text/javascript">var edCanvas = document.getElementById('canvas');</script>
    </p><p>
    <?php include ('include/autobreak.inc'); ?>
    </p><p>
    <input type="submit" name="submitedit" value="Update Anypage" />
    </p>
    </fieldset>
    </form>

<?php
    include ('include/foot.htm');
    exit();
} else {
    ## Show existing Anypages
    $pagetitle = 'Edit Anypage';
    include ('include/head.htm');

    switch ($p[Permission]) {
        case 1:
        case 2:
            $permission = "";
        break;
        case 3:
            $permission = "WHERE Author='$_COOKIE[auth_id]' ";
        break;
        default:
            $permission = "WHERE Author='$_COOKIE[auth_id]' ";
        break;
    }

    $list = mysql_query("SELECT AnyID, UrlName, AnyCat1, AnyCat2, AnyCat3, AnyCat4, Timestamp FROM ".ANYPAGE_TBL." ".$permission."ORDER BY
    UrlName ASC");
    $total = mysql_num_rows($list);
    echo '<p align="center">There are <strong>'.$total.'</strong> Anypages</p><p>';

    $get_cat_names = mysql_query("SELECT ACatID, AnyCatName FROM ".ANYCAT_TBL);
    while($get_cat_names_result = mysql_fetch_array($get_cat_names)) {
        $cname[$get_cat_names_result[ACatID]] = $get_cat_names_result[AnyCatName];
    }

    echo '
    <table border="0" cellpadding="5" cellspacing="2">
    <tr bgcolor="#FFFFFF">
    <th>URL string</th><th>Categories</th><th>Date</th>
    </tr>';
    $bg = '#EEEEEE';
    while ($page = mysql_fetch_array($list)) {
        $categories = NULL;
        for ($i = 1; $i <= 4; ++$i) {
            if(empty($page['AnyCat'.$i])) {$categories .= "";} else {
                $categories .= $cname[$page['AnyCat'.$i]].', ';
            }
        }
        $categories = substr($categories, 0, -2);
        $bg = ($bg == '#FFFFFF' ? '#EEEEEE' : '#FFFFFF');
        
        echo '
        <tr bgcolor="'.$bg.'">
        <td><a href="any.php?action=edit&amp;AnyID='.$page[AnyID].'">'.$page[UrlName].'</a></td>
        <td>'.$categories.'</td>
        <td>'.date('Y M d', $page[Timestamp]).'</td>
        </tr>';
    }
    echo '</table>';
    
    include ('include/foot.htm');
    exit();
}
?>
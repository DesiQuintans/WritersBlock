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

$pagetitle = 'Moderate comments';
include ('include/head.htm');

if ($_POST['submitted']) {
	## Bulk-delete comments before processing if required.
	if($_POST['submit_manage']) {
	    if(isset($_POST['delete_array'])) {
	        $del_list = implode(') OR (CommID=', $_POST['delete_array']);
	        $bulk_delete = mysql_query("DELETE FROM ".COMMENTS_TBL." WHERE (CommID=$del_list)");
		} else {
		    die('No comments selected for deletion.');
		}
        if(isset($bulk_delete)) {
            switch(count($_POST['delete_array'])) {
                case 1:
                    $subject = 'The comment was';
                    break;
                default:
                    $subject = 'The comments were';
                    break;
            }
            echo '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>'.$subject.' deleted.</strong>
            <br />Want to <a href="comments.php?action=manage&amp;PostID='.$_POST['PostID'].'">moderate another</a>?</p>';
            include ('include/foot.htm');
            exit();
        }
	}
    ## Parse the input for any oddness.
    include ('include/xmlfriendly.inc');
    if (empty($_POST['name'])) $name = 'Anonymous'; else $name = strip_tags($_POST['name']);
    $url = strip_tags($_POST['url']);
    $comment = strip_tags($_POST['comment'], '<i><em><b><strong><u><a><br><br /><span>');
    foreach($ItemArray as $key => $value) {
        $comment = str_replace($key, $value, $comment);
    }
    if($_POST['delete']) {
        $deleted = mysql_query("DELETE FROM ".COMMENTS_TBL." WHERE PostID=".$_POST['PostID']." AND CommID=".$_POST['CommID']);
        if($deleted) {
            echo '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Comment was deleted.</strong>
            <br />Want to <a href="comments.php?action=manage&amp;PostID='.$_POST['PostID'].'">edit another</a>?</p>';
            include ('include/foot.htm');
            exit();
        }
    }
    $edited = mysql_query("UPDATE ".COMMENTS_TBL." SET Name='$name', Url='$url', Body='$comment' WHERE PostID=".$_POST['PostID']." AND
    CommID=".$_POST['CommID']);
    if($edited) {
        echo '
        <p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Comment was edited.</strong>
        <br />Want to <a href="../permalink.php?PostID='.$_POST['PostID'].'#C'.$_POST['CommID'].'">see it</a>?
        </p>
        ';
        include ('include/foot.htm');
        exit();
    }
}

if($_GET['action'] == 'manage') {
	## Manage and bulk-delete comments.
	$mancomm = mysql_query("SELECT CommID, Name, Body FROM ".COMMENTS_TBL." WHERE PostID=$_GET[PostID] ORDER BY CommID DESC");
    echo '
    <form method="post" action="'.$_SERVER['PHP_SELF'].'">
    <input type="hidden" value="submitted" name="submitted" />
    <input type="hidden" value="'.$_GET['PostID'].'" name="PostID" />
   	<p align="center">
	<input type="submit" value="Delete checked comments" name="submit_manage" />
	</p>
    <table border="0" cellpadding="5" cellspacing="2">
    <tr bgcolor="#FFFFFF">
    <th>Delete</th><th>Edit</th><th>Author</th><th>Comment</th>
    </tr>';
    $bg = '#EEEEEE';
	while($manage_comm = mysql_fetch_array($mancomm)) {
 		$bg = ($bg == '#FFFFFF' ? '#EEEEEE' : '#FFFFFF');
        echo '
        <tr bgcolor="'.$bg.'">
        <td align="center"><input type="checkbox" name="delete_array[]" value="'.$manage_comm['CommID'].'" /></td>
		<td align="center"><a href="comments.php?action=edit&amp;CommID='.$manage_comm['CommID'].'" title="Edit comment"><span class="button"><img src="include/img/edit.png" width="16" height="16" alt="Edit comment" /></span></a></td>
		<td>'.$manage_comm['Name'].'</td>
        <td>'.substr($manage_comm['Body'], 0, 100).'</td>
        </tr>';
	}
	echo '
	</table>
	<p align="center">
	<input type="submit" value="Delete checked comments" name="submit_manage" />
	</p>
	</form>';
    include ('include/foot.htm');
    exit();
} elseif($_GET['action'] == 'edit') {
    ## Edit comments
    $showcomm = mysql_query("SELECT CommID, PostID, Name, Email, Url, Body FROM ".COMMENTS_TBL." WHERE CommID=$_GET[CommID]");
    $row = mysql_fetch_array($showcomm);
    echo '
    <form method="post" action="'.$_SERVER['PHP_SELF'].'">
    <fieldset>
    <legend>This is comment #'.$row['CommID'].'</legend>
    <input type="hidden" name="PostID" value="'.$row['PostID'].'" />
    <input type="hidden" name="CommID" value="'.$row['CommID'].'" />
    Name:<input type="text" name="name" size="30" value="'.$row['Name'].'" />
    URL:<input type="text" name="url" size="30" value="'.$row['Url'].'" />
    <p>
    Email:&nbsp;
    <a href="mailto:'.$row['Email'].'">'.$row['Email'].'</a>
    </p><p>
    <span style="vertical-align: top;">Comment:</span>
    <textarea cols="50" rows="5" name="comment">'.htmlspecialchars($row['Body']).'</textarea>
    </p><p>
    <input type="checkbox" name="delete" />Delete this comment?
    </p><p>
    <input type="submit" name="submitted" value="Edit this comment" />
    </p>
    </fieldset>
    </form>
    ';
    include ('include/foot.htm');
    exit();
} else {
    ## List posts and number of comments
    if (!isset($_GET['pg'])) $page = 1; else $page =& $_GET['pg'];
    if(!is_numeric($page)) $page = 1;
    $offset = ($page*30)-30;
    $getrows = mysql_query("SELECT PostID FROM ".POSTS_TBL);
    $pagecount = ceil(mysql_num_rows($getrows)/30);

    $paginate = NULL;
    if($page>1) $paginate .= '<a href="comments.php?pg='.($page-1).'">Back</a> | ';
    else $paginate .= '';
    for($pg = 1; $pg <= $pagecount; ++$pg) {
        if ($pg == $page) $paginate .= '<strong>'.$pg.'</strong> ';
        else $paginate .= '<a href="comments.php?pg='.$pg.'">'.$pg.'</a> ';
    }
    if($page<$pagecount) $paginate .= '| <a href="comments.php?pg='.($page+1).'">Next</a>';
    else $paginate .= '';

    echo $paginate;
    echo '
    <table border="0" cellpadding="5" cellspacing="2">
    <tr bgcolor="#FFFFFF">
    <th>Date</th><th>Title</th><th>Comments</th>
    </tr>';
    $showposts = mysql_query("SELECT PostID, Title, Timestamp FROM ".POSTS_TBL." ORDER BY Timestamp DESC LIMIT $offset,30");
    $bg = '#EEEEEE';
    while($row = mysql_fetch_array($showposts)) {
        $comms = mysql_query("SELECT CommID FROM ".COMMENTS_TBL." WHERE PostID=".$row['PostID']);
        $bg = ($bg == '#FFFFFF' ? '#EEEEEE' : '#FFFFFF');
        echo '
        <tr bgcolor="'.$bg.'">
        <td>'.date('Y M d', $row['Timestamp']).'</td>
        <td><a href="comments.php?action=manage&amp;PostID='.$row['PostID'].'">'.$row['Title'].'</a></td>
        <td>'.mysql_num_rows($comms).'</td>
        </tr>';
    }
    echo '</table>';
    echo '<p>'.$paginate.'</p>';

    include ('include/foot.htm');
}
?>

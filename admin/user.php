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
    if ($p['Permission'] != 1) {
        $pagetitle = 'Not authorised';
        include ('include/head.htm');
        print '<p align="center"><img src="include/img/unauthorised.png" width="16" height="16" alt="" /> You are not authorised to create or edit users.</a>';
        include ('include/foot.htm');
        exit();
    }
}

include ('include/xmlfriendly.inc');

if ($_POST['submitted']) {
    $error = NULL;
    if($_POST['pass1'] == $_POST['pass2']) $password =& $_POST['pass1']; else echo '<p>The two entered passwords do not match.</p>';
    if($_POST['submitnew']) {
        if(empty($_POST['username'])) {
            $pagetitle = 'No username entered';
            include ('include/head.htm');
            echo '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> You didn\'t enter a username.</p>';
       } else {
            $name = mysql_query("SELECT UserID FROM ".USERS_TBL." WHERE UserName='".$_POST['username']."'");
            if(mysql_num_rows($name) > 0) {
                $uname = False;
                $pagetitle = 'Username already exists';
                include ('include/head.htm');
                echo '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> The username entered is already in use.</p>';
                include ('include/foot.htm');
                exit();
            } else $uname =& $_POST['username'];
        }
    }
    $bio = trim($_POST['bio']);
    // This makes the biography XHTML-friendly.
    foreach($ItemArray as $key => $value) {
        $bio = str_replace($key, $value, $bio);
    }
    if ($_POST['submitnew'] and $uname) {
        if($password) {
            $user_made = mysql_query("INSERT INTO ".USERS_TBL." (UserID, DispName, UserName, Password, Email, Permission, Bio) VALUES
            (NULL, '".$_POST['dispname']."', '".$_POST['username']."', '".md5($password)."', '".$_POST['email']."', '".$_POST['permission']."', '$bio')");
            if ($user_made) {
                $pagetitle = 'Done';
                include ('include/head.htm');
                print '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>New user created.</strong>
                <br />Do you want to <a href="user.php">edit its settings</a> or <a href="post.php?action=new">make a new one</a>?</p>';
                include ('include/foot.htm');
                exit();
            }
        }
    } elseif($_POST['submitedit']) {
        if ($_POST['delete']) $deleted = mysql_query("DELETE FROM ".USERS_TBL." WHERE UserID='".$_POST['UserID']."'");
        if ($deleted) {
            $pagetitle = 'Done';
            include ('include/head.htm');
            print '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>User deleted.</strong>
            <br />Do you want to <a href="user.php?action=new">make a new one</a>?</p>';
            include ('include/foot.htm');
            exit();
        }
            $edited = mysql_query("UPDATE ".USERS_TBL." SET DispName='".$_POST['dispname']."', Email='".$_POST['email']."',
            Permission='".$_POST['permission']."', Bio='$bio' WHERE UserID='".$_POST['UserID']."'");
            if ($edited) {
                $pagetitle = 'Done';
                include ('include/head.htm');
                print '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>User edited.</strong><br />
                Do you want to <a href="user.php?action=new">make a new one</a>, or <a href="user.php">edit another</a>?</p>';
                include ('include/foot.htm');
                exit();
            }
    }
}

if ($_GET['action'] == 'new') {
    ## Make new user
    $pagetitle = 'Create a user';
    include ('include/head.htm');
    ?>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="submitted" value="submitted" />
    <fieldset>
    <legend>Administration details</legend>
    Username:<input type="text" name="username" size="20" />
    Email:<input type="text" name="email" size="20" />
    <p>
    Password:<input type="password" name="pass1" size="20" />
    Verify password:<input type="password" name="pass2" size="20" />
    </p><p>
    Permission:
    <select name="permission">
    <option value="3">Contributor</a>
    <option value="2">Assistant</a>
    <option value="1">Administrator</a>
    </select>
    </fieldset>
    <fieldset>
    <legend>Public details</legend>
    Display name:<input type="text" name="dispname" size="30" />
    <p>
    Biography/Signature:<br />
    <textarea cols="40" rows="6" name="bio"></textarea>
    </p><p>
    <input type="submit" name="submitnew" value="Create new user" />
    </p>
    </fieldset>
    </form>
<?php
    include ('include/foot.htm');
    exit();
} elseif ($_GET['action'] == 'edit') {
    ## Edit an existing user
    $get_user = mysql_query("SELECT DispName, UserName, Email, Permission, Bio FROM ".USERS_TBL." WHERE UserID='".$_GET['UserID']."'");
    $user = mysql_fetch_array($get_user);
    $pagetitle = 'Editing user '.$user['DispName'];
    include ('include/head.htm');
?>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="submitted" value="submitedit" />
    <input type="hidden" name="UserID" value="<?php echo $_GET['UserID']; ?>" />
    <fieldset>
    <legend>Administration details</legend>
    Email:<input type="text" name="email" size="20" value="<?php echo $user['Email']; ?>" />
    <p>
    Permission:
    <select name="permission">
    <option value="<?php echo $user['Permission']; ?>">
<?php
    switch ($user['Permission']) {
        case 1:
            echo 'Administrator';
            break;
        case 2:
            echo 'Assistant';
            break;
        case 3:
            echo 'Contributor';
            break;
        default:
            echo 'Invalid';
            break;
    }
?>
    </option>
    <option value="3">Contributor</a>
    <option value="2">Assistant</a>
    <option value="1">Administrator</a>
    </select>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <input type="checkbox" id="delete" name="delete" /><label for="delete"><strong>Delete</strong> this user?</label>
    </p><p>
    If deleted, all blog posts and Anypages made by this user will not display an author.
    </p>
    <input type="submit" name="submitedit" value="Update user" />
    </fieldset>
    <fieldset>
    <legend>Public details</legend>
    Display name:<input type="text" name="dispname" size="30" value="<?php echo $user['DispName']; ?>" />
    <p>
    Biography/Signature:<br />
    <textarea cols="40" rows="6" name="bio"><?php echo htmlspecialchars($user['Bio']); ?></textarea>
    </p><p>
    <input type="submit" name="submitedit" value="Update user" />
    </p>
    </fieldset>
    </form>
<?php
    include ('include/foot.htm');
    exit();
} else {
    ## Show list of users to edit
    $pagetitle = 'Edit an existing user';
    include('include/head.htm');
    $get_users = mysql_query("SELECT UserID, DispName, Permission FROM ".USERS_TBL);
    
    echo '
    <table border="0" cellpadding="5" cellspacing="2">
    <tr bgcolor="#FFFFFF">
    <th>User</th><th>Permission</th>
    </tr>';
    $bg = '#EEEEEE';
    while($users = mysql_fetch_array($get_users)) {
        $bg = ($bg == '#FFFFFF' ? '#EEEEEE' : '#FFFFFF');
        switch ($users['Permission']) {
            case 1:
                $user_permission = 'Administrator';
            break;
            case 2:
                $user_permission = 'Assistant';
            break;
            case 3:
                $user_permission = 'Contributor';
            break;
            default:
                $user_permission = 'Invalid Permission';
            break;
        }
        echo '
        <tr bgcolor="'.$bg.'">
        <td><a href="user.php?action=edit&amp;UserID='.$users['UserID'].'">'.$users['DispName'].'</a></td>
        <td>'.$user_permission.'</td>
        </tr>';
    }
    echo '</table>';
    include ('include/foot.htm');
    exit();
}
?>

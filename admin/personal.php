<?php
# Writer's Block v3.6: http://www.desiquintans.com/writersblock
# Writer's Block is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('control.php');

// Check for authentication
if (!isset($_COOKIE['auth_id'], $_COOKIE['auth_username'], $_COOKIE['auth_hash'])) {
    include('login.php');
    exit();
} else {
    // Special authentication query. Selects more fields than usual so its reslt resource can be reused in the Edit screen.
    $authenticate = mysql_query("SELECT DispName, UserName, Email, Permission, Bio FROM ".USERS_TBL." WHERE UserID='".$_COOKIE['auth_id']."' AND
    UserName='".$_COOKIE['auth_username']."' AND Password='".$_COOKIE['auth_hash']."'");
    if(mysql_num_rows($authenticate) != 1) {
        include ('login.php');
        exit();
    } else {
        $extend_expiry = time()+7200;
        setcookie('auth_id', $_COOKIE['auth_id'], $extend_expiry, '', '', 0);
        setcookie('auth_username', $_COOKIE['auth_username'], $extend_expiry, '', '', 0);
        setcookie('auth_hash', $_COOKIE['auth_hash'], $extend_expiry, '', '', 0);
    }
}

$pagetitle = 'Editing your settings';
include ('include/head.htm');
include ('include/xmlfriendly.inc');

if ($_POST['submitedit']) {
    if(empty($_POST['initialpass'])) {
        echo '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> You didn\'t enter your password as confirmation.</p>';
    } else {
            if(!empty($_POST['pass1']) && !empty($_POST['pass2'])) {
                if($_POST['pass1'] == $_POST['pass2']) {
                    $password = ", Password='".md5($_POST['pass1'])."'";
                } else {
                    echo '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> The two entered passwords do not match.</p>';
                    include('include/foot.htm');
                    exit();
                }
            } else {
                $password = '';
            }
            $bio = trim($_POST['bio']);
            // This makes the biography XHTML-friendly.
            foreach($ItemArray as $key => $value) {
                $bio = str_replace($key, $value, $bio);
            }
        if(isset($password)) {
            $edited = mysql_query("UPDATE ".USERS_TBL." SET DispName='".$_POST['dispname']."'$password, Email='".$_POST['email']."',
            Bio='$bio' WHERE UserID='".$_POST['UserID']."' AND Password='".md5($_POST['initialpass'])."'");
            if ($edited) {
                print '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> <strong>Your details were updated.</strong>
                <br />If you changed your password you will be asked to log in again when you load a new Admin page.</p>';
                include ('include/foot.htm');
                exit();
            } else {
                print '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> <strong>Your details could not be updated.</strong>
                <br />Be sure to enter your password correctly before submitting the form.</p>';
                include ('include/foot.htm');
                exit();
            }
        }
    }
}

## Edit an existing user
$user = mysql_fetch_array($authenticate);
    ?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<input type="hidden" name="UserID" value="<?php echo $_COOKIE['auth_id']; ?>" />
<fieldset>
<legend>Administration details</legend>
Email:<input type="text" name="email" size="20" value="<?php echo $user['Email']; ?>" />
<p>
Password:<input type="password" name="pass1" size="20" />
Verify password:<input type="password" name="pass2" size="20" />
</p>
</fieldset>
<fieldset>
<legend>Public details</legend>
Display name:<input type="text" name="dispname" size="30" value="<?php echo $user['DispName']; ?>" />
<p>
Biography/Signature (HTML allowed):<br />
    <textarea cols="40" rows="6" name="bio"><?php echo htmlspecialchars($user['Bio']); ?></textarea>
    </p><p>
    Enter your password to make these changes:<input type="password" name="initialpass" size="20" />
    </p>
    <input type="submit" name="submitedit" value="Update your details" />
    </fieldset>
    </form>
<?php
include ('include/foot.htm');
?>
<?php
# Writer's Block v3.6: http://www.desiquintans.com/writersblock
# Writer's Block is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('control.php');

if($_POST['login']) {
    $error = NULL;
    if(empty($_POST['username']) or strpos($_POST['username'], ' ')) {
        $u = FALSE;
        $error .= '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> Invalid username.</p>';
    } else {
        $u =& $_POST['username'];
    }
    if(empty($_POST['password']) or strpos($_POST['password'], ' ')) {
        $p = FALSE;
        $error .= '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> Invalid password.</p>';
    } else {
        $p =& $_POST['password'];
    }

    if($u and $p) {
        $get_user_auth = mysql_query("SELECT UserID, UserName, Password FROM ".USERS_TBL." WHERE UserName='$u' AND Password='".md5($p)."'");
        if(mysql_num_rows($get_user_auth) == 1) {
            $authenticated = mysql_fetch_array($get_user_auth);
            setcookie('auth_id', $authenticated['UserID'], time()+7200, '', '', 0);
            setcookie('auth_username', $authenticated['UserName'], time()+7200, '', '', 0);
            setcookie('auth_hash', $authenticated['Password'], time()+7200, '', '', 0);
            header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php");
            exit();
        } else {
            $error .= '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> No users with that username and password were found.</p>';
        }
    }
}

$pagetitle = 'Log in';
include ('include/head.htm');
if($error != NULL) echo '<p align="center"><strong>'.$error.'</strong></p>';
?>
<p align="center"><img src="include/img/unauthorised.png" width="16" height="16" alt="" /> You must log in to use the Admin section. Cookies must be turned on.</p>
<form method="post" action="login.php" style="text-align: center;">
Username<br /><input type="text" size="15" class="noformat" name="username" /><br />
Password<br /><input type="password" size="15" class="noformat" name="password" />
<p>
<input type="submit" name="login" value="Log in" />
</p><p>
<a href="forgot.php">I&#8217;ve forgotten my password!</a>
</p>
</form>

<?php
include ('include/foot.htm');
?>
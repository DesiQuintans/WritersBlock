<?php
# Writer's Block v3.6: http://www.desiquintans.com/writersblock
# Writer's Block is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('control.php');
$pagetitle = 'I forgot my password!';
include ('include/head.htm');

if($_POST['reset']) {
    if(empty($_POST['user'])) {
        $u = FALSE;
        echo '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> You didn\'t enter a username.</p>';
    } else {
        $u =& $_POST['user'];
    }
    if($u) {
        $getinfo = mysql_query("SELECT UserID, Email FROM ".USERS_TBL." WHERE UserName='$u'");
        if(mysql_num_rows($getinfo) != 1) {
            echo '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> No user was found by the username entered.</p>';
        } else {
            $info = mysql_fetch_array($getinfo);
            $p = substr(md5(uniqid(rand(),1)), 3, 10);
            $reset_pass = mysql_query("UPDATE ".USERS_TBL." SET Password='".md5($p)."' WHERE UserID='".$info['UserID']."'");
            $email_body = 'Your password to log into Writer\'s Block has been changed to "'.$p.'". Once you are
            logged in you can change your password to something more familiar.';
            $emailed = mail($info['Email'], 'Writer\'s Block Password', $email_body);
        }
    }
    if($reset_pass && $emailed) {
        echo '<p align="center"><img src="include/img/email.png" width="16" height="16" alt="" /> A temporary password has been emailed to you.</p>';
        include ('include/foot.htm');
        exit();
    }
}
?>
<p>
Enter your username to have your password resetted. The new password will be sent to the email address you supplied.
</p>
<form method="post" action="forgot.php" style="text-align: center;">
<label for="username">Username</label><br /><input type="text" size="15" id="username" name="user" class="noformat" />
<p>
<input type="submit" name="reset" value="Reset Password" />
</p>
</form>
<?php
include ('include/foot.htm');
?>
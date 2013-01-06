<?php
# Writer's Block v3.6: http://www.desiquintans.com/writersblock
# Writer's Block is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require('control.php');

if (!isset($_COOKIE['auth_id'], $_COOKIE['auth_username'], $_COOKIE['auth_hash'])) {
    include('login.php');
    exit();
} else {
    $authenticate = mysql_query("SELECT Permission FROM ".USERS_TBL." WHERE UserID='".$_COOKIE['auth_id']."' AND
    UserName='".$_COOKIE['auth_username']."' AND Password='".$_COOKIE['auth_hash']."'");
    if(mysql_num_rows($authenticate) != 1) {
        include('login.php');
        exit();
    }
}

$expiry = time()-600;
$wipe_id = setcookie('auth_id', '', $expiry, '', '', 0);
$wipe_user = setcookie('auth_username', '', $expiry, '', '', 0);
$wipe_hash = setcookie('auth_hash', '', $expiry, '', '', 0);

$pagetitle = 'Log out';
include ('include/head.htm');

if($wipe_id and $wipe_user and $wipe_hash) {
    echo '<p align="center"><img src="include/img/unauthorised.png" width="16" height="16" alt="" /> You are now logged out. Want to <a href="login.php">log in</a> again?</p>';
} else {
    echo '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> You couldn\'t be logged out. Were you <a href="login.php">logged in</a> to begin with?</p>';
}

include ('include/foot.htm');
?>
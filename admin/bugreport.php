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

$pagetitle = 'Sending a bug report';
include ('include/head.htm');

if($_POST['sendreport']) {
    $mail_message = NULL;
    if(empty($_POST['report'])) {
        echo '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> Invalid bug report.</p>';
    } else {
        $mail_message = substr(strip_tags($_POST['report']), 0, 1500);
    }
    if($mail_message) {
        $mail_text = 'From '.strip_tags($_POST['sender_email']).': '.$mail_message;
        if(mail('desiq@bigpond.com', 'WB Bug report', $mail_text)) {
            echo '<p align="center"><img src="include/img/email.png" width="16" height="16" alt="" /> Bug report sent. Thanks!</p>';
        } else {
            echo '<p align="center"><img src="include/img/failure.png" width="16" height="16" alt="" /> Bug report couldn\'t be sent. Ask your webhost if PHP can access a mail binary.</p>';
        }
    }
    include ('include/foot.htm');
    exit();
}
?>

<p>
Enter your email address and a report of the bug below. Be as concise as possible because there is a cut-off after 1500 characters (about 150-300 words).
</p><p>
Be sure to tell me what the bug is, what page it's on, any error messages you might receive and how you came across the bug.
</p>
<form method="post" action="bugreport.php" style="text-align: center;">
<label for="email">Email address (optional, but preferred)</label><br />
<input type="text" size="40" class="noformat" id="email" name="sender_email" />
<br /><br />
<label for="text">Report (1500 chars only, no HTML)</label><br />
<textarea class="noformat" name="report" id="text" cols="60" rows="5" /></textarea>
<br /><br />
<input type="submit" name="sendreport" value="Send my bug report" />
</form>

<?php
include ('include/foot.htm');
?>
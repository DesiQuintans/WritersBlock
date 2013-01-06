<?php
# Writer's Block v3.8: http://www.desiquintans.com/writersblock
# Writer's Block is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('control.php');

if (!isset($_COOKIE['auth_id'], $_COOKIE['auth_username'], $_COOKIE['auth_hash'])) {
    include('login.php');
    exit();
} else {
    $extend_expiry = time()+7200;
    setcookie('auth_id', $_COOKIE['auth_id'], $extend_expiry, '', '', 0);
    setcookie('auth_username', $_COOKIE['auth_username'], $extend_expiry, '', '', 0);
    setcookie('auth_hash', $_COOKIE['auth_hash'], $extend_expiry, '', '', 0);
}

$pagetitle = 'Welcome to Writer\'s Block';
include ('include/head.htm');
?>
If the written word is the wheel, then Writer's Block is the sweet, sweet fossil fuel in the engine that keeps it spinning. Oh yes. The functions you are
allowed to perform are on the toolbar. Simply hover your pointer over a button to find out what it does.

<h2>Must-read documentation</h2>
The <a href="http://www.desiquintans.com/writersblock/manual/index.php">Writer&#8217;s Block Online Manual</a> explains a lot of things that aren&#8217;t
explained within Writer&#8217;s Block itself.
<p>
The <a href="http://www.desiquintans.com/miniblog.php?blog=writersblock_dev">Writer&#8217;s Block Dev Diary</a> tracks updates, including bug fixes and
new blacklist releases, and is also available as <a href="http://www.desiquintans.com/rss.php?miniblog=writersblock_dev">an RSS feed</a>.
</p>
<?php
include ('include/foot.htm');
?>

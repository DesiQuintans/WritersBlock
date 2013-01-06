<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="EN">
<head>
<!--
	# Writer's Block v3.8a: http://www.desiquintans.com/writersblock
	# Writer's Block is free under version 2 or later of the GPL.
	# This program is distributed with cursory support, but without
	# warranty or guarantee of any sort.
-->
<?php require ('admin/control.php'); ?>
<title>Writer's Block v3.8a Installation</title>
<style type="text/stylesheet"><![CDATA[
BODY {font-family: Verdana; font-size: 9pt; font-weight: normal; color: #000000; text-align: justify; margin-left: 50px;}
H1 {font-size: 15pt; font-weight: normal; text-align: center; text-decoration: underline; margin-left: -40px;}
H2 {font-size: 12pt; font-weight: normal; margin-left: -40px;}
fieldset {padding: 5px; width: 500px;}
]]></style>
</head>
<body>
<h1>Install <em>Writer's Block</em> v3.8a</h1>
<?php
if($_POST[submit]) {
    // Part 1: Check entered information.
    if(!empty($_POST['dispname'])) $disp =& $_POST['dispname']; else print "Enter a display name.<br />";
    if(!empty($_POST['user'])) $user =& $_POST['user']; else print "Enter a username.<br />";
    if(!empty($_POST['pass1'])) $pass1 =& $_POST['pass1']; else print "Enter a password.<br />";
    if(!empty($_POST['pass2'])) $pass2 =& $_POST['pass2']; else print "Must type password a second time.<br />";
    if(!empty($_POST['email'])) $email =& $_POST['email']; else print "Enter an email address.<br />";
    // Check if passwords matched.
    if($pass1 == $pass2) $pass =& $pass1; else print "<em>The two entered passwords do not match.</em><br />";

    if(!empty($_POST['siteurl'])) $siteurl =& $_POST['siteurl']; else print "Must give a blog URL.<br />";
    if(!empty($_POST['defaulttitle'])) $defaulttitle =& $_POST['defaulttitle']; else print"Enter an index.php title.<br />";
    if(!empty($_POST['tagline'])) $tagline =& $_POST['tagline']; else print "Enter a tagline.<br />";
    if(!empty($_POST['showposts'])) $showposts =& $_POST['showposts']; else print "Enter number of posts to display.<br />";
    if(!empty($_POST['TZoffset'])) $tzoffset =& $_POST['TZoffset']; else print "Must enter a GMT difference.<br />";

    // If all fields are set, go ahead with installation, or else die.
    if($disp && $user && $pass && $email && $siteurl && $defaulttitle && $tagline && $showposts) {
        // Part 2: Create tables.

        $makeUSERS = mysql_query("CREATE TABLE ".USERS_TBL." (
        UserID INT NOT NULL AUTO_INCREMENT,
        DispName TINYTEXT NOT NULL,
        UserName TINYTEXT NOT NULL,
        Password CHAR(32) NOT NULL,
        Email TINYTEXT NOT NULL,
        Permission TINYINT(1) NOT NULL DEFAULT 3,
        Bio TEXT,
        PRIMARY KEY (UserID),
        UNIQUE KEY (UserID)
        )");
        if($makeUSERS) echo '<span style="color: green;">Table '.USERS_TBL.' successfully created.</span><br />';
            else echo '<span style="color: #FF0000;">'.mysql_error().'</span> You can <a href="mailto:me@desiquintans.com">email the author</a> for help.<br />';

        if($makeUSERS) $initialise_users = mysql_query("INSERT INTO ".USERS_TBL." (UserID, DispName, UserName, Password, Email,
        Permission, Bio) VALUES (NULL, '$disp', '$user', '".md5($pass)."', '$email', 1, '$disp should put a description of
        him/herself here, along with a link to his/her website.')");
        if ($initialise_users) print '<span style="color: green;">'.USERS_TBL.' table is storing your login details.</span><br />';
            else echo '<span style="color: #FF0000;">'.mysql_error().'</span> You can <a href="mailto:me@desiquintans.com">email the author</a> for help.<br />';

        $makePOSTS = mysql_query("CREATE TABLE ".POSTS_TBL." (
        PostID INT NOT NULL AUTO_INCREMENT,
        Title TINYTEXT NOT NULL,
        Body LONGTEXT NOT NULL,
        Timestamp INT(20) NOT NULL,
        PostCat1 TINYINT(3),
        PostCat2 TINYINT(3),
        PostCat3 TINYINT(3),
        PostCat4 TINYINT(3),
        Author TINYINT(2) DEFAULT 1,
        Draft TINYINT(1) DEFAULT 0,
        Thread TINYINT(1) DEFAULT 2,
        PRIMARY KEY (PostID),
        UNIQUE KEY (PostID)
        )");
        if ($makePOSTS) echo '<span style="color: green;">Table '.POSTS_TBL.' successfully created.</span><br />';
            else echo '<span style="color: #FF0000;">'.mysql_error().'</span> You can <a href="mailto:me@desiquintans.com">email the author</a> for help.<br />';

        $welcome = 'You can delete this post. It&#8217;s just here to welcome you to Writer&#8217;s Block, to
        encourage you to explore the program and use its features in interesting and unheard-of ways, and
        to tell you that you can delete this post by going into the appropriate Edit/Delete page in the Admin section.
        <p>
        Please remember that you must have a link to my site at the bottom of every page so that your friends
        can try Writer&#8217;s Block too. Just add
        </p>
        <p>
        <pre>Published by &lt;a href="http://www.desiquintans.com/writersblock">Writer&#8217;s Block</a></pre>
        </p><p>
        somewhere in your footer&#8228;htm template. It&#8217;s there so that more people can discover
        Writer&#8217;s Block and also so that I can Google for the link text and find your site, ask you
        about how you&#8217;re finding the program and if I can improve anything or add new features.
        </p><p>
        You can also use an image to link to my site, but please make the ALT text &#8220;Published by
        Writer&#8217;s Block&#8221; so I can still Google for your site.
        </p>';
        $now = time();
        $makepost="INSERT INTO ".POSTS_TBL." (PostID, Title, Body, Timestamp, PostCat1, PostCat2, PostCat3, PostCat4, Author, Draft)
        VALUES (NULL, 'Welcome to Writer\'s Block', '$welcome', '$now', 0, 0, 0, 0, 1, 0)";
        mysql_query ($makepost) OR die(mysql_error().'. You can <a href="mailto:desiq@bigpond.com">email the author</a> for help.');

        $makePOSTCAT = mysql_query("CREATE TABLE ".POSTCAT_TBL." (
        PCatID INT NOT NULL AUTO_INCREMENT,
        PostCatUrl TINYTEXT NOT NULL,
        PostCatName TINYTEXT NOT NULL,
        PostDesc TEXT NOT NULL,
        Author TINYINT(2) DEFAULT 1,
        PRIMARY KEY (PCatID),
        UNIQUE KEY (PCatID)
        )");
        if ($makePOSTCAT) echo '<span style="color: green;">Table '.POSTCAT_TBL.' successfully created.</span><br />';
            else echo '<span style="color: #FF0000;">'.mysql_error().'</span> You can <a href="mailto:me@desiquintans.com">email the author</a> for help.<br />';

        $makeCOMMENTS = mysql_query("CREATE TABLE ".COMMENTS_TBL." (
        CommID INT NOT NULL AUTO_INCREMENT,
        PostID INT NOT NULL,
        Name VARCHAR(40) NOT NULL,
        Email VARCHAR(40),
        Url TINYTEXT,
        Body TEXT NOT NULL,
        Timestamp INT(20) NOT NULL,
        PRIMARY KEY (CommID),
        UNIQUE KEY (CommID)
        )");
        if ($makeCOMMENTS) echo '<span style="color: green;">Table '.COMMENTS_TBL.' successfully created.</span><br />';
            else echo '<span style="color: #FF0000;">'.mysql_error().'</span> You can <a href="mailto:me@desiquintans.com">email the author</a> for help.<br />';

        $makeANYPAGE = mysql_query("CREATE TABLE ".ANYPAGE_TBL." (
        AnyID INT NOT NULL AUTO_INCREMENT,
        UrlName TINYTEXT NOT NULL,
        Title TINYTEXT NOT NULL,
        Timestamp INT(20) NOT NULL,
        PageDesc TEXT,
        Subheading TEXT,
        PageBody LONGTEXT NOT NULL,
        AnyCat1 TINYINT(3),
        AnyCat2 TINYINT(3),
        AnyCat3 TINYINT(3),
        AnyCat4 TINYINT(3),
        Author TINYINT(2) DEFAULT 1,
        PRIMARY KEY (AnyID),
        UNIQUE KEY (AnyID)
        )");
        if ($makeANYPAGE) echo '<span style="color: green;">Table '.ANYPAGE_TBL.' successfully created.</span><br />';
            else echo '<span style="color: #FF0000;">'.mysql_error().'</span> You can <a href="mailto:me@desiquintans.com">email the author</a> for help.<br />';

        $makeANYCAT = mysql_query("CREATE TABLE ".ANYCAT_TBL." (
        ACatID INT NOT NULL AUTO_INCREMENT,
        AnyCatUrl TINYTEXT NOT NULL,
        AnyCatName TINYTEXT NOT NULL,
        AnyDesc TEXT NOT NULL,
        Author TINYINT(2) DEFAULT 1,
        PRIMARY KEY (ACatID),
        UNIQUE KEY (ACatID)
        )");
        if ($makeANYCAT) echo '<span style="color: green;">Table '.ANYCAT_TBL.' successfully created.</span><br />';
            else echo '<span style="color: #FF0000;">'.mysql_error().'</span> You can <a href="mailto:me@desiquintans.com">email the author</a> for help.<br />';

        $makeMINIBLOG = mysql_query("CREATE TABLE ".MINIBLOG_TBL." (
        MiniBlogID INT NOT NULL AUTO_INCREMENT,
        MiniBlogUrl TINYTEXT NOT NULL,
        MiniBlogTitle TINYTEXT NOT NULL,
        MiniBlogDesc TEXT NOT NULL,
        MiniBlogDate TINYTEXT NOT NULL,
        MiniBlogShow TINYINT(2) NOT NULL DEFAULT 10,
        MiniBlogAuthor TINYINT(2) NOT NULL DEFAULT 1,
        PRIMARY KEY (MiniBlogID),
        UNIQUE KEY (MiniBlogID)
        )");
        if ($makeMINIBLOG) echo '<span style="color: green;">Table '.MINIBLOG_TBL.' successfully created.</span><br />';
            else echo '<span style="color: #FF0000;">'.mysql_error().'</span> You can <a href="mailto:me@desiquintans.com">email the author</a> for help.<br />';

        $makeCONFIG = mysql_query("CREATE TABLE ".CONFIG_TBL." (
        ShowPosts TINYINT(2) NOT NULL DEFAULT 5,
        DefaultTitle TINYTEXT NOT NULL,
        Tagline TINYTEXT NOT NULL,
        SiteUrl TINYTEXT NOT NULL,
        CommentOrder TINYTEXT NOT NULL,
        PostDatePattern TINYTEXT NOT NULL,
        CommDatePattern TINYTEXT NOT NULL,
        AnyDatePattern TINYTEXT NOT NULL,
        ListDatePattern TINYTEXT NOT NULL,
        ShowNew TINYINT(2) NOT NULL,
        Archive TINYINT(3) NOT NULL,
        Articles TINYINT(3) NOT NULL,
        LatestComments TINYINT(2) NOT NULL,
        TZoffset VARCHAR(5) NOT NULL,
        Author TINYTEXT,
        SiteName TINYTEXT,
        SiteDesc TEXT,
        BlogFeedNum TINYINT(2) NOT NULL,
        ThreadLife TINYINT(3) UNSIGNED DEFAULT 120,
        DelimiterList TINYTEXT NOT NULL,
        DelimiterMain TINYTEXT NOT NULL
        )");
        if ($makeCONFIG) echo '<span style="color: green;">Table '.CONFIG_TBL.' successfully created.</span><br />';
            else echo '<span style="color: #FF0000;">'.mysql_error().'</span> You can <a href="mailto:me@desiquintans.com">email the author</a> for help.<br />';

        $setCONFIG="INSERT INTO ".CONFIG_TBL." (ShowPosts, DefaultTitle, Tagline, SiteUrl, CommentOrder, PostDatePattern, CommDatePattern, AnyDatePattern, ShowNew, Archive, Articles, TZoffset,
            LatestComments, BlogFeedNum, DelimiterList, DelimiterMain)
        VALUES ('".$_POST['showposts']."', '".$_POST['defaulttitle']."', '".$_POST['tagline']."', '".$_POST['siteurl']."', 'DESC', 'jS \o\f F, Y', 'd-m-y', 'y-m-d', 3, 32, 10, '$tzoffset', 5, 4, ', ', ', ')";
        mysql_query ($setCONFIG) OR print (mysql_error() . '. ');
        if ($setCONFIG) echo '<span style="color: green;">Table '.CONFIG_TBL.' successfully initialised.</span><br />';
            else echo '<span style="color: #FF0000;">'.mysql_error().'</span> You can <a href="mailto:me@desiquintans.com">email the author</a> for help.<br />';

        die('<em>Writer&#8217;s Block</em> was successfully installed. You can enter the <a href="admin/index.php">Admin section</a>
        and log in with the details you entered.
        <p>It is very important that you <strong>delete install.php</strong> from your server for security reasons.</p>');
    } else print '<strong>You must complete all form fields to install <em>Writer&#8217;s Block</em>.</strong></p>';
}
?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<fieldset>
<legend>Administrator information</legend>
Display name:<br />
<input type="text" name="dispname" size="50" value="<?php if(isset($_POST['dispname'])) echo $_POST['dispname']; ?>" /><br />
User name:<br />
<input type="text" name="user" size="50" value="<?php if(isset($_POST['user'])) echo $_POST['user']; ?>" /><br />
Password:<br />
<input type="password" name="pass1" size="50" /><br />
Verify password:<br />
<input type="password" name="pass2" size="50" /><br />
Email address:<br />
<input type="text" name="email" size="50" value="<?php if(isset($_POST['email'])) echo $_POST['email']; ?>" /><br />
</fieldset>
<fieldset>
<legend>Site information</legend>
URL of Writer&#8217;s Block&#8217;s index.php page:<br />
<input type="text" name="siteurl" size="50" value="<?php if(isset($_POST['siteurl'])) echo $_POST['siteurl']; ?>" /><br />
Title of blog page (applies only to index.php page):<br />
<input type="text" name="defaulttitle" size="50" value="<?php if(isset($_POST['defaulttitle'])) echo $_POST['defaulttitle']; ?>" /><br />
Tagline (appears in every page&#8217;s titlebar):<br />
<input type="text" name="tagline" size="50" value="<?php if(isset($_POST['tagline'])) echo $_POST['tagline']; ?>" /><br />
Number of blog posts to show on index.php:<br />
<input type="text" name="showposts" size="2" maxlength="2" value="<?php if(isset($_POST['showposts'])) echo $_POST['showposts']; ?>" /><br />
Difference to GMT in hours:<br />
<input type="text" name="TZoffset" size="5" maxlength="5" value="<?php echo date('O'); ?>" />
<p>
Make sure you have given control.php the correct mySQL login information before continuing the installation.
</p><p>
<input type="submit" name="submit" value="Install Writer's Block v3.8a" />
</p>
</fieldset>
</form>
</body>
</html>

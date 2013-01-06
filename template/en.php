<?php
# Writer's Block: http://www.desiquintans.com/writersblock
# Writer's Block is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

// English, Anglais, Englese
// This file holds all of the text and code that Writer's Block outputs to a visitor (including
// the comment form and category display for Anypages).
// Escape all single quotes/apostrophes with a backslash (as in \'). The placeholder tags use
// curly braces {} (above the square brackets).

$language = @array(
# Message for undated Anypages. This only shows if the Anypage has a Timestamp of 0 — that is, if it was created with
# versions of Writer's Block older than v3.8a.
'undatedanypage' => 'From long ago',

# Category display. This only shows if the Anypage belongs to a category. {CATEGORIES} is the placeholder for the delimited
# list of categories. For example, {CATEGORIES} can output "Updates, New Things, Rantings".
'anycategory' => '<hr />Filed under {CATEGORIES}',

# Pagination.
'back' => 'Previous',
'next' => 'Next',
'prevpost' => 'Previous post',
'nextpost' => 'Next post',

# Pagination limit, hown when the user is viewing the first/last item or page. Can be empty strings ('').
'backlimit' => '',
'nextlimit' => '',
'prevpostlimit' => 'This is the oldest post',
'nextpostlimit' => 'This is the newest post',

# Page titles.
'archive' => 'Archive',
    // 'miniblogarchive' takes {MINIBLOG_URL} (the URL to the Mini-Blog's page) and {MINIBLOG_TITLE} (the Mini-Blog's title).
'miniblogarchive' => 'Archive for <a href="miniblog.php?blog={MINIBLOG_URL}">{MINIBLOG_TITLE}</a>',
'404' => '404 Page not Found',
'sitemap' => 'Sitemap',
'articles' => 'Articles',

# Categories
'nocategory' => 'no category',
'noblog' => 'There are no blog posts filed in this category.',
'noany' => 'There are no articles filed in this category.',
'noanycat' => 'No categories exist.',

# If the comment thread is closed:
'closedthread' => '<p align="center">This comment thread is closed, so you cannot post a new comment.',

# If a user tries to post to a closed comment thread:
'closedtitle' => 'Thread closed',
'closedmessage' => 'This comment thread is closed, so you cannot post a new comment.',

# If there are no comments yet:
'nocomments' => '<p align="center">There are no comments for this post yet. It\'s clearly not a talking point.</p>',

#If a closed thread has no comments:
'emptythread' => '<p align="center">No comments were ever made in this thread.</p>',

# If Spamguard Extra catches spam URLs in a comment:
'spamtitle' => 'Comments disabled',
'spammessage' => 'Comments have been disabled permanently due to spambots. The commenting functions will be taken down in due time. Meanwhile, apologies for the inconvenience.',

# If a comment is clean and successfully published:
'cleantitle' => 'Comment published',
    // 'cleanmessage' takes {COMMENT_LINK} (the comment's permalink).
'cleanmessage' => 'Your comment has been submitted. You can <a href="{COMMENT_LINK}">view your comment</a>, if you would like.',

# Normal comment form. This grabs the cookied information of the commenter, if any.
    // The form's action must be {SELF}. Takes {POST_ID} (the ID of the post), {COOKIE_NAME} (the user's name as
    // stored in a cookie), {COOKIE_EMAIL} (the user's email as ... ) and {COOKIE_URL} (the user's url as ... ).
'commentform' => '
    <form method="post" action="{SELF}" />
    <input type="hidden" name="PostID" value="{POST_ID}" />
    Name (required):<br />
    <input type="text" name="name" size="30" value="{COOKIE_NAME}" /><br />
    Email (will not be made public):<br />
    <input type="text" name="email" size="30" value="{COOKIE_EMAIL}" /><br />
    URL:<br />
    <input type="text" name="url" size="30" value="{COOKIE_URL}" /><br />
    Comment (required):<br />
    <textarea cols="30" rows="5" name="comment"></textarea>
    <br />
    HTML allowed: &lt;i>, &lt;b>, &lt;a>, &lt;u>, &lt;br>
    <br /><br />
    <input type="checkbox" name="remember" id="rememberme" /><label for="rememberme">Remember me</label>
    <br /><br />
    <input type="submit" name="submit" value="Say this" />
    </form>
    ',

# Half-filled comment form. This form displays the information already entered before submission.
'badcommenttitle' => 'Required fields not filled.',
    // The form's action must be {SELF}. Takes {POST_ID} (the ID of the post), {NAME} (the user's name),
    // {EMAIL} (the user's email), {URL} (the user's url) and {COMMENT} (the user's comment so far).
'badcommentform' => '
    To make a comment you must at least fill in the Name and Comment fields.
    <form method="post" action="{SELF}" />
    <input type="hidden" name="PostID" value="{POST_ID}" />
    Name (required):<br />
    <input type="text" name="name" size="30" value="{NAME}" /><br />
    Email (will not be made public):<br />
    <input type="text" name="email" size="30" value="{EMAIL}" /><br />
    URL:<br />
    <input type="text" name="url" size="30" value="{URL}" /><br />
    Comment (required):<br />
    <textarea cols="30" rows="5" name="comment">{COMMENT}</textarea>
    <br />
    HTML allowed: &lt;i>, &lt;b>, &lt;a>, &lt;u>, &lt;br>
    <br /><br />
    <input type="checkbox" name="remember" checked="checked" id="rememberme" /><label for="rememberme">Remember me</label>
    <br /><br />
    <input type="submit" name="submit" value="Say this" />
    </form>
    ',

## The following entries are the code for links ouput in the listings of articles.php and archive.php.

# Each item in the main blog's archive.  Takes {CATEGORIES}, {DATE}, {POST_ID}, {SUMMARY} and {TITLE}.
'bloglink' => '<p>{DATE} &#8212; <a href="permalink.php?PostID={POST_ID}">{TITLE}</a> ({CATEGORIES})<br />{SUMMARY}</p>',

# Each item in a Post category's directory. Takes {CATEGORIES}, {DATE}, {POST_ID}, {SUMMARY} and {TITLE}.
'postcatdirlink' => '{DATE} &#8212; <a href="permalink.php?PostID={POST_ID}">{TITLE}</a><br />',

# Each item in a Mini-Blog's archive. Takes {DATE}, {MINI_ID}, {SUMMARY}, {TITLE} and {URLSTRING}.
'minibloglink' => '{DATE} &#8212; <a href="miniblog.php?blog={URLSTRING}&amp;entry={MINI_ID}">{TITLE}</a><br />',

# Each item in Newest Articles. Takes {CATEGORIES}, {DATE}, {DESCRIPTION}, {TITLE} and {URLSTRING}.
'newestarticleslink' => '<dt><a href="articles.php?page={URLSTRING}">{TITLE}</a> ({CATEGORIES})<br />{DATE}</dt><dd>{DESCRIPTION}</dd>',

# How the list of Anypage categories is displayed on the default articles page (the page with Newest Articles).
# Takes {DESCRIPTION}, {TITLE} and {URLSTRING}.
'anycategorylisting' => '<br /><strong><a href="articles.php?cat={URLSTRING}">{TITLE}</a></strong><br />{DESCRIPTION}',

# Each item in an Anypage category's directory. Takes {CATEGORIES}, {DATE}, {DESCRIPTION}, {TITLE} and {URLSTRING}.
'anycatdirlink' => '<dt><a href="articles.php?page={URLSTRING}">{TITLE}</a> ({CATEGORIES})<br />{DATE}</dt><dd>{DESCRIPTION}</dd>'
);
?>

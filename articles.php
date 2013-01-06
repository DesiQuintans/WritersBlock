<?php
# Writer's Block v3.8a: http://www.desiquintans.com/writersblock
# Writer's Block is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('admin/control.php');
require ('template/'.LANGUAGE_FILE);

if(isset($_GET['page'])) {
## Check if the Anypage exists
    $check = strpos($_GET['page'], '+');
    $check .= strpos($_GET['page'], '%20');
    if(!$check) {
        $showpage = mysql_query("SELECT Title, Subheading, PageDesc, PageBody, AnyCat1, AnyCat2, AnyCat3, AnyCat4, Author, Timestamp FROM ".ANYPAGE_TBL."
            WHERE UrlName='".$_GET['page']."'");
    }
    if(mysql_num_rows($showpage) == 0 or $check) {
        $pagetitle =& $language['404'];
        include ('template/header.htm');
        include ('template/404.htm');
        include ('template/footer.htm');
        exit();
    }
## If script didn't EXIT(), show the Anypage 
    $row = mysql_fetch_array($showpage);
    $pagetitle =& $row['Title'];
    $get_author = mysql_query("SELECT DispName, Bio FROM ".USERS_TBL." WHERE UserID='".$row['Author']."'");
    $contributor = mysql_fetch_array($get_author);
    
    $get_cat_names = mysql_query("SELECT ACatID, AnyCatUrl, AnyCatName FROM ".ANYCAT_TBL);
    while($get_cat_names_result = mysql_fetch_array($get_cat_names)) {
        $cname[$get_cat_names_result['ACatID']] = $get_cat_names_result['AnyCatName'];
        $curl[$get_cat_names_result['ACatID']] = $get_cat_names_result['AnyCatUrl'];
    }
    
    $category = NULL;
    for ($i = 1; $i <= 4; ++$i) {
        if(empty($row['AnyCat'.$i])) {
            $category .= '';
        } else {
            $category .= '<a href="articles.php?cat='.$curl[$row['AnyCat'.$i]].'">'.$cname[$row['AnyCat'.$i]].'</a>'.$delimitermain_is;
        }
    }
    $categories = substr($category, 0, -strlen($delimitermain_is));
    if(empty($category)) {
        $bio = '';
        $author = '';
    } else {
        $bio =& $contributor['Bio'];
        $author =& $contributor['DispName'];
        $category = str_replace('{CATEGORIES}', $categories, $language['anycategory']);
    }
    $sitedesc_is = $row['PageDesc'];
    
    include ('template/header.htm');
    
    if($row['Timestamp'] == 0) {
        // Anypage was created before WB v3.8a.
        $anydate =& $language['undatedanypage'];
    } else {
        // Anypage is properly dated.
        $anydate = date($anydatepattern_is, $row['Timestamp']);
    }
    
    $use_template = array('{TITLE}' => $pagetitle, '{SUBHEADING}' => $row['Subheading'], '{DESCRIPTION}' => $row['PageDesc'],
        '{BODY}' => $row['PageBody'], '{CATEGORY}' => $category, '{AUTHOR}' => $author, '{BIO}' => $bio, '{DATE}' => $anydate);
    if ($file = file_get_contents('template/anypage.htm')) {
        foreach($use_template as $key => $value) {
            $file = @str_replace($key, $value, $file);
        }
    echo $file;
    }
    
    include ('template/footer.htm');
    exit();
} elseif(isset($_GET['cat'])) {
## Check if category exists
    $check = strpos($_GET['cat'], '+');
    $check .= strpos($_GET['cat'], '%20');
    if(!$check) {
        $getinfo = mysql_query("SELECT ACatID, AnyCatName, AnyDesc FROM ".ANYCAT_TBL." WHERE AnyCatUrl='".$_GET['cat']."'");
    }
    if (mysql_num_rows($getinfo) == 0 or $check) {
        $pagetitle =& $language['404'];
        include ('template/header.htm');
        include ('template/404.htm');
        include ('template/footer.htm');
        exit();
    }
## If script didn't EXIT(), show the Category Directory
    $anycat_data = mysql_fetch_array($getinfo);
    $pagetitle =& $anycat_data['AnyCatName'];
    include ('template/header.htm');
    
    if(!isset($_GET['pg'])) $page = 1; else $page =& $_GET['pg'];
    if(!is_numeric($page)) $page = 1;
    $offset = ($page*$articles_is)-$articles_is;
    $getrows = mysql_query("SELECT AnyID FROM ".ANYPAGE_TBL." WHERE (AnyCat1='".$anycat_data['ACatID']."') OR
        (AnyCat2='".$anycat_data['ACatID']."') OR (AnyCat3='".$anycat_data['ACatID']."') OR (AnyCat4='".$anycat_data['ACatID']."')");
    $pagecount = ceil(mysql_num_rows($getrows)/$articles_is);
    
    $paginate = NULL;
    if($page>1) $paginate .= '<a href="articles.php?cat='.$_GET['cat'].'&amp;pg='.($page-1).'">'.$language['back'].'</a> | ';
        else $paginate .= $language['backlimit'];
    for($pg = 1; $pg <= $pagecount; ++$pg) {
    if ($pg == $page) $paginate .= '<strong>'.$pg.'</strong> ';
        else $paginate .= '<a href="articles.php?cat='.$_GET['cat'].'&amp;pg='.$pg.'">'.$pg.'</a> ';
    }
    if($page<$pagecount) $paginate .= '| <a href="articles.php?cat='.$_GET['cat'].'&amp;pg='.($page+1).'">'.$language['next'].'</a>';
        else $paginate .= $language['nextlimit'];
    
    $get_cat_names = mysql_query("SELECT ACatID, AnyCatUrl, AnyCatName FROM ".ANYCAT_TBL);
    while($get_cat_names_result = mysql_fetch_array($get_cat_names)) {
        $cname[$get_cat_names_result['ACatID']] = $get_cat_names_result['AnyCatName'];
        $curl[$get_cat_names_result['ACatID']] = $get_cat_names_result['AnyCatUrl'];
    }
    
    $getcat = mysql_query("SELECT * FROM ".ANYPAGE_TBL." WHERE (AnyCat1='".$anycat_data['ACatID']."') OR (AnyCat2='".$anycat_data['ACatID']."') OR
        (AnyCat3='".$anycat_data['ACatID']."') OR (AnyCat4='".$anycat_data['ACatID']."') ORDER BY AnyID DESC LIMIT $offset,$articles_is");
    if(mysql_num_rows($getcat) == 0) {
        $links =& $language['noany'];
    } else {
        $links = NULL;
        while($row = mysql_fetch_array($getcat)) {
            $category = NULL;
            for ($i = 1; $i <= 4; ++$i) {
                if(empty($row['AnyCat'.$i])) {
                    $category .= '';
                } elseif($curl[$row['AnyCat'.$i]] == $_GET['cat']) {
                    $category .= $cname[$row['AnyCat'.$i]].$delimiterlist_is;
                } else {
                    $category .= '<a href="articles.php?cat='.$curl[$row['AnyCat'.$i]].'">'.$cname[$row['AnyCat'.$i]].'</a>'.$delimiterlist_is;
                }
            }
            $category = substr($category, 0, -strlen($delimiterlist_is));
            if($row['Timestamp'] == 0) {
                // Anypage was created before WB v3.8a.
                $anydate =& $language['undatedanypage'];
            } else {
                // Anypage is properly dated.
                $anydate = date($listdatepattern_is, $row['Timestamp']);
            }
            $anycatdirlink = $language['anycatdirlink'];
            $use_template = array('{URLSTRING}' => $row['UrlName'], '{TITLE}' => $row['Title'], '{DESCRIPTION}' => $row['PageDesc'], '{DATE}' => $anydate,
            '{CATEGORIES}' => $category);
            foreach($use_template as $key => $value) {
                $anycatdirlink = @str_replace($key, $value, $anycatdirlink);
            }
            $links .= $anycatdirlink;
        }
    }
    
    $use_template = array('{TITLE}' => $pagetitle, '{DESCRIPTION}' => $anycat_data['AnyDesc'], '{PAGINATE}' => $paginate,
        '{LINKS}' => $links);
    if ($file = file_get_contents('template/catdir.htm')) {
        foreach($use_template as $key => $value) {
            $file = @str_replace($key,$value,$file);
        }
    echo $file;
    }
    include ('template/footer.htm');
    exit();
} else {
## Generic page
    $pagetitle =& $language['articles'];
    include ('template/header.htm');

    $get_cat_names = mysql_query("SELECT ACatID, AnyCatUrl, AnyCatName, AnyDesc FROM ".ANYCAT_TBL);
    while($get_cat_names_result = mysql_fetch_array($get_cat_names)) {
        $cname[$get_cat_names_result['ACatID']] = $get_cat_names_result['AnyCatName'];
        $curl[$get_cat_names_result['ACatID']] = $get_cat_names_result['AnyCatUrl'];
        $cdesc[$get_cat_names_result['ACatID']] = $get_cat_names_result['AnyDesc'];
    }

    $newest = NULL;
    $getnew = mysql_query("SELECT Title, PageDesc, UrlName, AnyCat1, AnyCat2, AnyCat3, AnyCat4, Timestamp FROM ".ANYPAGE_TBL." WHERE
        AnyCat1!='' ORDER BY AnyID DESC LIMIT $shownew_is");
    while ($row = mysql_fetch_array($getnew)) {
        $category = NULL;
        for ($i = 1; $i <= 4; ++$i) {
            if(empty($row['AnyCat'.$i])) {
                $category .= '';
            } else {
                $category .= '<a href="articles.php?cat='.$curl[$row['AnyCat'.$i]].'">'.$cname[$row['AnyCat'.$i]].'</a>'.$delimiterlist_is;
            }
        }
        $category = substr($category, 0, -strlen($delimiterlist_is));
        
        if($row['Timestamp'] == 0) {
            // Anypage was created before WB v3.8a.
            $anydate =& $language['undatedanypage'];
        } else {
            // Anypage is properly dated.
            $anydate = date($listdatepattern_is, $row['Timestamp']);
        }
        
        $anypagelink = $language['newestarticleslink'];
        $use_template = array('{URLSTRING}' => $row['UrlName'],'{TITLE}' => $row['Title'],'{CATEGORIES}' => $category,'{DESCRIPTION}' => $row['PageDesc'], '{DATE}' => $anydate);
        foreach($use_template as $key => $value) {
            $anypagelink = @str_replace($key, $value, $anypagelink);
        }
        $newest .= $anypagelink;
    }
    
    $categories = NULL;
    if($curl) {
        foreach($curl as $catid => $caturl) {
            $anycategorylisting = $language['anycategorylisting'];
            $use_template = array('{URLSTRING}' => $caturl,'{TITLE}' => $cname[$catid],'{DESCRIPTION}' => $cdesc[$catid]);
            foreach($use_template as $key => $value) {
                $anycategorylisting = @str_replace($key, $value, $anycategorylisting);
            }
            $categories .= $anycategorylisting;
        }
    } else {
        $categories =& $language['noanycat'];
    }
    
    $use_template = array('{TITLE}' => $pagetitle,'{NEWEST_ARTICLES}' => $newest,'{CATEGORIES}' => $categories);
    if ($file = file_get_contents('template/articles.htm')) {
        foreach($use_template as $key => $value) {
            $file=@str_replace($key,$value,$file);
        }
    echo $file;
    }
    
    include ('template/footer.htm');
    exit();
}
?>

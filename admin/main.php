<?php
/*-----------引入檔案區--------------*/
$isAdmin                      = true;
$xoopsOption['template_main'] = 'tad_sitemap_adm_main.tpl';
include_once "header.php";
include_once "../function.php";

/*-----------功能函數區--------------*/

//自動取得tad_sitemap的最新排序
function tad_sitemap_max_sort()
{
    global $xoopsDB;
    $sql        = "SELECT max(`sort`) FROM `" . $xoopsDB->prefix("tad_sitemap") . "`";
    $result     = $xoopsDB->query($sql) or web_error($sql, __FILE__, __LINE__);
    list($sort) = $xoopsDB->fetchRow($result);
    return ++$sort;
}

//以流水號取得某筆tad_sitemap資料
function get_tad_sitemap($mid_name = "")
{
    global $xoopsDB;
    if (empty($mid_name)) {
        return;
    }

    $sql    = "select * from `" . $xoopsDB->prefix("tad_sitemap") . "` where `mid_name` = '{$mid_name}'";
    $result = $xoopsDB->query($sql) or web_error($sql, __FILE__, __LINE__);
    $data   = $xoopsDB->fetchArray($result);
    return $data;
}

//新增資料到tad_sitemap中
function insert_tad_sitemap()
{
    global $xoopsDB, $xoopsUser, $isAdmin;
    if (!$isAdmin) {
        redirect_header($_SERVER['PHP_SELF'], 3, _TAD_PERMISSION_DENIED);
    }

    //XOOPS表單安全檢查
    if (!$GLOBALS['xoopsSecurity']->check()) {
        $error = implode("<br />", $GLOBALS['xoopsSecurity']->getErrors());
        redirect_header($_SERVER['PHP_SELF'], 3, $error);
    }

    $myts                 = MyTextSanitizer::getInstance();
    $_POST['name']        = $myts->addSlashes($_POST['name']);
    $_POST['url']         = $myts->addSlashes($_POST['url']);
    $_POST['description'] = $myts->addSlashes($_POST['description']);

    $sql = "insert into `" . $xoopsDB->prefix("tad_sitemap") . "`
  (`mid` , `name` , `url` , `description` , `last_update` , `sort`)
  values('{$_POST['mid']}' , '{$_POST['name']}' , '{$_POST['url']}' , '{$_POST['description']}' , '" . date("Y-m-d H:i:s", xoops_getUserTimestamp(time())) . "' , '{$_POST['sort']}')";
    $xoopsDB->query($sql) or web_error($sql, __FILE__, __LINE__);

    //取得最後新增資料的流水編號
    $mid_name = $xoopsDB->getInsertId();

    return $mid_name;

}

//更新tad_sitemap某一筆資料
function update_tad_sitemap($mid_name = "")
{
    global $xoopsDB, $xoopsUser, $isAdmin;
    if (!$isAdmin) {
        redirect_header($_SERVER['PHP_SELF'], 3, _TAD_PERMISSION_DENIED);
    }

    $myts = MyTextSanitizer::getInstance();
    foreach ($_POST['name'] as $mid => $item) {
        foreach ($item as $sort => $name) {
            $name        = $myts->addSlashes($name);
            $description = $myts->addSlashes($_POST['description'][$mid][$sort]);

            $sql = "update `" . $xoopsDB->prefix("tad_sitemap") . "` set
       `name` = '{$name}' ,
       `description` = '{$description}' ,
       `last_update` = '" . date("Y-m-d H:i:s", xoops_getUserTimestamp(time())) . "'
      where `mid` = '$mid' and `sort` = '$sort'";
            $xoopsDB->queryF($sql) or web_error($sql, __FILE__, __LINE__);
        }
    }

}

//刪除tad_sitemap某筆資料資料
function delete_tad_sitemap($mid_name = "")
{
    global $xoopsDB, $isAdmin;
    if (!$isAdmin) {
        redirect_header($_SERVER['PHP_SELF'], 3, _TAD_PERMISSION_DENIED);
    }

    if (empty($mid_name)) {
        return;
    }

    $sql = "delete from `" . $xoopsDB->prefix("tad_sitemap") . "` where `mid_name` = '{$mid_name}'";
    $xoopsDB->queryF($sql) or web_error($sql, __FILE__, __LINE__);

}

//列出所有tad_sitemap資料
function list_tad_sitemap()
{
    global $xoopsDB, $xoopsTpl, $isAdmin;

    $myts = MyTextSanitizer::getInstance();

    $sql    = "SELECT * FROM " . $xoopsDB->prefix("modules") . " WHERE isactive='1' AND hasmain='1' AND weight!='0' ORDER BY weight,last_update";
    $result = $xoopsDB->query($sql) or web_error($sql, __FILE__, __LINE__);

    $all_content = [];
    $i           = 0;
    while ($all = $xoopsDB->fetchArray($result)) {

        $sql2    = "select * from " . $xoopsDB->prefix("tad_sitemap") . " where mid='{$all['mid']}' order by `sort`";
        $result2 = $xoopsDB->query($sql2) or web_error($sql, __FILE__, __LINE__);

        $j    = 0;
        $item = [];
        while ($all2 = $xoopsDB->fetchArray($result2)) {
            foreach ($all2 as $k => $v) {
                $$k = $v;
            }

            //過濾讀出的變數值
            $name        = $myts->htmlSpecialChars($name);
            $url         = $myts->htmlSpecialChars($url);
            $description = $myts->htmlSpecialChars($description);

            $item[$j]['mod_name']    = $mod_name;
            $item[$j]['mid']         = $mid;
            $item[$j]['name']        = $name;
            $item[$j]['url']         = $url;
            $item[$j]['description'] = $description;
            $item[$j]['last_update'] = $last_update;
            $item[$j]['sort']        = $sort;
            $j++;
        }
        $all['item'] = $item;

        $all_content[$i] = $all;
        $i++;
    }

    //刪除確認的JS

    $xoopsTpl->assign('bar', $bar);
    $xoopsTpl->assign('action', $_SERVER['PHP_SELF']);
    $xoopsTpl->assign('isAdmin', $isAdmin);
    $xoopsTpl->assign('all_content', $all_content);
    $xoopsTpl->assign('now_op', 'list_tad_sitemap');

    if (!file_exists(XOOPS_ROOT_PATH . "/modules/tadtools/sweet_alert.php")) {
        redirect_header("index.php", 3, _MA_NEED_TADTOOLS);
    }
    include_once XOOPS_ROOT_PATH . "/modules/tadtools/sweet_alert.php";
    $sweet_alert             = new sweet_alert();
    $delete_tad_sitemap_func = $sweet_alert->render('delete_tad_sitemap_func', "{$_SERVER['PHP_SELF']}?op=delete_tad_sitemap&mid_name=", "mid_name");
    $xoopsTpl->assign('delete_tad_sitemap_func', $delete_tad_sitemap_func);

    $xoopsTpl->assign('tad_sitemap_jquery_ui', get_jquery(true));
}

//更新排序
function update_tad_sitemap_sort()
{
    global $xoopsDB;
    $sort = 1;
    foreach ($_POST['tr'] as $mid_name) {
        $sql = "update " . $xoopsDB->prefix("tad_sitemap") . " set `sort`='{$sort}' where `mid_name`='{$mid_name}'";
        $xoopsDB->queryF($sql) or die(_TAD_SORT_FAIL . " (" . date("Y-m-d H:i:s") . ")");
        $sort++;
    }
    return _TAD_SORTED . " (" . date("Y-m-d H:i:s") . ")";
}

//網站地圖
function auto_sitemap()
{
    global $xoopsDB, $xoopsTpl;
    $sql    = "SELECT * FROM " . $xoopsDB->prefix("modules") . " WHERE isactive='1' AND hasmain='1' AND weight!='0' ORDER BY weight,last_update";
    $result = $xoopsDB->query($sql) or web_error($sql, __FILE__, __LINE__);

    while ($all = $xoopsDB->fetchArray($result)) {
        $i = get_submenu($all['dirname'], $all['mid']);
        get_tadmenu($all['dirname'], $all['mid'], $i, $all['name']);
    }
}

//
function get_submenu($dirname = "", $mid = "")
{
    global $xoopsDB;

    $myts        = MyTextSanitizer::getInstance();
    $modhandler  = xoops_getHandler('module');
    $xoopsModule = $modhandler->getByDirname($dirname);
    //$mod_id=$xoopsModule->getVar('mid');
    $interface_menu = $xoopsModule->subLink();
    $now            = date("Y-m-d H:i:s");
    $i              = 1;
    foreach ($interface_menu as $i => $menu) {
        $name = $menu['name'];
        $url  = $menu['url'];

        $name = $myts->addSlashes($name);
        $url  = $myts->addSlashes($url);

        $sql = "replace into `" . $xoopsDB->prefix("tad_sitemap") . "`  (`mid`, `name` , `url` , `description` , `last_update` , `sort`)  values('{$mid}' , '{$name}' , '{$url}' , '' , '{$now}', '{$i}')";
        $xoopsDB->queryF($sql) or web_error($sql, __FILE__, __LINE__);
        $i++;
    }
    return $i;
}

function get_tadmenu($dirname = "", $mid = "", $i = 0, $mod_name = "")
{
    global $xoopsDB, $xoopsModule, $xoopsUser;
    $filename = XOOPS_ROOT_PATH . "/uploads/menu_{$dirname}.txt";

    $myts = MyTextSanitizer::getInstance();
    if (!file_exists($filename)) {
        file(XOOPS_URL . "/modules/{$dirname}/index.php");
    }

    if (file_exists($filename)) {
        $now            = date("Y-m-d H:i:s");
        $json           = file_get_contents($filename);
        $interface_menu = json_decode($json, true);
        foreach ($interface_menu as $name => $url) {
            if ($name == _TAD_TO_MOD) {
                $name = $mod_name . _MA_TADSITEMA_HOMEPAGE;
            }
            $name = $myts->addSlashes($name);
            $url  = $myts->addSlashes($url);
            $sql  = "replace into `" . $xoopsDB->prefix("tad_sitemap") . "`  (`mid`, `name` , `url` , `description` , `last_update` , `sort`)  values('{$mid}' , '{$name}' , '{$url}' , '' , '{$now}', '{$i}')";
            $xoopsDB->queryF($sql) or web_error($sql, __FILE__, __LINE__);
            $i++;
        }
    }
}

/*-----------執行動作判斷區----------*/
$op      = empty($_REQUEST['op']) ? "" : $_REQUEST['op'];
$midname = empty($_REQUEST['midname']) ? "" : (int) $_REQUEST['midname'];

switch ($op) {
    /*---判斷動作請貼在下方---*/

    //替換資料
    case "auto_sitemap":
        auto_sitemap();
        header("location: {$_SERVER['PHP_SELF']}");
        exit;
        break;

    //新增資料
    case "insert_tad_sitemap":
        $mid_name = insert_tad_sitemap();
        header("location: {$_SERVER['PHP_SELF']}");
        exit;
        break;

    //更新資料
    case "update_tad_sitemap":
        update_tad_sitemap($mid_name);
        header("location: {$_SERVER['PHP_SELF']}");
        exit;
        break;

    case "delete_tad_sitemap":
        delete_tad_sitemap($mid_name);
        header("location: {$_SERVER['PHP_SELF']}");
        exit;
        break;

    //更新排序
    case "update_tad_sitemap_sort":
        $msg = update_tad_sitemap_sort();
        die($msg);
        break;

    default:
        list_tad_sitemap();
        break;

        /*---判斷動作請貼在上方---*/
}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign("isAdmin", true);
$xoTheme->addStylesheet(XOOPS_URL . '/modules/tadtools/css/xoops_adm.css');
include_once 'footer.php';

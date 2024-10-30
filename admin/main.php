<?php
use Xmf\Request;
use XoopsModules\Tadtools\SweetAlert;
use XoopsModules\Tadtools\Utility;
/*-----------引入檔案區--------------*/
$xoopsOption['template_main'] = 'tad_sitemap_admin.tpl';
require_once __DIR__ . '/header.php';

/*-----------執行動作判斷區----------*/
$op = Request::getString('op');

switch ($op) {

    //替換資料
    case 'auto_sitemap':
        auto_sitemap();
        header("location: {$_SERVER['PHP_SELF']}");
        exit;

    //新增資料
    case 'insert_tad_sitemap':
        $mid_name = insert_tad_sitemap();
        header("location: {$_SERVER['PHP_SELF']}");
        exit;

    //更新資料
    case 'update_tad_sitemap':
        update_tad_sitemap($mid_name);
        header("location: {$_SERVER['PHP_SELF']}");
        exit;

    case 'delete_tad_sitemap':
        delete_tad_sitemap($mid_name);
        header("location: {$_SERVER['PHP_SELF']}");
        exit;

    //更新排序
    case 'update_tad_sitemap_sort':
        $msg = update_tad_sitemap_sort();
        die($msg);

    default:
        list_tad_sitemap();
        $op = 'list_tad_sitemap';
        break;

}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign('now_op', $op);
require_once __DIR__ . '/footer.php';

/*-----------功能函數區--------------*/

//自動取得tad_sitemap的最新排序
function tad_sitemap_max_sort()
{
    global $xoopsDB;
    $sql = 'SELECT MAX(`sort`) FROM `' . $xoopsDB->prefix('tad_sitemap') . '`';
    $result = Utility::query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

    list($sort) = $xoopsDB->fetchRow($result);

    return ++$sort;
}

//以流水號取得某筆tad_sitemap資料
function get_tad_sitemap($mid_name = '')
{
    global $xoopsDB;
    if (empty($mid_name)) {
        return;
    }

    $sql = 'SELECT * FROM `' . $xoopsDB->prefix('tad_sitemap') . '` WHERE `mid_name` =?';
    $result = Utility::query($sql, 's', [$mid_name]) or Utility::web_error($sql, __FILE__, __LINE__);

    $data = $xoopsDB->fetchArray($result);

    return $data;
}

//新增資料到tad_sitemap中
function insert_tad_sitemap()
{
    global $xoopsDB, $xoopsUser;
    if (!$_SESSION['tad_sitemap_adm']) {
        redirect_header($_SERVER['PHP_SELF'], 3, _TAD_PERMISSION_DENIED);
    }

    //XOOPS表單安全檢查
    if ($_SERVER['SERVER_ADDR'] != '127.0.0.1' && !$GLOBALS['xoopsSecurity']->check()) {
        $error = implode('<br>', $GLOBALS['xoopsSecurity']->getErrors());
        redirect_header($_SERVER['PHP_SELF'], 3, $error);
    }

    $sql = 'INSERT INTO `' . $xoopsDB->prefix('tad_sitemap') . '` (`mid`, `name`, `url`, `description`, `last_update`, `sort`)
    VALUES (?, ?, ?, ?, ?, ?)';
    Utility::query($sql, 'issssi', [$_POST['mid'], $_POST['name'], $_POST['url'], $_POST['description'], date('Y-m-d H:i:s', xoops_getUserTimestamp(time())), $_POST['sort']]) or Utility::web_error($sql, __FILE__, __LINE__);

    //取得最後新增資料的流水編號
    $mid_name = $xoopsDB->getInsertId();

    return $mid_name;
}

//更新tad_sitemap某一筆資料
function update_tad_sitemap($mid_name = '')
{
    global $xoopsDB;
    if (!$_SESSION['tad_sitemap_adm']) {
        redirect_header($_SERVER['PHP_SELF'], 3, _TAD_PERMISSION_DENIED);
    }

    foreach ($_POST['name'] as $mid => $item) {
        foreach ($item as $sort => $name) {
            $description = $_POST['description'][$mid][$sort];

            $sql = 'UPDATE `' . $xoopsDB->prefix('tad_sitemap') . '` SET
            `name` = ?,
            `description` = ?,
            `last_update` = ?
            WHERE `mid` = ? AND `sort` = ?';
            Utility::query($sql, 'sssii', [$name, $description, date('Y-m-d H:i:s', xoops_getUserTimestamp(time())), $mid, $sort]) or Utility::web_error($sql, __FILE__, __LINE__);

        }
    }
}

//刪除tad_sitemap某筆資料資料
function delete_tad_sitemap($mid_name = '')
{
    global $xoopsDB;
    if (!$_SESSION['tad_sitemap_adm']) {
        redirect_header($_SERVER['PHP_SELF'], 3, _TAD_PERMISSION_DENIED);
    }

    if (empty($mid_name)) {
        return;
    }

    $sql = 'DELETE FROM `' . $xoopsDB->prefix('tad_sitemap') . '` WHERE `mid_name` = ?';
    Utility::query($sql, 's', [$mid_name]) or Utility::web_error($sql, __FILE__, __LINE__);

}

//列出所有tad_sitemap資料
function list_tad_sitemap()
{
    global $xoopsDB, $xoopsTpl;

    $myts = \MyTextSanitizer::getInstance();

    $sql = 'SELECT * FROM `' . $xoopsDB->prefix('modules') . '` WHERE `isactive`=? AND `hasmain`=? AND `weight`!=? ORDER BY `weight`, `last_update`';
    $result = Utility::query($sql, 'iii', [1, 1, 0]) or Utility::web_error($sql, __FILE__, __LINE__);

    $all_content = [];
    $i = 0;
    while (false !== ($all = $xoopsDB->fetchArray($result))) {
        $sql2 = 'SELECT * FROM `' . $xoopsDB->prefix('tad_sitemap') . '` WHERE `mid`=? ORDER BY `sort`';
        $result2 = Utility::query($sql2, 'i', [$all['mid']]) or Utility::web_error($sql, __FILE__, __LINE__);

        $j = 0;
        $item = [];
        while (false !== ($all2 = $xoopsDB->fetchArray($result2))) {
            foreach ($all2 as $k => $v) {
                $$k = $v;
            }

            //過濾讀出的變數值
            $name = $myts->htmlSpecialChars($name);
            $url = $myts->htmlSpecialChars($url);
            $description = $myts->htmlSpecialChars($description);

            $item[$j]['mod_name'] = $all['name'];
            $item[$j]['mid'] = $mid;
            $item[$j]['name'] = $name;
            $item[$j]['url'] = $url;
            $item[$j]['description'] = $description;
            $item[$j]['last_update'] = $last_update;
            $item[$j]['sort'] = $sort;
            $j++;
        }
        $all['item'] = $item;

        $all_content[$i] = $all;
        $i++;
    }

    $xoopsTpl->assign('action', $_SERVER['PHP_SELF']);
    $xoopsTpl->assign('all_content', $all_content);

    $SweetAlert = new SweetAlert();
    $SweetAlert->render('delete_tad_sitemap_func', "{$_SERVER['PHP_SELF']}?op=delete_tad_sitemap&mid_name=", 'mid_name');

    $xoopsTpl->assign('tad_sitemap_jquery_ui', Utility::get_jquery(true));
}

//更新排序
function update_tad_sitemap_sort()
{
    global $xoopsDB;
    $sort = 1;
    foreach ($_POST['tr'] as $mid_name) {
        $sql = 'UPDATE `' . $xoopsDB->prefix('tad_sitemap') . '` SET `sort`=? WHERE `mid_name`=?';
        Utility::query($sql, 'is', [$sort, $mid_name]) or die(_TAD_SORT_FAIL . ' (' . date('Y-m-d H:i:s') . ')');
        $sort++;
    }

    return _TAD_SORTED . ' (' . date('Y-m-d H:i:s') . ')';
}

//網站地圖
function auto_sitemap()
{
    global $xoopsDB;
    $sql = 'SELECT * FROM `' . $xoopsDB->prefix('modules') . '` WHERE `isactive`=1 AND `hasmain`=1 AND `weight`!=0 ORDER BY `weight`,`last_update`';
    $result = Utility::query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

    while (false !== ($all = $xoopsDB->fetchArray($result))) {
        $i = get_submenu($all['dirname'], $all['mid']);
        get_tad_menu($all['dirname'], $all['mid'], $i);
    }
}

function get_submenu($dirname = '', $mid = '')
{
    global $xoopsDB;

    $moduleHandler = xoops_getHandler('module');
    $xoopsModule = $moduleHandler->getByDirname($dirname);
    //$mod_id=$xoopsModule->getVar('mid');
    $interface_menu = $xoopsModule->subLink();
    $now = date('Y-m-d H:i:s');
    $i = 1;
    foreach ($interface_menu as $i => $menu) {
        $name = $menu['name'];
        $url = $menu['url'];

        $sql = 'REPLACE INTO `' . $xoopsDB->prefix('tad_sitemap') . '` (`mid`, `name`, `url`, `description`, `last_update`, `sort`) VALUES (?, ?, ?, ?, ?, ?)';
        Utility::query($sql, 'issssi', [$mid, $name, $url, '', $now, $i]) or Utility::web_error($sql, __FILE__, __LINE__);

        $i++;
    }

    return $i;
}

function get_tad_menu($dirname = '', $mid = '', $i = 0)
{
    global $xoopsDB, $xoopsConfig;
    $language_file = XOOPS_ROOT_PATH . "/modules/{$dirname}/language/{$xoopsConfig['language']}/main.php";
    $interface_file = XOOPS_ROOT_PATH . "/modules/{$dirname}/interface.php";
    $interface_menu_file = XOOPS_ROOT_PATH . "/modules/{$dirname}/interface_menu.php";

    if (file_exists($interface_file) or file_exists($interface_menu_file)) {
        require_once $language_file;
        if (file_exists($interface_file)) {
            require_once $interface_file;
        } elseif (file_exists($interface_menu_file)) {
            require_once $interface_menu_file;
        }

        $now = date('Y-m-d H:i:s');
        foreach ($interface_menu as $name => $url) {
            $sql = 'REPLACE INTO `' . $xoopsDB->prefix('tad_sitemap') . '` (`mid`, `name`, `url`, `description`, `last_update`, `sort`) VALUES (?, ?, ?, ?, ?, ?)';
            Utility::query($sql, 'issssi', [$mid, $name, $url, '', $now, $i]) or Utility::web_error($sql, __FILE__, __LINE__);

            $i++;
        }
    }
}

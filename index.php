<?php

/*-----------引入檔案區--------------*/
include "header.php";
$xoopsOption['template_main'] = 'tad_sitemap_index.tpl';
include_once XOOPS_ROOT_PATH . "/header.php";

/*-----------功能函數區--------------*/

//列出所有tad_sitemap資料
function list_tad_sitemap()
{
    global $xoopsDB, $xoopsTpl, $isAdmin, $xoopsModuleConfig;

    $myts = MyTextSanitizer::getInstance();

    $sql    = "SELECT * FROM " . $xoopsDB->prefix("modules") . " WHERE isactive='1' AND hasmain='1' AND weight!='0' ORDER BY weight,last_update";
    $result = $xoopsDB->query($sql) or web_error($sql, __FILE__, __LINE__);

    $all_content = array();
    $i           = 0;
    while ($all = $xoopsDB->fetchArray($result)) {

        $sql2    = "select * from " . $xoopsDB->prefix("tad_sitemap") . " where mid='{$all['mid']}' order by `sort`";
        $result2 = $xoopsDB->query($sql2) or web_error($sql, __FILE__, __LINE__);

        $j    = 0;
        $item = array();
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
    $xoopsTpl->assign('action', $_SERVER['PHP_SELF']);
    $xoopsTpl->assign('isAdmin', $isAdmin);
    $xoopsTpl->assign('all_content', $all_content);
    $xoopsTpl->assign('now_op', 'list_tad_sitemap');
    $xoopsTpl->assign('about_site', $xoopsModuleConfig['about_site']);

}

/*-----------執行動作判斷區----------*/
$op      = empty($_REQUEST['op']) ? "" : $_REQUEST['op'];
$midname = empty($_REQUEST['midname']) ? "" : (int) $_REQUEST['midname'];

switch ($op) {
    /*---判斷動作請貼在下方---*/
    default:
        list_tad_sitemap();
        break;
        /*---判斷動作請貼在上方---*/
}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign("toolbar", toolbar_bootstrap($interface_menu));
$xoopsTpl->assign("isAdmin", $isAdmin);
include_once XOOPS_ROOT_PATH . '/footer.php';

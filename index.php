<?php
use Xmf\Request;
use XoopsModules\Tadtools\Utility;
/*-----------引入檔案區--------------*/
$GLOBALS['xoopsOption']['template_main'] = 'tad_sitemap_index.tpl';
require __DIR__ . '/header.php';
require_once XOOPS_ROOT_PATH . '/header.php';

/*-----------執行動作判斷區----------*/
$op = Request::getString('op');

switch ($op) {

    default:
        show_sitemap();
        $op = 'show_sitemap';
        break;

}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign('now_op', $op);
$xoopsTpl->assign('toolbar', Utility::toolbar_bootstrap($interface_menu, false, $interface_icon));
require_once XOOPS_ROOT_PATH . '/footer.php';

/*-----------功能函數區--------------*/

//列出所有tad_sitemap資料
function show_sitemap()
{
    global $xoopsDB, $xoopsTpl, $xoopsModuleConfig;

    $myts = \MyTextSanitizer::getInstance();

    $sql = 'SELECT * FROM `' . $xoopsDB->prefix('modules') . '` WHERE `isactive`=1 AND `hasmain`=1 AND `weight`!=0 ORDER BY `weight`,`last_update`';
    $result = Utility::query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

    $all_content = [];
    $i = 0;
    while (false !== ($all = $xoopsDB->fetchArray($result))) {
        $sql2 = 'SELECT * FROM `' . $xoopsDB->prefix('tad_sitemap') . '` WHERE `mid`=? ORDER BY `sort`';
        $result2 = Utility::query($sql2, 'i', [$all['mid']]) or Utility::web_error($sql2, __FILE__, __LINE__);

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

            $item[$j]['mod_name'] = $mod_name;
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

    //刪除確認的JS
    $xoopsTpl->assign('action', $_SERVER['PHP_SELF']);
    $xoopsTpl->assign('all_content', $all_content);
    $xoopsTpl->assign('about_site', $xoopsModuleConfig['about_site']);
}

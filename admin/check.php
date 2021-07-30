<?php
use Xmf\Request;
use XoopsModules\Tadtools\Utility;

/*-----------引入檔案區--------------*/
$xoopsOption['template_main'] = 'tad_sitemap_adm_main.tpl';
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/function.php';
/*-----------功能函數區--------------*/

//開始檢查無障礙
function start_check($mode = '')
{
    global $xoopsDB, $xoopsTpl;
    $check_items['fontsize'] = 'font-size:';
    $regular['fontsize'] = "/font-size:\s?(\d*)(px|pt)/U";

    $check_items['iframe'] = '<iframe';
    $regular['iframe'] = "/<iframe (.*)><\/iframe>/U";

    $check_items['img'] = '<img';
    $regular['img'] = "/<img (.*)>/U";

    $check_items['blockquote'] = '<blockquote';
    $regular['blockquote'] = "/<blockquote(.*)<\/blockquote>/U";

    // $check_items['colspan'] = 'colspan';
    // $regular['colspan'] = "/colspan/U";

    $data = $need_data = [];

    $sql = 'show tables';
    $result = $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    while (list($table) = $xoopsDB->fetchRow($result)) {
        // 找出所有的表
        if ($table) {
            $sql2 = "SHOW COLUMNS FROM `$table`";
            $result2 = $xoopsDB->queryF($sql2) or Utility::web_error($sql2, __FILE__, __LINE__);
            $col_arr = $sql_arr = $text_col = $pri_col = $idx_col = [];
            // 找出該表中的文字欄位以及主索引
            while ($field = $xoopsDB->fetchArray($result2)) {
                foreach ($check_items as $kind => $chk_item) {
                    if (stripos($field['Type'], "VARCHAR") !== false || stripos($field['Type'], "TEXT") !== false) {
                        $text_col[$kind][] = $field['Field'];
                        $col_arr[$kind][] = "`{$field['Field']}`";
                        $sql_arr[$kind][] = "`{$field['Field']}` like '%{$chk_item}%'";
                    }
                    if ($field['Extra'] == 'auto_increment' || $field['Key'] == 'PRI') {
                        $col_arr[$kind][] = "`{$field['Field']}`";
                        $pri_col[$kind][] = $field['Field'];
                    }
                    if ($field['Key'] == 'MUL') {
                        $col_arr[$kind][] = "`{$field['Field']}`";
                        $idx_col[$kind][] = $field['Field'];
                    }
                }
            }

            if (empty($pri_col)) {
                $pri_col = $idx_col;
            }

            // 開始檢查該表的文字欄位
            foreach ($check_items as $kind => $chk_item) {
                $search_col = !empty($col_arr[$kind]) ? implode(', ', $col_arr[$kind]) : '';
                $search_item = !empty($sql_arr[$kind]) ? implode(' or ', $sql_arr[$kind]) : '';

                if ($search_item and $search_col) {
                    $i = 0;
                    $sql3 = "SELECT $search_col FROM `$table` WHERE $search_item";
                    $result3 = $xoopsDB->queryF($sql3) or Utility::web_error($sql3, __FILE__, __LINE__);
                    while ($all = $xoopsDB->fetchArray($result3)) {
                        $pri = [];
                        foreach ($all as $col_name => $col_val) {

                            if (in_array($col_name, $pri_col[$kind])) {
                                $pri[] = "`$col_name`='{$col_val}'";
                            }

                            // 假如有符合規則的欄位就做檢查
                            if (in_array($col_name, $text_col[$kind]) and strpos($col_val, $chk_item) !== false) {
                                $num = preg_match_all($regular[$kind], $col_val, $matches);
                                if (!empty($num)) {
                                    $fix_result = call_user_func($kind, $col_val, $matches, $table);
                                    if ($fix_result) {
                                        // Utility::dd($fix_result);
                                        // $fix_result['html_v']; //用來顯示原始值的HTML
                                        // $fix_result['fix_v']; //用來顯示修改後的HTML
                                        // $fix_result['save']; //用來顯示修改後的HTML
                                        $data[$table][$kind][$i]['sql'] = $sql3;
                                        $data[$table][$kind][$i]['col'][$col_name] = $fix_result;
                                        // 最後要存入的值
                                        $need_data[$table][$kind][$i]['col'][$col_name]['save'] = $fix_result['save'];
                                    }
                                }
                            }
                        }

                        // 有需要修正的內容，才加入主索引資料
                        if (!empty($need_data[$table][$kind][$i]['col'])) {
                            $data[$table][$kind][$i]['primary'] = $need_data[$table][$kind][$i]['primary'] = implode(' and ', $pri);
                        }
                        $i++;
                    }
                }
            }
        }
    }

    // Utility::dd($need_data);
    if ($mode == 'return') {
        return $need_data;
    } else {
        $xoopsTpl->assign('data', $data);
        $xoopsTpl->assign('all_data', json_encode($need_data, 256));
    }
}

function auto_fix()
{
    global $xoopsDB, $xoopsTpl;
    $data = start_check('return');
    // Utility::dd($data);
    foreach ($data as $table => $kind_cols) {
        foreach ($kind_cols as $kind => $cols) {
            foreach ($cols as $i => $items) {
                foreach ($items['col'] as $col => $item) {
                    $sql = "update `{$table}` set `{$col}`='{$item['save']}' where {$items['primary']}";

                    $save = htmlspecialchars($item['save']);
                    $sql_demo = "update `<span style='color: green;'>{$table}</span>` set `<span style='color: red;'>{$col}</span>`='<span style='color: gray;'>{$save}</span>' where <span style='color: blue;'>{$items['primary']}</span>";

                    if ($xoopsDB->queryF($sql)) {
                        $ok = 'check';
                        $color = 'success';
                        $error = '';
                    } else {
                        $ok = 'times';
                        $color = 'danger';
                        $error = "<div class='bg-danger text-white'>" . $xoopsDB->error() . "</div>";
                    }

                    echo "<p class='text-$color text-monospace'>(<i class='fa fa-$ok'></i>) {$sql_demo}{$error}</p>";
                }
            }
        }
    }
}

function fontsize($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    foreach ($matches[0] as $sk => $s) {
        $html_v = str_replace($s, "<span style='color:red;'>$s</span>", $html_v);
        if ($matches[2][$sk] == 'px') {
            $new_val = round($matches[1][$sk] / 16, 2);
        } elseif ($matches[2][$sk] == 'pt') {
            $new_val = round($matches[1][$sk] / 12, 2);
        }
        $v = str_replace($s, "font-size: {$new_val}em", $v);
        $fix_v = str_replace($s, "<span style='color:blue;'>font-size: {$new_val}em</span>", $fix_v);
    }
    $data['html_v'] = $html_v;
    $data['fix_v'] = $fix_v;
    $data['save'] = $myts->addSlashes($v);
    $data_line = round(strlen($v) / 60, 0);
    $data['line'] = $data_line > 12 ? 12 : $data_line < 6 ? 6 : $data_line;
    return $data;
}

function iframe($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    if (strpos($v, ' title') === false) {
        $v = str_replace('iframe ', "iframe title=\"iframe\" ", $v);
        $html_v = str_replace('&lt;iframe', "&lt;<span style='color:red;'>iframe</span>", $html_v);
        $fix_v = str_replace('&lt;iframe ', "&lt;<span style='color:blue;'>iframe title=\"iframe\"</span> ", $fix_v);
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : $data_line < 6 ? 6 : $data_line;
    }

    return $data;
}

function img($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    if (strpos($v, ' alt') === false) {
        $v = str_replace('img ', "img alt=\"img\" ", $v);
        $html_v = str_replace('&lt;img', "&lt;<span style='color:red;'>img</span>", $html_v);
        $fix_v = str_replace('&lt;img ', "&lt;<span style='color:blue;'>img alt=\"img\"</span> ", $fix_v);
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : $data_line < 6 ? 6 : $data_line;
    }

    return $data;
}

function blockquote($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    if (strpos($v, ' xml:lang') === false) {
        $v = str_replace('<blockquote', "<blockquote xml:lang=\"zh\"", $v);
        $html_v = str_replace('&lt;blockquote', "&lt;<span style='color:red;'>blockquote</span>", $html_v);
        $fix_v = str_replace('&lt;blockquote', "&lt;<span style='color:blue;'>blockquote xml:lang=\"zh\"</span>", $fix_v);
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : $data_line < 6 ? 6 : $data_line;
    }
    return $data;
}

// function colspan($v, $matches, $table)
// {
//     if (strpos($table, '_tplsource') !== false) {
//         return;
//     }
//     $myts = \MyTextSanitizer::getInstance();
//     $html_v = $fix_v = htmlspecialchars($v);
//     $data = [];
//     if (strpos($v, 'colspan') !== false) {
//         $v = str_replace('<th', "<td", $v);
//         $html_v = str_replace('&lt;th', "&lt;<span style='color:red;'>th</span>", $html_v);
//         $fix_v = str_replace('&lt;th', "&lt;<span style='color:blue;'>td</span>", $fix_v);
//         $data['html_v'] = $html_v;
//         $data['fix_v'] = $fix_v;
//         $data['save'] = $myts->addSlashes($v);
//         $data_line = round(strlen($v) / 60, 0);
//         $data['line'] = $data_line > 12 ? 12 : $data_line < 6 ? 6 : $data_line;
//     }
//     return $data;
// }

function check_form()
{
    global $xoopsTpl, $xoopsDB;
    $sql = 'select conf_value from `' . $xoopsDB->prefix('config') . "` where `conf_name` = 'allow_register'";
    $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    list($allow_register) = $xoopsDB->fetchRow($result);
    $xoopsTpl->assign('allow_register', $allow_register);

    $sql = 'select menuid, status, of_level from `' . $xoopsDB->prefix('tad_themes_menu') . "` where `itemurl` like '%/modules/tad_sitemap%'";
    $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    list($menuid, $status, $of_level) = $xoopsDB->fetchRow($result);

    if ($status == 1 and $of_level != 0) {
        $sql = 'select status, of_level from `' . $xoopsDB->prefix('tad_themes_menu') . "` where `menuid`='$of_level'";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        list($status) = $xoopsDB->fetchRow($result);
    }

    $xoopsTpl->assign('menuid', $menuid);
    $xoopsTpl->assign('status', $status);

    $css = file_get_contents(XOOPS_ROOT_PATH . '/xoops.css');
    $num = preg_match_all("/font-size:\s?(\d*)(px|pt)/U", $css, $matches);
    if ($num > 0) {
        $xoopsTpl->assign('css_path', XOOPS_ROOT_PATH . '/xoops.css');
    }
    $xoopsTpl->assign('num', $num);

}

function add2nav()
{
    global $xoopsDB;

    $sql = 'insert into `' . $xoopsDB->prefix('tad_themes_menu') . "` (`of_level`, `position`, `itemname`, `itemurl`, `status`, `target`, `icon`, `link_cate_name`, `link_cate_sn`, `read_group`) VALUES
    (0,	1,	'網站地圖',	'/modules/tad_sitemap/',	'1',	'_blank',	'fa-code-fork',	'',	0,	'1,2,3')";
    $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
}

function enable4nav($menuid)
{
    global $xoopsDB;

    $sql = 'update `' . $xoopsDB->prefix('tad_themes_menu') . "` set `status`= 1 , `of_level`= 0 , `read_group`='1,2,3' where menuid='$menuid'";
    $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
}

/*-----------執行動作判斷區----------*/
$op = Request::getString('op');
$menuid = Request::getInt('menuid');

switch ($op) {
    /*---判斷動作請貼在下方---*/

    case 'start_check':
        start_check();
        break;

    case 'auto_fix':
        auto_fix();
        // header("location: {$_SERVER['PHP_SELF']}");
        break;

    case 'add2nav':
        add2nav();
        header("location: {$_SERVER['PHP_SELF']}");
        exit;

    case 'enable4nav':
        enable4nav($menuid);
        header("location: {$_SERVER['PHP_SELF']}");
        exit;

    default:
        $op = 'check_form';
        check_form();
        break;
        /*---判斷動作請貼在上方---*/
}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign('now_op', $op);
$xoTheme->addStylesheet(XOOPS_URL . '/modules/tadtools/css/xoops_adm4.css');
require_once __DIR__ . '/footer.php';

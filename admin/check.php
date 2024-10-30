<?php
use Xmf\Request;
use XoopsModules\Tadtools\Utility;
use XoopsModules\Tadtools\Wcag;

/*-----------引入檔案區--------------*/
$xoopsOption['template_main'] = 'tad_sitemap_admin.tpl';
require_once __DIR__ . '/header.php';

/*-----------執行動作判斷區----------*/
$op = Request::getString('op');
$menuid = Request::getInt('menuid');
$need_check = Request::getArray('need_check');
$need_check_list = Request::getString('need_check_list');

$check_items = Wcag::getVar('check_items');
$check_title = Wcag::getVar('check_title');
$regular = Wcag::getVar('regular');

// 不用處理的資料表
$pass_tables = ['club_choice', 'groups_users_link', 'jill_booking', 'jill_booking_date', 'jill_booking_week', 'jill_query_col_value', 'jill_query_sn', 'logcounterx_log', 'tad_form_value', 'tad_form_fill', 'tad_uploader_dl_log', 'users', 'group_permission', 'sign_data', 'tad_gallery', 'tad_gphotos_images', 'tad_repair', 'tad_web_power', 'tad_web_video', 'tad_web_tags', 'tad_web_schedule_data'];
$pass_tables2 = ['copy_', 'scs_', '_files_center'];
// $pass_tables = $pass_tables2 = [];

switch ($op) {

    case 'start_check':
        if ($need_check_list) {
            $need_check = explode(';', $need_check_list);
        }
        start_check('', $need_check);
        break;

    case 'auto_fix':
        auto_fix($need_check_list);
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

}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign('now_op', $op);
require_once __DIR__ . '/footer.php';

/*-----------功能函數區--------------*/

//開始檢查無障礙
function start_check($mode = '', $need_check = [])
{
    global $xoopsDB, $xoopsTpl, $pass_tables, $pass_tables2, $check_title, $check_items, $regular;
    $data = $need_data = $pass_tables = [];
    $xoopsTpl->assign('check_title', $check_title);

    if (empty($need_check)) {
        $need_check = array_keys($check_items);
    }

    $sql = 'SHOW TABLES';
    $result = Utility::query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

    $total = 0;
    while (list($table) = $xoopsDB->fetchRow($result)) {
        if (in_array(str_replace(XOOPS_DB_PREFIX, '', $table), $pass_tables)) {
            continue;
        }
        foreach ($pass_tables2 as $passtable) {
            if (strpos($table, $passtable) !== false) {
                continue;
            }
        }

        $sql2 = 'SHOW COLUMNS FROM `' . $table . '`';
        $result2 = Utility::query($sql2) or Utility::web_error($sql2, __FILE__, __LINE__);

        $col_arr = $sql_arr = $text_col = $pri_col = $idx_col = [];
        // 找出該表中的文字欄位以及主索引
        while ($field = $xoopsDB->fetchArray($result2)) {
            foreach ($need_check as $kind) {
                $chk_item_arr = $check_items[$kind];
                if (stripos($field['Type'], "VARCHAR") !== false || stripos($field['Type'], "TEXT") !== false) {
                    $text_col[$kind][] = $field['Field'];
                    $col_arr[$kind][] = "`{$field['Field']}`";
                    foreach ($chk_item_arr as $chk_item) {
                        $sql_arr[$kind][] = "`{$field['Field']}` like '%{$chk_item}%'";
                    }
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
        foreach ($check_items as $kind => $chk_item_arr) {
            $search_col = !empty($col_arr[$kind]) ? implode(', ', $col_arr[$kind]) : '';
            $search_item = !empty($sql_arr[$kind]) ? implode(' or ', $sql_arr[$kind]) : '';

            if ($search_item and $search_col) {
                $i = 0;
                $sql3 = "SELECT $search_col FROM `$table` WHERE $search_item";
                $result3 = $xoopsDB->queryF($sql3) or Utility::web_error($sql3, __FILE__, __LINE__);
                while ($all = $xoopsDB->fetchArray($result3)) {
                    $pri = [];
                    foreach ($all as $col_name => $col_val) {

                        if (is_array($pri_col[$kind]) and in_array($col_name, $pri_col[$kind])) {
                            $pri[] = "`$col_name`='{$col_val}'";
                        }

                        // 假如有符合規則的欄位就做檢查
                        foreach ($chk_item_arr as $chk_item) {
                            if (is_array($text_col[$kind]) and in_array($col_name, $text_col[$kind]) and stripos($col_val, $chk_item) !== false) {
                                foreach ($regular[$kind] as $kind_title => $regular_rule) {
                                    $num = preg_match_all($regular_rule, $col_val, $matches);
                                    if (!empty($num)) {
                                        $fix_result = call_user_func($kind_title, $col_val, $matches, $table);
                                        if ($fix_result) {
                                            // Utility::dd($fix_result);
                                            // $fix_result['html_v']; //用來顯示原始值的HTML
                                            // $fix_result['fix_v']; //用來顯示修改後的HTML
                                            // $fix_result['save']; //用來顯示修改後的HTML
                                            $data[$table][$kind][$i]['sql'] = $sql3;
                                            $data[$table][$kind][$i]['col'][$col_name] = $fix_result;
                                            // 最後要存入的值
                                            $need_data[$table][$kind][$i]['col'][$col_name]['save'] = $fix_result['save'];

                                            $total++;

                                        }
                                    }
                                }
                            }
                        }
                    }

                    // 有需要修正的內容，才加入主索引資料
                    if (!empty($need_data[$table][$kind][$i]['col'])) {
                        $data[$table][$kind][$i]['primary'] = $need_data[$table][$kind][$i]['primary'] = implode(' and ', $pri);
                    }

                    if ($total > 200) {
                        break 2;
                    }
                    $i++;
                }
            }
        }

    }

    // Utility::dd($need_data);
    if ($mode == 'return') {
        return $need_data;
    } else {
        $xoopsTpl->assign('data', $data);
        $xoopsTpl->assign('need_check', $need_check);
        $xoopsTpl->assign('need_check_list', implode(';', $need_check));
        $xoopsTpl->assign('all_data', json_encode($need_data, 256));
    }
}

function auto_fix($need_check_list = '')
{
    global $xoopsDB;
    set_time_limit(0);
    $need_check = explode(';', $need_check_list);
    $data = start_check('return', $need_check);
    echo '<a href="check.php?op=start_check&need_check_list=' . $need_check_list . '" class="btn btn-primary">' . _MA_TADSITEMAP_VIEW_FIX_AGAIN . '</a>';
    foreach ($data as $table => $kind_cols) {
        foreach ($kind_cols as $kind => $cols) {
            foreach ($cols as $i => $items) {
                foreach ($items['col'] as $col => $item) {
                    $sql = "UPDATE `{$table}` SET `{$col}`=? WHERE {$items['primary']}";

                    $save = htmlspecialchars($item['save']);
                    $sql_demo = "update `<span style='color: green;'>{$table}</span>` set `<span style='color: red;'>{$col}</span>`='<span style='color: gray;'>{$save}</span>' where <span style='color: blue;'>{$items['primary']}</span>";

                    if (Utility::query($sql, 's', [$item['save']])) {
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
        if ($matches[2][$sk] == 'pt') {
            $new_val = round($matches[1][$sk] / 12, 2);
        } elseif ($matches[2][$sk] == 'px') {
            $new_val = round($matches[1][$sk] / 16, 2);
        } elseif ($matches[2][$sk] == '') {
            if (strpos($matches[1][$sk], '+') !== false) {
                $num = $matches[1][$sk];
                $new_val = 1 + 0.2 * $num;
            } elseif (strpos($matches[1][$sk], '-') !== false) {
                $num = $matches[1][$sk];
                $new_val = 1 - 0.2 * $num;
            } else {
                $num = $matches[1][$sk] - 3;
                $new_val = 1 + 0.2 * $num;
            }
        }
        $v = str_replace($s, "font-size: {$new_val}rem;", $v);
        $fix_v = str_replace($s, "<span style='color:blue;'>font-size: {$new_val}rem;</span>", $fix_v);
    }
    $data['html_v'] = $html_v;
    $data['fix_v'] = $fix_v;
    $data['save'] = $myts->addSlashes($v);
    $data_line = round(strlen($v) / 60, 0);
    $data['line'] = $data_line > 12 ? 12 : 6;
    return $data;
}

function font_size_adjust($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    foreach ($matches[0] as $old_str) {
        $v = str_replace($old_str, "", $v);
        $html_v = str_replace($old_str, "<span style='color:red;'>$old_str</span>", $html_v);
        $fix_v = str_replace($old_str, "<span style='color:blue;text-decoration: line-through;'>{$old_str}</span>", $fix_v);
    }
    $data['html_v'] = $html_v;
    $data['fix_v'] = $fix_v;
    $data['save'] = $myts->addSlashes($v);
    $data_line = round(strlen($v) / 60, 0);
    $data['line'] = $data_line > 12 ? 12 : 6;
    return $data;
}

function size($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    foreach ($matches[0] as $sk => $s) {
        $html_v = str_replace($s, "<span style='color:red;'>$s</span>", $html_v);
        if ($matches[2][$sk] == 'pt') {
            $new_val = round($matches[1][$sk] / 12, 2);
        } elseif ($matches[2][$sk] == 'px') {
            $new_val = round($matches[1][$sk] / 16, 2);
        }
        $v = str_replace($s, "font: {$new_val}em", $v);
        $fix_v = str_replace($s, "<span style='color:blue;'>font: {$new_val}em</span>", $fix_v);
    }
    $data['html_v'] = $html_v;
    $data['fix_v'] = $fix_v;
    $data['save'] = $myts->addSlashes($v);
    $data_line = round(strlen($v) / 60, 0);
    $data['line'] = $data_line > 12 ? 12 : 6;
    return $data;
}

function iframe($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];

    $fix = false;
    // Utility::dd($matches);
    foreach ($matches[0] as $old_str) {
        if (stripos($old_str, ' title=') === false) {
            // $old_str = str_replace("\r\n", " ", $old_str);
            $new_str = str_replace('<iframe ', "<iframe title=iframe ", $old_str);
            $v = str_replace($old_str, $new_str, $v);
            $old_str_tag = htmlspecialchars($old_str);
            $new_str_tag = htmlspecialchars($new_str);
            $html_v = str_replace($old_str_tag, "<span style='color:red;'>$old_str_tag</span>", $html_v);
            $fix_v = str_replace($old_str_tag, "<span style='color:blue;'>$new_str_tag</span>", $fix_v);
            $fix = true;
        }
    }

    if ($fix) {
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : 6;
    }

    return $data;
}

function object($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    if (stripos($v, ' title') === false) {
        $v = str_replace('<object ', "<object title=object ", $v);
        $v = str_replace('</object>', "<span class='sr-only visually-hidden'>some object</span></object>", $v);

        $html_v = str_replace('&lt;object', "&lt;<span style='color:red;'>object</span>", $html_v);
        $fix_v = str_replace('&lt;object ', "&lt;<span style='color:blue;'>object title=object</span> ", $fix_v);

        $html_v = str_replace('/object', "<span style='color:red;'>/object</span>", $html_v);
        $fix_v = str_replace('/object', "<span style='color:blue;'>span class=sr-only visually-hidden&gt;some object&lt;/span&gt;&lt;/object</span>", $fix_v);
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : 6;
    }

    return $data;
}

function applet($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    if (stripos($v, ' title') === false) {
        $v = str_replace('<applet ', "<applet title=applet ", $v);
        $v = str_replace('</applet>', "<span class='sr-only visually-hidden'>some applet</span></applet>", $v);

        $html_v = str_replace('&lt;applet', "&lt;<span style='color:red;'>applet</span>", $html_v);
        $fix_v = str_replace('&lt;applet ', "&lt;<span style='color:blue;'>applet title=applet</span> ", $fix_v);

        $html_v = str_replace('/applet', "<span style='color:red;'>/applet</span>", $html_v);
        $fix_v = str_replace('/applet', "<span style='color:blue;'>span class=sr-only visually-hidden&gt;some applet&lt;/span&gt;&lt;/applet</span>", $fix_v);
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : 6;
    }

    return $data;
}

function embed($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    $fix = false;
    if (stripos($v, ' title') === false) {
        $v = str_replace('<embed ', "<embed title=embed ", $v);
        $v = str_replace('</embed>', "<span class='sr-only visually-hidden'>some embed</span></embed>", $v);

        $html_v = str_replace('&lt;embed', "&lt;<span style='color:red;'>embed</span>", $html_v);
        $fix_v = str_replace('&lt;embed ', "&lt;<span style='color:blue;'>embed title=embed</span> ", $fix_v);

        $html_v = str_replace('/embed', "<span style='color:red;'>&lt;/embed&gt;</span>", $html_v);
        $fix_v = str_replace('/embed', "<span style='color:blue;'>&lt;span class=sr-only visually-hidden&gt;some embed&lt;/span&gt;&lt;/embed&gt;</span>", $fix_v);
        $fix = true;
    }

    if (stripos($v, 'noembed') === false) {
        $v = str_ireplace('</embed>', "<noembed>No way to embed content</noembed></embed>", $v);
        $html_v = str_replace('&lt;/embed&gt;', "<span style='color:red;'>&lt;/embed&gt;</span>", $html_v);
        $fix_v = str_replace('&lt;/embed&gt;', "<span style='color:blue;'>&lt;noembed&gt;No way to embed content&lt;/noembed&gt;&lt;/embed&gt;</span>", $fix_v);
        $fix = true;
    }

    if ($fix) {
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : 6;
    }
    return $data;
}

function input($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];

    $fix = false;
    foreach ($matches[0] as $old_str) {
        if (stripos($old_str, ' title =') === false and stripos($old_str, ' title=') === false) {
            $new_str = str_ireplace('<input', "<input title=input", $old_str);
            $v = str_ireplace($old_str, $new_str, $v);
            $old_str_tag = htmlspecialchars($old_str);
            $new_str_tag = htmlspecialchars($new_str);
            $html_v = str_ireplace($old_str_tag, "<span style='color:red;'>$old_str_tag</span>", $html_v);
            $fix_v = str_ireplace($old_str_tag, "<span style='color:blue;'>$new_str_tag</span>", $fix_v);
            $fix = true;
        }
    }

    if ($fix) {
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : 6;
    }

    return $data;
}

function select($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];

    $fix = false;
    foreach ($matches[0] as $old_str) {
        if (stripos($old_str, ' title =') === false and stripos($old_str, ' title=') === false) {
            $new_str = str_replace('<select', "<select title=select", $old_str);
            $v = str_replace($old_str, $new_str, $v);
            $old_str_tag = htmlspecialchars($old_str);
            $new_str_tag = htmlspecialchars($new_str);
            $html_v = str_replace($old_str_tag, "<span style='color:red;'>$old_str_tag</span>", $html_v);
            $fix_v = str_replace($old_str_tag, "<span style='color:blue;'>$new_str_tag</span>", $fix_v);
            $fix = true;
        }
    }

    if ($fix) {
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : 6;
    }

    return $data;
}

function textarea($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];

    $fix = false;
    foreach ($matches[0] as $old_str) {
        if (stripos($old_str, ' title =') === false and stripos($old_str, ' title=') === false) {
            $new_str = str_replace('<textarea ', "<textarea title=textarea ", $old_str);
            $v = str_replace($old_str, $new_str, $v);
            $old_str_tag = htmlspecialchars($old_str);
            $new_str_tag = htmlspecialchars($new_str);
            $html_v = str_replace($old_str_tag, "<span style='color:red;'>$old_str_tag</span>", $html_v);
            $fix_v = str_replace($old_str_tag, "<span style='color:blue;'>$new_str_tag</span>", $fix_v);
            $fix = true;
        }
    }

    if ($fix) {
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : 6;
    }

    return $data;
}

function img($v, $matches, $table)
{
    if (stripos($table, 'tplsource') !== false) {
        return;
    }
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];

    $fix = false;

    foreach ($matches[0] as $old_img) {
        if (stripos($old_img, 'alt=') === false) {
            $old_img = str_replace("\r\n", " ", $old_img);
            $new_img = str_replace('<img ', "<img alt=img ", $old_img);
            // $new_img = str_replace("<img\r\n", "<img alt=img\r\n", $new_img);
            $v = str_replace($old_img, $new_img, $v);
            $old_img_tag = htmlspecialchars($old_img);
            $new_img_tag = htmlspecialchars($new_img);
            $html_v = str_replace($old_img_tag, "<span style='color:red;'>$old_img_tag</span>", $html_v);
            $fix_v = str_replace($old_img_tag, "<span style='color:blue;'>$new_img_tag</span>", $fix_v);
            $fix = true;
        }
    }

    if ($fix) {
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : 6;
    }
    return $data;
}

function blockquote($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    if (stripos($v, ' xml:lang') === false) {
        $v = str_replace('<blockquote', "<blockquote xml:lang=zh", $v);
        $html_v = str_replace('&lt;blockquote', "&lt;<span style='color:red;'>blockquote</span>", $html_v);
        $fix_v = str_replace('&lt;blockquote', "&lt;<span style='color:blue;'>blockquote xml:lang=zh</span>", $fix_v);
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : 6;
    }
    return $data;
}

function empty_font($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];

    foreach ($matches[0] as $empty_str) {
        $v = str_replace($empty_str, '', $v);
        $empty_str_html = htmlspecialchars($empty_str);
        $html_v = str_replace($empty_str_html, "<span style='color:red;'>$empty_str_html</span>", $html_v);
        $fix_v = str_replace($empty_str_html, "<span style='color:gray; text-decoration: line-through '>$empty_str_html</span>", $fix_v);
    }
    $data['html_v'] = $html_v;
    $data['fix_v'] = $fix_v;
    $data['save'] = $myts->addSlashes($v);
    $data_line = round(strlen($v) / 60, 0);
    $data['line'] = $data_line > 12 ? 12 : 6;
    return $data;
}

function font($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];

    foreach ($matches[0] as $old_str) {
        $q = (stripos($old_str, '"') !== false) ? '"' : "'";
        $new_str = str_replace("'", '"', $old_str);
        $style = [];

        if ($new_str == '<font>') {
            $new_str = str_replace("<font>", "<span>", $new_str);
        } elseif (stripos($new_str, '<font ') !== false) {
            $re = '/face=[\"|\'](.*)[\"|\']/Uims';
            preg_match($re, $new_str, $face);
            if ($face[1]) {
                $style[] = "font-family: {$face[1]};";
            }

            $re = '/color=[\"|\'](.*)[\"|\']/Uims';
            preg_match($re, $new_str, $color);
            if ($color[1]) {
                $style[] = "color: {$color[1]};";
            }

            $re = '/size=[\"|\'](.*)[\"|\']/Uims';
            preg_match($re, $new_str, $size);
            if (strpos($size[1], '+') !== false) {
                $num = substr($size[1], 1);
                $new_size = 1 + 0.2 * $num;
            } elseif (strpos($size[1], '-') !== false) {
                $num = substr($size[1], 1);
                $new_size = 1 - 0.2 * $num;
            } else {
                $num = $size[1] - 3;
                $new_size = 1 + 0.2 * $num;
            }
            if ($size[1]) {
                $style[] = "font-size: {$new_size}rem;";
            }

            $new_str = strtolower("<span style={$q}" . implode(' ', $style) . "{$q}>");

        } else {
            $new_str = str_replace("</font>", "</span>", $new_str);
        }

        // if (stripos($new_str, '學生的情緒教育') !== false) {
        //     die($new_str);
        // }

        $v = str_replace($old_str, $new_str, $v);
        $old_str_html = htmlspecialchars($old_str);
        $new_str_html = htmlspecialchars($new_str);
        $html_v = str_replace($old_str_html, "<span style='color:red;'>$old_str_html</span>", $html_v);
        $fix_v = str_replace($old_str_html, "<span style='color:blue;'>$new_str_html</span>", $fix_v);
    }
    $data['html_v'] = $html_v;
    $data['fix_v'] = $fix_v;
    $data['save'] = $myts->addSlashes($v);
    $data_line = round(strlen($v) / 60, 0);
    $data['line'] = $data_line > 12 ? 12 : 6;
    return $data;
}

// function font_no_end($v, $matches, $table)
// {
//     $myts = \MyTextSanitizer::getInstance();
//     $html_v = $fix_v = htmlspecialchars($v);
//     $data = [];
//     if (stripos($v, '<font') !== false) {
//         $v = str_replace('<font', "<span", $v);
//         $html_v = str_replace('&lt;font', "&lt;<span style='color:red;'>font</span>", $html_v);
//         $html_v = str_replace('&lt;/font', "&lt;<span style='color:red;'>/font</span>", $html_v);
//         $fix_v = str_replace('&lt;font', "&lt;<span style='color:blue;'>span</span>", $fix_v);
//         $fix_v = str_replace('&lt;/font', "&lt;<span style='color:blue;'>/span</span>", $fix_v);
//         $data['html_v'] = $html_v;
//         $data['fix_v'] = $fix_v;
//         $data['save'] = $myts->addSlashes($v);
//         $data_line = round(strlen($v) / 60, 0);
//         $data['line'] = $data_line > 12 ? 12 : 6;
//     }
//     return $data;
// }

function center($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    if (stripos($v, '<center') !== false) {
        $v = str_replace('<center>', "<div style=\"text-align: center;\">", $v);
        $v = str_replace('</center>', "</div>", $v);
        $html_v = str_replace('&lt;center', "&lt;<span style='color:red;'>center</span>", $html_v);
        $html_v = str_replace('&lt;/center', "&lt;<span style='color:red;'>/center</span>", $html_v);
        $fix_v = str_replace('&lt;center', "&lt;<span style='color:blue;'>div style=\"text-align: center;\"</span>", $fix_v);
        $fix_v = str_replace('&lt;/center', "&lt;<span style='color:blue;'>/div</span>", $fix_v);
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : 6;
    }
    return $data;
}

function big($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    if (stripos($v, '<big') !== false) {
        $v = str_replace('<big>', "<span style=\"font-size: 1.2rem;\">", $v);
        $v = str_replace('</big>', "</span>", $v);
        $html_v = str_replace('&lt;big', "&lt;<span style='color:red;'>big</span>", $html_v);
        $html_v = str_replace('&lt;/big', "&lt;<span style='color:red;'>/big</span>", $html_v);
        $fix_v = str_replace('&lt;big', "&lt;<span style='color:blue;'>span style=\"font-size: 1.2rem;\"</span>", $fix_v);
        $fix_v = str_replace('&lt;/big', "&lt;<span style='color:blue;'>/span</span>", $fix_v);
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : 6;
    }
    return $data;
}

function th($v, $matches, $table)
{
    if (stripos($table, 'tplsource') !== false) {
        return;
    }
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    if (stripos($v, '<th scope') === false && (stripos($v, '<th>') !== false || stripos($v, '<th ') !== false)) {
        $v = str_replace('<th>', "<td>", $v);
        $v = str_replace('<th ', "<td ", $v);
        $v = str_replace('</th>', "</td>", $v);
        $html_v = str_replace('&lt;th&gt;', "&lt;<span style='color:red;'>th</span>&gt;", $html_v);
        $html_v = str_replace('&lt;th ', "&lt;<span style='color:red;'>th </span>", $html_v);
        $html_v = str_replace('&lt;/th', "&lt;<span style='color:red;'>/th</span>", $html_v);
        $fix_v = str_replace('&lt;th&gt;', "&lt;<span style='color:blue;'>td</span>&gt;", $fix_v);
        $fix_v = str_replace('&lt;th ', "&lt;<span style='color:blue;'>td </span>", $fix_v);
        $fix_v = str_replace('&lt;/th', "&lt;<span style='color:blue;'>/td</span>", $fix_v);
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : 6;
    }
    return $data;
}

function a_blank($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    // Utility::dd($matches);
    $fix = false;
    foreach ($matches[2] as $key => $content_in_tag) {
        if (stripos($content_in_tag, '<{$') === false and stripos($matches[1][$key], 'href') !== false and empty(trim(strip_tags($content_in_tag)))) {
            $old = $matches[0][$key];
            $linkto = str_ireplace(['href=', '"', "'", 'target=', '_blank', '_self'], '', $matches[1][$key]);
            $new = str_ireplace('</a>', "<span class=sr-only visually-hidden>link to $linkto</span></a>", $old);
            $v = str_ireplace($old, $new, $v);
            $old_tag = htmlspecialchars($old);
            $new_tag = htmlspecialchars($new);
            $html_v = str_ireplace($old_tag, "<span style='color:red;'>$old_tag</span>", $html_v);
            $fix_v = str_ireplace($old_tag, "<span style='color:blue;'>$new_tag</span>", $fix_v);
            $fix = true;
        }
    }

    if ($fix) {
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : 6;
    }
    return $data;
}

function same_alt($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    $fix = false;
    foreach ($matches[1] as $key => $content_in_tag) {
        preg_match_all("/(.*)<img.*alt=[\"|\'](.*?)[\"|\'].*?[\"|\']?>(.*)/is", $content_in_tag, $match);
        $alt = $txt1 = $txt2 = '';
        if (isset($match[2][0]) and $match[2][0]) {
            $alt = strip_tags(str_replace('&nbsp;', '', $match[2][0]));
        }
        if (isset($match[1][0]) and $match[1][0]) {
            $txt1 = strip_tags(str_replace('&nbsp;', '', $match[1][0]));
        }
        if (isset($match[3][0]) and $match[3][0]) {
            $txt2 = strip_tags(str_replace('&nbsp;', '', $match[3][0]));
        }

        if (!empty($match[2][0]) and stripos($match[2][0], '<{$') === false and ($alt == $txt1 or $alt == $txt2)) {
            $old = $matches[0][$key];
            // $replaceTo = str_replace(['alt=', '"', "'", 'target=', '_blank', '_self'], '', $matches[1][$key]);
            $new = str_replace(["alt='{$match[2][0]}'", 'alt="' . $match[2][0] . '"'], ["alt='image of {$alt}'", 'alt="image of ' . $alt . '"'], $old);
            $v = str_replace($old, $new, $v);
            $old_tag = htmlspecialchars($old);
            $new_tag = htmlspecialchars($new);
            $html_v = str_replace($old_tag, "<span style='color:red;'>$old_tag</span>", $html_v);
            $fix_v = str_replace($old_tag, "<span style='color:blue;'>$new_tag</span>", $fix_v);
            $fix = true;
        }
    }

    if ($fix) {
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : 6;
    }
    return $data;
}

function head_blank($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    // Utility::dd($matches);
    $fix = false;
    foreach ($matches[1] as $key => $content_in_tag) {
        if (stripos($content_in_tag, '<{$') === false and empty(trim(strip_tags($content_in_tag)))) {
            $old = $matches[0][$key];
            $new = str_replace('</h', "<span class=sr-only visually-hidden>empty head</span></h", $old);
            $v = str_replace($old, $new, $v);
            $old_tag = htmlspecialchars($old);
            $new_tag = htmlspecialchars($new);
            $html_v = str_replace($old_tag, "<span style='color:red;'>$old_tag</span>", $html_v);
            $fix_v = str_replace($old_tag, "<span style='color:blue;'>$new_tag</span>", $fix_v);
            $fix = true;
        }
    }

    if ($fix) {
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : 6;
    }
    return $data;
}

function lang_zh_tw($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    // Utility::dd($matches);
    $fix = false;
    foreach ($matches[0] as $key => $content_in_tag) {
        $old = $content_in_tag;
        $new = '';
        $v = str_replace($old, $new, $v);
        $old_tag = htmlspecialchars($old);
        $new_tag = htmlspecialchars($new);
        $html_v = str_replace($old_tag, "<span style='color:red;'>$old_tag</span>", $html_v);
        $fix_v = str_replace($old_tag, "<span style='color:blue;'>$new_tag</span>", $fix_v);
        $fix = true;

    }

    if ($fix) {
        $data['html_v'] = $html_v;
        $data['fix_v'] = $fix_v;
        $data['save'] = $myts->addSlashes($v);
        $data_line = round(strlen($v) / 60, 0);
        $data['line'] = $data_line > 12 ? 12 : 6;
    }
    return $data;
}

function check_form()
{
    global $xoopsTpl, $xoopsDB, $check_title;
    $sql = 'SELECT `conf_value` FROM `' . $xoopsDB->prefix('config') . '` WHERE `conf_name` =?';
    $result = Utility::query($sql, 's', ['allow_register']) or Utility::web_error($sql, __FILE__, __LINE__);

    list($allow_register) = $xoopsDB->fetchRow($result);
    $xoopsTpl->assign('allow_register', $allow_register);

    $sql = 'SELECT `menuid`, `status`, `of_level` FROM `' . $xoopsDB->prefix('tad_themes_menu') . '` WHERE `itemurl` LIKE "%/modules/tad_sitemap%"';
    $result = Utility::query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

    list($menuid, $status, $of_level) = $xoopsDB->fetchRow($result);

    if ($status == 1 and $of_level != 0) {
        $sql = 'SELECT `status`, `of_level` FROM `' . $xoopsDB->prefix('tad_themes_menu') . '` WHERE `menuid` =?';
        $result = Utility::query($sql, 'i', [$of_level]) or Utility::web_error($sql, __FILE__, __LINE__);

        list($status) = $xoopsDB->fetchRow($result);
    }

    $xoopsTpl->assign('menuid', $menuid);
    $xoopsTpl->assign('status', $status);
    $xoopsTpl->assign('check_title', $check_title);

    // $xoopsTpl->assign('need_check_list', implode(';', $need_check));

    // 修正佈景字型
    $theme_font_size_msg = '';
    $sql = 'SELECT `theme_id`, `theme_name`, `font_size` FROM `' . $xoopsDB->prefix('tad_themes') . '`';
    $result = Utility::query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

    while (list($theme_id, $theme_name, $font_size) = $xoopsDB->fetchRow($result)) {
        $new_val = (int) $font_size;
        if (stripos($font_size, 'pt') !== false) {
            $new_val = round($new_val / 12, 2);
            $sql = 'UPDATE `' . $xoopsDB->prefix('tad_themes') . '` SET `font_size`=? WHERE `theme_id`=?';
            Utility::query($sql, 'si', [$new_val, $theme_id]) or Utility::web_error($sql, __FILE__, __LINE__);

            $theme_font_size_msg .= '<li>' . sprintf(_MA_TADSITEMAP_FIX_THEME_FS, $theme_name, $font_size, "{$new_val}rem") . '</li>';
        } elseif (stripos($font_size, 'px') !== false) {
            $new_val = round($new_val / 16, 2);
            $sql = 'UPDATE `' . $xoopsDB->prefix('tad_themes') . '` SET `font_size`=? WHERE `theme_id`=?';
            Utility::query($sql, 'si', [$new_val . 'rem', $theme_id]) or Utility::web_error($sql, __FILE__, __LINE__);

            $xoopsTpl->assign('status', $status);
            $theme_font_size_msg .= '<li>' . sprintf(_MA_TADSITEMAP_FIX_THEME_FS, $theme_name, $font_size, "{$new_val}rem") . '</li>';
        }
    }
    $xoopsTpl->assign('theme_font_size_msg', $theme_font_size_msg);

    // 關閉評論
    $comment_msg = '';
    $sql = 'SELECT a.`conf_id`, b.`dirname`
    FROM `' . $xoopsDB->prefix('config') . '` AS a
    JOIN `' . $xoopsDB->prefix('modules') . '` AS b ON a.`conf_modid` = b.`mid`
    WHERE a.`conf_name` = ? AND a.`conf_value` = ?';
    $result = Utility::query($sql, 'ss', ['com_rule', '1']) or Utility::web_error($sql, __FILE__, __LINE__);

    while (list($conf_id, $dirname) = $xoopsDB->fetchRow($result)) {
        $sql = 'UPDATE `' . $xoopsDB->prefix('config') . '` SET `conf_value`= ? WHERE `conf_id`=?';
        Utility::query($sql, 'si', ['0', $conf_id]) or Utility::web_error($sql, __FILE__, __LINE__);

        $comment_msg .= '<li>' . sprintf(_MA_TADSITEMAP_COMMENT_CLOSED, $dirname) . '</li>';
    }
    $xoopsTpl->assign('comment_msg', $comment_msg);

    // 關閉facebook評論
    $facebook_msg = '';
    $sql = 'SELECT a.`conf_id`, b.`dirname`
    FROM `' . $xoopsDB->prefix('config') . '` AS a
    JOIN `' . $xoopsDB->prefix('modules') . '` AS b ON a.`conf_modid` = b.`mid`
    WHERE a.`conf_name` = ? AND a.`conf_value` = ?';
    $result = Utility::query($sql, 'ss', ['facebook_comments_width', '1']) or Utility::web_error($sql, __FILE__, __LINE__);

    while (list($conf_id, $dirname) = $xoopsDB->fetchRow($result)) {
        $sql = 'UPDATE `' . $xoopsDB->prefix('config') . '` SET `conf_value`=? WHERE `conf_id`=?';
        Utility::query($sql, 'si', ['0', $conf_id]) or Utility::web_error($sql, __FILE__, __LINE__);

        $comment_msg .= '<li>' . sprintf(_MA_TADSITEMAP_FACEBOOK_CLOSED, $dirname) . '</li>';
    }
    $xoopsTpl->assign('facebook_msg', $facebook_msg);

    // 移除功課表樣板的 id 及 headers
    $replace_arr = [' id="Tim"', ' id="Sct"', ' id="Mon"', ' id="Tue"', ' id="Wed"', ' id="Thu"', ' id="Fri"', ' headers="Tim"', ' headers="Sct"', ' headers="Mon"', ' headers="Tue"', ' headers="Wed"', ' headers="Thu"', ' headers="Fri"'];
    $schedule_msg = '';
    $clear_block_cache = false;
    $sql = 'SELECT `conf_id`, `conf_value` FROM `' . $xoopsDB->prefix('config') . '` WHERE `conf_name` = ?';
    $result = Utility::query($sql, 's', ['schedule_template']) or Utility::web_error($sql, __FILE__, __LINE__);

    while (list($conf_id, $conf_value) = $xoopsDB->fetchRow($result)) {
        $new_conf_value = str_replace($replace_arr, '', $conf_value);
        $sql = 'UPDATE `' . $xoopsDB->prefix('config') . '` SET `conf_value`=? WHERE `conf_id`=?';
        Utility::query($sql, 'si', [$new_conf_value, $conf_id]) or Utility::web_error($sql, __FILE__, __LINE__);

        $schedule_msg .= '<li>' . _MA_TADSITEMAP_TAD_WEB_SCHEDULE_FIX . '</li>';
        $clear_block_cache = true;
    }
    $xoopsTpl->assign('comment_msg', $comment_msg);
    // 重新多人網頁產生畫面
    if ($clear_block_cache) {
        // 關閉 tad_web facebook評論
        $tad_web_facebook_msg = '';
        $sql = 'SELECT a.`WebID`, a.`plugin`, b.`WebName`
        FROM `' . $xoopsDB->prefix('tad_web_plugins_setup') . '` as a
        JOIN `' . $xoopsDB->prefix('tad_web') . '` as b ON a.`WebID` = b.`WebID`
        WHERE a.`name` = ? AND a.`value` = ?';
        $result = Utility::query($sql, 'ss', ['use_fb_comments', '1']) or Utility::web_error($sql, __FILE__, __LINE__);

        while (list($WebID, $plugin, $WebName) = $xoopsDB->fetchRow($result)) {
            $sql = 'UPDATE `' . $xoopsDB->prefix('tad_web_plugins_setup') . '` SET `value`=0 WHERE `WebID`=? AND `plugin`=? AND `name`=?';
            Utility::query($sql, 'iss', [$WebID, $plugin, 'use_fb_comments']) or Utility::web_error($sql, __FILE__, __LINE__);

            $comment_msg .= '<li>' . sprintf(_MA_TADSITEMAP_TAD_WEB_FB_CLOSED, $WebName, $plugin) . '</li>';
        }
        $xoopsTpl->assign('tad_web_facebook_msg', $tad_web_facebook_msg);

        $sql = 'SELECT `WebID` FROM `' . $xoopsDB->prefix('tad_web') . '` ';
        $result = Utility::query($sql) or Utility::web_error($sql, __FILE__, __LINE__);

        while (list($WebID) = $xoopsDB->fetchRow($result)) {
            if (file_exists(XOOPS_VAR_PATH . "/tad_web/$WebID/web_blocks.json")) {
                unlink(XOOPS_VAR_PATH . "/tad_web/$WebID/web_blocks.json");
            }
        }
    }

    // 檢查 xoops.css
    $css = file_get_contents(XOOPS_ROOT_PATH . '/xoops.css');
    $num = preg_match_all("/font-size:\s?(\d*)(px|pt)/U", $css, $matches);
    if ($num > 0) {
        $xoopsTpl->assign('css_path', XOOPS_ROOT_PATH . '/xoops.css');
    }
    $xoopsTpl->assign('num', $num);

    // 檢查 module.textsanitizer.php
    $textsanitizer = file_get_contents(XOOPS_ROOT_PATH . '/class/module.textsanitizer.php');
    if (strpos($textsanitizer, "linkify") === false) {
        $xoopsTpl->assign('textsanitizer_path', XOOPS_ROOT_PATH . '/class/module.textsanitizer.php');
    } else {
        $xoopsTpl->assign('textsanitizer_path', '');
    }

}

function add2nav()
{
    global $xoopsDB;

    $sql = "INSERT INTO `" . $xoopsDB->prefix('tad_themes_menu') . "` (`of_level`, `position`, `itemname`, `itemurl`, `status`, `target`, `icon`, `link_cate_name`, `link_cate_sn`, `read_group`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $params = [0, 1, '網站地圖', '/modules/tad_sitemap/', 1, '_blank', 'fa-code-fork', '', 0, '1,2,3'];

    $result = Utility::query($sql, 'iissssssis', $params) or Utility::web_error($sql, __FILE__, __LINE__);

}

function enable4nav($menuid)
{
    global $xoopsDB;

    $sql = 'UPDATE `' . $xoopsDB->prefix('tad_themes_menu') . '` SET `status`= ? , `of_level`= 0 , `read_group`=? WHERE `menuid`=?';
    Utility::query($sql, 'ssi', ['1', '1,2,3', $menuid]) or Utility::web_error($sql, __FILE__, __LINE__);

}

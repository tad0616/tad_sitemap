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
    $regular['fontsize'] = "/font-size:\s?(\d*|\d*\.\d*)(px|pt)/Ui";

    // $check_items['text_indent'] = 'text-indent:';
    // $regular['text_indent'] = "/text-indent:\s?(-?\d*|\d*\.\d*)(px|pt)/Ui";

    // $check_items['line_height'] = 'line-height:';
    // $regular['line_height'] = "/line-height:\s?(-?\d*|\d*\.\d*)(px|pt)/Ui";

    $check_items['size'] = 'font:';
    $regular['size'] = "/font:\s?(\d*|\d*\.\d*)(px|pt)/Ui";

    $check_items['iframe'] = '<iframe ';
    $regular['iframe'] = "/(<iframe[^>]*>.*<\/iframe>)/Uims";

    $check_items['object'] = '<object';
    $regular['object'] = "/<object (.*)><\/object>/Ui";

    $check_items['applet'] = '<applet';
    $regular['applet'] = "/<applet (.*)><\/applet>/Ui";

    $check_items['embed'] = '<embed';
    $regular['embed'] = "/<embed (.*)><\/applet>/Ui";

    $check_items['img'] = '<img ';
    $regular['img'] = "/<\s*img\s+(.*)\/?\s?>/i";
    // $regular['img'] = "/<img\b.*?(?:\>|\/>)/Ui";
    // $regular['img'] = "/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/Ui";

    $check_items['blockquote'] = '<blockquote';
    $regular['blockquote'] = "/<blockquote(.*)<\/blockquote>/Ui";

    $check_items['empty_font'] = '<font ';
    $regular['empty_font'] = "/(<font[^>]*><\/font>)/Uim";

    $check_items['font'] = '<font ';
    $regular['font'] = "/(<font[^>]*>)|(<\/font>)/Uim";

    // $check_items['font_no_end'] = '<font';
    // $regular['font_no_end'] = "/<font(.*)/Ui";

    $check_items['center'] = '<center>';
    $regular['center'] = "/<center>/Ui";

    $check_items['th'] = '<th';
    $regular['th'] = "/<th(\s|>)(.*)>/Ui";

    $check_items['a_blank'] = '<a ';
    $regular['a_blank'] = "/<a (.*)>(.*)<\/a>/Ui";

    $check_items['h1_blank'] = '<h1';
    $regular['h1_blank'] = "/<h1(.*)>(.*)<\/h1>/Ui";

    $check_items['h2_blank'] = '<h2';
    $regular['h2_blank'] = "/<h2(.*)>(.*)<\/h2>/Ui";

    $check_items['h3_blank'] = '<h3';
    $regular['h3_blank'] = "/<h3(.*)>(.*)<\/h3>/Ui";

    $check_items['h4_blank'] = '<h4';
    $regular['h4_blank'] = "/<h4(.*)>(.*)<\/h4>/Ui";

    $check_items['h5_blank'] = '<h5';
    $regular['h5_blank'] = "/<h5(.*)>(.*)<\/h5>/Ui";

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
                            if (in_array($col_name, $text_col[$kind]) and stripos($col_val, $chk_item) !== false) {
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
    echo '<a href="check.php?op=start_check" class="btn btn-primary">' . _MA_TADSITEMAP_VIEW_FIX_AGAIN . '</a>';
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
        if ($matches[2][$sk] == 'pt') {
            $new_val = round($matches[1][$sk] / 12, 2);
        } elseif ($matches[2][$sk] == 'px') {
            $new_val = round($matches[1][$sk] / 16, 2);
        }
        $v = str_replace($s, "font-size: {$new_val}em", $v);
        $fix_v = str_replace($s, "<span style='color:blue;'>font-size: {$new_val}em</span>", $fix_v);
    }
    $data['html_v'] = $html_v;
    $data['fix_v'] = $fix_v;
    $data['save'] = $myts->addSlashes($v);
    $data_line = round(strlen($v) / 60, 0);
    $data['line'] = $data_line > 12 ? 12 : 6;
    return $data;
}

// function text_indent($v, $matches, $table)
// {
//     $myts = \MyTextSanitizer::getInstance();
//     $html_v = $fix_v = htmlspecialchars($v);
//     $data = [];
//     foreach ($matches[0] as $sk => $s) {
//         $html_v = str_replace($s, "<span style='color:red;'>$s</span>", $html_v);
//         if ($matches[2][$sk] == 'pt') {
//             $new_val = round($matches[1][$sk] / 12, 2);
//         } elseif ($matches[2][$sk] == 'px') {
//             $new_val = round($matches[1][$sk] / 16, 2);
//         }
//         $v = str_replace($s, "text-indent: {$new_val}em", $v);
//         $fix_v = str_replace($s, "<span style='color:blue;'>text-indent: {$new_val}em</span>", $fix_v);
//     }
//     $data['html_v'] = $html_v;
//     $data['fix_v'] = $fix_v;
//     $data['save'] = $myts->addSlashes($v);
//     $data_line = round(strlen($v) / 60, 0);
//     $data['line'] = $data_line > 12 ? 12 : 6;
//     return $data;
// }

// function line_height($v, $matches, $table)
// {
//     $myts = \MyTextSanitizer::getInstance();
//     $html_v = $fix_v = htmlspecialchars($v);
//     $data = [];
//     foreach ($matches[0] as $sk => $s) {
//         $html_v = str_replace($s, "<span style='color:red;'>$s</span>", $html_v);
//         if ($matches[2][$sk] == 'pt') {
//             $new_val = round($matches[1][$sk] / 12, 2);
//         } elseif ($matches[2][$sk] == 'px') {
//             $new_val = round($matches[1][$sk] / 16, 2);
//         }
//         $v = str_replace($s, "line-height: {$new_val}em", $v);
//         $fix_v = str_replace($s, "<span style='color:blue;'>line-height: {$new_val}em</span>", $fix_v);
//     }
//     $data['html_v'] = $html_v;
//     $data['fix_v'] = $fix_v;
//     $data['save'] = $myts->addSlashes($v);
//     $data_line = round(strlen($v) / 60, 0);
//     $data['line'] = $data_line > 12 ? 12 : 6;
//     return $data;
// }

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
        $v = str_replace('</object>', "<span class='sr-only'>some object</span></object>", $v);

        $html_v = str_replace('&lt;object', "&lt;<span style='color:red;'>object</span>", $html_v);
        $fix_v = str_replace('&lt;object ', "&lt;<span style='color:blue;'>object title=object</span> ", $fix_v);

        $html_v = str_replace('/object', "<span style='color:red;'>/object</span>", $html_v);
        $fix_v = str_replace('/object', "<span style='color:blue;'>span class=sr-only&gt;some object&lt;/span&gt;&lt;/object</span>", $fix_v);
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
        $v = str_replace('</applet>', "<span class='sr-only'>some applet</span></applet>", $v);

        $html_v = str_replace('&lt;applet', "&lt;<span style='color:red;'>applet</span>", $html_v);
        $fix_v = str_replace('&lt;applet ', "&lt;<span style='color:blue;'>applet title=applet</span> ", $fix_v);

        $html_v = str_replace('/applet', "<span style='color:red;'>/applet</span>", $html_v);
        $fix_v = str_replace('/applet', "<span style='color:blue;'>span class=sr-only&gt;some applet&lt;/span&gt;&lt;/applet</span>", $fix_v);
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
    if (stripos($v, ' title') === false) {
        $v = str_replace('<embed ', "<embed title=embed ", $v);
        $v = str_replace('</embed>', "<span class='sr-only'>some embed</span></embed>", $v);

        $html_v = str_replace('&lt;embed', "&lt;<span style='color:red;'>embed</span>", $html_v);
        $fix_v = str_replace('&lt;embed ', "&lt;<span style='color:blue;'>embed title=embed</span> ", $fix_v);

        $html_v = str_replace('/embed', "<span style='color:red;'>/embed</span>", $html_v);
        $fix_v = str_replace('/embed', "<span style='color:blue;'>span class=sr-only&gt;some embed&lt;/span&gt;&lt;/embed</span>", $fix_v);
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
        if (stripos($new_str, '<font ') !== false) {
            $re = '/face=\"(.*)\"/Uims';
            preg_match($re, $new_str, $face);
            if ($face[1]) {
                $style[] = "font-family: {$face[1]};";
            }

            $re = '/color=\"(.*)\"/Uims';
            preg_match($re, $new_str, $color);
            if ($color[1]) {
                $style[] = "color: {$color[1]};";
            }

            $re = '/size=\"(.*)\"/Uims';
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
                $style[] = "font-size: {$size[1]};";
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
            $linkto = str_replace(['href=', '"', "'", 'target=', '_blank', '_self'], '', $matches[1][$key]);
            $new = str_replace('</a>', "<span class=sr-only>link to $linkto</span></a>", $old);
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
    // if ($data['save']) {
    //     Utility::dd($data['save']);
    // }

    return $data;
}

function h1_blank($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    // Utility::dd($matches);
    $fix = false;
    foreach ($matches[2] as $key => $content_in_tag) {
        if (stripos($content_in_tag, '<{$') === false and empty(trim(strip_tags($content_in_tag)))) {
            $old = $matches[0][$key];
            $new = str_replace('</h1>', "<span class=sr-only>empty head3</span></h1>", $old);
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
    // if ($data['save']) {
    //     Utility::dd($data['save']);
    // }

    return $data;
}
function h2_blank($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    // Utility::dd($matches);
    $fix = false;
    foreach ($matches[2] as $key => $content_in_tag) {
        if (stripos($content_in_tag, '<{$') === false and empty(trim(strip_tags($content_in_tag)))) {
            $old = $matches[0][$key];
            $new = str_replace('</h2>', "<span class=sr-only>empty head3</span></h2>", $old);
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
    // if ($data['save']) {
    //     Utility::dd($data['save']);
    // }

    return $data;
}
function h3_blank($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    // Utility::dd($matches);
    $fix = false;
    foreach ($matches[2] as $key => $content_in_tag) {
        if (stripos($content_in_tag, '<{$') === false and empty(trim(strip_tags($content_in_tag)))) {
            $old = $matches[0][$key];
            $new = str_replace('</h3>', "<span class=sr-only>empty head3</span></h3>", $old);
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
    // if ($data['save']) {
    //     Utility::dd($data['save']);
    // }

    return $data;
}
function h4_blank($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    // Utility::dd($matches);
    $fix = false;
    foreach ($matches[2] as $key => $content_in_tag) {
        if (stripos($content_in_tag, '<{$') === false and empty(trim(strip_tags($content_in_tag)))) {
            $old = $matches[0][$key];
            $new = str_replace('</h4>', "<span class=sr-only>empty head3</span></h4>", $old);
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
    // if ($data['save']) {
    //     Utility::dd($data['save']);
    // }

    return $data;
}
function h5_blank($v, $matches, $table)
{
    $myts = \MyTextSanitizer::getInstance();
    $html_v = $fix_v = htmlspecialchars($v);
    $data = [];
    // Utility::dd($matches);
    $fix = false;
    foreach ($matches[2] as $key => $content_in_tag) {
        if (stripos($content_in_tag, '<{$') === false and empty(trim(strip_tags($content_in_tag)))) {
            $old = $matches[0][$key];
            $new = str_replace('</h5>', "<span class=sr-only>empty head3</span></h5>", $old);
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
    // if ($data['save']) {
    //     Utility::dd($data['save']);
    // }

    return $data;
}

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

    // 修正佈景字型
    $theme_font_size_msg = '';
    $sql = 'select `theme_id`, `theme_name`, `font_size` from `' . $xoopsDB->prefix('tad_themes') . "`";
    $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    while (list($theme_id, $theme_name, $font_size) = $xoopsDB->fetchRow($result)) {
        $new_val = (int) $font_size;
        if (stripos($font_size, 'pt') !== false) {
            $new_val = round($new_val / 12, 2);
            $sql = 'update `' . $xoopsDB->prefix('tad_themes') . "` set `font_size`='{$new_val}rem' where `theme_id`='$theme_id'";
            $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
            $theme_font_size_msg .= '<li>' . sprintf(_MA_TADSITEMAP_FIX_THEME_FS, $theme_name, $font_size, "{$new_val}rem") . '</li>';
        } elseif (stripos($font_size, 'px') !== false) {
            $new_val = round($new_val / 16, 2);
            $sql = 'update `' . $xoopsDB->prefix('tad_themes') . "` set `font_size`='{$new_val}rem' where `theme_id`='$theme_id'";
            $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
            $xoopsTpl->assign('status', $status);
            $theme_font_size_msg .= '<li>' . sprintf(_MA_TADSITEMAP_FIX_THEME_FS, $theme_name, $font_size, "{$new_val}rem") . '</li>';
        }
    }
    $xoopsTpl->assign('theme_font_size_msg', $theme_font_size_msg);

    // 關閉評論
    $comment_msg = '';
    $sql = 'select a.`conf_id`, b.`dirname`
    from `' . $xoopsDB->prefix('config') . '` as a
    join `' . $xoopsDB->prefix('modules') . "` as b on a.conf_modid = b.mid
    WHERE a.`conf_name` = 'com_rule' AND a.`conf_value` = '1'";
    $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    while (list($conf_id, $dirname) = $xoopsDB->fetchRow($result)) {
        $sql = 'update `' . $xoopsDB->prefix('config') . "` set `conf_value`='0' where `conf_id`='$conf_id'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $comment_msg .= '<li>' . sprintf(_MA_TADSITEMAP_COMMENT_CLOSED, $dirname) . '</li>';
    }
    $xoopsTpl->assign('comment_msg', $comment_msg);

    // 關閉facebook評論
    $facebook_msg = '';
    $sql = 'select a.`conf_id`, b.`dirname`
    from `' . $xoopsDB->prefix('config') . '` as a
    join `' . $xoopsDB->prefix('modules') . "` as b on a.conf_modid = b.mid
    WHERE a.`conf_name` = 'facebook_comments_width' AND a.`conf_value` = '1'";
    $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    while (list($conf_id, $dirname) = $xoopsDB->fetchRow($result)) {
        $sql = 'update `' . $xoopsDB->prefix('config') . "` set `conf_value`='0' where `conf_id`='$conf_id'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $comment_msg .= '<li>' . sprintf(_MA_TADSITEMAP_FACEBOOK_CLOSED, $dirname) . '</li>';
    }
    $xoopsTpl->assign('facebook_msg', $facebook_msg);

    // 移除功課表樣板的 id 及 headers
    $replace_arr = [' id="Tim"', ' id="Sct"', ' id="Mon"', ' id="Tue"', ' id="Wed"', ' id="Thu"', ' id="Fri"', ' headers="Tim"', ' headers="Sct"', ' headers="Mon"', ' headers="Tue"', ' headers="Wed"', ' headers="Thu"', ' headers="Fri"'];
    $schedule_msg = '';
    $clear_block_cache = false;
    $sql = 'select `conf_id`, `conf_value` from `' . $xoopsDB->prefix('config') . "` WHERE `conf_name` = 'schedule_template'";
    $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
    while (list($conf_id, $conf_value) = $xoopsDB->fetchRow($result)) {
        $new_conf_value = str_replace($replace_arr, '', $conf_value);
        $sql = 'update `' . $xoopsDB->prefix('config') . "` set `conf_value`='$new_conf_value' where `conf_id`='$conf_id'";
        $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        $schedule_msg .= '<li>' . _MA_TADSITEMAP_TAD_WEB_SCHEDULE_FIX . '</li>';
        $clear_block_cache = true;
    }
    $xoopsTpl->assign('comment_msg', $comment_msg);
    // 重新多人網頁產生畫面
    if ($clear_block_cache) {
        // 關閉 tad_web facebook評論
        $tad_web_facebook_msg = '';
        $sql = 'select a.`WebID`, a.`plugin`, b.`WebName`
        from `' . $xoopsDB->prefix('tad_web_plugins_setup') . '` as a
        join `' . $xoopsDB->prefix('tad_web') . "` as b on a.WebID = b.WebID
        WHERE a.`name` = 'use_fb_comments' AND a.`value` = '1'";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        while (list($WebID, $plugin, $WebName) = $xoopsDB->fetchRow($result)) {
            $sql = 'update `' . $xoopsDB->prefix('tad_web_plugins_setup') . "` set `value`='0' where `WebID`='$WebID' and `plugin`='$plugin' and `name`='use_fb_comments'";
            $xoopsDB->queryF($sql) or Utility::web_error($sql, __FILE__, __LINE__);
            $comment_msg .= '<li>' . sprintf(_MA_TADSITEMAP_TAD_WEB_FB_CLOSED, $WebName, $plugin) . '</li>';
        }
        $xoopsTpl->assign('tad_web_facebook_msg', $tad_web_facebook_msg);

        $sql = 'select `WebID` from `' . $xoopsDB->prefix('tad_web') . "` ";
        $result = $xoopsDB->query($sql) or Utility::web_error($sql, __FILE__, __LINE__);
        while (list($WebID) = $xoopsDB->fetchRow($result)) {
            unlink(XOOPS_VAR_PATH . "/tad_web/$WebID/web_blocks.json");
        }

    }

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

<?php
xoops_loadLanguage('admin_common', 'tadtools');

//tad_sitemap-edit
define('_MA_TADSITEMAP_MID', '模組編號');
define('_MA_TADSITEMAP_NAME', '項目名稱');
define('_MA_TADSITEMAP_URL', '連結位置');
define('_MA_TADSITEMAP_DESCRIPTION', '相關說明');
define('_MA_TADSITEMAP_LAST_UPDATE', '最後更新');
define('_MA_TADSITEMAP_SORT', '排序');
define('_MA_TADSITEMAP_INPUT_DESC', '可輸入說明');
define('_MA_TADSITEMAP_AUTO_IMPORT', '自動偵測網站地圖');
define('_MA_TADSITEMAP_HOMEPAGE', '首頁');
define('_MA_TADSITEMAP_CLEAN', '清空表示不顯示該項目');

define('_MA_TADSITEMAP_XOOPS_CSS', 'xoops.css有 %s 處未修正，若無<a href="https://schoolweb.tn.edu.tw/~matrix/xoops.css" target="_blank">請手動下載或按右鍵另存 xoops.css</a>，並覆蓋 %s 即可');
define('_MA_TADSITEMAP_TEXTSANITIZER_PATH', '開啟 <code>%s</code> 並搜尋「<code>$text = $this->makeClickable($text);</code>」（共三處），並將該行改為：「<code>$text = XoopsModules\Tadtools\Utility::linkify($text);</code>」後，儲存上傳並覆蓋');

define('_MA_TADSITEMAP_PROFILE', '請<a href="' . XOOPS_URL . '/modules/system/admin.php?fct=preferences&op=show&confcat_id=2" target="_blank">至偏好設定</a>將「允許新會員註冊」設為「否」，因為註冊表格並不符合無障礙2.0，也容易產生垃圾帳號。');

define('_MA_TADSITEMAP_NAV_LINK', '導覽列並無「網站地圖」連結，<a href="check.php?op=add2nav">可按此自動加入</a>');
define('_MA_TADSITEMAP_LINK_ENABLE', '導覽列有「網站地圖」連結，但未啟用，<a href="check.php?op=enable4nav&menuid=%s">可按此自動啟用</a>');

define('_MA_TADSITEMAP_DB_FIX', '按下方按鈕修正原有資料庫內容：');

define('_MA_TADSITEMAP_VIEW_FIX', '預覽後修正');
define('_MA_TADSITEMAP_VIEW_FIX_AGAIN', '再次預覽修正');
define('_MA_TADSITEMAP_AUTO_FIX', '直接自動修正');

define('_MA_TADSITEMAP_DL_FREEGO', '最後請 <a href="https://accessibility.moda.gov.tw/Download/Detail/2763?Category=70" target="_blank">下載FreeGo 110.07</a>，並以 AA標準檢測「' . XOOPS_URL . '」');
// define('_MA_TADSITEMAP_DL_FREEGO', '最後請 <a href="https://accessibility.moda.gov.tw/Download/Detail/2763?Category=70" target="_blank">下載FreeGo 110.07</a>，網址輸入「' . XOOPS_URL . '」，並設定「全網站」以「AA」標準進行檢測，<span class="text-danger">需於「排除網頁」加入「' . XOOPS_URL . '/modules/tadtools/download.php」</span>以避免檔案下載部份無法通過檢測');
define('_MA_TADSITEMAP_STATEMENT', '本程式僅盡量協助通過未登入網站狀態下的機器檢查，無法保證人工查核可以通過。');

define('_MA_TADSITEMAP_TABLE_COL', '欄位');
define('_MA_TADSITEMAP_NEED_FIX', '欄位（%s）需修改部份：');
define('_MA_TADSITEMAP_FIX_NOW', '立即修正');
define('_MA_TADSITEMAP_THATS_ALL', '修正程式能做的都做了，剩下的就請自行處理。');
define('_MA_TADSITEMAP_FIX_THEME_FS', '已將 %s 佈景預設字型尺寸單位從 %s 改為 %s');
define('_MA_TADSITEMAP_COMMENT_CLOSED', '已將 %s 的評論功能關閉，以符合無障礙標準，並避免垃圾訊息入侵');
define('_MA_TADSITEMAP_FACEBOOK_CLOSED', '已將 %s 的facebook留言框關閉，以符合無障礙標準');
define('_MA_TADSITEMAP_TAD_WEB_SCHEDULE_FIX', '完成多人網頁功課表樣板的修正，以符合無障礙標準');
define('_MA_TADSITEMAP_TAD_WEB_FB_CLOSED', '已關閉「%s」的「%s」功能中的facebook留言框關閉，以符合無障礙標準');

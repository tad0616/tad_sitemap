<?php
xoops_loadLanguage('admin_common', 'tadtools');
define('_TAD_NEED_TADTOOLS', ' 需要 modules/tadtools，可至<a href="http://campus-xoops.tn.edu.tw/modules/tad_modules/index.php?module_sn=1" target="_blank">XOOPS輕鬆架</a>下載。');

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

define('_MA_TADSITEMAP_PROFILE', '請<a href="' . XOOPS_URL . '/modules/system/admin.php?fct=preferences&op=show&confcat_id=2" target="_blank">至偏好設定</a>將「允許新會員註冊」設為「否」，因為註冊表格並不符合無障礙2.0，也容易產生垃圾帳號。');

define('_MA_TADSITEMAP_NAV_LINK', '導覽列並無「網站地圖」連結，<a href="check.php?op=add2nav">可按此自動加入</a>');
define('_MA_TADSITEMAP_LINK_ENABLE', '導覽列有「網站地圖」連結，但未啟用，<a href="check.php?op=enable4nav&menuid=%s">可按此自動啟用</a>');

define('_MA_TADSITEMAP_DB_FIX', '按下方按鈕修正原有資料庫內容：');

define('_MA_TADSITEMAP_VIEW_FIX', '預覽後自動修正');
define('_MA_TADSITEMAP_AUTO_FIX', '直接自動修正');

define('_MA_TADSITEMAP_DL_FREEGO', '最後請 <a href="https://accessibility.ncc.gov.tw/Download/Detail/1743?Category=70" target="_blank">下載FreeGo 110.07</a>，並以 AA標準檢測「' . XOOPS_URL . '」');
define('_MA_TADSITEMAP_STATEMENT', '本程式僅盡量協助通過未登入網站狀態下的機器檢查，無法保證人工查核可以通過。');

define('_MA_TADSITEMAP_TABLE_COL', '欄位');
define('_MA_TADSITEMAP_NEED_FIX', '欄位（%s）需修改部份：');
define('_MA_TADSITEMAP_FIX_NOW', '立即修正');
define('_MA_TADSITEMAP_THATS_ALL', '修正程式能做的都做了，剩下的就請自行處理。');

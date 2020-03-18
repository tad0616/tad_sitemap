<?php
xoops_loadLanguage('admin_common', 'tadtools');
if (!defined('_TAD_NEED_TADTOOLS')) {
    define('_TAD_NEED_TADTOOLS', 'This module needs TadTools module. You can download TadTools from <a href="https://campus-xoops.tn.edu.tw/modules/tad_modules/index.php?module_sn=1" target="_blank">XOOPS EasyGO</a>.');
}

//tad_sitemap-edit
define('_MA_TADSITEMAP_MID', 'Module ID');
define('_MA_TADSITEMAP_NAME', 'Name');
define('_MA_TADSITEMAP_URL', 'URL');
define('_MA_TADSITEMAP_DESCRIPTION', 'Description');
define('_MA_TADSITEMAP_LAST_UPDATE', 'Last Update');
define('_MA_TADSITEMAP_SORT', 'Weight');
define('_MA_TADSITEMAP_INPUT_DESC', 'Please input the description');
define('_MA_TADSITEMAP_AUTO_IMPORT', 'Auto Detect Site Map');
define('_MA_TADSITEMAP_HOMEPAGE', 'Homepage');
define('_MA_TADSITEMAP_CLEAN', 'Empty items will not be displayed');

define('_MA_TADSITEMAP_XOOPS_CSS', 'xoops.css has %s places that have not been fixed. If there is no <a href="https://schoolweb.tn.edu.tw/~matrix/xoops.css" target="_blank"> Please download it manually or right click Save xoops.css </a> and overwrite %s');

define('_MA_TADSITEMAP_PROFILE', 'Please go to <a href="' . XOOPS_URL . '/modules/system/admin.php?fct=preferences&op=show&confcat_id=2" target="_blank">your preferences</a> and set "Allow New Member Registration" to "No", because the registration form does not comply with Accessibility 2.0, and it is easy to generate spam accounts.');

define('_MA_TADSITEMAP_NAV_LINK', 'The navigation bar does not have a "Site Map" link. <a href="check.php?op=add2nav"> You can click here to automatically add it </a>');
define('_MA_TADSITEMAP_LINK_ENABLE', 'The navigation bar has a "Site Map" link, but it is not enabled, <a href="check.php?op=enable4nav&menuid=%s"> Click here to enable it automatically </a>');

define('_MA_TADSITEMAP_DB_FIX', 'Press the button below to correct the original database content. Currently only automatic corrections such as font-size, iframe, blockquote, etc. are supported. Others such as table are much more complex and cannot be automatically corrected by programs. Please handle them yourself (eg, replace them with table image files):');

define('_MA_TADSITEMAP_VIEW_FIX', 'Automatic correction after preview');
define('_MA_TADSITEMAP_AUTO_FIX', 'Direct automatic correction');

define('_MA_TADSITEMAP_DL_FREEGO', '<a href="https://www.handicap-free.nat.gov.tw/Download/Detail/1375?Category=52" target="_blank"> Download FreeGo 2.0 </a> and detect "' . XOOPS_URL . '" with the AA standard');
define('_MA_TADSITEMAP_STATEMENT', 'This program only helps to pass the machine check when not logged in to the website. It cannot guarantee that the manual check can pass.');

define('_MA_TADSITEMAP_TABLE', 'Table');
define('_MA_TADSITEMAP_NEED_FIX', 'Field (%s) needs to be modified:');
define('_MA_TADSITEMAP_FIX_NOW', 'Fix now');
define('_MA_TADSITEMAP_THATS_ALL', 'All the correction programs can be done, please correct the rest.');

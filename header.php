<?php
use XoopsModules\Tadtools\Utility;
require_once dirname(dirname(__DIR__)) . '/mainfile.php';
require_once __DIR__ . '/preloads/autoloader.php';

//判斷是否對該模組有管理權限 $_SESSION['tad_sitemap_adm']
if (!isset($_SESSION['tad_sitemap_adm'])) {
    $_SESSION['tad_sitemap_adm'] = ($xoopsUser) ? $xoopsUser->isAdmin() : false;
}

$interface_menu[_MD_TADSITEMA_SMNAME1] = 'index.php';
$interface_icon[_MD_TADSITEMA_SMNAME1] = 'fa-map-o';
$interface_menu[_MD_TADSITEMA_SMNAME2] = 'privacy_policy.php';
$interface_icon[_MD_TADSITEMA_SMNAME2] = 'fa-user-secret';
$interface_menu[_MD_TADSITEMA_SMNAME3] = 'remove_data.php';
$interface_icon[_MD_TADSITEMA_SMNAME3] = 'fa-ban';

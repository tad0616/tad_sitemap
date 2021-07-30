<?php
use XoopsModules\Tadtools\Utility;
require_once dirname(dirname(__DIR__)) . '/mainfile.php';
require_once __DIR__ . '/preloads/autoloader.php';
require_once __DIR__ . '/function.php';

//判斷是否對該模組有管理權限 $_SESSION['tad_sitemap_adm']
if (!isset($_SESSION['tad_sitemap_adm'])) {
    $_SESSION['tad_sitemap_adm'] = ($xoopsUser) ? $xoopsUser->isAdmin() : false;
}

//$interface_menu[_TAD_TO_MOD]="index.php";
$interface_menu[_MD_TADSITEMA_SMNAME1] = 'index.php';
$interface_icon[_MD_TADSITEMA_SMNAME1] = 'fa-chevron-right';

if ($_SESSION['tad_sitemap_adm']) {
    $interface_menu[_TAD_TO_ADMIN] = 'admin/main.php';
    $interface_icon[_TAD_TO_ADMIN] = 'fa-sign-in';
}

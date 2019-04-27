<?php
require_once dirname(dirname(__DIR__)) . '/mainfile.php';
require_once __DIR__ . '/function.php';

//判斷是否對該模組有管理權限
$isAdmin = false;
if ($xoopsUser) {
    $module_id = $xoopsModule->getVar('mid');
    $isAdmin = $xoopsUser->isAdmin($module_id);
}

//$interface_menu[_TAD_TO_MOD]="index.php";
$interface_menu[_MD_TADSITEMA_SMNAME1] = 'index.php';
$interface_icon[_MD_TADSITEMA_SMNAME1] = 'fa-chevron-right';

if ($isAdmin) {
    $interface_menu[_TAD_TO_ADMIN] = 'admin/main.php';
    $interface_icon[_TAD_TO_ADMIN] = 'fa-sign-in';
}

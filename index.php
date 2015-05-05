<?php
//  ------------------------------------------------------------------------ //
// 本模組由 tad 製作
// 製作日期：2015-05-04
// $Id:$
// ------------------------------------------------------------------------- //

/*-----------引入檔案區--------------*/
include "header.php";
$xoopsOption['template_main'] = set_bootstrap('tad_sitemap_index.html');
include_once XOOPS_ROOT_PATH."/header.php";

/*-----------功能函數區--------------*/

//列出所有tad_sitemap資料
function list_tad_sitemap(){
  global $xoopsDB , $xoopsTpl , $isAdmin ,$xoopsModuleConfig;

  $myts =& MyTextSanitizer::getInstance();

  $sql = "select * from ".$xoopsDB->prefix("modules")." where isactive='1' and hasmain='1' and weight!='0' order by weight,last_update";
  $result = $xoopsDB->query($sql) or redirect_header('index.php',3, mysql_error());

  $all_content="";
  $i=0;
  while($all=$xoopsDB->fetchArray($result)){

    $sql2 = "select * from ".$xoopsDB->prefix("tad_sitemap")." where mid='{$all['mid']}' order by `sort`";
    $result2 = $xoopsDB->query($sql2) or redirect_header('index.php',3, mysql_error());

    $j=0;
    $item="";
    while($all2=$xoopsDB->fetchArray($result2)){
      foreach($all2 as $k=>$v){
        $$k=$v;
      }

      //過濾讀出的變數值
      $name = $myts->htmlSpecialChars($name);
      $url = $myts->htmlSpecialChars($url);
      $description = $myts->htmlSpecialChars($description);

      $item[$j]['mod_name']=$mod_name;
      $item[$j]['mid']=$mid;
      $item[$j]['name']=$name;
      $item[$j]['url']=$url;
      $item[$j]['description']=$description;
      $item[$j]['last_update']=$last_update;
      $item[$j]['sort']=$sort;
      $j++;
    }
    $all['item']=$item;

    $all_content[$i]=$all;
    $i++;
  }


  //刪除確認的JS
  $xoopsTpl->assign('action' , $_SERVER['PHP_SELF']);
  $xoopsTpl->assign('isAdmin' , $isAdmin);
  $xoopsTpl->assign('all_content' , $all_content);
  $xoopsTpl->assign('now_op' , 'list_tad_sitemap');
  $xoopsTpl->assign('about_site' , $xoopsModuleConfig['about_site']);


}



/*-----------執行動作判斷區----------*/
$op=empty($_REQUEST['op'])?"":$_REQUEST['op'];
$midname=empty($_REQUEST['midname'])?"":intval($_REQUEST['midname']);

switch($op){
  /*---判斷動作請貼在下方---*/
  default:
    list_tad_sitemap();
  break;
  /*---判斷動作請貼在上方---*/
}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign( "toolbar" , toolbar_bootstrap($interface_menu)) ;
$xoopsTpl->assign( "isAdmin" , $isAdmin) ;
include_once XOOPS_ROOT_PATH.'/footer.php';
?>
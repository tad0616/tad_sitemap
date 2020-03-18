<style>
ol.wcag{
    list-style: decimal inside;
}
ol.wcag li{
    line-height:2em;
}
</style>

<ol class="wcag">
<{if $num > 0}>
    <li>xoops.css有 <{$num}> 處未修正，若無<a href="https://schoolweb.tn.edu.tw/~matrix/xoops.css" target="_blank">請手動下載或按右鍵另存 xoops.css</a>，並覆蓋 <{$css_path}> 即可</li>
<{/if}>
<{if $allow_register==1}>
    <li>請<a href="<{$xoops_url}>/modules/system/admin.php?fct=preferences&op=show&confcat_id=2" target="_blank">至偏好設定</a>將「允許新會員註冊」設為「否」，因為註冊表格並不符合無障礙2.0，也容易產生垃圾帳號。</li>
<{/if}>
<{if !$menuid}>
    <li>導覽列並無「網站地圖」連結，<a href="check.php?op=add2nav">可按此自動加入</a></li>
<{elseif $status!=1}>
    <li>導覽列有「網站地圖」連結，但未啟用，<a href="check.php?op=enable4nav&menuid=<{$menuid}>">可按此自動啟用</a></li>
<{/if}>

    <li>按下方按鈕修正原有資料庫內容，目前僅支援 font-size、iframe、blockquote 等自動修正，其他如 table 表格因複雜許多，故無法用程式自動修正，請自行處理（如：用表格圖檔取代HTML表格）：<br>
    <a href="check.php?op=start_check" class="btn btn-primary">預覽後自動修正</a>
    <a href="check.php?op=auto_fix" class="btn btn-success">直接自動修正</a>
    </li>
    <li>最後請 <a href="https://www.handicap-free.nat.gov.tw/Download/Detail/1375?Category=52" target="_blank">下載FreeGo 2.0</a>，並以 AA標準檢測「<{$xoops_url}>」</li>
    <li>本程式僅盡量協助通過未登入網站狀態下的機器檢查，無法保證人工查核可以通過。</li>
</ol>

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
    <li><{$smarty.const._MA_TADSITEMAP_XOOPS_CSS|sprintf:$num:$css_path}></li>
<{/if}>
<{if $theme_font_size_msg}>
    <{$theme_font_size_msg}>
<{/if}>
<{if $comment_msg}>
    <{$comment_msg}>
<{/if}>
<{if $facebook_msg}>
    <{$facebook_msg}>
<{/if}>
<{if $tad_web_facebook_msg}>
    <{$tad_web_facebook_msg}>
<{/if}>
<{if $schedule_msg}>
    <{$schedule_msg}>
<{/if}>
<{if $allow_register==1}>
    <li><{$smarty.const._MA_TADSITEMAP_PROFILE}></li>
<{/if}>
<{if !$menuid}>
    <li><{$smarty.const._MA_TADSITEMAP_NAV_LINK}></li>
<{elseif $status!=1}>
    <li><{$smarty.const._MA_TADSITEMAP_LINK_ENABLE|sprintf:$menuid}></li>
<{/if}>
    <li><{$smarty.const._MA_TADSITEMAP_DB_FIX}><br>
    <a href="check.php?op=start_check" class="btn btn-primary"><{$smarty.const._MA_TADSITEMAP_VIEW_FIX}></a>
    <a href="check.php?op=auto_fix" class="btn btn-success"><{$smarty.const._MA_TADSITEMAP_AUTO_FIX}></a>
    </li>
    <li><{$smarty.const._MA_TADSITEMAP_DL_FREEGO}></li>
    <li><{$smarty.const._MA_TADSITEMAP_STATEMENT}></li>
</ol>

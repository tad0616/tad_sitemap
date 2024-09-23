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
<{if $textsanitizer_path|default:false}>
    <li><{$smarty.const._MA_TADSITEMAP_TEXTSANITIZER_PATH|sprintf:$textsanitizer_path}></li>
<{/if}>
<{if $theme_font_size_msg|default:false}>
    <{$theme_font_size_msg|default:''}>
<{/if}>
<{if $comment_msg|default:false}>
    <{$comment_msg|default:''}>
<{/if}>
<{if $facebook_msg|default:false}>
    <{$facebook_msg|default:''}>
<{/if}>
<{if $tad_web_facebook_msg|default:false}>
    <{$tad_web_facebook_msg|default:''}>
<{/if}>
<{if $schedule_msg|default:false}>
    <{$schedule_msg|default:''}>
<{/if}>
<{if $allow_register|default:false}>
    <li><{$smarty.const._MA_TADSITEMAP_PROFILE}></li>
<{/if}>
<{if !$menuid}>
    <li><{$smarty.const._MA_TADSITEMAP_NAV_LINK}></li>
<{elseif $status!=1}>
    <li><{$smarty.const._MA_TADSITEMAP_LINK_ENABLE|sprintf:$menuid}></li>
<{/if}>
    <li><{$smarty.const._MA_TADSITEMAP_DB_FIX}><br>
    <{include file="$xoops_rootpath/modules/tad_sitemap/templates/sub_check_form.tpl"}>
    </li>
    <li><{$smarty.const._MA_TADSITEMAP_DL_FREEGO}></li>
    <li><{$smarty.const._MA_TADSITEMAP_STATEMENT}></li>
</ol>

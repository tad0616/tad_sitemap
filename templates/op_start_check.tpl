<form action="check.php" method="post">
    <{if $data}>

        <div class="text-center" style="margin: 30px auto;">
            <input type="hidden" name="need_check_list" value="<{$need_check_list}>">
            <button type="submit" name="op" value="auto_fix" class="btn btn-primary"><{$smarty.const._MA_TADSITEMAP_FIX_NOW}></button>
        </div>

        <{foreach from=$data key=table item=kind_cols}>
            <h3><{$table}> <{$smarty.const._MA_TADSITEMAP_TABLE_COL}></h3>
            <{foreach from=$kind_cols key=kind item=cols}>
                <{foreach from=$cols key=i item=items}>
                    <{foreach from=$items.col key=col item=item}>
                        <h4>(<{$items.primary}>) <{$col}> <{$smarty.const._MA_TADSITEMAP_TABLE_COL|sprintf:$items.primary}></h4>
                        <div class="my-3 p-2 bg-dark text-warning text-monospace text-height-2"><small><{$items.sql|htmlspecialchars}></small></div>

                        <div class="alert alert-info">
                            <div class="row">
                                <div class="col-sm-6"><pre style="height:<{$item.line}>em;overflow: auto;color: #afafaf;" class="form-control"><{$item.html_v}></pre></div>
                                <div class="col-sm-6"><pre style="height:<{$item.line}>em;overflow: auto;color: #afafaf;" class="form-control"><{$item.fix_v}></pre></div>
                            </div>
                        </div>
                    <{/foreach}>
                <{/foreach}>
            <{/foreach}>
        <{/foreach}>

        <div class="text-center" style="margin: 30px auto;">
            <button type="submit" name="op" value="auto_fix" class="btn btn-primary"><{$smarty.const._MA_TADSITEMAP_FIX_NOW}></button>
        </div>
    <{else}>
        <div class="alert alert-success"><{$smarty.const._MA_TADSITEMAP_THATS_ALL}></div>
        <{include file="$xoops_rootpath/modules/tad_sitemap/templates/sub_check_form.tpl"}>
    <{/if}>
</form>
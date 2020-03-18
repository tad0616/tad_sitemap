<form action="check.php" method="post">
<{if $data}>
    <{foreach from=$data key=table item=cols}>
        <h3><{$table}> <{$smarty.const._MA_TADSITEMAP_TABLE}></h3>
        <{foreach from=$cols key=i item=items}>
            <{foreach from=$items.col key=col item=item}>
                <h4>(<{$i}>) <{$col}> <{$smarty.const._MA_TADSITEMAP_TABLE|sprintf:$items.primary}></h4>
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-sm-6"><pre style="height:<{$item.line}>em;overflow: auto;" class="form-control"><{$item.html_v}></pre></div>
                        <div class="col-sm-6"><pre style="height:<{$item.line}>em;overflow: auto;" class="form-control"><{$item.fix_v}></pre></div>
                    </div>
                </div>
            <{/foreach}>
        <{/foreach}>
    <{/foreach}>

    <div class="text-center" style="margin: 30px auto;">
        <button type="submit" name="op" value="auto_fix" class="btn btn-primary"><{$smarty.const._MA_TADSITEMAP_FIX_NOW}></button>
    </div>
<{else}>
    <div class="alert alert-success"><{$smarty.const._MA_TADSITEMAP_THATS_ALL}></div>
<{/if}>
</form>
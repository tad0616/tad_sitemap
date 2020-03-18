<form action="check.php" method="post">
<{if $data}>
    <{foreach from=$data key=table item=cols}>
        <h3><{$table}> 資料表</h3>
        <{foreach from=$cols key=i item=items}>
            <{foreach from=$items.col key=col item=item}>
                <h4>(<{$i}>) <{$col}> 欄位（<{$items.primary}>）需修改部份：</h4>
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
        <button type="submit" name="op" value="auto_fix" class="btn btn-primary">立即修正</button>
    </div>
<{else}>
    <div class="alert alert-success">修正程式能做的都做了，剩下的就請自行處理。</div>
<{/if}>
</form>
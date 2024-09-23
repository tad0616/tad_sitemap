<form action="check.php" method="post" id="myForm" enctype="multipart/form-data" class="form-horizontal">

    <div class="form-check-inline checkbox-inline">
        <label class="form-check-label">
            <input class="form-check-input" id="clickAll" type="checkbox" name="need_check[]" value="<{$type|default:''}>" <{if $type|in_array:$need_check}>checked<{/if}>>
            <{$smarty.const._ALL}>
        </label>
    </div>
    <{foreach from=$check_title key=type item=title}>
        <div class="form-check-inline checkbox-inline">
            <label class="form-check-label">
                <input class="form-check-input chkItem" type="checkbox" name="need_check[]" value="<{$type|default:''}>" <{if $type|in_array:$need_check}>checked<{/if}>>
                <{$title|default:''}>
            </label>
        </div>
    <{/foreach}>
    <input type="hidden" name="op" value="start_check">
    <div class="bar mt-2">
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-save" aria-hidden="true"></i> <{$smarty.const._MA_TADSITEMAP_VIEW_FIX}>
        </button>
    </div>
</form>


<script type='text/javascript'>
    $().ready(function(){
      $("#clickAll").click(function() {
        var x = document.getElementById("clickAll").checked;
        if(x){
          $(".chkItem").each(function() {
            $(this).attr("checked", true);
          });
        }else{
         $(".chkItem").each(function() {
            $(this).attr("checked", false);
         });
    }
      });

    });
</script>
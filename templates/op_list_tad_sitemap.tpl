<{if $all_content|default:false}>
    <{if $smarty.session.tad_sitemap_adm|default:false}>
        <{$tad_sitemap_jquery_ui|default:''}>
        <script type="text/javascript">
            $(document).ready(function(){
                $("#tad_sitemap_sort").sortable({ opacity: 0.6, cursor: "move", update: function() {
                    var order = $(this).sortable("serialize");
                    $.post("<{$xoops_url}>/modules/tad_sitemap/admin/main.php", order + "&op=update_tad_sitemap_sort", function(theResponse){
                        $("#tad_sitemap_save_msg").html(theResponse);
                    });
                }
                });
            });
        </script>
    <{/if}>

    <div id="tad_sitemap_save_msg"></div>

    <{assign var="i" value=0}>
    <form action="main.php" method="post">
        <table class="table">
        <{foreach from=$all_content item=map}>
            <{assign var="i" value=$i+1}>
            <{assign var="j" value=0}>
            <tr>
                <th><{$i|default:''}>.<{$map.name}></th>
                <td>
                    <{if $map.item|default:false}>
                        <{foreach from=$map.item item=sub}>
                            <{assign var="j" value=$j+1}>
                            <div class="row my-1">
                                <div class="col-sm-3">
                                    <input type="text" name="name[<{$sub.mid}>][<{$sub.sort}>]" value="<{$sub.name}>" class="form-control form-control-sm" placeholder="<{$smarty.const._MA_TADSITEMAP_CLEAN}>" >
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" name="description[<{$sub.mid}>][<{$sub.sort}>]" value="<{$sub.description}>" class="form-control form-control-sm" placeholder="<{$smarty.const._MA_TADSITEMAP_INPUT_DESC}>">
                                </div>
                                <div class="col-sm-3">
                                    <{$sub.url}>
                                </div>
                            </div>
                        <{/foreach}>
                    <{/if}>
                </td>
            </tr>
        <{/foreach}>
        </table>
        <div class="text-center">
            <input type="hidden" name="op" value="update_tad_sitemap">
            <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk" aria-hidden="true"></i>  <{$smarty.const._TAD_SAVE}></button>
        </div>
    </form>

    <{if $smarty.session.tad_sitemap_adm|default:false}>
        <div class="text-right text-end">
            <a href="<{$xoops_url}>/modules/tad_sitemap/admin/main.php?op=auto_sitemap" class="btn btn-info"><{$smarty.const._MA_TADSITEMAP_AUTO_IMPORT}></a>
        </div>
    <{/if}>

    <{$bar|default:''}>
<{else}>
    <{if $smarty.session.tad_sitemap_adm|default:false}>
    <div class="jumbotron bg-light p-5 rounded-lg m-3 text-center">
        <a href="<{$xoops_url}>/modules/tad_sitemap/admin/main.php?op=auto_sitemap" class="btn btn-info"><{$smarty.const._MA_TADSITEMAP_AUTO_IMPORT}></a>
    </div>
    <{/if}>
<{/if}>
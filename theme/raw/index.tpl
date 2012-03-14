{include file="header.tpl"}
<div id="ilpswrap">
    <div class="rbuttons">
        <a class="btn" href="{$WWWROOT}artefact/ilps/new.php">{str section="artefact.ilps" tag="newilp"}</a>
    </div>
{if !$ilps.data}
    <div class="message">{$strnoilpsaddone|safe}</div>
{else}
<table id="ilpslist" class="fullwidth listing">
    <tbody>
        {$ilps.tablerows|safe}
    </tbody>
</table>
   {$ilps.pagination|safe}
{/if}
</div>
{include file="footer.tpl"}

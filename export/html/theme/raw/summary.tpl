{if $ilps}
    <ul>
        {foreach from=$ilps item=ilp}
            <li><a href="{$ilp.link}">{$ilp.title}</a></li>
        {/foreach}
    </ul>
{/if}

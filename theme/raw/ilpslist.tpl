{foreach from=$ilps.data item=ilp}
    <tr class="{cycle values='r0,r1'}">
        <td>
            <div class="fr ilpstatus">
                 <a href="{$WWWROOT}artefact/ilps/edit/index.php?id={$ilp->id}" title="{str tag="edit"}" ><img src="{theme_url filename='images/edit.gif'}" alt="{str tag=edit}"></a>
                 <a href="{$WWWROOT}artefact/ilps/ilp.php?id={$ilp->id}" title="{str tag=manageunits section=artefact.ilps}"><img src="{theme_url filename='images/manage.gif'}" alt="{str tag=manageunits}"></a>
                 <a href="{$WWWROOT}artefact/ilps/delete/index.php?id={$ilp->id}" title="{str tag="delete"}"><img src="{theme_url filename='images/icon_close.gif'}" alt="{str tag=delete}"></a>
            </div>

            <h3><a href="{$WWWROOT}artefact/ilps/ilp.php?id={$ilp->id}">{$ilp->title}</a></h3>

            <div class="codesc">{$ilp->description}</div>
        </td>
    </tr>

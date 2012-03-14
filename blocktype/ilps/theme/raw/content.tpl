<div class="blockinstance-content">
{if $units.data}
<table id="unittable_{$blockid}" class="ilpsblocktable">
    <thead>
        <tr>
        	<th class="c1">{str tag='title' section='artefact.ilps'}</th>
             <th class="c2">{str tag='status' section='artefact.ilps'}</th>
             <th class="c3">{str tag='targetcompletion' section='artefact.ilps'}</th>
            <th class="c4">{str tag='datecompleted' section='artefact.ilps'}</th>
            <th class="c5" style="text-align:right">{str tag='points' section='artefact.ilps'}</th>
        </tr>
    </thead>
    <tbody>
    {$units.tablerows|safe}
    </tbody>
</table>
{if $units.pagination}
<div id="ilps_page_container">{$units.pagination|safe}</div>
{/if}
{else}
    <p>{str tag='nounits' section='artefact.ilps'}</p>
{/if}
</div>

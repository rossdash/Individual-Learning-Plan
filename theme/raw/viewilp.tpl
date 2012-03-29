<table id="unittable">
    <thead>
        <tr>
            <th class="c1">{str tag='title' section='artefact.ilps'}</th>
            <th class="c2">{str tag='status' section='artefact.ilps'}</th>
            <th class="c3">{str tag='targetcompletion' section='artefact.ilps'}</th>
            <th class="c4">{str tag='datecompleted' section='artefact.ilps'}</th>
            <th class="c5 right">{str tag='points' section='artefact.ilps'}</th>
        </tr>
    </thead>
    <tbody>
        {$units.tablerows|safe}
    </tbody>
</table>
<div id="ilps_page_container">{$units.pagination|safe}</div>
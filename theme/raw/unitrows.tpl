{$totalpoints=0}
{$acquiredpoints = 0}
{foreach from=$units.data item=unit}
    <tr class="{cycle values='r0,r1'}" style="width:100%" >
        <td class="c1">{$unit->title}</td>
        <td class="c2">{$unit->status}</td>
        <td class="c3">{$unit->targetcompletion}</td>
        <td class="c4">{$unit->datecompleted}</td>
        <td class="c5 right">{$unit->points}</td>
    </tr>
    
{if $unit->datecompleted !=''}
{$acquiredpoints = $acquiredpoints + $unit->points}
{/if}
    
{$totalpoints = $totalpoints + $unit->points}
{/foreach}
<tr></tr>
<tr class="summarypoints">
<th colspan="4" class="right">{str tag='totalpoints' section='artefact.ilps'}</th><td class="right totalpoints">{$totalpoints}</td><td></td>
</tr>
<tr><td></td></tr><tr><td></td></tr>
<tr class="feedbackpoints">
<th colspan="4" class="right">{str tag='acquiredpoints' section='artefact.ilps'}</th><td class="right aquiredpoints">{$acquiredpoints}</td><td></td>
</tr>
<tr class="feedbackpoints">
<th colspan="4" class="right">{str tag='remainingpoints' section='artefact.ilps'}</th><td class="right remainingpoints">{math equation="t - a" t=185 a=$acquiredpoints}</td><td></td>
</tr>
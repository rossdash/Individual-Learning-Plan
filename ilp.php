<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage artefact-ilps
 * @author     Ross Dash
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2012 Ross Dash
 *
 */


define('INTERNAL', 1);
define('MENUITEM', 'content/ilps');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'ilps');
define('SECTION_PAGE', 'ilps');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'ilps');

define('TITLE', get_string('units','artefact.ilps'));

$id = param_integer('id');

// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 20);

$ilp = new ArtefactTypeIlp($id);
if (!$USER->can_edit_artefact($ilp)) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}


$units = ArtefactTypeUnit::get_units($ilp->get('id'), $offset, $limit);
ArtefactTypeUnit::build_units_list_html($units);

$js = <<< EOF
addLoadEvent(function () {
    {$units['pagination_js']}
});
EOF;

$smarty = smarty(array('paginator'));
$smarty->assign_by_ref('units', $units);
$smarty->assign_by_ref('ilp', $id);
$smarty->assign('strnounitsaddone',
    get_string('nounitsaddone', 'artefact.ilps',
    '<a href="' . get_config('wwwroot') . 'artefact/ilps/new.php?id='.$ilp->get('id').'">', '</a>'));
$smarty->assign('PAGEHEADING', get_string("ilpsunits", "artefact.ilps",$ilp->get('title')));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('artefact:ilps:ilp.tpl');

?>

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
define('SECTION_PAGE', 'index');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'ilps');

define('TITLE', get_string('myilps', 'artefact.ilps'));

// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit = param_integer('limit', 20);

$ilps = ArtefactTypeIlp::get_ilps($offset, $limit);
ArtefactTypeIlp::build_ilps_list_html($ilps);

$js = <<< EOF
addLoadEvent(function () {
    {$ilps['pagination_js']}
});
EOF;

$smarty = smarty(array('paginator'));
$smarty->assign('ilps', $ilps);
$smarty->assign('strnoilpsaddone', get_string('noilpsaddone', 'artefact.ilps', '<a href="' . get_config('wwwroot') . 'artefact/ilps/new.php">', '</a>'));
$smarty->assign('PAGEHEADING', hsc(get_string("myilps", "artefact.ilps")));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('artefact:ilps:index.tpl');
?>

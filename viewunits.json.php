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
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'ilps');
require_once(get_config('docroot') . 'blocktype/lib.php');
require_once(get_config('docroot') . 'artefact/ilps/blocktype/ilps/lib.php');

$offset = param_integer('offset', 0);
$limit = param_integer('limit', 20);

if ($blockid = param_integer('block', null)) {
    $bi = new BlockInstance($blockid);
    $options = $configdata = $bi->get('configdata');

    $units = ArtefactTypeUnit::get_units($configdata['artefactid'], $offset, $limit);

    $template = 'artefact:ilps:unitrows.tpl';
    $pagination = array(
        'baseurl'   => $bi->get_view()->get_url() . '&block=' . $blockid,
        'id'        => 'block' . $blockid . '_pagination',
        'datatable' => 'unittable_' . $blockid,
        'jsonscript' => 'artefact/ilps/viewunits.json.php',
    );
}
else {
    $ilpid = param_integer('artefact');
    $viewid = param_integer('view');
    $options = array('viewid' => $viewid);
    $units = ArtefactTypeUnit::get_units($ilpid, $offset, $limit);

    $template = 'artefact:ilps:unitrows.tpl';
    $baseurl = get_config('wwwroot') . 'view/artefact.php?artefact=' . $ilpid . '&view=' . $options['viewid'];
    $pagination = array(
        'baseurl' => $baseurl,
        'id' => 'unit_pagination',
        'datatable' => 'unitlist',
        'jsonscript' => 'artefact/ilps/viewunits.json.php',
    );

}
ArtefactTypeUnit::render_units($units, $template, $options, $pagination);

json_reply(false, (object) array('message' => false, 'data' => $units));

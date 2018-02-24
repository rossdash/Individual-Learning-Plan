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

defined('INTERNAL') || die();

class PluginBlocktypeIlps extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.ilps/ilps');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.ilps/ilps');
    }

    public static function get_categories() {
        return array('general');
    }

    /**
     * Optional method. If exists, allows this class to decide the title for
     * all blockinstances of this type
     */
    public static function get_instance_title(BlockInstance $bi) {
        $configdata = $bi->get('configdata');

        if (!empty($configdata['artefactid'])) {
            safe_require('artefact', 'ilps');
            $ilp = new ArtefactTypeilp($configdata['artefactid']);
            $title = $ilp->get('title');
            return $title;
        }
        return '';
    }

    public static function render_instance(BlockInstance $instance, $editing = false) {
        global $exporter;

        require_once(get_config('docroot') . 'artefact/lib.php');
        safe_require('artefact', 'ilps');

        $configdata = $instance->get('configdata');

        $smarty = smarty_core();
        if (isset($configdata['artefactid'])) {
            $units = ArtefactTypeunit::get_units($configdata['artefactid']);
            $template = 'artefact:ilps:unitrows.tpl';
            $blockid = $instance->get('id');
            if ($exporter) {
                $pagination = false;
            } else {
                $pagination = array(
                    'baseurl' => $instance->get_view()->get_url() . '&block=' . $blockid,
                    'id' => 'block' . $blockid . '_pagination',
                    'datatable' => 'unittable_' . $blockid,
                    'jsonscript' => 'artefact/ilps/viewunits.json.php',
                );
            }
            ArtefactTypeUnit::render_units($units, $template, $configdata, $pagination);

            if ($exporter && $units['count'] > $units['limit']) {
                $artefacturl = get_config('wwwroot') . 'view/artefact.php?artefact=' . $configdata['artefactid']
                        . '&amp;view=' . $instance->get('view');
                $units['pagination'] = '<a href="' . $artefacturl . '">' . get_string('allunits', 'artefact.ilps') . '</a>';
            }
            $smarty->assign('units', $units);
        } else {
            $smarty->assign('noilps', 'blocktype.ilps/ilps');
        }
        $smarty->assign('blockid', $instance->get('id'));
        return $smarty->fetch('blocktype:ilps:content.tpl');
    }

    // My ilps blocktype only has 'title' option so next two functions return as normal
    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $configdata = $instance->get('configdata');

        $form = array();

        // Which resume field does the user want
        $form[] = self::artefactchooser_element((isset($configdata['artefactid'])) ? $configdata['artefactid'] : null);

        return $form;
    }

    public static function artefactchooser_element($default = null) {
        safe_require('artefact', 'ilps');
        return array(
            'name' => 'artefactid',
            'type' => 'artefactchooser',
            'title' => get_string('ilpstoshow', 'blocktype.ilps/ilps'),
            'defaultvalue' => $default,
            'blocktype' => 'ilps',
            'selectone' => true,
            'search' => false,
            'artefacttypes' => array('ilp'),
            'template' => 'artefact:ilps:artefactchooser-element.tpl',
        );
    }

    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

}


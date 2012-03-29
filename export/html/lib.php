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

class HtmlExportilps extends HtmlExportArtefactPlugin {

    public function pagination_data($artefact) {
        if ($artefact instanceof ArtefactTypeilp) {
            return array(
                'perpage' => 10,
                'childcount' => $artefact->count_children(),
                'plural' => get_string('ilps', 'artefact.ilps'),
            );
        }
    }

    public function dump_export_data() {
        foreach ($this->exporter->get('artefacts') as $artefact) {
            if ($artefact instanceof ArtefactTypeilp) {
                $this->paginate($artefact);
            }
        }
    }

    public function get_summary() {
        $smarty = $this->exporter->get_smarty();
        $ilps = array();
        foreach ($this->exporter->get('artefacts') as $artefact) {
            if ($artefact instanceof ArtefactTypeilp) {
                $ilps[] = array(
                    'link' => 'files/ilps/' . PluginExportHtml::text_to_path($artefact->get('title')) . '/index.html',
                    'title' => $artefact->get('title'),
                );
            }
        }
        $smarty->assign('ilps', $ilps);

        return array(
            'title' => get_string('ilps', 'artefact.ilps'),
            'description' => $smarty->fetch('export:html/ilps:summary.tpl'),
        );
    }

    public function get_summary_weight() {
        return 40;
    }

}

<?php

/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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

class PluginArtefactilps extends PluginArtefact {

    public static function get_artefact_types() {
        return array(
            'unit',
            'ilp',
        );
    }

    public static function get_block_types() {
        return array();
    }

    public static function get_plugin_name() {
        return 'ilps';
    }

    public static function menu_items() {
        return array(
            array(
                'path' => 'content/ilps',
                'title' => get_string('ilps', 'artefact.ilps'),
                'url' => 'artefact/ilps/',
                'weight' => 40,
            ),
        );
    }

}

class ArtefactTypeilp extends ArtefactType {

    protected $points;

    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);

        if (empty($this->id)) {
            $this->container = 1;
        }

        // load points
        if ($this->id) {
            $points = get_field('artefact_ilps_points', 'points', 'artefact', $this->id);
            $this->points = $points;
        }
    }

    /**
     * This function returns the points assigned to the ILP.
     * 
     */
    public static function get_points($id) {
        $points = get_field('artefact_ilps_points', 'points', 'artefact', $id);
        return $points;
    }

    public static function get_links($id) {
        return array();
    }

    public function commit() {
        if (empty($this->dirty)) {
            return;
        }

        // Return whether or not the commit worked
        $success = false;

        db_begin();
        $new = empty($this->id);

        parent::commit();

        $this->dirty = true;

        $data = (object) array(
                    'artefact' => $this->get('id'),
                    'points' => $this->get('points'),
        );

        if ($new) {
            $success = insert_record('artefact_ilps_points', $data);
        } else {
            $success = update_record('artefact_ilps_points', $data, 'artefact');
        }

        db_commit();

        $this->dirty = $success ? false : true;

        return $success;
    }

    public function delete() {
        if (empty($this->id)) {
            return;
        }

        db_begin();
        delete_records('artefact_ilps_points', 'artefact', $this->id);

        parent::delete();
        db_commit();
    }

    public static function get_icon($options = null) {
        
    }

    public static function is_singular() {
        return false;
    }

    /**
     * This function returns a list of the given user's ilps.
     *
     * @param limit how many ilps to display per page
     * @param offset current page to display
     * @return array (count: integer, data: array)
     */
    public static function get_ilps($offset = 0, $limit = 10) {
        global $USER;

        ($ilps = get_records_sql_array("SELECT *
            FROM {artefact} a
            JOIN {artefact_ilps_points} at ON at.artefact = a.id
            WHERE artefacttype = 'ilp' AND owner = ?
            ORDER BY id", array($USER->get('id')), $offset, $limit))
                || ($ilps = array());

        $result = array(
            'count' => count_records('artefact', 'owner', $USER->get('id'), 'artefacttype', 'ilp'),
            'data' => $ilps,
            'offset' => $offset,
            'limit' => $limit,
        );

        return $result;
    }

    /**
     * Builds the ilps list table
     *
     * @param ilps (reference)
     */
    public static function build_ilps_list_html(&$ilps) {
        $smarty = smarty_core();
        $smarty->assign_by_ref('ilps', $ilps);
        $ilps['tablerows'] = $smarty->fetch('artefact:ilps:ilpslist.tpl');
        $pagination = build_pagination(array(
            'id' => 'ilplist_pagination',
            'class' => 'center',
            'url' => get_config('wwwroot') . 'artefact/ilps/index.php',
            'jsonscript' => 'artefact/ilps/ilps.json.php',
            'datatable' => 'ilpslist',
            'count' => $ilps['count'],
            'limit' => $ilps['limit'],
            'offset' => $ilps['offset'],
            'firsttext' => '',
            'previoustext' => '',
            'nexttext' => '',
            'lasttext' => '',
            'numbersincludefirstlast' => false,
            'resultcounttextsingular' => get_string('ilp', 'artefact.ilps'),
            'resultcounttextplural' => get_string('ilps', 'artefact.ilps'),
                ));
        $ilps['pagination'] = $pagination['html'];
        $ilps['pagination_js'] = $pagination['javascript'];
    }

    public static function validate(Pieform $form, $values) {
        global $USER;
        if (!empty($values['ilp'])) {
            $id = (int) $values['ilp'];
            $artefact = new ArtefactTypeilp($id);
            if (!$USER->can_edit_artefact($artefact)) {
                $form->set_error('submit', get_string('canteditdontownilp', 'artefact.ilps'));
            }
        }
    }

    public static function submit(Pieform $form, $values) {
        global $USER, $SESSION;

        $new = false;

        if (!empty($values['ilp'])) {
            $id = (int) $values['ilp'];
            $artefact = new ArtefactTypeilp($id);
        } else {
            $artefact = new ArtefactTypeilp();
            $artefact->set('owner', $USER->get('id'));
            $new = true;
        }

        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);
        $artefact->set('points', (int) $values['points']);
        $artefact->commit();

        $SESSION->add_ok_msg(get_string('ilpsavedsuccessfully', 'artefact.ilps'));

        if ($new) {
            redirect('/artefact/ilps/ilp.php?id=' . $artefact->get('id'));
        } else {
            redirect('/artefact/ilps/');
        }
    }

    /**
     * Gets the new/edit ilps pieform
     *
     */
    public static function get_form($ilp = null) {
        require_once(get_config('libroot') . 'pieforms/pieform.php');
        $elements = call_static_method(generate_artefact_class_name('ilp'), 'get_ilpform_elements', $ilp);
        $elements['submit'] = array(
            'type' => 'submitcancel',
            'value' => array(get_string('saveilp', 'artefact.ilps'), get_string('cancel')),
            'goto' => get_config('wwwroot') . 'artefact/ilps/',
        );
        $ilpform = array(
            'name' => empty($ilp) ? 'addilp' : 'editilp',
            'plugintype' => 'artefact',
            'pluginname' => 'unit',
            'validatecallback' => array(generate_artefact_class_name('ilp'), 'validate'),
            'successcallback' => array(generate_artefact_class_name('ilp'), 'submit'),
            'elements' => $elements,
        );

        return pieform($ilpform);
    }

    /**
     * Gets the new/edit fields for the ilp pieform
     *
     */
    public static function get_ilpform_elements($ilp) {
        $elements = array(
            'title' => array(
                'type' => 'text',
                'defaultvalue' => null,
                'title' => get_string('title', 'artefact.ilps'),
                'size' => 30,
                'rules' => array(
                    'required' => true,
                ),
            ),
            'points' => array(
                'type' => 'text',
                'defaultvalue' => null,
                'title' => get_string('points', 'artefact.ilps'),
                'size' => 3,
                'rules' => array(
                    'required' => true,
                    'integer' => true,
                ),
            ),
            'description' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'defaultvalue' => null,
                'title' => get_string('description', 'artefact.ilps'),
            ),
        );

        if (!empty($ilp)) {
            foreach ($elements as $k => $element) {
                $elements[$k]['defaultvalue'] = $ilp->get($k);
            }
            $elements['ilp'] = array(
                'type' => 'hidden',
                'value' => $ilp->id,
            );
        }

        return $elements;
    }

    public function render_self($options) {
        $this->add_to_render_path($options);

        $limit = !isset($options['limit']) ? 30 : (int) $options['limit'];
        $offset = isset($options['offset']) ? intval($options['offset']) : 0;

        $units = ArtefactTypeUnit::get_units($this->id, $offset, $limit);

        $template = 'artefact:ilps:unitrows.tpl';

        $baseurl = get_config('wwwroot') . 'view/artefact.php?artefact=' . $this->id;
        if (!empty($options['viewid'])) {
            $baseurl .= '&view=' . $options['viewid'];
        }

        $pagination = array(
            'baseurl' => $baseurl,
            'id' => 'unit_pagination',
            'datatable' => 'unitlist',
            'jsonscript' => 'artefact/ilps/viewunits.json.php',
        );

        ArtefactTypeUnit::render_units($units, $template, $options, $pagination);

        $smarty = smarty_core();
        $smarty->assign_by_ref('units', $units);
        if (isset($options['viewid'])) {
            $smarty->assign('artefacttitle', '<a href="' . $baseurl . '">' . hsc($this->get('title')) . '</a>');
        } else {
            $smarty->assign('artefacttitle', hsc($this->get('title')));
        }
        $smarty->assign('ilp', $this);

        return array('html' => $smarty->fetch('artefact:ilps:viewilp.tpl'), 'javascript' => '');
    }

}

class ArtefactTypeUnit extends ArtefactType {

    protected $points;
    protected $status;
    protected $targetcompletion;
    protected $datecompleted;

    /**
     * We override the constructor to fetch the extra data.
     *
     * @param integer
     * @param object
     */
    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);

        if ($this->id) {
            if ($pdata = get_record('artefact_ilps_unit', 'artefact', $this->id, null, null, null, null, '*, ' . db_format_tsfield('targetcompletion') . ', ' . db_format_tsfield('datecompleted'))) {
                foreach ($pdata as $name => $value) {
                    if (property_exists($this, $name)) {
                        $this->$name = $value;
                    }
                }
            } else {
                // This should never happen unless the user is playing around with unit IDs in the status bar or similar
                throw new ArtefactNotFoundException(get_string('unitdoesnotexist', 'artefact.ilps'));
            }
        }
    }

    public static function get_links($id) {
        return array();
    }

    public static function get_icon($options = null) {
        
    }

    public static function is_singular() {
        return false;
    }

    /**
     * This method extends ArtefactType::commit() by adding additional data
     * into the artefact_ilps_unit table.
     *
     */
    public function commit() {
        if (empty($this->dirty)) {
            return;
        }

        // Return whether or not the commit worked
        $success = false;

        db_begin();
        $new = empty($this->id);

        parent::commit();

        $this->dirty = true;

        $targetcompletion = $this->get('targetcompletion');
        if (!empty($targetcompletion)) {
            $date = db_format_timestamp($targetcompletion);
        }
        $data = (object) array(
                    'artefact' => $this->get('id'),
                    'points' => $this->get('points'),
                    'status' => $this->get('status'),
                    'targetcompletion' => $date,
                    'datecompleted' => db_format_timestamp($this->get('datecompleted')),
        );

        if ($new) {
            $success = insert_record('artefact_ilps_unit', $data);
        } else {
            $success = update_record('artefact_ilps_unit', $data, 'artefact');
        }

        db_commit();

        $this->dirty = $success ? false : true;

        return $success;
    }

    /**
     * This function extends ArtefactType::delete() by also deleting anything
     * that's in unit.
     */
    public function delete() {
        if (empty($this->id)) {
            return;
        }

        db_begin();
        delete_records('artefact_ilps_unit', 'artefact', $this->id);

        parent::delete();
        db_commit();
    }

    public static function bulk_delete($artefactids) {
        if (empty($artefactids)) {
            return;
        }

        $idstr = join(',', array_map('intval', $artefactids));

        db_begin();
        delete_records_select('artefact_ilps_unit', 'artefact IN (' . $idstr . ')');
        parent::bulk_delete($artefactids);
        db_commit();
    }

    /**
     * Gets the new/edit units pieform
     *
     */
    public static function get_form($parent, $unit = null) {
        require_once(get_config('libroot') . 'pieforms/pieform.php');
        $elements = call_static_method(generate_artefact_class_name('unit'), 'get_unitform_elements', $parent, $unit);
        $elements['submit'] = array(
            'type' => 'submitcancel',
            'value' => array(get_string('saveunit', 'artefact.ilps'), get_string('cancel')),
            'goto' => get_config('wwwroot') . 'artefact/ilps/ilp.php?id=' . $parent,
        );
        $unitform = array(
            'name' => empty($unit) ? 'addunits' : 'editunit',
            'plugintype' => 'artefact',
            'pluginname' => 'unit',
            'validatecallback' => array(generate_artefact_class_name('unit'), 'validate'),
            'successcallback' => array(generate_artefact_class_name('unit'), 'submit'),
            'elements' => $elements,
        );

        return pieform($unitform);
    }

    /**
     * Gets the new/edit fields for the units pieform
     *
     */
    public static function get_unitform_elements($parent, $unit = null) {
        $elements = array(
            'title' => array(
                'type' => 'text',
                'defaultvalue' => null,
                'title' => get_string('unit', 'artefact.ilps'),
                'description' => get_string('titledesc', 'artefact.ilps'),
                'size' => 30,
                'rules' => array(
                    'required' => true,
                ),
            ),
            'status' => array(
                'type' => 'text',
                'size' => 30,
                'defaultvalue' => null,
                'title' => get_string('status', 'artefact.ilps'),
                'description' => get_string('statusdesc', 'artefact.ilps'),
                'rules' => array(
                    'required' => true,
                ),
            ),
            'targetcompletion' => array(
                'type' => 'calendar',
                'caloptions' => array(
                    'showsTime' => false,
                    'ifFormat' => '%Y/%m/%d'
                ),
                'defaultvalue' => null,
                'title' => get_string('targetcompletion', 'artefact.ilps'),
                'description' => get_string('dateformatguide'),
                'rules' => array(
                    'required' => true,
                ),
            ),
            'datecompleted' => array(
                'type' => 'calendar',
                'caloptions' => array(
                    'showsTime' => false,
                    'ifFormat' => '%Y/%m/%d'
                ),
                'defaultvalue' => null,
                'title' => get_string('datecompleted', 'artefact.ilps'),
                'description' => get_string('dateformatguide'),
                'rules' => array(
                    'required' => false,
                ),
            ),
            'points' => array(
                'type' => 'text',
                'size' => '7',
                'defaultvalue' => '0',
                'title' => get_string('points', 'artefact.ilps'),
                'description' => get_string('pointsdesc', 'artefact.ilps'),
                'rules' => array(
                    'integer' => true,
                ),
            ),
        );

        if (!empty($unit)) {
            foreach ($elements as $k => $element) {
                $elements[$k]['defaultvalue'] = $unit->get($k);
            }
            $elements['unit'] = array(
                'type' => 'hidden',
                'value' => $unit->id,
            );
        }

        $elements['parent'] = array(
            'type' => 'hidden',
            'value' => $parent,
        );

        return $elements;
    }

    public static function validate(Pieform $form, $values) {
        global $USER;
        if (!empty($values['unit'])) {
            $id = (int) $values['unit'];
            $artefact = new ArtefactTypeUnit($id);
            if (!$USER->can_edit_artefact($artefact)) {
                $form->set_error('submit', get_string('canteditdontown'));
            }
        }
    }

    public static function submit(Pieform $form, $values) {
        global $USER, $SESSION;

        if (!empty($values['unit'])) {
            $id = (int) $values['unit'];
            $artefact = new ArtefactTypeUnit($id);
        } else {
            $artefact = new ArtefactTypeunit();
            $artefact->set('owner', $USER->get('id'));
            $artefact->set('parent', $values['parent']);
        }

        $artefact->set('title', $values['title']);
        $artefact->set('status', $values['status']);
        $artefact->set('points', $values['points']);
        $artefact->set('targetcompletion', $values['targetcompletion']);
        $artefact->set('datecompleted', $values['datecompleted']);
        $artefact->commit();

        $SESSION->add_ok_msg(get_string('ilpsavedsuccessfully', 'artefact.ilps'));

        redirect('/artefact/ilps/ilp.php?id=' . $values['parent']);
    }

    public static function get_summarypoints($ilp) {
        ($results = get_records_sql_array("
            SELECT a.id, at.artefact AS unit, at.status, at.points, " . db_format_tsfield('targetcompletion') . ", " . db_format_tsfield('datecompleted') . ",
                a.title, a.description, a.parent
                FROM {artefact} a
            JOIN {artefact_ilps_unit} at ON at.artefact = a.id
            WHERE a.artefacttype = 'unit' AND a.parent = ?
            ORDER BY at.targetcompletion DESC", array($ilp)))
                || ($results = array());

        // format the date and calculate grand total of points
        $grandtotalpoints = 0;
        $aquiredpoints = 0;
        $remainingpoints = ArtefactTypeilp::get_points($ilp);


        foreach ($results as $result) {

            $grandtotalpoints = $grandtotalpoints + $result->points;

            if (!empty($result->targetcompletion)) {
                $result->targetcompletion = strftime(get_string('strftimedate'), $result->targetcompletion);
            }

            if (!empty($result->datecompleted)) {
                $result->datecompleted = strftime(get_string('strftimedate'), $result->datecompleted);
                $aquiredpoints = $aquiredpoints + $result->points;
                $remainingpoints = $remainingpoints - $result->points;
            }
        }


        $result = array(
            'grandtotalpoints' => $grandtotalpoints,
            'aquiredpoints' => $aquiredpoints,
            'remainingpoints' => $remainingpoints,
        );

        return $result;
    }

    /**
     * This function returns a list of the current ilps units.
     *
     * @param limit how many units to display per page
     * @param offset current page to display
     * @return array (grandtotalpoints: number, count: integer, data: array)
     * 
     */
    public static function get_units($ilp, $offset = 0, $limit = 30) {

        ($results = get_records_sql_array("
            SELECT a.id, at.artefact AS unit, at.status, at.points, " . db_format_tsfield('targetcompletion') . ", " . db_format_tsfield('datecompleted') . ",
                a.title, a.description, a.parent
                FROM {artefact} a
            JOIN {artefact_ilps_unit} at ON at.artefact = a.id
            WHERE a.artefacttype = 'unit' AND a.parent = ?
            ORDER BY at.targetcompletion DESC", array($ilp), $offset, $limit))
                || ($results = array());

        // format the date and calculate grand total of points
        $grandtotalpoints = 0;
        $aquiredpoints = 0;
        $remainingpoints = ArtefactTypeilp::get_points($ilp);


        foreach ($results as $result) {

            $grandtotalpoints = $grandtotalpoints + $result->points;

            if (!empty($result->targetcompletion)) {
                $result->targetcompletion = strftime(get_string('strftimedate'), $result->targetcompletion);
            }

            if (!empty($result->datecompleted)) {
                $result->datecompleted = strftime(get_string('strftimedate'), $result->datecompleted);
                $aquiredpoints = $aquiredpoints + $result->points;
                $remainingpoints = $remainingpoints - $result->points;
            }
        }


        $result = array(
            'grandtotalpoints' => $grandtotalpoints,
            'aquiredpoints' => $aquiredpoints,
            'remainingpoints' => $remainingpoints,
            'count' => count_records('artefact', 'artefacttype', 'unit', 'parent', $ilp),
            'data' => $results,
            'offset' => $offset,
            'limit' => $limit,
            'id' => $ilp,
        );

        return $result;
    }

    /**
     * Builds the units list table for current ilp
     *
     * @param units (reference)
     */
    public function build_units_list_html(&$units) {
        $summarypoints = ArtefactTypeUnit::get_summarypoints($units['id']);
        $smarty = smarty_core();
        $smarty->assign_by_ref('units', $units);
        $smarty->assign_by_ref('summarypoints', $summarypoints);
        $units['tablerows'] = $smarty->fetch('artefact:ilps:unitslist.tpl');
        $pagination = build_pagination(array(
            'id' => 'unitlist_pagination',
            'class' => 'center',
            'url' => get_config('wwwroot') . 'artefact/ilps/ilp.php?id=' . $units['id'],
            'jsonscript' => 'artefact/ilps/units.json.php',
            'datatable' => 'unitslist',
            'count' => $units['count'],
            'limit' => $units['limit'],
            'offset' => $units['offset'],
            'firsttext' => '',
            'previoustext' => '',
            'nexttext' => '',
            'lasttext' => '',
            'numbersincludefirstlast' => false,
            'resultcounttextsingular' => get_string('unit', 'artefact.ilps'),
            'resultcounttextplural' => get_string('units', 'artefact.ilps'),
                ));
        $units['pagination'] = $pagination['html'];
        $units['pagination_js'] = $pagination['javascript'];
    }

    // @TODO: make blocktype use this too
    public function render_units(&$units, $template, $options, $pagination) {
        $smarty = smarty_core();
        $smarty->assign_by_ref('units', $units);
        $smarty->assign_by_ref('options', $options);
        $units['tablerows'] = $smarty->fetch($template);

        if ($units['limit'] && $pagination) {
            $pagination = build_pagination(array(
                'id' => $pagination['id'],
                'class' => 'center',
                'datatable' => $pagination['datatable'],
                'url' => $pagination['baseurl'],
                'jsonscript' => $pagination['jsonscript'],
                'count' => $units['count'],
                'limit' => $units['limit'],
                'offset' => $units['offset'],
                'numbersincludefirstlast' => false,
                'resultcounttextsingular' => get_string('unit', 'artefact.ilps'),
                'resultcounttextplural' => get_string('units', 'artefact.ilps'),
                    ));
            $units['pagination'] = $pagination['html'];
            $units['pagination_js'] = $pagination['javascript'];
        }
    }

}

?>
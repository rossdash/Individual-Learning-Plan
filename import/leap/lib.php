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

/**
 * Implements LEAP2A import of ilp/unit entries into Mahara
 *
 * Mahara currently only has two levels of ilp, but the exporting
 * system may have more, so the strategy will be to find all the ilps
 * that are not part of another ilp, use those for the top level, with
 * everything else crammed in at the second level.
 */

class LeapImportilps extends LeapImportArtefactPlugin {

    const STRATEGY_IMPORT_AS_ilp = 1;

    // Keep track of ilp ancestors which will become unit parents
    private static $ancestors = array();
    private static $parents = array();

    public static function get_import_strategies_for_entry(SimpleXMLElement $entry, PluginImportLeap $importer) {
        $strategies = array();

        // Mahara can't handle html ilps yet, so don't claim to be able to import them.
        if (PluginImportLeap::is_rdf_type($entry, $importer, 'ilp')
            && (empty($entry->content['type']) || (string)$entry->content['type'] == 'text')) {
            $strategies[] = array(
                'strategy' => self::STRATEGY_IMPORT_AS_ilp,
                'score'    => 90,
                'other_required_entries' => array(),
            );
        }

        return $strategies;
    }

    public static function import_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {

        if ($strategy != self::STRATEGY_IMPORT_AS_ilp) {
            throw new ImportException($importer, 'TODO: get_string: unknown strategy chosen for importing entry');
        }

        $artefactmapping = array();
        $artefactmapping[(string)$entry->id] = self::create_ilp($entry, $importer);
        return $artefactmapping;
    }

    /**
     * Get the id of the ilp entry which ultimately contains this entry
     */
    public static function get_ancestor_entryid(SimpleXMLElement $entry, PluginImportLeap $importer) {
        $entryid = (string)$entry->id;

        if (!isset(self::$ancestors[$entryid])) {
            self::$ancestors[$entryid] = null;
            $child = $entry;

            while ($child) {
                $childid = (string)$child->id;

                if (!isset(self::$parents[$childid])) {
                    self::$parents[$childid] = null;

                    foreach ($child->link as $link) {
                        $href = (string)$link['href'];
                        if ($href != $entryid
                            && $importer->curie_equals($link['rel'], PluginImportLeap::NS_LEAP, 'is_part_of')
                            && $importer->entry_has_strategy($href, self::STRATEGY_IMPORT_AS_ilp, 'ilps')) {
                            self::$parents[$childid] = $href;
                            break;
                        }
                    }
                }

                if (!self::$parents[$childid]) {
                    break;
                }
                if ($child = $importer->get_entry_by_id(self::$parents[$childid])) {
                    self::$ancestors[$entryid] = self::$parents[$childid];
                }
            }
        }

        return self::$ancestors[$entryid];
    }


    /**
     * Creates a ilp or unit from the given entry
     *
     * @param SimpleXMLElement $entry    The entry to create the ilp or unit from
     * @param PluginImportLeap $importer The importer
     * @return array A list of artefact IDs created, to be used with the artefact mapping.
     */
    private static function create_ilp(SimpleXMLElement $entry, PluginImportLeap $importer) {

        // First decide if it's going to be a ilp or a unit depending
        // on whether it has any ancestral ilps.

        if (self::get_ancestor_entryid($entry, $importer)) {
            $artefact = new ArtefactTypeunit();
        }
        else {
            $artefact = new ArtefactTypeilp();
        }

        $artefact->set('title', (string)$entry->title);
        $artefact->set('description', PluginImportLeap::get_entry_content($entry, $importer));
        $artefact->set('owner', $importer->get('usr'));
        if (isset($entry->author->name) && strlen($entry->author->name)) {
            $artefact->set('authorname', $entry->author->name);
        }
        else {
            $artefact->set('author', $importer->get('usr'));
        }
        if ($published = strtotime((string)$entry->published)) {
            $artefact->set('ctime', $published);
        }
        if ($updated = strtotime((string)$entry->updated)) {
            $artefact->set('mtime', $updated);
        }

        $artefact->set('tags', PluginImportLeap::get_entry_tags($entry));

        // Set targetcompletion and points status if we can find them
        if ($artefact instanceof ArtefactTypeunit) {

            $namespaces = $importer->get_namespaces();
            $ns = $importer->get_leap2a_namespace();

            $dates = PluginImportLeap::get_leap_dates($entry, $namespaces, $ns);
            if (!empty($dates['target']['value'])) {
                $targetcompletion = strtotime($dates['target']['value']);
            }
            $artefact->set('targetcompletion', empty($targetcompletion) ? $artefact->get('mtime') : $targetcompletion);

            if ($entry->xpath($namespaces[$ns] . ':status[@' . $namespaces[$ns] . ':stage="points"]')) {
                $artefact->set('points', 1);
            }
        }

        $artefact->commit();

        return array($artefact->get('id'));
    }

    /**
     * Set unit parents
     */
    public static function setup_relationships(SimpleXMLElement $entry, PluginImportLeap $importer) {
        if ($ancestorid = self::get_ancestor_entryid($entry, $importer)) {
            $ancestorids = $importer->get_artefactids_imported_by_entryid($ancestorid);
            $artefactids = $importer->get_artefactids_imported_by_entryid((string)$entry->id);
            if (empty($artefactids[0])) {
                throw new ImportException($importer, 'unit artefact not found: ' . (string)$entry->id);
            }
            if (empty($ancestorids[0])) {
                throw new ImportException($importer, 'ilp artefact not found: ' . $ancestorid);
            }
            $artefact = new ArtefactTypeunit($artefactids[0]);
            $artefact->set('parent', $ancestorids[0]);
            $artefact->commit();
        }
    }

}

?>

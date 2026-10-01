<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_courseaudit\form;

global $CFG;
defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

use local_courseaudit\local\audit_manager;
use moodleform;
use section_info;

/**
 * Course audit options form.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class audit_form extends moodleform {
    /**
     * Define form fields.
     */
    protected function definition(): void {
        $mform = $this->_form;
        $course = $this->_customdata['course'];

        $mform->addElement('hidden', 'courseid', $course->id);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('select', 'mode', get_string('mode', 'local_courseaudit'), [
            'full' => get_string('modefull', 'local_courseaudit'),
            'structure' => get_string('modestructure', 'local_courseaudit'),
            'contentai' => get_string('modecontentai', 'local_courseaudit'),
        ]);
        $mform->setDefault('mode', 'full');

        $sectionoptions = [0 => get_string('allsections', 'local_courseaudit')];
        $modinfo = get_fast_modinfo($course);
        foreach ($modinfo->get_section_info_all() as $section) {
            if ((int)$section->section === 0 && trim((string)$section->name) === '') {
                $name = get_string('sectionzero', 'local_courseaudit');
            } else {
                $name = get_section_name($course, $section);
            }
            $sectionoptions[(int)$section->id] = format_string($name);
        }

        $mform->addElement('select', 'sectionid', get_string('sectionfilter', 'local_courseaudit'), $sectionoptions);
        $mform->setDefault('sectionid', 0);

        $mform->addElement('advcheckbox', 'force', get_string('forcerun', 'local_courseaudit'));
        $mform->addHelpButton('force', 'forcerun', 'local_courseaudit');

        $this->add_action_buttons(false, get_string('runaudit', 'local_courseaudit'));
    }

    /**
     * Validate submitted data.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        if (!in_array($data['mode'] ?? '', audit_manager::MODES, true)) {
            $errors['mode'] = get_string('invalidmode', 'local_courseaudit');
        }

        $sectionid = (int)($data['sectionid'] ?? 0);
        if ($sectionid > 0) {
            $course = $this->_customdata['course'];
            $validsectionids = array_map(
                static fn(section_info $section): int => (int)$section->id,
                get_fast_modinfo($course)->get_section_info_all()
            );
            if (!in_array($sectionid, $validsectionids, true)) {
                $errors['sectionid'] = get_string('invalidsection', 'local_courseaudit');
            }
        }
        return $errors;
    }
}

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

/**
 * Abstract class used as a base for the 3 screens.
 *
 * @package   gradereport_singleview
 * @copyright 2014 Moodle Pty Ltd (http://moodle.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradereport_singleview\local\screen;

use context_course;
use moodle_url;
use html_writer;
use grade_structure;
use grade_grade;
use grade_item;
use stdClass;

defined('MOODLE_INTERNAL') || die;

/**
 * Abstract class used as a base for the 3 screens.
 *
 * @package   gradereport_singleview
 * @copyright 2014 Moodle Pty Ltd (http://moodle.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class screen {

    /** @var int $courseid The id of the course */
    protected $courseid;

    /** @var int $itemid Either a user id or a grade_item id */
    protected $itemid;

    /** @var int $groupid The currently set groupid (if set) */
    protected $groupid;

    /** @var course_context $context The course context */
    protected $context;

    /** @var int $page The page number */
    protected $page;

    /** @var int $perpage Results per page */
    protected $perpage;

    /** @var array $items List of items on the page, they could be users or grade_items */
    protected $items;

    /**
     * Constructor
     *
     * @param int $courseid The course id
     * @param int $itemid The item id
     * @param int $groupid The group id
     */
    public function __construct($courseid, $itemid, $groupid = null) {
        global $DB;

        $this->courseid = $courseid;
        $this->itemid = $itemid;
        $this->groupid = $groupid;

        $this->context = context_course::instance($this->courseid);
        $this->course = $DB->get_record('course', array('id' => $courseid));

        $this->page = optional_param('page', 0, PARAM_INT);
        $this->perpage = optional_param('perpage', 100, PARAM_INT);
        if ($this->perpage > 100) {
            $this->perpage = 100;
        }

        $this->init(empty($itemid));
    }

    /**
     * Cache the grade_structure class
     */
    public function setup_structure() {
        $this->structure = new grade_structure();
        $this->structure->modinfo = get_fast_modinfo($this->course);
    }

    /**
     * Create a nice link from a thing (user or grade_item).
     *
     * @param string $screen
     * @param int $itemid
     * @param bool $display Should we wrap this in an anchor ?
     * @return string The link
     */
    public function format_link($screen, $itemid, $display = null) {
        $url = new moodle_url('/grade/report/singleview/index.php', array(
            'id' => $this->courseid,
            'item' => $screen,
            'itemid' => $itemid,
            'group' => $this->groupid,
        ));

        if ($display) {
            return html_writer::link($url, $display);
        } else {
            return $url;
        }
    }

    /**
     * Get the grade_grade
     *
     * @param grade_item $item The grade_item
     * @param int $userid The user id
     * @return grade_grade
     */
    public function fetch_grade_or_default($item, $userid) {
        $grade = grade_grade::fetch(array(
            'itemid' => $item->id, 'userid' => $userid
        ));

        if (!$grade) {
            $default = new stdClass;

            $default->userid = $userid;
            $default->itemid = $item->id;
            $default->feedback = '';

            $grade = new grade_grade($default, false);
        }

        $grade->grade_item = $item;

        return $grade;
    }

    /**
     * Make the HTML element that toggles all the checkboxes on or off.
     *
     * @param string $key A unique key for this control - inserted in the classes.
     * @return string
     */
    public function make_toggle($key) {
        $attrs = array('href' => '#');

        $all = html_writer::tag('a', get_string('all'), $attrs + array(
            'class' => 'include all ' . $key
        ));

        $none = html_writer::tag('a', get_string('none'), $attrs + array(
            'class' => 'include none ' . $key
        ));

        return html_writer::tag('span', "$all / $none", array(
            'class' => 'inclusion_links'
        ));
    }

    /**
     * Make a toggle link with some text before it.
     *
     * @param string $key A unique key for this control - inserted in the classes.
     * @return string
     */
    public function make_toggle_links($key) {
        return get_string($key, 'gradereport_singleview') . ' ' .
            $this->make_toggle($key);
    }

    /**
     * Get the default heading for the screen.
     *
     * @return string
     */
    public function heading() {
        return get_string('pluginname', 'gradereport_singleview');
    }

    /**
     * Override this to init the screen.
     *
     * @param boolean $selfitemisempty True if no item has been selected yet.
     */
    public abstract function init($selfitemisempty = false);

    /**
     * Get the type of items in the list.
     *
     * @return string
     */
    public abstract function item_type();

    /**
     * Get the entire screen as a string.
     *
     * @return string
     */
    public abstract function html();

    /**
     * Does this screen support paging?
     *
     * @return bool
     */
    public function supports_paging() {
        return true;
    }

    /**
     * Default pager
     *
     * @return string
     */
    public function pager() {
        return '';
    }

    /**
     * Initialise the js for this screen.
     */
    public function js() {
        global $PAGE;

        $module = array(
            'name' => 'gradereport_singleview',
            'fullpath' => '/grade/report/singleview/js/singleview.js',
            'requires' => array('base', 'dom', 'event', 'event-simulate', 'io-base')
        );

        $PAGE->requires->js_init_call('M.gradereport_singleview.init', array(), false, $module);
    }

    /**
     * Process the data from a form submission.
     *
     * @param array $data
     * @return array of warnings
     */
    public function process($data) {
        $warnings = array();

        $fields = $this->definition();

        foreach ($data as $varname => $throw) {
            if (preg_match("/(\w+)_(\d+)_(\d+)/", $varname, $matches)) {
                $itemid = $matches[2];
                $userid = $matches[3];
            } else {
                continue;
            }

            $gradeitem = grade_item::fetch(array(
                'id' => $itemid, 'courseid' => $this->courseid
            ));

            if (preg_match('/^old[oe]{1}/', $varname)) {
                $elementname = preg_replace('/^old/', '', $varname);
                if (!isset($data->$elementname)) {
                    $data->$elementname = false;
                }
            }

            if (!in_array($matches[1], $fields)) {
                continue;
            }

            if (!$gradeitem) {
                continue;
            }

            $grade = $this->fetch_grade_or_default($gradeitem, $userid);

            $classname = '\\gradereport_singleview\\local\\ui\\' . $matches[1];
            $element = new $classname($grade);

            $name = $element->get_name();
            $oldname = "old$name";

            $posted = $data->$name;

            $format = $element->determine_format();

            if ($format->is_textbox() and trim($data->$name) === '') {
                $data->$name = null;
            }

            // Same value; skip.
            if (isset($data->$oldname) && $data->$oldname == $posted) {
                continue;
            }

            $msg = $element->set($posted);

            // Optional type.
            if (!empty($msg)) {
                $warnings[] = $msg;
            }
        }

        // Some post-processing.
        $eventdata = new stdClass;
        $eventdata->warnings = $warnings;
        $eventdata->post_data = $data;
        $eventdata->instance = $this;

        return $eventdata->warnings;
    }

    /**
     * By default there are no options.
     * @return array
     */
    public function options() {
        return array();
    }

    /**
     * Should we show the group selector?
     * @return bool
     */
    public function display_group_selector() {
        return true;
    }

    /**
     * Should we show the next prev selector?
     * @return bool
     */
    public function supports_next_prev() {
        return true;
    }
}

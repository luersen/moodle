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
 * Steps definitions related to mod_scorm.
 *
 * @package    mod_scorm
 * @category   test
 * @copyright  2014 Vignesh P
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given,
    Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ExpectationException as ExpectationException,
    Behat\Mink\Exception\DriverException as DriverException,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;

/**
 * Steps definitions related to mod_scorm.
 *
 * @package    mod_scorm
 * @category   test
 * @copyright  2014 Vignesh P
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_scorm extends behat_base {
    /**
     * Checks if the Course Structure is on the left/Dropdown/disabled/hidden.
     *
     * We should be on the player page.
     *
     * @Then /^I should see Course Structure "(?P<position_string>(?:[^"]|\\")*)"$/
     * @param string $position
     */
    public function i_see_course_structure($position) {
        switch ($position) {
            case 'Left':
                $item = $this->find("css", sprintf("#scorm_toc"));
                return !($item->hasClass("disabled"));
                break;
            
            case 'Dropdown':
                $item = $this->find("css", sprintf('#single_select"%s"[name="scoid"]'));
                return $item->hasClass("singleselect");
                break;

            case 'Disabled':
                $toc = $this->find("css", sprintf("#scorm_toc"));
                $toc_toggle = $this->find("css", sprintf("#scorm_toc"));
                return $toc->hasClass("disabled") && $toc_toggle->hasClass("disabled");
                break;
            
            case 'Hidden':
                $toc = $this->find("css", sprintf("#scorm_toc"));
                return $toc->hasClass("disabled");
                break;
        }
    }

    /**
     * Checks if the Course Navigation is not present.
     *
     * We should be on the player page.
     *
     * @Then /^I should not see Course Navigation Buttons$/
     */
    public function i_should_not_see_course_navigation_buttons() {
        $item = $this->find("css", sprintf("#scorm_navpanel"));
        return $item->getHtml() == "";
    }
}

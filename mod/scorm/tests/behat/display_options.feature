@mod @mod_scorm @_file_upload @_switch_frame
Feature: Setting Display Options
  In order to set display options
  As a teacher
  I need to add scorm activity to a course

  @javascript @wip
  Scenario: Add a scorm activity to a course and set display options
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@asd.com |
      | student1 | Student | 1 | student1@asd.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    When I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "SCORM package" to section "1"
    And I set the following fields to these values:
      | Name | Scorm Pantallas |
      | Description | Scorm Pantallas Description |
      | Display course structure in player | Hidden |
    And I upload "mod/scorm/tests/packages/scorm10pantallas.zip" file to "Package file" filemanager
    And I should see "Display course structure in player"
    And I click on "Save and display" "button"
    Then I should see "Scorm Pantallas"
    And I should see "Normal"
    And I should see "Preview"
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    And I follow "Scorm Pantallas"
    And I should see "Normal"
    And I press "Enter"
    Then I should see Course Structure "Hidden"
    And I follow "Exit activity"
    And I log out
    And I log in as "teacher1"
    And I follow "Course 1"
    And I follow "Scorm Pantallas"
    And I follow "Edit settings"
    And I set the following fields to these values:
      | Display course structure in player | In a drop down menu |
    And I click on "Save and display" "button"
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    And I follow "Scorm Pantallas"
    And I should see "Normal"
    And I press "Enter"
    Then I should see Course Structure "Dropdown"
    And I follow "Exit activity"
    And I log out
    And I log in as "teacher1"
    And I follow "Course 1"
    And I follow "Scorm Pantallas"
    And I follow "Edit settings"
    And I set the following fields to these values:
      | Display course structure in player | Disabled |
    And I click on "Save and display" "button"
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    And I follow "Scorm Pantallas"
    And I should see "Normal"
    And I press "Enter"
    Then I should see Course Structure "Disabled"
    And I follow "Exit activity"
    And I log out
    And I log in as "teacher1"
    And I follow "Course 1"
    And I follow "Scorm Pantallas"
    And I follow "Edit settings"
    And I set the following fields to these values:
      | Display course structure in player | To the side |
      | Show Navigation | No |
    And I click on "Save and display" "button"
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    And I follow "Scorm Pantallas"
    And I should see "Normal"
    And I press "Enter"
    Then I should see Course Structure "Left"
    And I should not see Course Navigation Buttons
    And I follow "Exit activity"
    And I log out
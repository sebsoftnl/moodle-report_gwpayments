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
 * Lib functions
 *
 * File         lib.php
 * Encoding     UTF-8
 *
 * @package     report_gwpayments
 * @copyright   2024 RvD
 * @author      RvD <helpdesk@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_gwpayments_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/gwpayments:view', $context)) {
        $url = new moodle_url('/report/gwpayments/index.php', ['courseid' => $course->id]);
        $txt = get_string('reportname', 'report_gwpayments');
        $navigation->add($txt, $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

/**
 * Adds nodes to category navigation
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param context $context The context of the coursecategory
 * @return void|null return null if we don't want to display the node.
 */
function report_gwpayments_extend_navigation_category_settings($navigation, $context) {
    if (has_capability('report/gwpayments:overview', $context)) {
        $url = new moodle_url('/report/gwpayments/index.php', ['categoryid' => $context->instanceid]);
        $txt = get_string('reportname', 'report_gwpayments');
        $navigation->add($txt, $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree tree
 * @param stdClass $user user
 * @param bool $iscurrentuser
 * @param stdClass $course course
 *
 * @return bool
 */
function report_gwpayments_myprofile_navigation(\core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    if (isguestuser($user) || !isloggedin()) {
        return false;
    }
    $context = \context_user::instance($user->id);
    if (has_capability('report/gwpayments:userview', $context)) {
        $url = new moodle_url('/report/gwpayments/index.php', ['userid' => $user->id]);
        $txt = get_string('reportname', 'report_gwpayments');
        $node = new \core_user\output\myprofile\node('reports', 'gwpayments', $txt, null, $url);
        $tree->add_node($node);
    }
    return true;
}

/**
 * Return a list of page types.
 *
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 * @return array
 */
function report_gwpayments_page_type_list($pagetype, $parentcontext, $currentcontext) {
    return [
        '*' => new \lang_string('page-x', 'pagetype'),
        'report-*' => new \lang_string('page-report-x', 'pagetype'),
        'report-gwpayments-*' => new \lang_string('page-report-gwpayments-x', 'report_gwpayments'),
        'report-gwpayments-index' => new \lang_string('page-report-gwpayments-index', 'report_gwpayments'),
        'report-gwpayments-course' => new \lang_string('page-report-gwpayments-course', 'report_gwpayments'),
        'report-gwpayments-user' => new \lang_string('page-report-gwpayments-user', 'report_gwpayments'),
    ];
}

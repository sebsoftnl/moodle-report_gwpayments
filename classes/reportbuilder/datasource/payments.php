<?php
// This file is part of the tool_certificate plugin for Moodle - http://moodle.org/
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
 * Payments datasource
 *
 * File         payments.php
 * Encoding     UTF-8
 *
 * @package     report_gwpayments
 *
 * @copyright   2024 RvD
 * @author      RvD <helpdesk@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace report_gwpayments\reportbuilder\datasource;

use core_course\reportbuilder\local\entities\enrolment;
use core_reportbuilder\datasource;
use core_reportbuilder\local\entities\{course, user};
use report_gwpayments\reportbuilder\local\entities\payment;

/**
 * Payments datasource
 *
 * @package     report_gwpayments
 * @copyright   2024 RvD
 * @author      RvD <helpdesk@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class payments extends datasource {
    /**
     * Initialise report
     */
    protected function initialise(): void {

        $main = new payment();
        $mainalias = $main->get_table_alias('payments');
        $cusagealias = $main->get_table_alias('enrol_gwpayments_cusage');
        $mainname = $main->get_entity_name();
        $this->set_main_table('payments', $mainalias);
        $this->add_entity($main);
        $this->add_base_condition_simple("{$mainalias}.component", 'enrol_gwpayments');

        $course = new course();
        $coursealias = $course->get_table_alias('course');
        $coursename = $course->get_entity_name();

        $enrol = new enrolment();
        $enrolalias = $enrol->get_table_alias('enrol');
        $userenrolalias = $enrol->get_table_alias('user_enrolments');

        $user = new user();
        $useralias = $user->get_table_alias('user');
        $username = $user->get_entity_name();
        $this->add_entity($user->add_join(
            "JOIN {user} {$useralias} ON {$useralias}.id = {$mainalias}.userid
             INNER JOIN {user_enrolments} {$userenrolalias} ON {$userenrolalias}.userid = {$mainalias}.userid
             INNER JOIN {enrol} {$enrolalias} ON {$enrolalias}.id = {$userenrolalias}.enrolid
                 AND {$enrolalias}.id = {$mainalias}.itemid AND {$mainalias}.itemid IS NOT NULL"
        ));

        $this->add_entity($course->add_join(
            "INNER JOIN {course} {$coursealias} ON {$coursealias}.id = {$enrolalias}.courseid
                AND {$coursealias}.id = {$enrolalias}.courseid"
        ));

        $this->add_columns_from_entity($mainname);
        $this->add_filters_from_entity($mainname);
        $this->add_conditions_from_entity($mainname);

        $this->add_columns_from_entity($username);
        $this->add_filters_from_entity($username);
        $this->add_conditions_from_entity($username);

        $this->add_columns_from_entity($coursename);
        $this->add_filters_from_entity($coursename);
        $this->add_conditions_from_entity($coursename);
    }

    /**
     * Get the visible name of the report.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('reportname', 'report_gwpayments');
    }

    /**
     * Return the columns that will be added to the report once is created
     *
     * @return string[]
     */
    public function get_default_columns(): array {
        return [
            'payment:accountid',
            'payment:gateway',
            'user:fullnamewithlink',
            'course:fullname',
            'payment:amount',
            'payment:currency',
            'payment:code',
            'payment:typ',
            'payment:value',
            'payment:discount',
            'payment:timecreated',
        ];
    }

    /**
     * Return the filters that will be added to the report once is created
     *
     * @return string[]
     */
    public function get_default_filters(): array {
        return [
            'course:fullname',
            'user:fullname',
            'payment:gateway',
            'payment:amount',
            'payment:currency',
            'payment:timecreated',
        ];
    }

    /**
     * Return the conditions that will be added to the report once is created
     *
     * @return string[]
     */
    public function get_default_conditions(): array {
        return [];
    }
}

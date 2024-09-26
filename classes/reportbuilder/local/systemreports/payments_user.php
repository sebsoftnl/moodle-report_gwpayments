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
 * User payments
 *
 * File         payments_user.php
 * Encoding     UTF-8
 *
 * @package     report_gwpayments
 *
 * @copyright   2024 RvD
 * @author      RvD <helpdesk@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_gwpayments\reportbuilder\local\systemreports;

use core_course\reportbuilder\local\entities\enrolment;
use core_reportbuilder\local\entities\{
    user,
    course
};
use core_reportbuilder\local\report\action;
use core_reportbuilder\local\helpers\database;
use core_reportbuilder\system_report;
use report_gwpayments\reportbuilder\local\entities\payment;

/**
 * User payments
 *
 * @package     report_gwpayments
 * @copyright   2024 RvD
 * @author      RvD <helpdesk@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class payments_user extends system_report {

    /**
     * Initialise report, we need to set the main table, load our entities and set columns/filters
     */
    protected function initialise(): void {
        $context = $this->get_context();

        $main = new payment();
        $mainalias = $main->get_table_alias('payments');
        $this->set_main_table('payments', $mainalias);
        $this->add_entity($main);
        $this->add_base_fields("{$mainalias}.id");
        $this->add_base_condition_simple("{$mainalias}.userid", $context->instanceid);
        $this->add_base_condition_simple("{$mainalias}.component", 'enrol_gwpayments');

        $enrol = new enrolment();
        $enrolalias = $enrol->get_table_alias('enrol');
        $userenrolalias = $enrol->get_table_alias('user_enrolments');
        $enrol->add_join("INNER JOIN {user_enrolments} {$userenrolalias} ON {$userenrolalias}.userid = {$mainalias}.userid");

        $this->add_entity($enrol);
        $this->add_base_condition_simple("{$userenrolalias}.userid", $context->instanceid);

        $course = new course();
        $coursealias = $course->get_table_alias('course');
        $course
            ->add_joins($enrol->get_joins())
            ->add_join("INNER JOIN {enrol} {$enrolalias} ON
                {$enrolalias}.id = {$userenrolalias}.enrolid AND
                {$enrolalias}.id = {$mainalias}.itemid")
            ->add_join("INNER JOIN {course} {$coursealias} ON {$coursealias}.id = {$enrolalias}.courseid");
        $this->add_entity($course);

        $this->add_columns();
        $this->set_downloadable(true, get_string('payments'));
    }

    /**
     * Validates access to view this report
     *
     * @return bool
     */
    protected function can_view(): bool {
        return has_capability('report/gwpayments:view', $this->get_context());
    }

    /**
     * Get the visible name of the report
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('reportname', 'report_gwpayments');
    }

    /**
     * Adds the columns we want to display in the report
     */
    public function add_columns(): void {
        $this->add_columns_from_entities(
                [
                    'payment:gateway',
                ]
        );

        // Add COST column from enrol table.
        $enrol = new enrolment();
        $enrolalias = $enrol->get_table_alias('enrol');
        $this->add_column((new \core_reportbuilder\local\report\column('cost', new \lang_string('cost')
                                , $enrol->get_entity_name()))
                        ->add_field("{$enrolalias}.cost")
                        ->add_callback(function (?string $value): string {
                            return ($value === '') ? '0' : number_format(floatval($value), 2);
                        }));

        $this->add_columns_from_entities(
                [
                    'payment:discount',
                    'payment:amount',
                    'payment:currency',
                    'payment:code',
                    'payment:typ',
                    'payment:value',
                    'payment:timecreated',
                    'course:coursefullnamewithlink',
                ]
        );

        if ($column = $this->get_column('course:coursefullnamewithlink')) {
            $column->set_title(new \lang_string('course'));
        }

        $this->set_initial_sort_column('payment:timecreated', SORT_DESC);
    }
}

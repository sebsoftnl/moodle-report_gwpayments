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
 * Payment entity class implementation.
 *
 * File         payment.php
 * Encoding     UTF-8
 *
 * @package     report_gwpayments
 *
 * @copyright   2024 RvD
 * @author      RvD <helpdesk@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_gwpayments\reportbuilder\local\entities;

use core_reportbuilder\local\filters\{date, duration, number, select, text, autocomplete};
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\{column, filter};
use lang_string;

/**
 * Payment entity class implementation.
 *
 * @package     report_gwpayments
 * @copyright   2024 RvD
 * @author      RvD <helpdesk@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class payment extends base {
    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return [
            'payments' => 'pa',
            'enrol_gwpayments_cusage' => 'gwpcu',
        ];
    }

    /**
     * The default title for this entity in the list of columns/conditions/filters in the report builder
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('payments');
    }

    /**
     * Initialise the entity
     *
     * @return base
     */
    public function initialise(): base {
        $tablealias = $this->get_table_alias('payments');
        $name = $this->get_entity_name();

        $usagetablealias = $this->get_table_alias('enrol_gwpayments_cusage');

        $this->add_join("LEFT JOIN {enrol_gwpayments_cusage} {$usagetablealias} ON
            {$usagetablealias}.paymentid = {$tablealias}.id AND
            {$usagetablealias}.userid = {$tablealias}.userid
            ");

        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }

        // All the filters defined by the entity can also be used as conditions.
        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this
                ->add_filter($filter)
                ->add_condition($filter);
        }

        return $this;
    }

    /**
     * Database tables that this entity uses
     *
     * @return string[]
     */
    protected function get_default_tables(): array {
        return [
            'payments',
            'enrol_gwpayments_cusage',
        ];
    }

    /**
     * Returns list of all available columns
     *
     * @return column[]
     */
    private function get_all_columns(): array {
        $tablealias = $this->get_table_alias('payments');
        $name = $this->get_entity_name();

        $usagetablealias = $this->get_table_alias('enrol_gwpayments_cusage');

        // Accountid column.
        $columns[] = (new column('accountid', new lang_string('name'), $name))
            ->add_joins($this->get_joins())
            ->add_join("LEFT JOIN {payment_accounts} pac ON {$tablealias}.accountid = pac.id")
            ->set_type(column::TYPE_TEXT)
            ->add_field("pac.name")
            ->set_is_sortable(true);

        // Component column.
        $columns[] = (new column('component', new lang_string('plugin'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$tablealias}.component")
            ->set_is_sortable(true);

        // Paymentarea column.
        $columns[] = (new column('paymentarea', new lang_string('area', 'report_gwpayments'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$tablealias}.paymentarea")
            ->set_is_sortable(true);

        // Itemid column.
        $columns[] = (new column('itemid', new lang_string('itemid', 'report_gwpayments'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$tablealias}.itemid")
            ->set_is_sortable(true);

        // Gateway column.
        $columns[] = (new column('gateway', new lang_string('type_paygw', 'plugin'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$tablealias}.gateway")
            ->set_is_sortable(true);

        // Amount column.
        $columns[] = (new column('amount', new lang_string('paidamount', 'report_gwpayments'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$tablealias}.amount")
            ->set_is_sortable(true)
            ->add_callback(function (?string $value): string {
                return ($value === '') ? '0' : number_format(floatval($value), 2);
            });

        // Currency column.
        $columns[] = (new column('currency', new lang_string('currency'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$tablealias}.currency")
            ->set_is_sortable(true);

        // Date column.
        $columns[] = (new column('timecreated', new lang_string('date'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$tablealias}.timecreated")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate'], get_string('strftimedatetimeshortaccurate', 'core_langconfig'));

        // Coupon usage.
        $columns[] = (new column('code', new lang_string('code', 'report_gwpayments'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$usagetablealias}.code")
            ->set_is_sortable(true);

        $columns[] = (new column('typ', new lang_string('discounttype', 'report_gwpayments'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$usagetablealias}.typ")
            ->set_is_sortable(true);

        $columns[] = (new column('value', new lang_string('value', 'report_gwpayments'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_field("{$usagetablealias}.value")
            ->set_is_sortable(true);

        $columns[] = (new column('discount', new lang_string('discount', 'report_gwpayments'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_field("{$usagetablealias}.discount")
            ->set_is_sortable(true)
            ->add_callback(function (?float $value): string {
                return ($value === '') ? '0' : number_format(floatval($value), 2);
            });

        $columns[] = (new column('originalcost', new lang_string('originalcost', 'report_gwpayments'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->add_field("COALESCE({$usagetablealias}.discount, 0) + {$tablealias}.amount", 'grossamount');

        return $columns;
    }

    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    private function get_all_filters(): array {

        $tablealias = $this->get_table_alias('payments');
        $name = $this->get_entity_name();

        $ownermethod = static function (): array {
            global $DB;
            return $DB->get_records_menu('payment_accounts', ['enabled' => true]);
        };

        // Name filter.
        $filters[] = (new filter(select::class, 'accountid', new lang_string('name'), $name, "{$tablealias}.accountid"))
            ->add_joins($this->get_joins())
            ->set_options_callback($ownermethod);

        // Component filter.
        $filters[] = (new filter(text::class, 'component', new lang_string('plugin'), $name, "{$tablealias}.component"))
            ->add_joins($this->get_joins());

        // Gateway filter.
        $filters[] = (new filter(text::class, 'gateway', new lang_string('type_paygw', 'plugin'), $name, "{$tablealias}.gateway"))
            ->add_joins($this->get_joins());

        // Currency filter.
        $filters[] = (new filter(text::class, 'currency', new lang_string('currency'), $name, "{$tablealias}.currency"))
            ->add_joins($this->get_joins());

        // Amount filter.
        $filters[] = (new filter(text::class, 'amount', new lang_string('cost'), $name, "{$tablealias}.amount"))
            ->add_joins($this->get_joins());

        // Date filter.
        $filters[] = (new filter(date::class, 'timecreated', new lang_string('date'), $name, "{$tablealias}.timecreated"))
            ->add_joins($this->get_joins())
            ->set_limited_operators([date::DATE_ANY, date::DATE_RANGE, date::DATE_PREVIOUS, date::DATE_CURRENT]);

        return $filters;
    }
}

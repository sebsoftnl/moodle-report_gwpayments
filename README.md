# moodle-report_gwpayments

Report specifically written for use with enrol_gwpayments.

Based on the original report_payments but edited specifically for enrol_gwpayments
reports (and _only_ enrol_gwpayments as this is a hardcoded condition in the report).

## Installation

* Unzip code into the report/gwpayments directory
* Log into Moodle as administrator.
* Visit Site admin => Notifications.

## Important note

The report will display payment details as well as applied discount codes along with their details.
_However_, due to the nature of the payment subsystem in Moodle, which lacks any and every
way to provide the payment gateway with details of any relevant kind (it only stores "the" payment
but also seems to assume payments are _always_ synchronous. The reality of this assumption is just
 plain wrong but alas....).
The gwpayments enrolment plugin, which is required to being able to install this report,
will try to add those details and track the discount code usage.
With the above shortcomings of Moodle's payment subsystem, this only works for payment flows that are
100% synchronous. Any asynchronous processing most likely leads to losing the details of
the applied discount code and will therefore not be tracked.

This is a limit that's very well known by us and cannot be easily remedied with the correct code base.

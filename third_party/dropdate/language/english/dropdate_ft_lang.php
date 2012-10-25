<?php

/**
 * DropDate fieldtype English language strings.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Dropdate
 */

$lang = array(

  // The basics.
  'dropdate_fieldtype_name'        => 'DropDate',
  'dropdate_fieldtype_description' => 'Better date selection.',

  // Settings.
  'label__format'      => 'Save date as',
  'label__format_unix' => 'UNIX timestamp',
  'label__format_ymd'  => 'YMD timestamp',
  'label__range'       => 'Year range',
  'label__time'        => 'Show time?',
  'label__time_no'     => "Don't show time",
  'label__time_5'      => 'Yes, in 5 minute intervals',
  'label__time_15'     => 'Yes, in 15 minute intervals',

  'message__no_global_settings' => "<p>We lured you here under false pretences.
    <p>There are no global settings, but ExpressionEngine won't update a fieldtype unless you visit its global settings page. Obviously.</p>
    <p>Just click the 'Submit' button, and get back to your nice life where you don't have to deal with this nonsense for a living.</p>",

  // Field.
  'label__day'      => 'Day',
  'label__month'    => 'Month',
  'label__year'     => 'Year',
  'label__hour'     => 'Hour',
  'label__minute'   => 'Minute',
  'label__month_1'  => 'January',
  'label__month_2'  => 'February',
  'label__month_3'  => 'March',
  'label__month_4'  => 'April',
  'label__month_5'  => 'May',
  'label__month_6'  => 'June',
  'label__month_7'  => 'July',
  'label__month_8'  => 'August',
  'label__month_9'  => 'September',
  'label__month_10' => 'October',
  'label__month_11' => 'November',
  'label__month_12' => 'December',

  // Template tag errors.
  'error__invalid_format_parameter'   => 'An invalid format parameter was passed to DropDate.',
  'error__invalid_saved_date'         => 'Invalid saved date.',
  'error__invalid_timezone_parameter' => 'An invalid timezone parameter was passed to DropDate.',
  'error__template_error_prefix'      => '[ERROR]: ',
  'error__template_notice_prefix'     => '[NOTICE]: ',

  // Exceptions.
  'exception__invalid_saved_date'     => 'Invalid saved date.',
  'exception__invalid_submitted_date' => 'Invalid submitted date.',
  'exception__invalid_year_settings'  => 'Invalid year settings.',
  'exception__missing_year_settings'  => 'Missing year settings.',

  // Template tag notices.
  'notice__percentage_sign_deprecated' => 'The DropDate format parameter no longer supports the % sign. Backward compatiblity may be removed in a future version; please update your template code.',

  // All done.
  '' => ''
);


/* End of file      : dropdate_ft_lang.php */
/* File location    : third_party/dropdate/language/english/dropdate_ft_lang.php */

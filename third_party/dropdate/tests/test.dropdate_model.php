<?php if ( ! defined('BASEPATH')) exit('Invalid file request');

/**
 * DropDate model tests.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Dropdate
 */

require_once PATH_THIRD .'dropdate/models/dropdate_model.php';

class Test_dropdate_model extends Testee_unit_test_case {

  private $_extension_class;
  private $_module_class;
  private $_namespace;
  private $_package_name;
  private $_package_title;
  private $_package_version;
  private $_subject;


  /* --------------------------------------------------------------
   * PUBLIC METHODS
   * ------------------------------------------------------------ */

  /**
   * Constructor.
   *
   * @access  public
   * @return  void
   */
  public function setUp()
  {
    parent::setUp();

    $this->_namespace       = 'com.example';
    $this->_package_name    = 'MY_package';
    $this->_package_title   = 'My Package';
    $this->_package_version = '1.0.0';

    $this->_extension_class = 'My_package_ext';
    $this->_module_class    = 'My_package';

    $this->_subject = new Dropdate_model($this->_package_name,
      $this->_package_title, $this->_package_version, $this->_namespace);
  }


  /* --------------------------------------------------------------
   * PACKAGE TESTS
   * ------------------------------------------------------------ */
  
  public function test__get_package_theme_url__pre_240_works()
  {
    if (defined('URL_THIRD_THEMES'))
    {
      $this->pass();
      return;
    }

    $package    = strtolower($this->_package_name);
    $theme_url  = 'http://example.com/themes/';
    $full_url   = $theme_url .'third_party/' .$package .'/';

    $this->EE->config->expectOnce('slash_item', array('theme_folder_url'));
    $this->EE->config->setReturnValue('slash_item', $theme_url);

    $this->assertIdentical($full_url, $this->_subject->get_package_theme_url());
  }


  public function test__get_site_id__returns_site_id_as_integer()
  {
    $site_id = '100';

    $this->EE->config->expectOnce('item', array('site_id'));
    $this->EE->config->setReturnValue('item', $site_id);

    $this->assertIdentical((int) $site_id, $this->_subject->get_site_id());
  }


  public function test__update_array_from_input__ignores_unknown_keys_and_updates_known_keys_and_preserves_unaltered_keys()
  {
    $base_array = array(
      'first_name'  => 'John',
      'last_name'   => 'Doe',
      'gender'      => 'Male',
      'occupation'  => 'Unknown'
    );

    $update_array = array(
      'dob'         => '1941-05-24',
      'first_name'  => 'Bob',
      'last_name'   => 'Dylan',
      'occupation'  => 'Writer'
    );

    $expected_result = array(
      'first_name'  => 'Bob',
      'last_name'   => 'Dylan',
      'gender'      => 'Male',
      'occupation'  => 'Writer'
    );

    $this->assertIdentical($expected_result,
      $this->_subject->update_array_from_input($base_array, $update_array));
  }


  /* --------------------------------------------------------------
   * ADD-ON TESTS
   * ------------------------------------------------------------ */
  
  public function test__get_minutes__uses_one_minute_increments_if_show_time_is_not_an_integer()
  {
    $expected_result = array();

    for ($count = 0; $count < 60; $count++)
    {
      $expected_result[$count] = str_pad($count, 2, '0', STR_PAD_LEFT);
    }
  
    $settings = array('show_time' => 'no');
    $this->_subject->set_field_settings($settings);

    $this->assertIdentical($expected_result, $this->_subject->get_minutes());
  }

  
  public function test__get_minutes__uses_five_minute_increments_if_show_time_is_5()
  {
    $expected_result = array();

    for ($count = 0; $count < 60; $count += 5)
    {
      $expected_result[$count] = str_pad($count, 2, '0', STR_PAD_LEFT);
    }
  
    $settings = array('show_time' => '5');
    $this->_subject->set_field_settings($settings);

    $this->assertIdentical($expected_result, $this->_subject->get_minutes());
  }

  
  public function test__get_minutes__uses_fifteen_minute_increments_if_show_time_is_15()
  {
    $expected_result = array();

    for ($count = 0; $count < 60; $count += 15)
    {
      $expected_result[$count] = str_pad($count, 2, '0', STR_PAD_LEFT);
    }
  
    $settings = array('show_time' => '15');
    $this->_subject->set_field_settings($settings);

    $this->assertIdentical($expected_result, $this->_subject->get_minutes());
  }

  
  public function test__get_years__throws_exception_if_invalid_year_format()
  {
    $message   = 'EPIC FAIL!';
    $exception = new DropDateException_InvalidFieldSettings($message);

    $this->EE->lang->expectOnce('line');
    $this->EE->lang->returns('line', $message);
  
    $settings = array('year_from' => '1999-ish', 'year_to' => '2012');
    $this->_subject->set_field_settings($settings);

    $this->expectException($exception);
    $this->_subject->get_years();
  }


  public function test__get_years__works_with_simple_year_declarations()
  {
    $expected_result = array(
      2001 => 2001,
      2002 => 2002,
      2003 => 2003,
      2004 => 2004,
      2005 => 2005,
      2006 => 2006,
      2007 => 2007,
      2008 => 2008,
      2009 => 2009,
      2010 => 2010
    );

    $settings = array('year_from' => '2001', 'year_to' => '2010');
    $this->_subject->set_field_settings($settings);

    $this->assertIdentical($expected_result, $this->_subject->get_years());
  }


  public function test__get_years__works_with_offset_year_declarations()
  {
    $current_year    = intval(date('Y'));
    $expected_result = array();

    for ($count = $current_year - 5; $count <= $current_year + 5; $count++)
    {
      $expected_result[$count] = $count;
    }

    $settings = array('year_from' => 'now-5', 'year_to' => 'now+5');
    $this->_subject->set_field_settings($settings);

    $this->assertIdentical($expected_result, $this->_subject->get_years());
  }


  public function test__get_years__can_count_backwards()
  {
    $current_year    = intval(date('Y'));
    $expected_result = array();

    for ($count = $current_year + 5; $count >= $current_year - 5; $count--)
    {
      $expected_result[$count] = $count;
    }

    $settings = array('year_from' => 'now+5', 'year_to' => 'now-5');
    $this->_subject->set_field_settings($settings);

    $this->assertIdentical($expected_result, $this->_subject->get_years());
  }


  public function test__parse_field_data__handles_an_empty_string()
  {
    $expected_result = array(
      'year'   => Dropdate_model::NO_VALUE,
      'month'  => Dropdate_model::NO_VALUE,
      'day'    => Dropdate_model::NO_VALUE,
      'hour'   => Dropdate_model::NO_VALUE,
      'minute' => Dropdate_model::NO_VALUE
    );
  
    $this->assertIdentical($expected_result
      ,$this->_subject->parse_field_data(''));
  }


  public function test__parse_field_data__handles_an_associative_array()
  {
    $field_data = array(
      'year'   => '1969',
      'month'  => '2',
      'day'    => '19',
      'hour'   => '13',
      'minute' => '25'
    );
  
    $expected_result = array(
      'year'   => 1969,
      'month'  => 2,
      'day'    => 19,
      'hour'   => 13,
      'minute' => 25
    );

    $this->assertIdentical($expected_result
      ,$this->_subject->parse_field_data($field_data));
  }


  public function test__parse_field_data__handles_a_saved_unix_string()
  {
    $now = new DateTime('1969-03-14 15:35:00', new DateTimeZone('UTC'));

    $expected_result = array(
      'year'   => intval($now->format('Y')),
      'month'  => intval($now->format('n')),
      'day'    => intval($now->format('j')),
      'hour'   => intval($now->format('G')),
      'minute' => intval($now->format('i'))
    );
  
    $this->_subject->set_field_settings(
      array('date_format' => Dropdate_model::UNIX_DATE));

    $this->assertIdentical($expected_result
      ,$this->_subject->parse_field_data($now->format('U')));
  }


  public function test__parse_field_data__handles_a_saved_ymd_string()
  {
    $now = new DateTime('now', new DateTimeZone('UTC'));

    $expected_result = array(
      'year'   => intval($now->format('Y')),
      'month'  => intval($now->format('n')),
      'day'    => intval($now->format('j')),
      'hour'   => intval($now->format('G')),
      'minute' => intval($now->format('i'))
    );
  
    $this->_subject->set_field_settings(
      array('date_format' => Dropdate_model::YMD_DATE));

    $this->assertIdentical($expected_result
      ,$this->_subject->parse_field_data($now->format(DateTime::W3C)));
  }


  public function test__parse_field_data__throws_exception_if_invalid_saved_unix_string()
  {
    $message   = 'EPIC FAIL!';
    $exception = new DropDateException_InvalidSavedDate($message);

    $this->EE->lang->expectOnce('line');
    $this->EE->lang->returns('line', $message);
  
    $this->_subject->set_field_settings(
      array('date_format' => Dropdate_model::UNIX_DATE));

    $this->expectException($exception);
    $this->_subject->parse_field_data('2012-01-02');
  }


  public function test__parse_field_data__throws_exception_if_invalid_saved_ymd_string()
  {
    $message   = 'EPIC FAIL!';
    $exception = new DropDateException_InvalidSavedDate($message);

    $this->EE->lang->expectOnce('line');
    $this->EE->lang->returns('line', $message);
  
    $this->_subject->set_field_settings(
      array('date_format' => Dropdate_model::YMD_DATE));

    $this->expectException($exception);
    $this->_subject->parse_field_data('1234567890');
  }


  public function test__prep_submitted_data_for_save__throws_exception_if_missing_data()
  {
    $field_data = array('year' => '1999', 'month' => '2');

    $message = 'MUCHOS DISASTRE!';

    $this->EE->lang->expectOnce('line');
    $this->EE->lang->returns('line', $message);

    $exception = new DropDateException_InvalidSubmittedDate($message);
    $this->expectException($exception);
  
    $this->_subject->prep_submitted_data_for_save($field_data);
  }
  

  public function test__prep_submitted_data_for_save__throws_exception_if_invalid_data()
  {
    $field_data = array('year' => '1999', 'month' => '2', 'day' => 'null');

    $message = 'MUCHOS DISASTRE!';

    $this->EE->lang->expectOnce('line');
    $this->EE->lang->returns('line', $message);

    $exception = new DropDateException_InvalidSubmittedDate($message);
    $this->expectException($exception);
  
    $this->_subject->prep_submitted_data_for_save($field_data);
  }


  public function test__prep_submitted_data_for_save__returns_valid_unix_timestamp()
  {
    $field_data = array(
      'year'  => '1969',
      'month' => '3',
      'day'   => '14'
    );

    $settings = array('date_format' => Dropdate_model::UNIX_DATE);
    $this->_subject->set_field_settings($settings);

    $date = new DateTime('1969-03-14', new DateTimeZone('UTC'));
    $expected_result = $date->format('U');
  
    $this->assertIdentical($expected_result,
      $this->_subject->prep_submitted_data_for_save($field_data));
  }
  
  
  public function test__prep_submitted_data_for_save__returns_valid_unix_timestamp_with_hours_and_minutes()
  {
    $field_data = array(
      'year'   => '1969',
      'month'  => '3',
      'day'    => '14',
      'hour'   => '9',
      'minute' => '35'
    );

    $settings = array('date_format' => Dropdate_model::UNIX_DATE);
    $this->_subject->set_field_settings($settings);

    $date = new DateTime('1969-03-14 09:35:00', new DateTimeZone('UTC'));
    $expected_result = $date->format('U');
  
    $this->assertIdentical($expected_result,
      $this->_subject->prep_submitted_data_for_save($field_data));
  }
  
  
  public function test__prep_submitted_data_for_save__returns_valid_ymd_string()
  {
    $field_data = array(
      'year'  => '1969',
      'month' => '3',
      'day'   => '14'
    );

    $settings = array('date_format' => Dropdate_model::YMD_DATE);
    $this->_subject->set_field_settings($settings);

    $date = new DateTime('1969-03-14', new DateTimeZone('UTC'));
    $expected_result = $date->format(DateTime::W3C);
  
    $this->assertIdentical($expected_result,
      $this->_subject->prep_submitted_data_for_save($field_data));
  }
  
  
  public function test__prep_submitted_data_for_save__returns_valid_ymd_string_with_hours_and_minutes()
  {
    $field_data = array(
      'year'   => '1969',
      'month'  => '3',
      'day'    => '14',
      'hour'   => '8',
      'minute' => '5'
    );

    $settings = array('date_format' => Dropdate_model::YMD_DATE);
    $this->_subject->set_field_settings($settings);

    $date = new DateTime('1969-03-14 08:05:00', new DateTimeZone('UTC'));
    $expected_result = $date->format(DateTime::W3C);
  
    $this->assertIdentical($expected_result,
      $this->_subject->prep_submitted_data_for_save($field_data));
  }
  
  

}


/* End of file      : test.dropdate_model.php */
/* File location    : third_party/dropdate/tests/test.dropdate_model.php */

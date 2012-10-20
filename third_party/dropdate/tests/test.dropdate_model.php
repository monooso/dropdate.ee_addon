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
  
  public function test__get_years__throws_exception_if_missing_settings()
  {
    $message   = 'OH NOES!';
    $exception = new Exception($message);
    $settings  = array('not_helpful' => 'wibble');

    $this->EE->lang->expectOnce('line');
    $this->EE->lang->returns('line', $message);
  
    $this->expectException($exception);
    $this->_subject->get_years($settings);
  }


  public function test__get_years__throws_exception_if_invalid_year_format()
  {
    $message   = 'EPIC FAIL!';
    $exception = new Exception($message);

    $settings = array(
      'year_from' => '1999-ish',
      'year_to'   => '2012'
    );
  
    $this->EE->lang->expectOnce('line');
    $this->EE->lang->returns('line', $message);
  
    $this->expectException($exception);
    $this->_subject->get_years($settings);
  }


  public function test__get_years__works_with_simple_year_declarations()
  {
    $settings = array(
      'year_from' => '2001',
      'year_to'   => '2010'
    );

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
  
    $this->assertIdentical($expected_result, $this->_subject->get_years($settings));
  }


  public function test__get_years__works_with_offset_year_declarations()
  {
    $settings = array(
      'year_from' => 'now-5',
      'year_to'   => 'now+5'
    );

    $current_year    = intval(date('Y'));
    $expected_result = array();

    for ($count = $current_year - 5; $count <= $current_year + 5; $count++)
    {
      $expected_result[$count] = $count;
    }

    $this->assertIdentical($expected_result, $this->_subject->get_years($settings));
  }


  public function test__get_years__can_count_backwards()
  {
    $settings = array(
      'year_from' => 'now+5',
      'year_to'   => 'now-5'
    );

    $current_year    = intval(date('Y'));
    $expected_result = array();

    for ($count = $current_year + 5; $count >= $current_year - 5; $count--)
    {
      $expected_result[$count] = $count;
    }

    $this->assertIdentical($expected_result, $this->_subject->get_years($settings));
  }
  

}


/* End of file      : test.dropdate_model.php */
/* File location    : third_party/dropdate/tests/test.dropdate_model.php */

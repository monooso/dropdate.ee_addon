<?php if ( ! defined('BASEPATH')) exit('Invalid file request');

/**
 * DropDate fieldtype tests.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Dropdate
 */

require_once PATH_FT .'EE_Fieldtype.php';
require_once PATH_THIRD .'dropdate/ft.dropdate.php';
require_once PATH_THIRD .'dropdate/models/dropdate_model.php';

class Test_dropdate_ft extends Testee_unit_test_case {

  protected $_default_settings;
  protected $_model;
  protected $_subject;


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

    // Generate the mock model.
    Mock::generate('Dropdate_model',
      get_class($this) .'_mock_model');

    /**
     * The subject loads the models using $this->EE->load->model().
     * Because the Loader class is mocked, that does nothing, so we
     * can just assign the mock models here.
     */

    $this->EE->dropdate_model = $this->_get_mock('model');

    $this->_default_settings = array(
      'date_format' => Dropdate_model::UNIX_DATE,
      'year_from'   => '1995',
      'year_to'     => '2025',
      'show_time'   => 'no'
    );

    // Required in subject constructor.
    $this->_model = $this->EE->dropdate_model;
    $this->_model->returns('get_default_field_settings', $this->_default_settings);

    $this->_subject = new Dropdate_ft();
  }


  public function test__save_settings__updates_post_data_with_default_settings_and_returns_array()
  {
    $this->_test_save_settings_method('save_settings');
  }


  public function test__save_var_settings__functions_in_the_same_way_as_save_settings()
  {
    $this->_test_save_settings_method('save_var_settings');
  }


  public function test__display_field__retrieves_dropdown_data_from_model()
  {
    $saved_data = '';

    $settings = array(
      'date_format' => Dropdate_model::UNIX_DATE,
      'year_from'   => '2000',
      'year_to'     => 'now+10',
      'show_time'   => '15'
    );

    $years   = array('2000' => '2000', '2001' => '2001', '2002' => '2002');
    $months  = array('1' => 'Jan', '2' => 'Feb', '3' => 'Mar');
    $days    = array('1' => '01', '2' => '02', '3' => '03');
    $hours   = array('0' => '00', '1' => '01', '2' => '02');
    $minutes = array('0' => '00', '15' => '15', '30' => '30');

    $this->_subject->settings = $settings;

    $this->EE->load->expectOnce('helper', array('form'));

    $this->_model->expectOnce('get_years');
    $this->_model->expectOnce('get_months');
    $this->_model->expectOnce('get_days');
    $this->_model->expectOnce('get_hours');
    $this->_model->expectOnce('get_minutes');

    $this->_model->returns('get_years', $years);
    $this->_model->returns('get_months', $months);
    $this->_model->returns('get_days', $days);
    $this->_model->returns('get_hours', $hours);
    $this->_model->returns('get_minutes', $minutes);
  
    $this->_subject->display_field($saved_data);
  }



  /* --------------------------------------------------------------
   * PROTECTED METHODS
   * ------------------------------------------------------------ */
  
  /**
   * This is probably terribly bad practise, but it works, and prevents a load 
   * of duplicate code. The various 'save settings' methods all do exactly the 
   * same thing, so our expectations of them are identical. This method does the 
   * testing, and simply accepts the name of the method we're testing.
   *
   * @access  protected
   * @param   string    $method_name    The method to test.
   * @return  void
   */
  protected function _test_save_settings_method($method_name)
  {
    // The POSTed data.
    $post_data = array(
      'date_format' => 'Wibble',
      'year_from'   => 'now-10',
      'invalid'     => 'Do not use'
    );

    // The POSTed settings, with unknown keys removed, and missing keys added.
    $post_settings = array(
      'date_format' => 'Wibble',
      'year_from'   => 'now-10',
      'year_to'     => FALSE,
      'show_time'   => FALSE
    );

    $expected_result = array(
      'date_format' => 'Wibble',    // No validation is performed on this.
      'year_from'   => 'now-10',
      'year_to'     => '2020',
      'show_time'   => 'no'         // Default.
    );

    // POST data.
    $this->EE->input->expectCallCount('post', 4);
    $this->EE->input->returns('post', $post_data['date_format'], array('date_format'));
    $this->EE->input->returns('post', $post_data['year_from'], array('year_from'));
    $this->EE->input->returns('post', FALSE, array('year_to'));
    $this->EE->input->returns('post', FALSE, array('show_time'));

    // Model merging.
    $this->_model->expectOnce('update_array_from_input',
      array($this->_subject->default_settings, $post_settings));

    $this->_model->returns('update_array_from_input', $expected_result);

    // Run the tests.
    $actual_result = $this->_subject->$method_name();

    ksort($expected_result);
    ksort($actual_result);

    $this->assertIdentical($expected_result, $actual_result);
  }
  

}


/* End of file      : test.ft_dropdate.php */
/* File location    : third_party/dropdate/tests/test.ft_dropdate.php */
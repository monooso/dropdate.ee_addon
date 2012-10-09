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

  private $_model;
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

    // Generate the mock model.
    Mock::generate('Dropdate_model',
      get_class($this) .'_mock_model');

    /**
     * The subject loads the models using $this->EE->load->model().
     * Because the Loader class is mocked, that does nothing, so we
     * can just assign the mock models here.
     */

    $this->EE->dropdate_model = $this->_get_mock('model');

    $this->_model   = $this->EE->dropdate_model;
    $this->_subject = new Dropdate_ft();
  }


}


/* End of file      : test.ft_dropdate.php */
/* File location    : third_party/dropdate/tests/test.ft_dropdate.php */

<?php if ( ! defined('BASEPATH')) exit('Direct script access not allowed');

/**
 * DropDate fieldtype.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Dropdate
 */

// Thanks to the  public $info property, we need to load the config here. Bah.
require_once dirname(__FILE__) .'/config.php';

class Dropdate_ft extends EE_Fieldtype {

  private $_model;

  /**
   * Stupid EE forces us to do this here, rather than calling the appropriate
   * model methods from the Constructor.
   */

  public $info = array(
    'name'    => OPTIONS_TITLE,
    'version' => OPTIONS_VERSION
  );


  /* --------------------------------------------------------------
   * PUBLIC METHODS
   * ------------------------------------------------------------ */

  /**
   * Constructor.
   *
   * @access  public
   * @param   mixed     $settings     Extension settings.
   * @return  void
   */
  public function __construct()
  {
    $this->EE =& get_instance();

    $this->EE->load->add_package_path(PATH_THIRD .'dropdate/');
    $this->EE->lang->loadfile('dropdate_ft', 'dropdate');

    // Load the model.
    $this->EE->load->model('dropdate_model');
    $this->_model = $this->EE->dropdate_model;
  }


  /**
   * Tidies up after one or more entries are deleted.
   *
   * @access public
   * @param  array $entry_ids The IDs of the deleted entries.
   * @return void
   */
  public function delete(Array $entry_ids)
  {

  }


  /**
   * Displays the fieldtype on the Publish / Edit page.
   *
   * @access public
   * @param  string $data Previously saved field data.
   * @return string
   */
  public function display_field($data = '')
  {

  }


  /**
   * Displays the fieldtype settings form.
   *
   * @access public
   * @param  array $settings Previously-saved settings.
   * @return string
   */
  public function display_settings(Array $settings = array())
  {

  }


  /**
   * Installs the fieldtype, and sets the default values.
   *
   * @access public
   * @return array
   */
  public function install()
  {
    return $this->_model->install_fieldtype();
  }


  /**
   * Performs additional processing after the entry has been saved.
   *
   * @access public
   * @param  string $data The submitted field data.
   * @return void
   */
  public function post_save($data)
  {

  }


  /**
   * Processes the field data in preparation for the "replace tag" method(s).
   * Performing the prep work here minimises the overhead when a template
   * contains multiple fieldtype tags.
   *
   * @access public
   * @param  string $data The fieldtype data.
   * @return mixed  The prepped data.
   */
  public function pre_process($data)
  {

  }


  /**
   * Displays the fieldtype in a template.
   *
   * @access public
   * @param  string $data    The saved field data.
   * @param  array  $params  The tag parameters.
   * @param  string $tagdata The tag data (for tag pairs).
   * @return string The modified tagdata.
   */
  public function replace_tag($data, Array $params = array(), $tagdata = '')
  {

  }


  /**
   * Prepares the field data for saving to the databasae.
   *
   * @access public
   * @param  string $data The submitted field data.
   * @return string The data to save.
   */
  public function save($data)
  {

  }


  /**
   * Saves the fieldtype settings.
   *
   * @access public
   * @param  array $settings The submitted settings.
   * @return array
   */
  public function save_settings(Array $settings = array())
  {

  }


  /**
   * Uninstalls the fieldtype.
   *
   * @access public
   * @return void
   */
  public function uninstall()
  {
    $this->_model->uninstall_fieldtype();
  }


  /**
   * Validates the submitted field data.
   *
   * @access public
   * @param  string $data The submitted field data.
   * @return bool
   */
  public function validate($data)
  {

  }


  /* --------------------------------------------------------------
   * LOW VARIABLES
   * ------------------------------------------------------------ */

  /**
   * Displays the input field on the Low Variables home page.
   *
   * @access public
   * @param  string $var_data The current variable data.
   * @return string The input field HTML.
   */
  public function display_var_field($var_data)
  {

  }


  /**
   * Displays the Low Variables fieldtype settings form.
   *
   * @access public
   * @param  array  $var_settings Previously saved settings.
   * @return array  An array containing the name / label, and the form elements.
   */
  public function display_var_settings(Array $var_settings = array())
  {

  }


  /**
   * Displays the Low Variable in a template.
   *
   * @access public
   * @param  string $var_data The Low Variable field data.
   * @param  Array  $params   The tag parameters.
   * @param  string $tagdata  The tag data (for tag pairs).
   * @return string The modified tag data.
   */
  public function display_var_tag($var_data, Array $params, $tagdata)
  {

  }


  /**
   * Performs additional processing after the Low Variable has been saved.
   *
   * @access public
   * @param  string $var_data The submitted Low Variable data.
   * @return void
   */
  public function post_save_var($var_data)
  {

  }


  /**
   * Modifies the Low Variables field data before it is saved to the database.
   *
   * @access public
   * @param  string $var_data The submitted Low Variable field data.
   * @return string The field data to save to the database.
   */
  public function save_var_field($var_data)
  {

  }


  /**
   * Modifies the Low Variables settings data before it is saved to the
   * database.
   *
   * @access public
   * @param  array  $var_settings The submitted Low Variable settings.
   * @return array  The settings data to be saved to the database.
   */
  public function save_var_settings(Array $var_settings = array())
  {

  }


  /* --------------------------------------------------------------
   * MATRIX
   * ------------------------------------------------------------ */


}


/* End of file      : ft.dropdate.php */
/* File location    : third_party/dropdate/ft.dropdate.php */

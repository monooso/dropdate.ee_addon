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

  // Constants.
  const DROPDATE_FMT_UNIX = 'unix';
  const DROPDATE_FMT_YMD  = 'ymd';

  // Protected properties.
  protected $_model;

  // Public properties.
  public $default_settings;

  // Have to do this here. EE won't let us call the model methods from the 
  // constructor.
  public $info = array(
    'name'    => DROPDATE_TITLE,
    'version' => DROPDATE_VERSION
  );

  public $postpone_saves;


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

    // Set the public properties.
    $this->default_settings = array(
      'date_format' => self::DROPDATE_FMT_UNIX,
      'year_from'   => '1900',
      'year_to'     => '2020',
      'show_time'   => 'no'
    );

    $this->postpone_saves = TRUE;

    // Hidden config. variable.
    $this->_time_format = $this->EE->config->item('time_format');
  }


  /**
   * Displays the fieldtype on the Publish / Edit page (or within SafeCracker).
   *
   * @access public
   * @param  string   $saved_data   Previously saved field data.
   * @return string
   */
  public function display_field($saved_data = '')
  {
    return $this->_display_field_or_cell($saved_data, FALSE);
  }


  /**
   * Displays the fieldtype settings form.
   *
   * @access public
   * @param  array    $settings     Previously-saved settings.
   * @return string
   */
  public function display_settings(Array $settings = array())
  {
    $settings_html = $this->_build_settings($settings);

    foreach ($settings_html AS $settings_row)
    {
      $this->EE->table->add_row('<strong>' .$settings_row[0] .'</strong>',
        $settings_row[1]);
    }
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
   * @param  string   $data   The submitted field data.
   * @return string   The data to save.
   */
  public function save($data)
  {

  }


  /**
   * Saves the fieldtype settings.
   *
   * @access public
   * @param  array  $settings   The submitted settings.
   * @return array
   */
  public function save_settings(Array $settings = array())
  {
    return $this->_update_default_settings_with_post_data();
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
   * @param  array  $var_settings   Previously saved settings.
   * @return array  An array containing the name / label, and the form elements.
   */
  public function display_var_settings(Array $var_settings = array())
  {
    return $this->_build_settings($var_settings);
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
   * @param  array  $var_settings   The submitted Low Variable settings.
   * @return array  The settings data to be saved to the database.
   */
  public function save_var_settings(Array $var_settings = array())
  {
    return $this->_update_default_settings_with_post_data();
  }



  /* --------------------------------------------------------------
   * MATRIX
   * ------------------------------------------------------------ */

  /**
   * Displays the Matrix cell on the Publish / Edit page (or within 
   * SafeCracker).
   *
   * @access  public
   * @param   string    $saved_data    Previously-saved cell data.
   * @return  string
   */
  public function display_cell($saved_data = '')
  {
    return $this->_display_field_or_cell($saved_data, TRUE);
  }


  /**
   * Displays custom Matrix cell settings on the Create / Edit Field page.
   *
   * @access  public
   * @param   array    $settings    Previously-saved settings.
   * @return  void
   */
  public function display_cell_settings(Array $settings = array())
  {
    return $this->_build_settings($settings);
  }
  

  /**
   * Modifies a Matrix cell's POST data, before it is saved to the database.
   *
   * @access  public
   * @param   mixed   $post_data  The POST data.
   * @param   array   $settings   The cell settings.
   * @param   mixed   $entry_id   The entry ID, if postponed saving is enabled, 
   *                              or FALSE.
   * @return  string
   */
  public function save_cell($post_data = '', Array $settings = array(),
    $entry_id = FALSE
  )
  {
    
  }



  /* --------------------------------------------------------------
   * PROTECTED METHODS
   * ------------------------------------------------------------ */

  /**
   * Builds the settings form controls, and returns them as a nested array.
   *
   * @access  protected
   * @param   array    $saved_settings    Previously-saved settings.
   * @return  array
   */
  protected function _build_settings(Array $saved_settings = array())
  {
    $settings = array_merge($this->default_settings,
      array_intersect_key($saved_settings, $this->default_settings));

    $return = array();

    // Format (UNIX or YMD).
    $format_index = $this->EE->lang->line('label__format');

    $format_html = '<label style="margin-right: 20px;">'
      .form_radio('date_format', self::DROPDATE_FMT_UNIX, ($settings['date_format'] == self::DROPDATE_FMT_UNIX))
      .' ' .$this->EE->lang->line('label__format_unix')
      .'</label>';

    $format_html .= '<label>'
      .form_radio('date_format', self::DROPDATE_FMT_YMD, ($settings['date_format'] == self::DROPDATE_FMT_YMD))
      .' ' .$this->EE->lang->line('label__format_ymd')
      .'</label>';

    $return[] = array($format_index, $format_html);

    // Year range.
    $year_index = $this->EE->lang->line('label__range');

    $year_html = form_input(array(
      'name'  => 'year_from',
      'value' => $settings['year_from'],
      'style' => 'width: 75px;'));

    $year_html .= NBS .NBS .'to' .NBS .NBS;

    $year_html .= form_input(array(
      'name'  => 'year_to',
      'value' => $settings['year_to'],
      'style' => 'width: 75px;'));

    $return[] = array($year_index, $year_html);

    // Time.
    $time_index = $this->EE->lang->line('label__time');

    $time_html = form_dropdown('show_time', array(
      'no' => $this->EE->lang->line('label__time_no'),
      '5'  => $this->EE->lang->line('label__time_5'),
      '15' => $this->EE->lang->line('label__time_15')
    ), $settings['show_time']);

    $return[] = array($time_index, $time_html);

    return $return;
  }
  

  /**
   * Displays the fieldtype or Matrix cell on the Publish / Edit page (or within 
   * SafeCracker).
   *
   * @access  protected
   * @param   string    $saved_data   Previously-saved data.
   * @param   bool      $is_cell      Are we processing a Matrix cell?
   * @return  string
   */
  protected function _display_field_or_cell($saved_data = '', $is_cell = FALSE)
  {
    $this->EE->load->helper('form');

    $saved_day   = '21';
    $saved_month = '10';
    $saved_year  = '2008';

    $field_name = $is_cell ? $this->cell_name : $this->field_name;
    $field_html = '';

    // Days.
    $days_data = $this->_model->get_days();
    $days_data = array_merge(
      array('null' => $this->EE->lang->line('label__day'))
      ,$days_data
    );

    $field_html .= form_dropdown("{$field_name}[day]", $days_data, $saved_day);

    // Months.
    $months_data = $this->_model->get_months();
    $months_data = array_merge(
      array('null' => $this->EE->lang->line('label__month'))
      ,$months_data
    );

    $field_html .= NBS .form_dropdown("{$field_name}[month]", $months_data,
      $saved_month);

    // Years.
    $years_data = $this->_model->get_years($this->settings);
    $years_data = array_merge(
      array('null' => $this->EE->lang->line('label__year'))
      ,$years_data
    );

    $field_html .= NBS. form_dropdown("{$field_name}[year]", $years_data,
      $saved_year);

    // Time.

    return $field_html;
  }


  /**
   * Updates the default settings with any POST data, and returns the array.
   *
   * @access  protected
   * @return  array
   */
  protected function _update_default_settings_with_post_data()
  {
    $post_settings = array();

    foreach ($this->default_settings AS $setting_key => $setting_value)
    {
      $post_settings[$setting_key] = $this->EE->input->post($setting_key);
    }

    // Delete any FALSE values.
    array_filter($post_settings);

    return $this->_model->update_array_from_input($this->default_settings,
      $post_settings);
  }


}


/* End of file      : ft.dropdate.php */
/* File location    : third_party/dropdate/ft.dropdate.php */

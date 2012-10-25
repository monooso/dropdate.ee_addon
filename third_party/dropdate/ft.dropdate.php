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
    parent::__construct();

    $this->EE->load->add_package_path(PATH_THIRD .'dropdate/');
    $this->EE->lang->loadfile('dropdate_ft', 'dropdate');

    // Load the model.
    $this->EE->load->model('dropdate_model');
    $this->_model = $this->EE->dropdate_model;

    // Set the public properties.
    $this->default_settings = $this->_model->get_default_field_settings();

    // @TODO : do I still need this?
    $this->postpone_saves = TRUE;
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
    // Do nothing.
  }


  /**
   * Displays the fieldtype in a template.
   *
   * @access public
   * @param  string $saved_data   The saved field data.
   * @param  array  $tag_params   The tag parameters.
   * @return string The modified tagdata.
   */
  public function replace_tag($saved_data, Array $tag_params = array())
  {
    // Hugely annoying that we can't set this in the constructor.
    $this->_model->set_field_settings($this->settings);

    // Be prepared.
    $error_prefix  = $this->EE->lang->line('error__template_error_prefix');
    $notice_prefix = $this->EE->lang->line('error__template_notice_prefix');

    try
    {
      $date = $this->_model->convert_field_data_to_datetime($saved_data);

      if ( ! $date instanceof DateTime)
      {
        $message = $this->EE->lang->line('error__invalid_saved_date');

        $this->EE->TMPL->log_item($error_prefix .$message);
        $this->_model->log_message($message, 3);

        return $message;
      }
    }
    catch (Exception $e)
    {
      $message = $e->getMessage();

      $this->EE->TMPL->log_item($error_prefix .$message);
      $this->_model->log_message($message, 3);

      return $message;
    }

    $default_params = $this->_model->get_default_template_tag_parameters();
    $params         = $this->_model->update_array_from_input($default_params,
                        array_filter($tag_params));

    // Instantiate the timezone instance.
    try
    {
      $timezone = new DateTimeZone($params['timezone']);
    }
    catch (Exception $e)
    {
      $message = $this->EE->lang->line('error__invalid_timezone_parameter');

      $this->EE->TMPL->log_item($error_prefix .$message);
      $this->_model->log_message($message, 3);

      return $message;
    }

    // Convert the date to the desired timezone.
    if ( ! $date->setTimezone($timezone))
    {
      $message = $this->EE->lang->line('error__invalid_timezone_parameter');

      $this->EE->TMPL->log_item($error_prefix .$message);
      $this->_model->log_message($message, 3);

      return $message;
    }

    // If a custom language has not been specified, or the formatting string is 
    // not 'localised', we're done.
    if ($params['language'] == $default_params['language']
      OR ! preg_match('/(?<!\\\)%?\w{1}/', $params['format'])
    )
    {
      return $date->format($params['format']);
    }

    /**
     * TRICKY:
     * If the template tag needs to be localised into a language other than 
     * English, we've got some work to do.
     *
     * Unfortunately, the DateTime::format method does not support localisation 
     * of Date/Time strings. Adding to our woes, the strftime function relies on 
     * setlocale, and uses completely different formatting codes to the general 
     * date function. Stupid PHP.
     *
     * By default, ExpressionEngine does what is probably the only sane thing in 
     * this situation, which is to treat standard date formatting strings 
     * preceded by a % sign as 'localised' date formatting strings. We follow 
     * their lead.
     *
     * Unfortunately, EE does not support arbitrary localisation into the 
     * language of our choice; it bases its decisions on the currently-selected 
     * CP language. Even worse, the Localize::convert_timestamp method doesn't 
     * just localise the strings, it insists on localising the time too. Bloody 
     * fuck Jean.
     *
     * So, in an attempt to fashion something vaguelly useful out of this 
     * stinking manure mountain, we do the following:
     *
     * 1. Determine if there we have an EE language pack for the requested 
     *    language. If not, we're sunk. We just use the DateTime::format method.
     * 2. If the EE language pack exists, we load it, and then attempt to parse 
     *    the string. Of course, we can't just "load" it using 
     *    EE->lang->loadfile, because that will load whatever language is 
     *    specified in the CP. Instead we have to seek it out, and manually 
     *    include it. Any missing language strings fall back to the default 
     *    DateTime::format output.
     * 3. Drink.
     */

    $lang_file = APPPATH ."language/{$params['language']}/core_lang.php";

    if ( ! is_file($lang_file) OR ((@include $lang_file) === FALSE))
    {
      $params['format'] = preg_replace('/(?<!\\\)%/', '', $params['format']);
      return $date->format($params['format']);
    }

    /**
     * Huzzah, we have a language file, and this method now has a local $lang 
     * variable. Now the hard work begins.
     */

    // Explode the formatting string into its constituent parts.
    $formatted_date = '';
    $pattern        = '/(\\\%.{1}|(?<!\\\)%?[^\\\]{1})/';

    preg_match_all($pattern, $params['format'], $format_match);

    foreach ($format_match[1] AS $format_character)
    {
      // No point translating something if we don't need to.
      if ( ! preg_match('/^%(\w){1}$/', $format_character, $character_match))
      {
        $formatted_date .= $date->format($format_character);
        continue;
      }

      $en_string = $date->format($character_match[1]);

      $formatted_date .= (array_key_exists($en_string, $lang))
        ? $lang[$en_string] : $en_string;
    }

    return $formatted_date;
  }


  /**
   * Prepares the field data for saving to the database.
   *
   * @access public
   * @param  array    $data   The submitted field data.
   * @return string   The data to save.
   */
  public function save($data)
  {
    return $this->_save($data);
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
    // Do nothing.
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
    // @TODO
  }


  /* --------------------------------------------------------------
   * LOW VARIABLES
   * ------------------------------------------------------------ */

  /**
   * Displays the input field on the Low Variables home page.
   *
   * @access public
   * @param  string   $saved_data   The current variable data.
   * @return string   The input field HTML.
   */
  public function display_var_field($saved_data = '')
  {
    return $this->_display_field_or_cell($saved_data, FALSE);
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
    return $this->replace_tag($var_data, $params);
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
    return $this->_save($var_data);
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
    return $this->_save($post_data);
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
      .form_radio('date_format', Dropdate_model::UNIX_DATE, ($settings['date_format'] == Dropdate_model::UNIX_DATE))
      .' ' .$this->EE->lang->line('label__format_unix')
      .'</label>';

    $format_html .= '<label>'
      .form_radio('date_format', Dropdate_model::YMD_DATE, ($settings['date_format'] == Dropdate_model::YMD_DATE))
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
   * @param   string    $field_data   Previously-saved or submitted data.
   * @param   bool      $is_cell      Are we processing a Matrix cell?
   * @return  string
   */
  protected function _display_field_or_cell($field_data = '', $is_cell = FALSE)
  {
    $this->_model->set_field_settings($this->settings);
    $this->EE->load->helper('form');

    $no_value   = Dropdate_model::NO_VALUE;
    $field_name = $is_cell ? $this->cell_name : $this->field_name;
    $field_html = '';
    $notice     = array();

    // Parse the field data.
    try
    {
      $saved_date = $this->_model->parse_field_data($field_data);
    }
    catch (Exception $e)
    {
      $this->_model->log_message($e->getMessage(), 3);

      $notice[]   = $e->getMessage();
      $saved_date = $this->_model->parse_field_data('');
    }

    // Days.
    $days_data = $this->_model->get_days();
    $days_data = array($no_value => $this->EE->lang->line('label__day'))
      + $days_data;

    $field_html .= form_dropdown("{$field_name}[day]", $days_data,
      $saved_date['day']);

    // Months.
    $months_data = $this->_model->get_months();
    $months_data = array($no_value => $this->EE->lang->line('label__month'))
      + $months_data;

    $field_html .= NBS .form_dropdown("{$field_name}[month]", $months_data,
      $saved_date['month']);

    // Years.
    $years_data = $this->_model->get_years();
    $years_data = array($no_value => $this->EE->lang->line('label__year'))
      + $years_data;

    $field_html .= NBS .form_dropdown("{$field_name}[year]", $years_data,
      $saved_date['year']);


    if ($this->settings['show_time'] != 'no')
    {
      $field_html .= '&nbsp;&nbsp;at&nbsp;';

      // Hours.
      $hours_data = $this->_model->get_hours();
      $hours_data = array($no_value => $this->EE->lang->line('label__hour'))
        + $hours_data;

      $field_html .= NBS .form_dropdown("{$field_name}[hour]", $hours_data,
        $saved_date['hour']);

      // Minute.
      $minutes_data = $this->_model->get_minutes();
      $minutes_data = array($no_value => $this->EE->lang->line('label__minute'))
        + $minutes_data;

      $field_html .= NBS .form_dropdown("{$field_name}[minute]", $minutes_data,
        $saved_date['minute']);
    }

    // Append an error messages.
    if ($notice)
    {
      $field_html .= '<div class="notice">'
        .implode($notice, '<br />') .'</div>';
    }

    return $field_html;
  }


  /**
   * Preps the submitted data, and returns it for saving.
   *
   * @access  protected
   * @param   array    $field_data    The submitted data.
   * @return  string
   */
  protected function _save($field_data)
  {
    $this->_model->set_field_settings($this->settings);

    try
    {
      return $this->_model->prep_submitted_data_for_save($field_data);
    }
    catch (Exception $e)
    {
      $this->_model->log_message($e->getMessage(), 3);
      return '';
    }
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

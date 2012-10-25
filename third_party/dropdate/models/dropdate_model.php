<?php if ( ! defined('BASEPATH')) exit('Direct script access not allowed');

/**
 * DropDate 'Package' model.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Dropdate
 */

require_once dirname(__FILE__) .'/../classes/dropdate_exceptions.php';
require_once dirname(__FILE__) .'/../config.php';

class Dropdate_model extends CI_Model {

  // Constants.
  const UNIX_DATE = 'unix';
  const YMD_DATE  = 'ymd';
  const NO_VALUE  = 'null';    // Used for non-date values in dropdowns.

  protected $EE;
  protected $_default_field_settings;
  protected $_default_tag_parameters;
  protected $_field_settings;
  protected $_namespace;
  protected $_package_name;
  protected $_package_title;
  protected $_package_version;
  protected $_site_id;


  /* --------------------------------------------------------------
   * PUBLIC METHODS
   * ------------------------------------------------------------ */

  /**
   * Constructor.
   *
   * @access  public
   * @param   string    $package_name       Package name. Used for testing.
   * @param   string    $package_title      Package title. Used for testing.
   * @param   string    $package_version    Package version. Used for testing.
   * @param   string    $namespace          Session namespace. Used for testing.
   * @return  void
   */
  public function __construct($package_name = '', $package_title = '',
    $package_version = '', $namespace = ''
  )
  {
    parent::__construct();

    $this->EE =& get_instance();

    // Load the number helper.
    $this->EE->load->helper('EI_number_helper');

    // Load the OmniLogger class.
    if (file_exists(PATH_THIRD .'omnilog/classes/omnilogger.php'))
    {
      include_once PATH_THIRD .'omnilog/classes/omnilogger.php';
    }

    $this->_namespace     = $namespace ? strtolower($namespace) : 'experience';
    $this->_package_name  = $package_name ? $package_name : DROPDATE_NAME;
    $this->_package_title = $package_title ? $package_title : DROPDATE_TITLE;

    $this->_package_version = $package_version
      ? $package_version : DROPDATE_VERSION;

    // Initialise the add-on cache.
    if ( ! array_key_exists($this->_namespace, $this->EE->session->cache))
    {
      $this->EE->session->cache[$this->_namespace] = array();
    }

    if ( ! array_key_exists($this->_package_name,
      $this->EE->session->cache[$this->_namespace]))
    {
      $this->EE->session->cache[$this->_namespace]
        [$this->_package_name] = array();
    }

    $this->_default_tag_parameters = array(
      'format'   => 'U',
      'language' => 'english',
      'timezone' => 'UTC'
    );

    $this->_default_field_settings = array(
      'date_format' => self::UNIX_DATE,
      'year_from'   => '1900',
      'year_to'     => '2020',
      'show_time'   => 'no'
    );

    // This should be set by the fieldtype using the `set_field_settings`. We 
    // set some default values here, just in case.
    $this->_field_settings = $this->_default_field_settings;
  }



  /* --------------------------------------------------------------
   * PUBLIC PACKAGE METHODS
   * ------------------------------------------------------------ */
  
  /**
   * Returns the package name.
   *
   * @access  public
   * @return  string
   */
  public function get_package_name()
  {
    return $this->_package_name;
  }


  /**
   * Returns the package theme URL.
   *
   * @access  public
   * @return  string
   */
  public function get_package_theme_url()
  {
    // Much easier as of EE 2.4.0.
    if (defined('URL_THIRD_THEMES'))
    {
      return URL_THIRD_THEMES .$this->get_package_name() .'/';
    }

    return $this->EE->config->slash_item('theme_folder_url')
      .'third_party/' .$this->get_package_name() .'/';
  }


  /**
   * Returns the package title.
   *
   * @access  public
   * @return  string
   */
  public function get_package_title()
  {
    return $this->_package_title;
  }


  /**
   * Returns the package version.
   *
   * @access  public
   * @return  string
   */
  public function get_package_version()
  {
    return $this->_package_version;
  }


  /**
   * Returns the site ID.
   *
   * @access  public
   * @return  int
   */
  public function get_site_id()
  {
    if ( ! $this->_site_id)
    {
      $this->_site_id = (int) $this->EE->config->item('site_id');
    }

    return $this->_site_id;
  }


  /**
   * Logs a message to OmniLog.
   *
   * @access  public
   * @param   string      $message        The log entry message.
   * @param   int         $severity       The log entry 'level'.
   * @return  void
   */
  public function log_message($message, $severity = 1)
  {
    if (class_exists('Omnilog_entry') && class_exists('Omnilogger'))
    {
      switch ($severity)
      {
        case 3:
          $notify = TRUE;
          $type   = Omnilog_entry::ERROR;
          break;

        case 2:
          $notify = FALSE;
          $type   = Omnilog_entry::WARNING;
          break;

        case 1:
        default:
          $notify = FALSE;
          $type   = Omnilog_entry::NOTICE;
          break;
      }

      $omnilog_entry = new Omnilog_entry(array(
        'addon_name'    => 'Dropdate',
        'date'          => time(),
        'message'       => $message,
        'notify_admin'  => $notify,
        'type'          => $type
      ));

      Omnilogger::log($omnilog_entry);
    }
  }


  /**
   * Updates a 'base' array with data contained in an 'update' array. Both
   * arrays are assumed to be associative.
   *
   * - Elements that exist in both the base array and the update array are
   *   updated to use the 'update' data.
   * - Elements that exist in the update array but not the base array are
   *   ignored.
   * - Elements that exist in the base array but not the update array are
   *   preserved.
   *
   * @access public
   * @param  array  $base   The 'base' array.
   * @param  array  $update The 'update' array.
   * @return array
   */
  public function update_array_from_input(Array $base, Array $update)
  {
    return array_merge($base, array_intersect_key($update, $base));
  }


  /**
   * Updates the package. Called from the 'update' methods of any package 
   * add-ons (module, extension, etc.), to ensure that everything gets updated 
   * at the same time.
   *
   * @access  public
   * @param   string    $installed_version    The installed version.
   * @return  bool
   */
  public function update_package($installed_version = '')
  {
    // Can't do anything without valid data.
    if ( ! is_string($installed_version) OR $installed_version == '')
    {
      return FALSE;
    }

    $package_version = $this->get_package_version();

    // Up to date?
    if (version_compare($installed_version, $package_version, '>='))
    {
      return FALSE;
    }

    return TRUE;
  }

  
  /* --------------------------------------------------------------
   * PUBLIC ADD-ON SPECIFIC METHODS
   * ------------------------------------------------------------ */

  /**
   * Converts the supplied field data to a DateTime object, if possible.
   *
   * The field data may be:
   * 1. An empty string (i.e. no field data), in which case the method returns 
   *    FALSE.
   * 2. A saved string, in UNIX or YMD format.
   * 3. An array of field values, from a Publish / Edit form (or SafeCracker 
   *    form) submission.
   *
   * In the case of options 2 and 3, the method will attempt to convert the data 
   * into a valid DateTime object. If this fails, the method will throw an 
   * exception.
   *
   * @access  public
   * @param   mixed    $field_data    The field data.
   * @return  mixed
   */
  public function convert_field_data_to_datetime($field_data)
  {
    // Start with the assumption that there is no data.
    if ( ! $field_data)
    {
      return FALSE;
    }

    $timezone = new DateTimeZone('UTC');

    // If $field_data is an array, it (should) mean we have form data.
    if (is_array($field_data))
    {
      if ( ! array_key_exists('year', $field_data)
        OR ! array_key_exists('month', $field_data)
        OR ! array_key_exists('day', $field_data)
        OR ! valid_int($field_data['day'], 1, 31)
        OR ! valid_int($field_data['month'], 1, 12)
        OR ! valid_int($field_data['year'])
      )
      {
        throw new DropDateException_InvalidSubmittedDate(
          $this->EE->lang->line('exception__invalid_submitted_date'));
      }
      else
      {
        $year  = $field_data['year'];
        $month = str_pad($field_data['month'], 2, '0', STR_PAD_LEFT);
        $day   = str_pad($field_data['day'], 2, '0', STR_PAD_LEFT);

        // Hour and minute are optional.
        $hour = $minute = '00';

        if (array_key_exists('hour', $field_data)
          && valid_int($field_data['hour'], 0, 23)
        )
        {
          $hour = str_pad($field_data['hour'], 2, '0', STR_PAD_LEFT);
        }

        if (array_key_exists('minute', $field_data)
          && valid_int($field_data['minute'], 0, 59)
        )
        {
          $minute = str_pad($field_data['minute'], 2, '0', STR_PAD_LEFT);
        }

        // Return the DateTime object.
        return new DateTime("{$year}-{$month}-{$day} {$hour}:{$minute}:00",
          $timezone);
      }
    }

    // A string should mean we have previously-saved data.
    if (is_string($field_data))
    {
      if ($this->_field_settings['date_format'] == self::YMD_DATE)
      {
        $date = DateTime::createFromFormat(DateTime::W3C, $field_data, $timezone);
      }
      else
      {
        $date = valid_int($field_data)
          ? new DateTime('@' .$field_data)
          : FALSE;
      }

      if ( ! $date instanceof DateTime)
      {
        throw new DropDateException_InvalidSavedDate(
          $this->EE->lang->line('exception__invalid_saved_date'));
      }

      // Return the DateTime object.
      return $date;
    }
  }


  /**
   * Returns an associative array of days, for use with the `form_dropdown` Form 
   * helper.
   *
   * @access  public
   * @return  array
   */
  public function get_days()
  {
    $days = array();

    for ($count = 1; $count <= 31; $count++)
    {
      $days[$count] = str_pad($count, 2, '0', STR_PAD_LEFT);
    }

    return $days;
  }


  /**
   * Returns an array of 'default' field settings.
   *
   * @access  public
   * @return  array
   */
  public function get_default_field_settings()
  {
    return $this->_default_field_settings;
  }


  /**
   * Returns the default template tag parameters.
   *
   * @access  public
   * @return  array
   */
  public function get_default_template_tag_parameters()
  {
    return $this->_default_tag_parameters;
  }


  /**
   * Returns an associative array of hours, for use with the `form_dropdown` 
   * Form helper.
   *
   * @access  public
   * @return  array
   */
  public function get_hours()
  {
    $hours = array();

    for ($count = 0; $count < 24; $count++)
    {
      $hours[$count] = str_pad($count, 2, '0', STR_PAD_LEFT);
    }

    return $hours;
  }


  /**
   * Returns an associative array of minutes, for use with the `form_dropdown` 
   * Form helper.
   *
   * @access  public
   * @return  array
   */
  public function get_minutes()
  {
    $minutes = array();

    $step = valid_int($this->_field_settings['show_time'])
      ? intval($this->_field_settings['show_time'])
      : 1;

    for ($count = 0; $count < 60; $count += $step)
    {
      $minutes[$count] = str_pad($count, 2, '0', STR_PAD_LEFT);
    }

    return $minutes;
  }


  /**
   * Returns an associative array of months in the localised language, for use 
   * with the `form_dropdown` Form helper.
   *
   * @access  public
   * @return  array
   */
  public function get_months()
  {
    $months = array();

    for ($count = 1; $count <= 12; $count++)
    {
      $months[$count] = $this->EE->lang->line('label__month_' .$count);
    }

    return $months;
  }


  /**
   * Returns an associative array of years, for use with the `form_dropdown` 
   * Form helper.
   *
   * Callers should ensure that the `set_field_settings` method has been called 
   * prior to running this method.
   *
   * @access  public
   * @return  array
   */
  public function get_years()
  {
    $s =& $this->_field_settings;

    if ( ! array_key_exists('year_from', $s)
      OR ! array_key_exists('year_to', $s)
    )
    {
      throw new DropDateException_InvalidFieldSettings(
        $this->EE->lang->line('exception__missing_year_settings'));
    }

    // Parse the 'from' and 'to' years.
    $date_pattern = '/^(now([\-|+]))?(\d+)$/i';

    if ( ! preg_match($date_pattern, $s['year_from'], $from_matches)
      OR ! preg_match($date_pattern, $s['year_to'], $to_matches)
    )
    {
      throw new DropDateException_InvalidFieldSettings(
        $this->EE->lang->line('exception__invalid_year_settings'));
    }

    $now = date('Y');   // Make a note of the current year.

    $from_year_base     = $from_matches[1] ? $now : 0;
    $from_year_operator = $from_matches[2] ? $from_matches[2] : '+';
    $from_year_offset   = $from_matches[3];

    $to_year_base     = $to_matches[1] ? $now : 0;
    $to_year_operator = $to_matches[2] ? $to_matches[2] : '+';
    $to_year_offset   = $to_matches[3];

    // OH MY GOD, HE'S USING EVAL! SOMEBODY STOP THIS MAN!
    $from_year = eval("return ({$from_year_base}{$from_year_operator}{$from_year_offset});");
    $to_year   = eval("return ({$to_year_base}{$to_year_operator}{$to_year_offset});");

    // We can count backwards, if required.
    $year_step = $from_year > $to_year ? -1 : 1;

    // Build the data array.
    $years      = array();
    $year_count = $from_year;

    while ($year_count != ($to_year + $year_step))
    {
      $years[$year_count]  = $year_count;
      $year_count         += $year_step;
    }

    return $years;
  }


  /**
   * Parses the supplied field data. Data can be blank (an empty string), an 
   * associative array (data submitted during a failed attempt to publish an 
   * entry), or a string (previously-saved data).
   *
   * @access  public
   * @param   mixed    $field_data    The field data.
   * @return  array
   */
  public function parse_field_data($field_data = '')
  {
    $date_parts = array(
      'year'   => self::NO_VALUE,
      'month'  => self::NO_VALUE,
      'day'    => self::NO_VALUE,
      'hour'   => self::NO_VALUE,
      'minute' => self::NO_VALUE
    );

    // Exceptions are allowed to bubble.
    $date = $this->convert_field_data_to_datetime($field_data);

    if ($date === FALSE)
    {
      return $date_parts;
    }

    // Split the DateTime object into its constituent parts.
    return array(
      'year'   => intval($date->format('Y')),
      'month'  => intval($date->format('n')),
      'day'    => intval($date->format('j')),
      'hour'   => intval($date->format('G')),
      'minute' => intval($date->format('i'))    // intval strips leading zero.
    );
  }


  /**
   * Prepares the submitted field data for saving to the database. Returns a 
   * string in the format specified by the 'date_format' field setting.
   *
   * @access  public
   * @param   array    $field_data    The submitted field data.
   * @return  string
   */
  public function prep_submitted_data_for_save(Array $field_data)
  {
    // Exceptions are allowed to bubble.
    $date = $this->convert_field_data_to_datetime($field_data);

    return $this->_field_settings['date_format'] == self::YMD_DATE
      ? $date->format(DateTime::W3C)
      : $date->format('U');
  }


  /**
   * Sets the field settings. Fieldtypes are a slightly strange, in the EE 
   * automatically provides the fieldtype itself with a copy of any 
   * previously-saved settings.
   *
   * There's no point in us replicating this functionality, so instead we just 
   * ensure that the fieldtype passes the settings on to the model, using this 
   * method.
   *
   * This method should be called from the 'display field' method(s) in the 
   * fieldtype.
   *
   * @access  public
   * @param   array   $settings   The field settings.
   * @return  void
   */
  public function set_field_settings(Array $settings)
  {
    $this->_field_settings = $this->update_array_from_input(
      $this->get_default_field_settings(), $settings);
  }



  /* --------------------------------------------------------------
   * PROTECTED PACKAGE METHODS
   * ------------------------------------------------------------ */

  /**
   * Returns a references to the package cache. Should be called
   * as follows: $cache =& $this->_get_package_cache();
   *
   * @access  protected
   * @return  array
   */
  protected function &_get_package_cache()
  {
    return $this->EE->session->cache[$this->_namespace][$this->_package_name];
  }


}


/* End of file      : dropdate_model.php */
/* File location    : third_party/dropdate/models/dropdate_model.php */

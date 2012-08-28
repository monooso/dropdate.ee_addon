<?php if ( ! defined('BASEPATH')) exit('Invalid file request');

/**
 * Fieldtype enabling users to select a date using 3 or 5 drop-downs (day, 
 * month, year[, hour, minute]).
 *
 * @author      Stephen Lewis (http://experienceinternet.co.uk/software/)
 * @author      Lodewijk Schutte (http://github.com/lodewijk)
 * @copyright   Copyright (c) 2010, Stephen Lewis
 * @link        http://experienceinternet.co.uk/software/dropdate/
 */

require_once 'config.php';

class Dropdate_ft extends EE_Fieldtype {
  
  const DROPDATE_FMT_UNIX = 'unix';
  const DROPDATE_FMT_YMD  = 'ymd';
  
  protected $_class;
  protected $_lower_class;
  protected $_time_format;

  // Annoyingly, we can't populate this in the constructor.
  public $info = array(
    'name'     => DROPDATE_NAME,
    'version'  => DROPDATE_VERSION,
    'desc'     => 'Fieldtype enabling users to select a date using 3 or 5 drop-downs (day, month, year[, hour, minute]).',
    'docs_url' => 'http://experienceinternet.co.uk/software/dropdate/'
  );
    
  public $postpone_saves;
  public $default_settings;
  
  
  /**
   * --------------------------------------------------------------
   * PUBLIC METHODS
   * --------------------------------------------------------------
   */

  /**
   * Constructor function.
   *
   * @access  public
   * @return  void
   */
  public function __construct()
  {
    parent::EE_Fieldtype();

    $this->_class       = get_class($this);
    $this->_lower_class = strtolower($this->_class);
    $this->_time_format = $this->EE->config->item('time_format');

    $this->postpone_saves = TRUE;

    $this->default_settings = array(
      'date_format' => self::DROPDATE_FMT_UNIX,
      'year_range'  => '1900-2020',
      'show_time'   => ''
    );
  }
  
  
  /**
   * Adds custom cell settings to an FF Matrix field in the "Create / Edit 
   * Field" form.
   *
   * @access  public
   * @param   array   $cell_settings    Previously saved cell settings.
   * @return  void
   */
  public function display_cell_settings(Array $cell_settings = array())
  {
    return $this->_get_settings($cell_settings);
  }
  
  
  /**
   * Displays the custom cell HTML for the "Publish / Edit" form.
   *
   * @access  public
   * @param   string    $cell_data      Previously saved cell data.
   * @return  string
   */
  public function display_cell($cell_data = '')
  {
    return $this->display_field($cell_data, TRUE);
  }
  
  
  /**
   * Displays the custom field HTML for the "Publish / Edit" form.
   *
   * @access  public
   * @param   string    $field_data     Previously saved field data.
   * @return  string
   */
  public function display_field($field_data = '', $cell = FALSE)
  {
    $this->EE->lang->loadfile('dropdate');
    
    $field_name = $cell ? $this->cell_name : $this->field_name;
    
    // Days.
    $days[] = lang('day');
    for ($count = 1; $count <= 31; $count++)
    {
      $days[] = str_pad($count, 2, '0', STR_PAD_LEFT);
    }
    
    // Months.
    $months = array(
      lang('month'),
      lang('jan'), lang('feb'),
      lang('mar'), lang('apr'),
      lang('may'), lang('jun'),
      lang('jul'), lang('aug'),
      lang('sep'), lang('oct'),
      lang('nov'), lang('dec')
    );
    
    /**
     * Get year range from settings. Replace 'now' with current year.
     * Examples: '2000-2010' or '2010-now+5'
     *
     * @author  Lodewijk Schutte (http://github.com/lodewijk)
     * @since 1.0.1
     */
    
    $year_range = isset($this->settings['year_range']) ? $this->settings['year_range'] : $this->default_settings['year_range'];
    $year_range = str_replace('now', date('Y', time()), $year_range);
    
    /**
     * Read year range and optional modifier.
     *
     * @author  Lodewijk Schutte (http://github.com/lodewijk)
     * @since 1.0.1
     */
    
    if (preg_match('/^([0-9]{4})([\+|\-]{1}\d+)?-([0-9]{4})([\+|\-]{1}\d+)?$/', $year_range, $matches))
    {
      /**
       * $from_year modifier implemented in version 2.0.2.
       *
       * @author  Stephen Lewis
       * @since   2.0.2
       */

      $from_year = isset($matches[2])
          ? (int) $matches[1] + (int) $matches[2]
          : (int) $matches[1];

      $to_year = isset($matches[4])
          ? (int) $matches[3] + (int) $matches[4]
          : (int) $matches[3];
    }
    else
    {
      $from_year  = 1900;
      $to_year  = 2020;
    }

    /**
     * Implement support for counting backwards (e.g. 2020-1990).
     *
     * @author  Stephen Lewis
     * @since   2.0.1
     */

    $years[]        = lang('year');
    $year_step      = $from_year > $to_year ? -1 : 1;
    $year_counter   = $from_year;

    while ($year_counter != ($to_year + $year_step))
    {
      $years[$year_counter] = $year_counter;
      $year_counter += $year_step;
    }

    /**
     * Hours and minutes.
     *
     * @author  Lodewijk Schutte
     * @since   2.0.3
     */

    $hours = $minutes = array();

    if (isset($this->settings['show_time']) && is_numeric($this->settings['show_time']))
    {
      $hours[]   = lang('hour');
      $minutes[] = lang('minute');

      // Force minute interval to be an integer
      $interval = (int) $this->settings['show_time'];

      // Based on time format, show amount of hours
      $from_hour = ($this->_time_format == 'eu') ?  0 :  1;
      $to_hour   = ($this->_time_format == 'eu') ? 23 : 12;

      for ($hour = $from_hour; $hour <= $to_hour; $hour++)
      {
        $h = str_pad($hour, 2, '0', STR_PAD_LEFT);
        $hours[$h] = $h;
      }
      
      for ($minute = 0; $minute < 60; $minute += $interval)
      {
        $m = str_pad($minute, 2, '0', STR_PAD_LEFT);
        $minutes[$m] = $m;
      }
    }

    /**
     * There are 4 situations to deal with:
     * 1. There is no previously-saved OR previously-submitted field data.
     * 2. There is no previously-saved data, BUT data was submitted (occurs when 
     *    required fields are not filled out).
     * 3. There is previously-saved data, in YMD format.
     * 4. There is previously-saved data, in UNIX format.
     *
     * @since 1.1.1
     * @author  Stephen Lewis <addons@experienceinternet.co.uk>
     */
    
    // We start by assuming there is no previously-saved data OR submitted data.
    $saved_year = $saved_month = $saved_day = $saved_hour = $saved_minute = $saved_ampm = '';

    if ($field_data)
    {
      if ( is_array($field_data) && in_array(count($field_data), array(3,5,6)) )
      {
        // No previously-saved data, BUT submitted data.
        $saved_day    = $field_data[0];
        $saved_month  = $field_data[1];
        $saved_year   = $field_data[2];
        $saved_hour   = @$field_data[3];
        $saved_minute = @$field_data[4];
        $saved_ampm   = @$field_data[5];
        
      }
      elseif (isset($this->settings['date_format']) && $this->settings['date_format'] == self::DROPDATE_FMT_YMD)
      {
        // Previously-saved data, in YMD format.
        $pattern = '/^([0-9]{4})([0-9]{2})([0-9]{2})(([0-9]{2})([0-9]{2}))?$/';
        if (preg_match($pattern, $field_data, $matches))
        {
          $saved_year   = $matches[1];
          $saved_month  = $matches[2];
          $saved_day    = $matches[3];
          $saved_hour   = @$matches[5];
          $saved_minute = @$matches[6];

          // Convert 24h to 12h format
          if ($this->_time_format == 'us' && $saved_hour !== FALSE && strlen($saved_hour))
          {
            $time = "{$saved_hour}:{$saved_minute}";
            $saved_hour = date('h', strtotime($time));
            $saved_ampm = date('A', strtotime($time));
          }
        }
      }
      else
      {
        // Previously-saved data, in UNIX format.
        $saved_year   = date('Y', $field_data);
        $saved_month  = date('n', $field_data);
        $saved_day    = date('j', $field_data);
        $saved_hour   = date(($this->_time_format == 'eu' ? 'H' : 'h'), $field_data);
        $saved_minute = date('i', $field_data);
        $saved_ampm   = date('A', $field_data);
      }
      
    }

    // Begin building output.
    $output = ''
      . form_dropdown($field_name ."[]", $days, $saved_day)
      . NBS
      . form_dropdown($field_name ."[]", $months, $saved_month)
      . NBS
      . form_dropdown($field_name ."[]", $years, $saved_year);

    // Account for hour & minute drop downs.
    if ($hours && $minutes)
    {
      $output .= NBS.'@'.NBS
        . form_dropdown($field_name ."[]", $hours, $saved_hour)
        . ':'
        . form_dropdown($field_name ."[]", $minutes, $saved_minute);

      // Add am/pm drop down for you yanks.
      if ($this->_time_format == 'us')
      {
        $output .= NBS . form_dropdown($field_name ."[]", array(
          ''   => 'am/pm',
          'AM' => 'am',
          'PM' => 'pm'
        ), $saved_ampm);
      }
    }

    // Return generated HTML.
    return $output;
  }
  
  
  /**
   * Adds custom settings to the "Create / Edit Field" form.
   *
   * @access  public
   * @param   array   $field_settings   Previously saved field settings.
   * @return  array
   */
  public function display_settings(Array $field_settings = array())
  {
    $settings = $this->_get_settings($field_settings);
    
    foreach ($settings AS $row)
    {
      $this->EE->table->add_row('<strong>'. $row[0] .'</strong>', $row[1]);
    }
  }
  
  
  /**
   * Displays the field data in a template tag.
   *
   * @access  public
   * @param   array     $params       The template tag parameters (key / value pairs).
   * @param   string    $tagdata      The content between the opening and closing tags, if it's a tag pair.
   * @param   string    $field_data     The field data.
   * @param   array     $field_settings   The field settings.
   * @return  string
   */
  public function replace_tag($field_data = '', Array $params = array(), $tagdata = '')
  {
    if (isset($this->settings['date_format'])
      && $this->settings['date_format'] == self::DROPDATE_FMT_YMD)
    {
      $pattern = '/^([0-9]{4})([0-9]{2})([0-9]{2})T?(([0-9]{2})([0-9]{2}))?$/';
      if (preg_match($pattern, $field_data, $matches))
      {
        $hour       = (int) (isset($matches[5]) ? $matches[5] : 0);
        $minute     = (int) (isset($matches[6]) ? $matches[6] : 0);
        $field_data = mktime($hour, $minute, 1, $matches[2], $matches[3], $matches[1]);
      }
    }

    if ( ! $field_data)
    {
      return '';
    }

    $params = array_merge(array('format' => 'U'), $params);

    // @low: if there's a percentage sign in the format, use EE's native date function for language file use
    if (strpos($params['format'], '%') === FALSE)
    {
      return date($params['format'], $field_data);
    }
    else
    {
      return $this->EE->localize->decode_date($params['format'], $field_data);
    }
  }
  
  
  /**
   * Modifies the cell's POST data, before it's saved to the database.
   *
   * @access  public
   * @param mixed   $cell_data      The cell's POST data.
   * @param array   $cell_settings    The cell's settings.
   * @param   mixed   $entry_id     The entry ID (if postponed saving is enabled), or FALSE.
   * @return  string
   */
  public function save_cell($cell_data = '', Array $cell_settings = array(), $entry_id = FALSE)
  {
    return $this->save($cell_data);
  }
  
  
  /**
   * Modifies the field's POST data, before it's saved to the database.
   *
   * @access  public
   * @param   mixed   $field_data     The field's POST data.
   * @param   array   $field_settings The field settings.
   * @param   mixed   $entry_id       The entry ID (if postponed saving is enabled), or FALSE.
   * @return  string
   */
  public function save($field_data = '')
  {
    if ( ! is_array($field_data)
      OR ! in_array(count($field_data), array(3, 5, 6))
    )
    {
      return '';
    }
    
    $day    = $field_data[0];
    $month  = $field_data[1];
    $year   = $field_data[2];
    $hour   = isset($field_data[3]) ? $field_data[3] : 0;
    $minute = isset($field_data[4]) ? $field_data[4] : 0;
    $ampm   = isset($field_data[5]) ? $field_data[5] : 'am';

    // Convert 12h to 24h format.
    if ($ampm && $this->_time_format == 'us')
    {
      $hour = date('H', strtotime("{$hour}:{$minute} {$ampm}"));
    }

    // Do we have the bare minimum?
    if ( ! $day OR ! $month OR ! $year)
    {
      return '';
    }

    // Format the strings.
    $day    = str_pad($day, 2, '0', STR_PAD_LEFT);
    $month  = str_pad($month, 2, '0', STR_PAD_LEFT);
    $hour   = str_pad($hour, 2, '0', STR_PAD_LEFT);
    $minute = str_pad($minute, 2, '0', STR_PAD_LEFT);

    // Create a DateTime object.
    $date = new DateTime("{$year}-{$month}-{$day}T{$hour}:{$minute}:00",
      new DateTimeZone('UTC'));

    // Format and return the date as a string.
    return (isset($this->settings['date_format'])
      && $this->settings['date_format'] == self::DROPDATE_FMT_YMD)
        ? $date->format('YmdTHi')
        : $date->format('U');
  }
  
  
  /**
   * Save field settings
   *
   * @access  public
   * @param array   $field_settings   The field settings.
   * @return  array
   */
  public function save_settings(Array $field_settings = array())
  {
    return $this->_get_posted_settings();
  }
  

  /**
   * Displays the custom field HTML for the Low Variables module home page.
   *
   * @since   1.1.0
   * @author  Lodewijk Schutte (http://github.com/lodewijk)
   * @access  public
   * @param string    $var_name     The variable name.
   * @param string    $var_data     Previously saved variable data.
   * @param   array     $var_settings   The variable settings.
   * @return  string
   */
  public function display_var_field($var_data = '')
  {
    return $this->display_field($var_data);
  }
  
  
  /**
   * Adds custom settings to a Low Variables instance.
   *
   * @since   1.1.0
   * @author  Lodewijk Schutte (http://github.com/lodewijk)
   * @access  public
   * @param array   $var_settings   Previously-saved variable settings.
   * @return  string
   */
  public function display_var_settings(Array $var_settings = array())
  {
    return $this->_get_settings($var_settings);
  }
  
  
  /**
   * Return dropdate settings in Low Variables format.
   *
   * @since   1.1.0
   * @author  Lodewijk Schutte (http://github.com/lodewijk)
   * @access  public
   * @param   array     $var_settings   Previously-saved variable settings.
   * @return  array
   */
  public function save_var_settings(Array $var_settings = array())
  {
    return $this->_get_posted_settings();
  }
  
  
  /**
   * Save Low Variables field.
   *
   * @since   1.1.0
   * @author  Lodewijk Schutte (http://github.com/lodewijk)
   * @access  public
   * @param   string    $var_data     Previously-saved variable data.
   * @param   array     $var_settings   Previously-saved variable settings.
   * @return  string
   */
  public function save_var_field($var_data = '')
  {
    return $this->save($var_data);
  }
  
  
  /**
   * Display Low Variables field.
   *
   * @since   1.1.0
   * @author  Lodewijk Schutte (http://github.com/lodewijk)
   * @access  public
   * @param   array     $params       Tag parameters.
   * @param   string    $tagdata      Tag data.
   * @param   string    $var_data     Previously-saved variable data.
   * @param   array     $var_settings   Previously-saved variable settings.
   * @return  string
   */
  public function display_var_tag($var_data = '', Array $params = array(), $tagdata = '')
  {
    return $this->replace_tag($var_data, $params, $tagdata);
  }
  
  
  /**
   * Install fieldtype
   *
   * Check to see if FF2EE2 exists to migrate existing fields
   */
  function install()
  {
    $ff2ee2_file = PATH_THIRD.'pt_field_pack/ff2ee2/ff2ee2.php';
    
    if ( ! class_exists('FF2EE2') && file_exists($ff2ee2_file))
    {
      require $ff2ee2_file;
    }

    if (class_exists('FF2EE2'))
    {
      new FF2EE2('dropdate');
    }
  }
  
  
  /* --------------------------------------------------------------
   * PRIVATE METHODS
   * ------------------------------------------------------------ */
  
  /**
   * Returns settings in a nested array for easy access
   *
   * @access  private
   * @param array   $field_settings   Previously saved field settings.
   * @return  array
   */
  private function _get_settings(Array $field_settings = array())
  {
    $this->EE->lang->loadfile('dropdate');
    
    foreach ($this->default_settings AS $setting => $value)
    {
      if ( ! array_key_exists($setting, $field_settings))
      {
        $field_settings[$setting] = $value;
      }
    }

    // Drop down of time options
    $time_options = array(
      ''   => lang('show_time_no'),
      '5'  => lang('show_time_5'),
      '15' => lang('show_time_15')
    );

    return array(
      array(
        lang('save_format_label'),
         '<label style="margin-right:20px">'
        . form_radio('date_format', self::DROPDATE_FMT_UNIX, ($field_settings['date_format'] == self::DROPDATE_FMT_UNIX))
        . ' '. $this->EE->lang->line('unix_format_label')
        .'</label>'
        .'<label>'
        . form_radio('date_format', self::DROPDATE_FMT_YMD, ($field_settings['date_format'] == self::DROPDATE_FMT_YMD))
        . ' '. $this->EE->lang->line('ymd_format_label')
        .'</label>'
      ),
      array(
        lang('year_range_label'),
        form_input(array(
          'name'  => 'year_range',
          'value' => $field_settings['year_range'],
          'style' => 'width:75px'
        ))
      ),
      array(
        lang('show_time_label'),
        form_dropdown('show_time', $time_options, $field_settings['show_time'])
      )
    );
  }
  
  /**
   * Returns posted settings in an array, fallback to default
   *
   * @access  private
   * @return  array
   */
  private function _get_posted_settings()
  {
    $settings = array();
    
    foreach ($this->default_settings AS $setting => $value)
    {
      if (($settings[$setting] = $this->EE->input->post($setting)) === FALSE)
      {
        $settings[$setting] = $value;
      }
    }
    
    return $settings;
  }
  
}

/* End of file      : ft.dropdate.php */
/* Location of file   : /system/expressionengine/third_party/dropdate/ft.dropdate.php */

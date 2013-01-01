<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Freeform fieldtype enabling users to select a date using 3 or 5 drop-downs
 * (day, month, year[, hour, minute]).
 *
 * @author      Stephen Lewis (http://experienceinternet.co.uk/software/)
 * @author      Lodewijk Schutte (http://github.com/lodewijk)
 * @author      Lea Hayes (http://leahayes.co.uk)
 * @copyright   Copyright (c) 2010, Stephen Lewis
 * @link        http://experienceinternet.co.uk/software/dropdate/
 */

require_once 'config.php';

class Dropdate_freeform_ft extends Freeform_base_ft {

	const DROPDATE_FMT_UNIX = 'unix';
	const DROPDATE_FMT_YMD  = 'ymd';

	public $info = array(
		'name'			=> DROPDATE_NAME,
		'version'		=> DROPDATE_VERSION,
		'description'	=> DROPDATE_DESCRIPTION
	);

	public $default_settings = array(
		'date_format'	=> self::DROPDATE_FMT_UNIX,
		'year_range'	=> '1900-2020',
		'show_time'		=> '',
		'required'		=> ''
	);


	/* --------------------------------------------------------------
	* PUBLIC METHODS
	* ------------------------------------------------------------ */

	/**
	 * Constructor function.
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct() {
		parent::__construct();

		$this->_time_format = $this->EE->config->item('time_format');
	}

	/**
	 * Displays the custom field HTML for the "Publish / Edit" form.
	 *
	 * @access  public
	 * @param   string    $field_data     Previously saved field data.
	 * @return  string
	 */
	public function display_field($field_data) {
		$this->EE->lang->loadfile('dropdate');

		$field_name = $this->field_name;

		// Days.
		$days[] = lang('day');
		for ($count = 1; $count <= 31; ++$count)
			$days[] = str_pad($count, 2, '0', STR_PAD_LEFT);

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

		$year_range = isset($this->settings['year_range'])
			? $this->settings['year_range']
			: $this->default_settings['year_range']
			;
		$year_range = str_replace('now', date('Y', time()), $year_range);

		/**
		 * Read year range and optional modifier.
		 *
		 * @author  Lodewijk Schutte (http://github.com/lodewijk)
		 * @since 1.0.1
		 */

		if (preg_match('/^([0-9]{4})([\+|\-]{1}\d+)?-([0-9]{4})([\+|\-]{1}\d+)?$/', $year_range, $matches)) {
			/**
			 * $from_year modifier implemented in version 2.0.2.
			 *
			 * @author  Stephen Lewis
			 * @since   2.0.2
			 */
			
			$from_year = isset($matches[2])
				? (int)$matches[1] + (int)$matches[2]
				: (int)$matches[1]
				;
			
			$to_year = isset($matches[4])
				? (int)$matches[3] + (int)$matches[4]
				: (int)$matches[3]
				;
		}
		else {
			$from_year = 1900;
			$to_year = 2020;
		}

		/**
		 * Implement support for counting backwards (e.g. 2020-1990).
		 *
		 * @author  Stephen Lewis
		 * @since   2.0.1
		 */

		$years[]		= lang('year');
		$year_step		= $from_year > $to_year ? -1 : 1;
		$year_counter	= $from_year;

		while ($year_counter != ($to_year + $year_step)) {
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

		if (isset($this->settings['show_time']) && is_numeric($this->settings['show_time'])) {
			$hours[]	= lang('hour');
			$minutes[]	= lang('minute');

			// Force minute interval to be an integer
			$interval = (int)$this->settings['show_time'];

			// Based on time format, show amount of hours
			$from_hour	= ($this->_time_format == 'eu') ?  0 :  1;
			$to_hour	= ($this->_time_format == 'eu') ? 23 : 12;

			for ($hour = $from_hour; $hour <= $to_hour; $hour++) {
				$h = str_pad($hour, 2, '0', STR_PAD_LEFT);
				$hours[$h] = $h;
			}

			for ($minute = 0; $minute < 60; $minute += $interval) {
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

		if ($field_data) {
			if ( is_array($field_data) && in_array(count($field_data), array(3,5,6)) ) {
				// No previously-saved data, BUT submitted data.
				$saved_day		= $field_data[0];
				$saved_month	= $field_data[1];
				$saved_year		= $field_data[2];
				$saved_hour		= @$field_data[3];
				$saved_minute	= @$field_data[4];
				$saved_ampm		= @$field_data[5];
			}
			elseif (isset($this->settings['date_format']) && $this->settings['date_format'] == self::DROPDATE_FMT_YMD) {
				// Previously-saved data, in YMD format.
				$pattern = '/^([0-9]{4})([0-9]{2})([0-9]{2})(([0-9]{2})([0-9]{2}))?$/';
				if (preg_match($pattern, $field_data, $matches)) {
					$saved_year		= $matches[1];
					$saved_month	= $matches[2];
					$saved_day		= $matches[3];
					$saved_hour		= @$matches[5];
					$saved_minute	= @$matches[6];
					
					// Convert 24h to 12h format
					if ($this->_time_format == 'us' && $saved_hour !== FALSE && strlen($saved_hour)) {
						$time = "{$saved_hour}:{$saved_minute}";
						$saved_hour = date('h', strtotime($time));
						$saved_ampm = date('A', strtotime($time));
					}
				}
			}
			else {
				// Previously-saved data, in UNIX format.
				$saved_year		= date('Y', $field_data);
				$saved_month	= date('n', $field_data);
				$saved_day		= date('j', $field_data);
				$saved_hour		= date(($this->_time_format == 'eu' ? 'H' : 'h'), $field_data);
				$saved_minute	= date('i', $field_data);
				$saved_ampm		= date('A', $field_data);
			}
		}

		// Begin building output.
		$output = ''
			. form_dropdown($field_name . '[]', $days, $saved_day)
			. NBS
			. form_dropdown($field_name . '[]', $months, $saved_month)
			. NBS
			. form_dropdown($field_name . '[]', $years, $saved_year)
			;

		// Account for hour & minute drop downs.
		if ($hours && $minutes) {
			$output .= NBS.'@'.NBS
			. form_dropdown($field_name ."[]", $hours, $saved_hour)
			. ':'
			. form_dropdown($field_name ."[]", $minutes, $saved_minute);

			// Add am/pm drop down for you yanks.
			if ($this->_time_format == 'us') {
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

	public function validate($field_data = '') {
		// Unfortunately the required field must be specified twice, once using the
		// Freeform user interface, and once for the field type settings because
		// there does not otherwise appear to be a way to validate an empty input.

		if ($this->settings['required'] == 'y') {
			if ( ! $field_data OR ! is_array($field_data) )
				return lang('missing_required_field');
	
			if ( ! $field_data[0] OR ! $field_data[1] OR ! $field_data[2] )
				return lang('missing_required_field');
		}

		return true;
	}

	/**
	 * Modifies the field's POST data, before it's saved to the database.
	 *
	 * @access  public
	 * @param   mixed   $field_data     The field's POST data.
	 * @return  string
	 */
	public function save($field_data = '') {
		if ( ! is_array($field_data) OR ! in_array(count($field_data), array(3, 5, 6)) )
			return '';

		$day	= $field_data[0];
		$month	= $field_data[1];
		$year	= $field_data[2];
		$hour	= isset($field_data[3]) ? $field_data[3] : 0;
		$minute	= isset($field_data[4]) ? $field_data[4] : 0;
		$ampm	= isset($field_data[5]) ? $field_data[5] : 'am';

		// Convert 12h to 24h format.
		if ($ampm && $this->_time_format == 'us')
			$hour = date('H', strtotime("{$hour}:{$minute} {$ampm}"));
		
		// Do we have the bare minimum?
		if ( ! $day OR ! $month OR ! $year)
			return '';

		// Format the strings.
		$day	= str_pad($day, 2, '0', STR_PAD_LEFT);
		$month	= str_pad($month, 2, '0', STR_PAD_LEFT);
		$hour	= str_pad($hour, 2, '0', STR_PAD_LEFT);
		$minute	= str_pad($minute, 2, '0', STR_PAD_LEFT);

		// Create a DateTime object.
		$date = new DateTime("{$year}-{$month}-{$day}T{$hour}:{$minute}:00",
		new DateTimeZone('UTC'));

		// Format and return the date as a string.
		return (isset($this->settings['date_format']) && $this->settings['date_format'] == self::DROPDATE_FMT_YMD)
			? $date->format('YmdTHi')
			: $date->format('U');
	}

	public function display_settings($field_settings) {
		$settings = $this->_get_settings($field_settings);
		
		foreach ($settings AS $row)
			$this->EE->table->add_row('<strong>'. $row[0] .'</strong>', $row[1]);
	}

	public function save_settings() {
		$settings = array();

		foreach ($this->default_settings AS $setting => $value)
			if (($settings[$setting] = $this->EE->input->post($setting)) === FALSE)
				$settings[$setting] = $value;

		return $settings;
	}

	public function replace_tag($field_data, $params = array(), $tagdata = false, $query = null) {
		if (isset($this->settings['date_format']) && $this->settings['date_format'] == self::DROPDATE_FMT_YMD) {
			$pattern = '/^([0-9]{4})([0-9]{2})([0-9]{2})T?(([0-9]{2})([0-9]{2}))?$/';
			if (preg_match($pattern, $field_data, $matches)) {
				$hour		= (int)(isset($matches[5]) ? $matches[5] : 0);
				$minute		= (int)(isset($matches[6]) ? $matches[6] : 0);
				$field_data	= mktime($hour, $minute, 1, $matches[2], $matches[3], $matches[1]);
			}
		}

		if ( ! $field_data)
			return '';

		$params = array_merge(array('format' => 'U'), $params);

		// @low: if there's a percentage sign in the format, use EE's native date function for language file use
		return (strpos($params['format'], '%') === FALSE)
			? date($params['format'], $field_data)
			: $this->EE->localize->decode_date($params['format'], $field_data)
			;
	}

	public function display_entry_cp($field_data) {
		return $this->replace_tag($field_data, array(
			'format'	=> $this->EE->config->item('log_date_format')
		));
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
	private function _get_settings(Array $field_settings = array()) {
		$this->EE->lang->loadfile('dropdate');

		foreach ($this->default_settings AS $setting => $value)
			if ( ! array_key_exists($setting, $field_settings))
				$field_settings[$setting] = $value;

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
				. '</label>'
				. '<label>'
				. form_radio('date_format', self::DROPDATE_FMT_YMD, ($field_settings['date_format'] == self::DROPDATE_FMT_YMD))
				. ' '. $this->EE->lang->line('ymd_format_label')
				. '</label>'
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
			),
			array(
				lang('required_field'),
				form_checkbox('required', 'y', $field_settings['required'] == 'y')
			)
		);
	}

}
// END Dropdown_freeform_ft class

// End of file freeform_ft.dropdate.php
// Location: ./system/expressionengine/third_party/dropdate/freeform_ft.dropdate.php
?>
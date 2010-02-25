<?php

if ( ! defined('EXT'))
{
	exit('Invalid file request');
}

/**
 * Fieldtype enabling users to select a date using 3 drop-downs (day, month, year).
 *
 * @package   	DropDate
 * @version   	0.1.0
 * @author    	Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright 	Copyright (c) 2010, Stephen Lewis
 * @link      	http://experienceinternet.co.uk/dropdate/
 */

class Dropdate extends Fieldframe_Fieldtype {
	
	/**
	 * --------------------------------------------------------------
	 * CLASS CONSTANTS
	 * --------------------------------------------------------------
	 */
	
	const DROPDATE_FMT_UNIX = 'unix';
	const DROPDATE_FMT_YMD	= 'ymd';
	
	
	/**
	 * --------------------------------------------------------------
	 * INSTANCE VARIABLES
	 * --------------------------------------------------------------
	 */
	
	/**
	 * Basic fieldtype information.
	 *
	 * @access	public
	 * @var 	array
	 */
	public $info = array(
		'name'				=> 'DropDate',
		'version'			=> '0.1.0',
		'desc'				=> 'Fieldtype enabling users to select a date using 3 drop-downs (day, month, year).',
		'docs_url'			=> 'http://experienceinternet.co.uk/dropdate/',
		'versions_xml_url'	=> 'http://experienceinternet.co.uk/addon-versions.xml'
	);

	/**
	 * Fieldtype requirements.
	 *
	 * @access	public
	 * @var 	array
	 */
	public $requirements = array(
		'ff'        => '1.3.4',
		'cp_jquery' => '1.1'
	);

	/**
	 * Default site settings.
	 *
	 * @access	public
	 * @var 	array
	 */
	public $default_site_settings = array('date_format' => self::DROPDATE_FMT_UNIX);
	
	/**
	 * The site ID.
	 *
	 * @access	private
	 * @var 	string
	 */
	private $site_id = '';
	
	/**
	 * The class name.
	 *
	 * @access	private
	 * @var 	string
	 */
	private $class = '';
	
	/**
	 * Lower-class classname.
	 *
	 * @access	private
	 * @var 	string
	 */
	private $lower_class = '';
	
	/**
	 * The Session namespace.
	 *
	 * @access	private
	 * @var 	string
	 */
	private $namespace = '';
	
	
	
	/**
	 * --------------------------------------------------------------
	 * PUBLIC METHODS
	 * --------------------------------------------------------------
	 */

	/**
	 * Constructor function.
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		global $DB, $PREFS;
		
		$this->site_id 		= $DB->escape_str($PREFS->ini('site_id'));
		$this->class 		= get_class($this);
		$this->lower_class 	= strtolower($this->class);
		$this->namespace	= 'sl';
	}
	
	
	/**
	 * Adds custom settings to the "Create / Edit Field" form.
	 *
	 * @access	public
	 * @param	array		$field_settings		Previously-saved field settings.
	 * @return	array
	 */
	public function display_field_settings($field_settings = array())
	{
		$SD = new Fieldframe_SettingsDisplay();
		
		$html = $SD->block('DropDate Settings');
		
		if (isset($field_settings['date_format']))
		{
			$value = $field_settings['date_format'];
		}
		else
		{
			$value = isset($this->site_settings['date_format'])
				? $this->site_settings['date_format']
				: '';
		}
		
		$options = array(
			self::DROPDATE_FMT_UNIX => 'Unix Timestamp',
			self::DROPDATE_FMT_YMD	=> 'YYYYMMDD'
		);
			
		$html .= $SD->row(array(
			$SD->label('Save Dates As'),
			$SD->radio_group('date_format', $value, $options)
		));
		
		$html .= $SD->block_c();
		
		return array('cell2' => $html);
	}
	
	
	/**
	 * Displays the custom field HTML for the "Publish / Edit" form.
	 *
	 * @access	public
	 * @param	string		$field_name			The field name.
	 * @param	string		$field_data			Previously-saved field data.
	 * @param 	array 		$field_settings		The field settings.
	 * @return	string
	 */
	public function display_field($field_name = '', $field_data = '', $field_settings = array())
	{
		
	}
	
	
	/**
	 * Modifies the field's POST data, before it's saved to the database.
	 *
	 * @access	public
	 * @param	mixed		$field_data			The field's POST data.
	 * @param	array		$field_settings		The field settings.
	 * @param 	mixed		$entry_id			The entry ID (if postponed saving is enabled), or FALSE.
	 * @return	string
	 */
	public function save_field($field_data = '', $field_settings = array(), $entry_id = FALSE)
	{
		
	}
	
	
	/**
	 * Displays the field data in a template tag.
	 *
	 * @access	public
	 * @param	array 		$params				The template tag parameters (key / value pairs).
	 * @param	string		$tagdata			The content between the opening and closing tags, if it's a tag pair.
	 * @param 	string		$field_data			The field data.
	 * @param 	array 		$field_settings		The field settings.
	 * @return	string
	 */
	public function display_tag($params = array(), $tagdata = '', $field_data = '', $field_settings = array())
	{
		
	}
	
	
}

/* End of file			: ft.dropdate.php */
/* Location of file		: /system/extensions/fieldtypes/dropdate/ft.dropdate.php */
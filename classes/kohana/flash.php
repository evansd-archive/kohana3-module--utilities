<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Provides a simple mechanism for passing data between requests (e.g.,
 * error or confirmation messages) and for rendering that data in the
 * view.
 *
 * @package    Utilities
 * @author     David Evans
 * @copyright  (c) 2009-2010 David Evans
 * @license    MIT-style
 */
class Kohana_Flash {
	
	// Configuration
	protected static $config;
	
	// Data available during current request
	protected static $data;

	// Data available during next request
	protected static $session;
	
	/**
	 * Loads the config, connects to the session and gets any flash
	 * data from the previous request
	 *
	 * @return  void
	 */
	protected static function initialize()
	{
		Flash::$config = Kohana::config('flash');
		
		// Get a reference to the session data array
		$session_data =& Session::instance(Flash::$config['session_group'])->as_array();
		
		// Bind the flash session to the flash array within the session data
		Flash::$session =& $session_data[Flash::$config['session_key']];
		
		// Copy existing flash data into the data array
		Flash::$data = is_array(Flash::$session) ? Flash::$session: array();
		
		// Clear any old flash data
		Flash::$session = array();
	}
	
	/**
	 * Gets a flash variable and removes it from the variables
	 * available to the next request (though it will still be available
	 * for the remainder of the current request).
	 * 
	 * If `$key` is NULL, gets the entire flash array.
	 *
	 * @param   mixed   variable to get (or NULL to get all)
	 * @param   mixed   default value
	 * @return  mixed
	 */
	public static function get($key, $default = NULL)
	{
		isset(Flash::$config) or Flash::initialize();
		
		if ($key !== NULL)
		{
			// Remove this key from the session, so it won't appear on
			// the next request and return the value
			unset(Flash::$session[$key]);
			return isset(Flash::$data[$key]) ? Flash::$data[$key] : $default;
		}
		
		// If key is NULL, return all data and clear it from the session
		else
		{
			Flash::$session = array();
			return empty(Flash::$data) ? $default : Flash::$data;
		}
	}
	
	/**
	 * Sets a flash variable, making it available during both the
	 * current and next request. Note, if the variable is read during
	 * the current request it will no longer be available during the
	 * next request.
	 * 
	 * Accepts either a single pair of key/value arguments or an array
	 * with multiple key/value pairs.
	 *
	 * @param   mixed   variable to set (or array of variables)
	 * @param   mixed   value
	 * @return  void
	 */
	public static function set($key, $value = NULL)
	{
		isset(Flash::$config) or Flash::initialize();
		
		// Accept a single key/value pair, or an array
		$values = is_array($key) ? $key : array($key => $value);
		
		foreach($values as $key => $value)
		{
			// Place value in both $data and $session so it's available
			// on this request and the next
			Flash::$session[$key] = Flash::$data[$key] = $value;
		}
	}
	
	/**
	 * Deletes a flash variable
	 *
	 * @param   string  variable to delete
	 * @return  void
	 */
	public static function delete($key)
	{
		isset(Flash::$config) or Flash::initialize();
		
		unset(Flash::$data[$key], Flash::$session[$key]);
	}
	
	/**
	 * Loads an array of flash variables into a view and renders it.
	 * 
	 * If `$key` is NULL, the array will contain all the flash data.
	 * 
	 * If the array contains a 'view' member, it will use that view.
	 * Otherwise it will use the default view specified in the flash
	 * config.
	 *
	 * @param   mixed  variable to render (NULL to render all)
	 * @return  string
	 */
	public static function render($key = NULL)
	{
		$data = Flash::get($key, '');
		
		if (is_array($data))
		{
			$view = isset($data['view']) ? $data['view'] : Flash::$config['default_view'];
			return View::factory($view, $data)->render(); 
		}
		else
		{
			return (string) $data;
		}
	}
	
	final private function __construct()
	{
		// This is a static class
	}
}

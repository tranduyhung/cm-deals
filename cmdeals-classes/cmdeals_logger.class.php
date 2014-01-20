<?php
/**
 * Allows log files to be written to for debugging purposes.
 *
 * @package WordPress
 * @subpackage CM Deals
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

class cmdeals_logger {
	
	private $handles;
	
	/** constructor */
	function __construct() {
		$this->handles = array();
	}

	/** destructor */
	function __destruct() {
	
		foreach ($this->handles as $handle) :
	       fclose( $handle );
	    endforeach;
	    
	}
	
	/**
	 * Open log file for writing
	 */
	private function open( $handle ) {
		global $cmdeals;
		
		if (isset($this->handles[$handle])) return true;
		
		if ($this->handles[$handle] = fopen( $cmdeals->plugin_path() . '/cmdeals-logs/' . $handle . '.txt', 'a' )) return true;
		
		return false;
	}
	
	/**
	 * Add a log entry to chosen file
	 */
	public function add( $handle, $message ) {
		
		if ($this->open($handle)) :
		
			$time = date('m-d-Y @ H:i:s -'); //Grab Time
			fwrite($this->handles[$handle], $time . " " . $message . "\n");
		
		endif;
		
	}
	
	/**
	 * Clear entrys from chosen file
	 */
	public function clear( $handle ) {
		
		if ($this->open($handle)) :
		
			ftruncate( $this->handles[$handle], 0 );
			
		endif;
		
	}

}
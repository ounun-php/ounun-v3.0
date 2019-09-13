<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

class session_storage_file extends session_storage
{
	function __construct($options = array())
	{
		$path = $options['session_n'] > 0 ? $options['session_n'].';"'.$options['session_path'].'"' : $options['session_path'];
		ini_set('session.save_handler', 'files');
    	session_save_path($path);
	}
}

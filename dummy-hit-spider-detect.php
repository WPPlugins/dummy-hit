<?php
/*
* Determines if the user agent is a spider, crawler or feed reader
*
* @return boolean is the user agent a spider, crawler or feed reader.
*/

function robot_agent() {
	// From inspecting my logs, this should catch almost any spider etc, but not "human" browswers
	// There is no guarantee though, the useragent string is fickle
	$robot_agents = array(
	 	'bot',
	 	'spider',
	 	'crawler',
	 	'scraper',
	 	'archiver',
	 	'blogpulselive',
	 	'crowsnest',
	 	'curl',
	 	'feed',
	 	'java',
	 	'wordpress',
	 	'kimengi',
	 	'magpie',
	 	'metauri',
	 	'mormor',
	 	'indy',
	 	'webcapture',
	 	'ics',
	 	'maxthon',
	 	'deepnet',
	 	'butterfly',
	 	'ezooms',
	 	'linkchecker',
	 	'slurp',
	 	'newsme',
	 	'extractor',
	 	'pear',
	 	'photon',
	 	'php',
	 	'ruby',
	 	'sopresto',
	 	'start.exe',
	 	'unwindfetchor',
	 	'w3c',
	 	'zend_http_client',
	 );
 
	 // Yup made a typo here
	 $reuqest_agent = $_SERVER["HTTP_USER_AGENT"];
 
	 foreach ($robot_agents as $a_robot) {
	 	// See the typo return :-)
		if (stripos($reuqest_agent, $a_robot) !== false) {
			 return true;
		}
	}
	return false;
}
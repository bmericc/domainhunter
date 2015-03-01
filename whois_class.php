<?php
/*

 +-----------------------------------------------------------------------+
 | Domain Hunter - A Simple Domain Monitoring Application                |
 | Version 0.1.2                                                         |
 |                                                                       |
 | Copyright (C) 2006-2015, Bahri.Info - Turkey                          |
 |									 | 
 | This program is free software: you can redistribute it and/or modify  |
 | it under the terms of the GNU General Public License as published by  |
 | the Free Software Foundation, either version 3 of the License, or     |
 | (at your option) any later version.					 |
 |									 |
 | This program is distributed in the hope that it will be useful,	 |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of	 |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the	 |
 | GNU General Public License for more details.				 |
 |									 |
 | You should have received a copy of the GNU General Public License	 |
 | along with this program.  If not, see <http://www.gnu.org/licenses/>. |                                     |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Bahri Meric CANLI <bahri@bahri.info>                          |
 +-----------------------------------------------------------------------+

Whois domain class based this files

*/

//////////////////////////////////////////////////////////////////////////////////////////////

$servers['com']['address'] = "whois.crsnic.net";
$servers['com']['free'] = "No match for";
$servers['com']['param'] = "";

$servers['net']['address'] = "whois.crsnic.net";
$servers['net']['free'] = "No match for";
$servers['net']['param'] = "";

//////////////////////////////////////////////////////////////////////////////////////////////


class Whois_domain {
	
	var $possible_tlds;
	var $whois_server;
	var $free_string;
	var $whois_param;
	var $domain;
	var $tld;
	var $compl_domain;
	var $msg;
	var $info;
	 
	function Whois_domain() {
		$this->info = "";
		$this->msg = "";
	}
	function process() {
		if ($this->create_domain()) {
			$this->get_domain_info();
		} else {
			$this->msg = "Only letters, numbers and hyphens (-) are valid!";
		}
	}
	function create_domain() {
		if (preg_match("/^([A-Za-z0-9]+(\-?[A-za-z0-9]*)){2,63}$/", $this->domain)) {
			$this->domain = strtolower($this->domain);
			$this->compl_domain = $this->domain.".".$this->tld;
			return true;
		} else {
			return false;
		}
	}
	function get_domain_info() {
		if ($this->create_domain()) {
			$data = ($this->tld == "nl") ? $this->get_whois_data(true) : $this->get_whois_data();
			if (is_array($data)) {
				foreach ($data as $val) {
					if (eregi($this->free_string, $val)) {
						$this->msg = "The domain name: <b>".$this->compl_domain."</b> is free.";
						$this->info = "";

						break;
					}
					$this->info .= $val;
				}
			} else {
				$this->msg = "Error, please try it again.";
			}
		} else {
			$this->msg = "Only letters, numbers and hyphens (-) are valid!";
		}
	}

	function get_whois_data($empty_param = false) { 
	// the parameter is new since version 1.20 and is used for .nl (dutch) domains only
		if ($empty_param) {
			$this->whois_param = "";
		}

			$connection = @fsockopen($this->whois_server, 43);
			if (!$connection) {
				unset($connection);
				$this->msg = "Can't connect to the server!";
				return;
			} else {
				sleep(2);

				fputs($connection, $this->whois_param.$this->compl_domain."\r\n");
				while (!feof($connection)) {
					$buffer[] = fgets($connection, 128);  // 4096
				}
				fclose($connection);
			}

		if (isset($buffer)) {
			// print_r($buffer);

			return $buffer;
		} else {
			$this->msg = "Can't retrieve data from the server!";
		}
	}
}





?>



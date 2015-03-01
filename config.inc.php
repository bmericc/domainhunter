<?
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

*/

$script_version = "0.1.2";

$per_page_domain = 20;

$hunter_email = "please@change.it";

/*
1- domainlabs.eu
2- domaintools.com
3- aboutuorg
*/

$detail_server = 2;

////////////////////////////////////////////////////////////////////

$dbhost   = "hostname"; // database hostname
$dbuname  = "database_user";  // database user name
$dbpass   = "password";  // database user password
$dbname   = "database_name";  // database name

//////////////////// mysql connect //////////////////////////////////

mysql_connect("$dbhost", "$dbuname", "$dbpass") || die (mysql_error());
mysql_select_db("$dbname") || die (mysql_error());


?>

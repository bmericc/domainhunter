<?
/*
 +-----------------------------------------------------------------------+
 | Domain Hunter - A Simple Domain Monitoring Application                |
 | Version 0.1.0                                                         |
 |                                                                       |
 | Copyright (C) 2006-2007, DomainLabs.EU - Turkey                       |
 | Licensed under the GNU GPLv3                                          |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Bahri Meric CANLI <bahri@bahri.info>                          |
 +-----------------------------------------------------------------------+

*/

$script_version = "0.1.0";

$per_page_domain = 20;

$hunter_email = "hostmaster@canlisoft.com";

/*
1- domainlabs.eu
2- domaintools.com
3- aboutuorg
*/

$detail_server = 2;

////////////////////////////////////////////////////////////////////

$dbhost = "localhost";
$dbuname = "hunter_user";
$dbpass = "fVaQWW";
$dbname = "domain_hunter";


//////////////////// mysql connect //////////////////////////////////

mysql_connect("$dbhost", "$dbuname", "$dbpass") || die (mysql_error());
mysql_select_db("$dbname") || die (mysql_error());


?>

<?php
/*

 +-----------------------------------------------------------------------+
 | Domain Hunter - A Simple Domain Monitoring Application                |
 | Version 0.1.2                                                         |
 |                                                                       |
 | Copyright (C) 2006-2015, Bahri.Info - Turkey                       |
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

function satirbul($satir) {

$satir = trim($satir);

/*********************** Domain Name *******************************/

$tok = strpos($satir, "Domain Name");
if ($tok !== false) {
$satir_sonuc[0] = "Domain Name";
$satir_sonuc[1] = str_replace("Domain Name: ", "", $satir);
}

/*********************** Registrar *******************************/

$tok = strpos($satir, "Registrar");
if ($tok !== false) {
$satir_sonuc[0] = "Registrar";
$satir_sonuc[1] = str_replace("Registrar: ", "", $satir);
}

/*********************** Whois Server *******************************/

$tok = strpos($satir, "Whois Server");
if ($tok !== false) {
$satir_sonuc[0] = "Whois Server";
$satir_sonuc[1] = str_replace("Whois Server: ", "", $satir);
}

/*********************** Referral URL *******************************/

$tok = strpos($satir, "Referral URL");
if ($tok !== false) {
$satir_sonuc[0] = "Referral URL";
$satir_sonuc[1] = str_replace("Referral URL: ", "", $satir);
}

/*********************** Name Server *******************************/

$tok = strpos($satir, "Name Server");
if ($tok !== false) {
$satir_sonuc[0] = "Name Server";
$satir_sonuc[1] = str_replace("Name Server: ", "", $satir);
}

/*********************** Status *******************************/

$tok = strpos($satir, "Status");
if ($tok !== false) {
$satir_sonuc[0] = "Status";
$satir_sonuc[1] = str_replace("Status: ", "", $satir);
}

/*********************** EPP Status *******************************/

$tok = strpos($satir, "EPP Status");
if ($tok !== false) {
$satir_sonuc[0] = "EPP Status";
$satir_sonuc[1] = str_replace("EPP Status: ", "", $satir);
}

/*********************** Updated Date *******************************/

$tok = strpos($satir, "Updated Date");
if ($tok !== false) {
$satir_sonuc[0] = "Updated Date";
$satir_sonuc[1] = str_replace("Updated Date: ", "", $satir);
}

/*********************** Creation Date *******************************/

$tok = strpos($satir, "Creation Date");
if ($tok !== false) {
$satir_sonuc[0] = "Creation Date";
$satir_sonuc[1] = str_replace("Creation Date: ", "", $satir);
}

/*********************** Expiration Date *******************************/

$tok = strpos($satir, "Expiration Date");
if ($tok !== false) {
$satir_sonuc[0] = "Expiration Date";
$satir_sonuc[1] = str_replace("Expiration Date: ", "", $satir);
}



return $satir_sonuc;
}







?>

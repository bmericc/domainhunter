<?php
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

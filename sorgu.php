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
 
include("whois_class.php");
include("satir_func.php");
include_once("config.inc.php");

function hunter_islemci($dom) {

Global $servers,$hunter_email;

$target_domain = explode(".", $dom);

$target_domainss = strtoupper($target_domain[0].".".$target_domain[1]);

echo $target_domainss;


	$my_whois = new Whois_domain;
	$my_whois->possible_tlds = array_keys($servers); // this is the array from the included server list
	$my_whois->tld = $target_domain[1];
	$my_whois->domain = $target_domain[0];
	$my_whois->free_string = $servers[ $target_domain[1] ]['free'];
	$my_whois->whois_server = $servers[ $target_domain[1] ]['address'];
	$my_whois->whois_param = $servers[ $target_domain[1] ]['param'];
	$my_whois->full_info = "yes";  // between "no" and "yes" to get all whois information
	$my_whois->process();


  if ($my_whois->info != "") { 
    	$sonuc = nl2br($my_whois->info); 
    	echo "    ok\n";
  	} 
else { 
	echo "    error\n";
	$bilgi_kontrol = 1;
	}



$sonuc = str_replace("   ", "", $sonuc);
// $sonuc = str_replace("   ", "<br>", $sonuc);
// $sonuc = str_replace("<br />", "<br>", $sonuc);
ereg('(.*)>>> Last update', $sonuc, $lines);
$temp = explode('<br />',$lines[1]);


$d=1; $j=1;$k = 1;

while($d<count($temp)) {


$kol =  satirbul($temp[$d]);


if ($kol[0] == "Domain Name") { $domain_name = $kol[1]; } 
if ($kol[0] == "Registrar") { $registrar = $kol[1]; } 
if ($kol[0] == "Whois Server") { $whois_server = $kol[1]; } 
if ($kol[0] == "Referral URL") { $referral_url = $kol[1]; } 
if ($kol[0] == "Status")  {  $status[$k] = $kol[1];  $k++; }
if ($kol[0] == "Updated Date") { $updated_date = $kol[1]; } 
if ($kol[0] == "Creation Date") { $creation_date = $kol[1]; } 
if ($kol[0] == "Expiration Date") { $expiration_date = $kol[1]; } 
if ($kol[0] == "Name Server")  {  $name_server[$j] = $kol[1];  $j++; }

	$d++;

}


$registrar = str_replace(",", " ", $registrar);
$creation_date  = strftime ("%Y-%m-%d", strtotime($creation_date));
$updated_date = strftime ("%Y-%m-%d", strtotime($updated_date)); 
$expiration_date = strftime ("%Y-%m-%d", strtotime($expiration_date)); 


$soru=mysql_query("SELECT count(id) FROM monitors where domain = '$target_domainss' ");
$row = mysql_fetch_assoc($soru);
$varmi = $row['count(id)'];


if ( ($varmi == 0 ) && ($bilgi_kontrol != 1) ) {


$new_domain = "INSERT INTO monitors (`domain`, `register`, `whois_serv`, `ref_url`, `nameserv1`, `nameserv2`, `nameserv3`, `nameserv4`, `nameserv5`,  `status1`, `status2`, `status3`, `create_date`, `update_date`, `expirate_date`) 

VALUES ('$domain_name', '$registrar',  '$whois_server', '$referral_url', '$name_server[1]', '$name_server[2]', '$name_server[3]', '$name_server[4]', '$name_server[5]', '$status[1]', '$status[2]', '$status[3]', '$creation_date', '$updated_date', '$expiration_date')";

$soru=mysql_query($new_domain) || die (mysql_error());

}
else if ($varmi != 0 ) {

$creation_date  = strftime ("%Y-%m-%d", strtotime($creation_date));
$updated_date = strftime ("%Y-%m-%d", strtotime($updated_date));
$expiration_date = strftime ("%Y-%m-%d", strtotime($expiration_date));


$b_s = "SELECT * FROM `monitors` WHERE domain = '$target_domainss'";
$b_r =  mysql_query ($b_s) ;
$sattir = mysql_fetch_array($b_r);


$esda_register = $sattir['register'];
$esda_whois_serv = $sattir['whois_serv'];
$esda_ref_url = $sattir['ref_url'];
$esda_nameserv1 = $sattir['nameserv1'];
$esda_nameserv2 = $sattir['nameserv2'];
$esda_nameserv3 = $sattir['nameserv3'];
$esda_nameserv4 = $sattir['nameserv4'];
$esda_nameserv5 = $sattir['nameserv5'];
$esda_status1 = $sattir['status1'];
$esda_status2 = $sattir['status2'];
$esda_status3 = $sattir['status3'];
$esda_create_date = $sattir['create_date'];
$esda_update_date = $sattir['update_date'];
$esda_expirate_date = $sattir['expirate_date'];


$update_sorgu = "UPDATE `monitors` SET ";


/***************       register             ***********************/

if ($esda_register != $registrar) { 

$update_sorgu .=" register  = '$registrar'  ";
$update_sorgu .=" , 
"; 

if ($mail_message == "") { $mail_message = "Change domain register	".$esda_register." -->  ".$registrar."\n"; }
else if ($mail_message != "") { $mail_message .= "Change domain register	".$esda_register." -->  ".$registrar."\n"; }
}

/***************       whois server             ***********************/

if ($esda_whois_serv != $whois_server) {

$update_sorgu .=" whois_serv = '$whois_server' ";
$update_sorgu .=" , 
"; 


if ($mail_message == "") { $mail_message = "Change whois server		".$esda_whois_serv." -->  ".$whois_server."\n"; }
else if ($mail_message != "") { $mail_message .= "Change whois server		".$esda_whois_serv." -->  ".$whois_server."\n"; }
}

/***************       referral url             ***********************/


if ($esda_ref_url != $referral_url) {
$update_sorgu .=" ref_url = '$referral_url' ";
$update_sorgu .=" , 
"; 


if ($mail_message == "") { $mail_message = "Change referral url		".$esda_ref_url." -->  ".$referral_url."\n"; }
else if ($mail_message != "") { $mail_message .= "Change referral url		".$esda_ref_url." -->  ".$referral_url."\n"; }
}


/***************       name server  1           ***********************/

if ($esda_nameserv1 != $name_server[1]) {
$update_sorgu .=" nameserv1 = '$name_server[1]' ";
$update_sorgu .=" , 
"; 


if ($mail_message == "") { $mail_message = "Change nameserver1		".$esda_nameserv1." -->  ".$name_server[1]."\n"; }
else if ($mail_message != "") { $mail_message .= "Change nameserver1		".$esda_nameserv1." -->  ".$name_server[1]."\n"; }
}

/***************       name server  2           ***********************/

if ($esda_nameserv2 != $name_server[2]) {
$update_sorgu .=" nameserv2 = '$name_server[2]' ";
$update_sorgu .=" , 
"; 


if ($mail_message == "") { $mail_message = "Change nameserver2		".$esda_nameserv2." -->  ".$name_server[2]."\n"; }
else if ($mail_message != "") { $mail_message .= "Change nameserver2		".$esda_nameserv2." -->  ".$name_server[2]."\n"; }
}

/***************       name server  3           ***********************/

if ($esda_nameserv3 != $name_server[3]) {
$update_sorgu .=" nameserv3 = '$name_server[3]' ";
$update_sorgu .=" , 
";


if ($mail_message == "") { $mail_message = "Change nameserver3		".$esda_nameserv3." -->  ".$name_server[3]."\n"; }
else if ($mail_message != "") { $mail_message .= "Change nameserver3		".$esda_nameserv3." -->  ".$name_server[3]."\n"; }
}

/***************       name server  4           ***********************/

if ($esda_nameserv4 != $name_server[4]) {
$update_sorgu .=" nameserv4 = '$name_server[4]' ";
$update_sorgu .=" , 
"; 


if ($mail_message == "") { $mail_message = "Change nameserver4		".$esda_nameserv4." -->  ".$name_server[4]."\n"; }
else if ($mail_message != "") { $mail_message .= "Change nameserver4		".$esda_nameserv4." -->  ".$name_server[4]."\n"; }
}

/***************       name server  5           ***********************/

if ($esda_nameserv5 != $name_server[5]) {
$update_sorgu .=" nameserv5 = '$name_server[5]' ";
$update_sorgu .=" , 
";


if ($mail_message == "") { $mail_message = "Change nameserver5		".$esda_nameserv5." -->  ".$name_server[5]."\n"; }
else if ($mail_message != "") { $mail_message .= "Change nameserver5		".$esda_nameserv5." -->  ".$name_server[5]."\n"; }
}

/***************       status 1         ***********************/

if ($esda_status1 != $status[1]) {

$update_sorgu .=" status1 = '$status[1]' ";
$update_sorgu .=" , 
"; 



if ($mail_message == "") { $mail_message = "Change status 1		".$esda_status1." -->  ".$status[1]."\n"; }
else if ($mail_message != "") { $mail_message .= "Change status 1		".$esda_status1." -->  ".$status[1]."\n"; }
}

/***************       status 2         ***********************/

if ($esda_status2 != $status[2]) {

$update_sorgu .=" status2 = '$status[2]'  ";
$update_sorgu .=" , 
"; 



if ($mail_message == "") { $mail_message = "Change status 2		".$esda_status2." -->  ".$status[2]."\n"; }
else if ($mail_message != "") { $mail_message .= "Change status 2		".$esda_status2." -->  ".$status[2]."\n"; }
}

/***************       status 3         ***********************/

if ($esda_status3 != $status[3]) {

$update_sorgu .=" status3 = '$status[3]' ";
$update_sorgu .=" , 
"; 



if ($mail_message == "") { $mail_message = "Change status 3		".$esda_status3." -->  ".$status[3]."\n"; }
else if ($mail_message != "") { $mail_message .= "Change status 3		".$esda_status3." -->  ".$status[3]."\n"; }
}

/***************       creation date         ***********************/

if ($esda_create_date != $creation_date) {

$update_sorgu .=" create_date = '$creation_date' ";
$update_sorgu .=" , 
"; 



if ($mail_message == "") { $mail_message = "Change creation date	".$esda_create_date." -->  ".$creation_date."\n"; }
else if ($mail_message != "") { $mail_message .= "Change creation date		".$esda_create_date." -->  ".$creation_date."\n"; }
}

/***************       updated date         ***********************/

if ($esda_update_date != $updated_date) {

$update_sorgu .=" update_date = '$updated_date' ";
$update_sorgu .=" , 
"; 



if ($mail_message == "") { $mail_message = "Change updated date		".$esda_update_date." -->  ".$updated_date."\n"; }
else if ($mail_message != "") { $mail_message .= "Change updated date		".$esda_update_date." -->  ".$updated_date."\n"; }
}

/***************       expiration date         ***********************/

if ($esda_expirate_date != $expiration_date) {

$update_sorgu .=" expirate_date = '$expiration_date' ";
 $update_sorgu .=" , 
";


if ($mail_message == "") { $mail_message = "Change expiration date		".$esda_expirate_date." -->  ".$expiration_date."\n"; }
else if ($mail_message != "") { $mail_message .= "Change expiration date		".$esda_expirate_date." -->  ".$expiration_date."\n"; }
}


$tarih = mktime (date ("H"), date ("i"), date ("s"), date("m"), date ("d"), date("Y"));
$hunter_update = date ("Y-m-d H:i:s", $tarih);


$update_sorgu .=" hunter_update = '$hunter_update' WHERE domain = '$target_domainss' ";

// echo $update_sorgu;


if ( ($bilgi_kontrol != 1) && ($mail_message != "") ) {

$soru=mysql_query($update_sorgu) || die (mysql_error());



$send_message = "
Dear domain hunter user

Domain alert for $target_domainss

$mail_message

Thanks
Domain Hunter Control Systems 
";



$subject = "Domain alert for $target_domainss"; //Subject of the e-mail

mail($hunter_email, $subject, $send_message, "From: Undisclosed-Recipient:;\nX-Mailer: PHP/" . phpversion());

}


} /// if end






} /// function end




?>
  


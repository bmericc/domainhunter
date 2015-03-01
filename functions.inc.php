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

*/


function tr_strtolower($metin) {
    $metin = strtr($metin, "IĞÜŞİÖÇ", "igusioc");
    return strtolower($metin);
}
function tr_strtoupper($metin) {
    $metin = strtr($metin, "ığüşiöç", "IGUSIOC");
    return strtoupper($metin);
}


function standartcontrol($kelime) {

$error = "0";

$xnsor = $kelime[0].$kelime[1].$kelime[2].$kelime[3];

if ( ($kelime[strlen($kelime)-1] == "-" ) || ($kelime[0]=="-") ) { $error = "1"; }

else if (strlen($kelime)>64) { $error = "2"; }

else {

        for ($i = 0; $i < strlen($kelime); $i++) {

        if ( ($kelime[$i] != "-") && (!eregi("[a-z0-9]", $kelime[$i])) ) { $error = "3"; }

        }

        if ($xnsor == "xn--")  { $error = "4"; }
}

return $error;
}


function getdetail($domain, $serverid=1) {

$serverlist[1] = "whois.domainlabs.eu";
$serverlist[2] = "whois.domaintools.com";
$serverlist[3] = "www.aboutus.org";

$detailurl = "http://".$serverlist[$serverid]."/".tr_strtolower($domain);

return $detailurl;
}


function secPOST($Value) {
	$Value = mres($_POST[$Value]);
	return $Value;
}

function secGET($Value) {
	$Value = mres($_GET[$Value]);
	return $Value;
}

function mres($q) {
    if(is_array($q)) 
        foreach($q as $k => $v) 
            $q[$k] = mres($v); //recursive
    elseif(is_string($q))
        $q = mysql_real_escape_string($q);
    return $q;
}

?>

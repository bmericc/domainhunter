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

include ("sorgu.php");
include ("config.inc.php");
include ("functions.inc.php");

$b_s = "SELECT * FROM `monitors` ";

$b_r =  mysql_query ($b_s) ;

while ($sattir = mysql_fetch_array($b_r)) {

$z = hunter_islemci(tr_strtolower($sattir['domain']));


}


?>

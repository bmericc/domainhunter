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

include ("sorgu.php");
include ("config.inc.php");
include ("functions.inc.php");

$b_s = "SELECT * FROM `monitors` ";

$b_r =  mysql_query ($b_s) ;

while ($sattir = mysql_fetch_array($b_r)) {

$z = hunter_islemci(tr_strtolower($sattir['domain']));


}


?>

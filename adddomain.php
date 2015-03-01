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

include ("config.inc.php");
include ("header.inc.php");
include ("functions.inc.php");
include ("sorgu.php");

?>


<div id="edit_form">
<form name="create_domain" method="post">
<table>
   <tr>
      <td colspan="3"><h3>Add new domain</h3></td>

   </tr>
   <tr>
      <td>Domain:</td>
      <td><input class="flat" type="text" name="fDomain" value="" /></td>
      <td>&nbsp;</td>
   </tr>
   <tr>
      </td>
      <td>&nbsp;</td>
   </tr>
   <tr>
      <td colspan="3" class="hlp_center"><input class="button" type="submit" name="submit" value="Add domain" /></td>
   </tr>

<?php

if ( secPOST('fDomain') != false ) {

$domain = tr_strtolower(secPOST('fDomain'));

$domain_array = explode(".", $domain);

if ( ( strlen($domain_array[0]) >= 3 ) && ($domain_array[0]!="www") && ( ($domain_array[1]=="com") ||  ($domain_array[1]=="net") ) ) {

$realdomain = tr_strtoupper($domain_array[0].'.'.$domain_array[1]);
$error_status =  standartcontrol($domain_array[0]);

}
else { $error_status = 8; $error_message = "version ".$script_version." only com and net extension <br> (example : domainhunter.com)"; }


if ($error_status==0) {

	$var = mysql_result(mysql_query("SELECT count( id ) FROM `monitors` WHERE `domain` = '$realdomain'  "),0,0);

	if ($var == 0 ) {

		$sql = "INSERT INTO monitors (domain) VALUES ('$realdomain')";
		$results = mysql_query($sql);

		echo '
		<tr>
		      <td colspan="3" class="standout">Adding table domain!<br />('; 

		$z = hunter_islemci(tr_strtolower($realdomain));

		echo ')</br></td>
   		</tr>
		';


	}
	else { $error_status = 9; $error_message = "Existing domain in table"; }

}
else if ($error_status==4) { $error_message = "Does not IDN support"; }



 	if ($error_status!=0) {

		echo '
		<tr>
      			<td colspan="3" class="standout">Error '.$error_status.' : '.$error_message.'</br /></td>
   		</tr>
		';
	}


}
else 

{ 

echo '
   <tr>
      <td colspan="3" class="standout">&nbsp;</td>
   </tr>';

} 
?>

</table>
</form>
</div>


<?php

include "foother.inc.php"; 

?>

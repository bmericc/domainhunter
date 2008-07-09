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

if ( (isset($_POST['fDomain'])) && ($_POST['fDomain'] !="") ) {

$domain = tr_strtolower($_POST['fDomain']);

$domain_array = explode(".", $domain);

if ( ( strlen($domain_array[0]) > 3 ) && ($domain_array[0]!="www") && ( ($domain_array[1]=="com") ||  ($domain_array[1]=="net") ) ) {

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

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

?>

<br>
<table border="0" align="center" cellpadding="0" cellspacing="0" class="tblPaging">
<tr>

<?php


if ($start!=0) {
$previous_page = $start - 10;
echo "<td style=\"width:1em\" valign=\"bottom\">&nbsp;&nbsp;<a style=\"color:#000000\" href=\"?start=$previous_page&order=".$order_by." \"><b>Previous</b></a>&nbsp;&nbsp;</td>";
}

if ($start<90) { $page_no = 0; $kaclik = 11;}
else { $page_no = $start - 100; }


for($i=$page_no;$i<=$domain_count;$i=$i+$per_page_domain) {
$j++;

 if ( ($kaclik==11) && ($j==11) ) { break; }
 else if ($j==21)  { break; }

$sonuc_hane = $i/$per_page_domain+1;

if ($i==$start) {

echo '<td valign="bottom" class="tdPaging"><span style="width:1em;text-align:center;color:#ff8b2e" ><b>'.$sonuc_hane.'</b></span>&nbsp;&nbsp;</td>';

}
else if ($start<=$domain_count) {
echo '<td style="width:1em" valign="bottom"><a class="aPaging" href="?start='.$i.'&order='.$order_by.'">'.$sonuc_hane.'</a>&nbsp;&nbsp;</td>';

}


}


$next_page = $start + $per_page_domain;

if ($next_page<=$domain_count) {


echo '<td style="width:1em" valign="bottom">&nbsp;&nbsp;<a style="color:#000000" href="?start='.$next_page.'&order='.$order_by.'"><b>
Next</b></a>&nbsp;&nbsp;</td>';

}

?>

</tr></table>

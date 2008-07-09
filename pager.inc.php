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

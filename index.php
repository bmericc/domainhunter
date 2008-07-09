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


include "config.inc.php"; 
include "header.inc.php";
include "functions.inc.php";

$domain_count = mysql_result(mysql_query("SELECT count( id ) FROM `monitors` "),0,0);

if( ($_GET['start']!= "")  &&  ( isset($_GET['start']) )  )  { $start = $_GET['start']; }
else { $start=0; }

$h_id = $start;
$order_by = $_GET['order']; 

if ($order_by=="expira") { $order_sql = " ORDER BY `monitors`.`expirate_date` ASC ";  }
else if ($order_by=="control") { $order_sql = " ORDER BY `monitors`.`hunter_update` DESC ";  }
else if ($order_by=="update") { $order_sql = " ORDER BY `monitors`.`update_date` DESC ";  }
else { $order_sql = " ORDER BY `monitors`.`hunter_update` DESC ";  }

////////////////////////////////////////////////////////////////////////////////////////////////////////

if(isset($_POST['process'])) {

 $delete=$_POST['delete'];
 foreach ($delete as $key => $value) {
 $delete_id = $key;
 }

$domain_name = mysql_result(mysql_query("SELECT domain FROM `monitors` WHERE id = '$delete_id' "),0,0);

$delete_query = "DELETE FROM `monitors` WHERE id = '$delete_id' ";
$delete_result = mysql_query($delete_query) or die($delete_query."<br>".mysql_error());

echo "<p class=\"result\"><b>Wiped domain:</b> $domain_name</p>";

}

////////////////////////////////////////////////////////////////////////////////////////////////////////

$sql = "SELECT * FROM `monitors` $order_sql  LIMIT $start,$per_page_domain";
$results = mysql_query($sql) or die(mysql_error());

?>

<form name="delete_domain" method="post">
<input name="process" type="hidden" value="delete">
<table id="admin_table" >
<tr class="header"  >
<td>Id</td>
<td>Domain</td>
<td>Status</td>
<td>Created</td>
<td class="hilightoff" onMouseOver="className='hilighton';" onMouseOut="className='hilightoff';" ><a href ="?start=<?php echo $_GET['start'];?>&order=update">Last Update</a></td>
<td class="hilightoff" onMouseOver="className='hilighton';" onMouseOut="className='hilightoff';" ><a href ="?start=<?php echo $_GET['start'];?>&order=expira">Expires</a></td>
<td class="hilightoff" onMouseOver="className='hilighton';" onMouseOut="className='hilightoff';" ><a href ="?start=<?php echo $_GET['start'];?>&order=control">Last Control</a></td>
<td>Register</td>
<td></td>

</tr>
<?php
while($rows = mysql_fetch_array($results)) {

$h_id++;

echo "<tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\"> <td>";

echo $h_id;
echo "</td><td><a href=\"".getdetail($rows[domain], $detail_server)."\" target=\"_blank\">". $rows[domain]." <img src=\"new-win-icon.gif\" border=\"0\" align=\"absmiddle\" /> </a>";
echo "</td><td>". $rows[status1];
echo "</td><td>". $rows[create_date];
echo "</td><td>". $rows[update_date];
echo "</td><td>". $rows[expirate_date];
echo "</td><td>". $rows[hunter_update];
echo "</td><td>". $rows[register];
echo "</td><td>";

echo "<input name=\"delete[".$rows[id]."]\" type=\"submit\" id=\"delete\" style=\"width:50px\" ";

echo "value=\"delete\" class=\"button\" />";

echo "</td></tr>\n\n";
}

 ?>
</table>
</form>
<?php 
if($domain_count >= $per_page_domain) {
include "pager.inc.php"; } 
include "foother.inc.php"; 
?>


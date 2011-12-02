<?
/*
 * File: example.php
 * Description: Received Mail Example
 * Created: 01-03-2006
 * Author: Mitul Koradia
 * Email: mitulkoradia@gmail.com
 * Cell : +91 9825273322
 */
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("display_errors","true");
header('Content-Type: text/html; charset=utf-8');
 /*
 * По протоколу pop3 нельзя работать с флагами. Нужно это иметь ввиду.
 * Для работы скрипта нужно, чтобы был установлен mod_imap и ssl (ну, если нужно использовать ssl)
 *
 */

include("receivemail.class.php");
// Creating a object of reciveMail Class
//$obj= new receiveMail('igor.deyashkin@gmail.com','','igor.deyashkin@gmail.com','imap.gmail.com','imap','993',true);
$obj= new receiveMail('Oboltus45@yandex.ru','bugaga','Oboltus45@yandex.ru','imap.yandex.ru','imap','993',true); //143 false
//Connect to the Mail Box
$obj->connect();         //If connection fails give error message and exit
//echo('connect ok');
// Get Total Number of Unread Email in mail box
$tot=$obj->getTotalMails(); //Total Mails in Inbox Return integer value
//echo "Total Mails:: $tot<br>";
// $tot -= 1;
for($i=$tot;$i>$tot-1;$i--)
{
	$head=$obj->getHeaders($i);  // Get Header Info Return Array Of Headers **Array Keys are (subject,to,toOth,toNameOth,from,fromName)
	
	echo "Subject :: ".$head['subject']."<br>";
	echo "SubjectD :: ".$head['subjectD']."<br>";
	//echo "TO :: ".$head['to']."<br>";
	//echo "To Other :: ".$head['toOth']."<br>";
	//echo "ToName Other :: ".$head['toNameOth']."<br>";
	//echo "From :: ".$head['from']."<br>";
	//echo "FromName :: ".$head['fromName']."<br>";
	echo "Recent :: ".$head['Recent']."<br>";
	echo "Unseen :: ".$head['Unseen']."<br>";
	//echo "Flagged :: ".$head['Flagged']."<br>";
	//echo "<br><br>";
	echo "<br>*******************************************************************************************<BR>";
	echo $obj->getBody($i);  // Get Body Of Mail number Return String Get Mail id in interger
	
	$str=$obj->GetAttach($i,"./"); // Get attached File from Mail Return name of file in comma separated string  args. (mailid, Path to store file)
	$ar=explode(",",$str);
	foreach($ar as $key=>$value)
		echo ($value=="")?"":"Atteched File :: ".$value."<br>";
	echo "<br>------------------------------------------------------------------------------------------<BR>";
	
	//Если флагов не выставлять - он автоматом все равно ставит seen
	//$s = imap_setflag_full($obj->marubox, $i, "\\Unseen");
	//$s = imap_clearflag_full($obj->marubox, $i, "\\Seen");
	//var_dump($s);
	//echo("{$obj->marubox}, {$i}, \\Sen");
	$head=$obj->getHeaders($i);
	//echo "Recent :: ".$head['Recent']."<br>";
	//$obj->deleteMails($i); // Delete Mail from Mail box
}
$obj->close_mailbox();   //Close Mail Box

?>
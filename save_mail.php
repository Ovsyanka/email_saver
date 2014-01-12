<?
/*
 * Скрипт сохранения почты на диске
 */
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("display_errors","true");
header('Content-Type: text/html; charset=utf-8');
 /*
 * По протоколу pop3 нельзя работать с флагами. Нужно это иметь ввиду.
 * Для работы скрипта нужно, чтобы был установлен mod_imap и ssl (ну, если нужно использовать ssl)
 */
include("mreadd.php");
require("config.php");
// Creating a object of reciveMail Class
#TODO: Сделать проверку на то, что данное письмо уже сохранено.
#TODO: Вынести настройки прав к файлам и директориям в конфиг.
#TODO: Сделать отдельную ф-ю для сохранения файлов.
#TODO: 

// $dir = scandir('./oboltus45@yandex.ru/');
// $dir1 = scandir('./oboltus45@yandex.ru/'.$dir[2]);
// print_r("$dir, $dir1");
// unlink('./oboltus45@yandex.ru/'.$dir[2].'/'.$dir1[2]);
// rmdir('./oboltus45@yandex.ru/'.$dir[2]);
// die();

foreach($mail_boxes as $mail_box) {
	echo save_mails($mail_box);
}

/*
Сохраняет письма с определенного ящика, настройки доступа передаются в переменной (3) mail_access_conf
и представляют собой array(connect string, login, password);
login string = строка типа "{imap.yandex.ru:993/imap/ssl}"
*/
function save_mails($mail_access_conf) {
	$host = $mail_access_conf[0];
	$login = $mail_access_conf[1];
	$password = $mail_access_conf[2];
	$mbox = new mread($host, $login, $password);
	$mails = $mbox->mail; //Тут содержатся все непрочитанные письма
	if (!$mails) return 'Новых писем нету.';
	$total_count = count($mails);
	
	//Цикл по всем письмам.
	for($i=$total_count-1;$i>=0;$i--) {
		//echo 'from: '.$mails[$i]['from']."<br>";
		//echo 'subject: '.$mails[$i]['subject']."<br>";
		
		$from = $mails[$i]['from'];
		//Оставляю только часть до @
		// $from = preg_match('/(\w+)@(\w+)/', $mails[$i]['from'], $matches);
		// $from = $matches[1].'_'.$matches[2];
		//Нужно оставить в теме только разрешенные символы. Кодировка - utf-8
		$subject = $mails[$i]['subject'];
		$subject = mb_convert_encoding($subject, FILENAME_ENCODING, 'utf-8');
		$reg_exp = '/[^а-яА-Яa-zA-z\d\-_]/'; //Запрещенные символы все кроме перечисленных
		$reg_exp = mb_convert_encoding($reg_exp, FILENAME_ENCODING, 'utf-8');
		$subject = mb_ereg_replace($reg_exp, '', $subject);
		$subject = mb_substr($subject, 0, 20, FILENAME_ENCODING);
		$body = $mails[$i]['html'];
		if (!$body) $body = $mails[$i]['plain'];
		
		$letter = "Дата: ".$mails[$i]['date']." <br />\n";
		$letter .= "От: ".$mails[$i]['from']." <br />\n";
		$letter .= "Кому: ".$mails[$i]['to']." <br />\n";
		$letter .= "Тема: ".$mails[$i]['subject']." <br />\n";
		$letter .= " <br />\n Тело письма: <br />\n ". $body;
		
		$date = date('d.m.Y', strtotime($mails[$i]['date']));
		
		$mail_store_path = "./$login/".$mails[$i]['uid'].'_'.$from."_".$date."_".$subject;
		// unlink($mail_store_path."/$subject.html");
		// rmdir($mail_store_path);
		// continue;
		//echo($mail_store_path);
		if (!is_dir($mail_store_path)) create_dir($mail_store_path);
		file_put_contents($mail_store_path."/letter.html",$letter);
		chmod($mail_store_path."/letter.html",0777);
		
		if ($mails[$i]['attach']) foreach($mails[$i]['attach'] as $file_name => $file_data) {
			//echo "Atteched File :: ".$file_name."<br>";
			file_put_contents($mail_store_path.'/'.$file_name, $file_data);
			chmod($mail_store_path.'/'.$file_name,0777);
		}
		//echo "<br>------------------------------------------------------------------------------------------<BR>";
		
		//Если флагов не выставлять - он автоматом все равно ставит seen
		//$s = imap_setflag_full($mails->marubox, $i, "\\Unseen");
		//$s = imap_clearflag_full($mbox->mbox, $i, "\\Seen");
	}
	return "сохранено $total_count писем";
}
?>
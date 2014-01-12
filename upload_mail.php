<?
/*Нужно создать папку uattach. В ней папки с именами ящиков и в них файлы, которые надо загрузить на почту. Если надо в одно письмо несколько файлов, тогда надо их поместить в одну папку.*/

require('config.php');

$muploader = new mail_uploader($mail_boxes);
$muploader->upload();

class mail_uploader {
	
	private $mail_config;
	
	function __construct($mail_boxes) {
		//Формируем mail_config как массив, где в каач-ве ключа будет название папки из которой будут браться аттачи (совпадает с логином)
		//а в кач-ве параметров все три настройки.
		$this->mail_config = array();
		foreach($mail_boxes as $mail) {
			$this->mail_config[$mail[1]] = array(
				'connect'=>$mail[0],
				'login'=>$mail[1],
				'pass'=>$mail[2],
			);
		}
	}
	
	 function upload() {
		$uattach_dir = 'uattach';
		$attached_dir = 'attached';
		foreach ($this->mail_config as $path=>$conf) {
			$attaches_dir = $uattach_dir.'/'.$path;
			if (!is_dir($attaches_dir)) continue;
			$attaches = scandir($attaches_dir);
			if (!$attaches or count($attaches) == 2) continue;
			unset($attaches[0]);
			unset($attaches[1]);
			foreach($attaches as $attach) {
				$success = $this->upload_one_mail($attaches_dir.'/'.$attach, $conf);
				if ($success) {
					$new_path = $attached_dir.'/'.$path;
					if (!is_dir($new_path)) create_dir($new_path);
					//rename($attaches_dir.'/'.$attach, $new_path.'/'.$attach);
				}
			}
		}
	}
	
	function upload_one_mail($attach_path, $conf) {
		//if (is_dir($attach_path)) $attaches = scandir($attach_path);
		//if (is_file($attach_path)) $attaches = array($attach_path);
		//unset($attaches[0]);
		//unset($attaches[1]);
		
		if (!is_file($attach_path)) return false;
		
		/*$mbox = imap_open ($conf['connect'], $conf['login'], $conf['pass']) or die(imap_last_error());
		$list = imap_getmailboxes($mbox, $conf['connect'], "*");
		if (is_array($list)) {
			foreach ($list as $key => $val) {
				echo "($key) ";
				//echo imap_utf7_decode($val->name) . ",";
				//mb_convert_encoding( $str,"WINDOWS-1251", "UTF7-IMAP" )
				echo $val->name . ",";
				echo "'" . $val->delimiter . "',";
				echo $val->attributes . "<br />\n";
			}
		} else {
			echo "imap_getmailboxes failed: " . imap_last_error() . "\n";
		}
		return false;
		
		*/
		
		//Это название папки для яндекса
		$folder="&BCcENQRABD0EPgQyBDgEOgQ4-";
		//$folder='INBOX';
		$mbox = imap_open ($conf['connect'].$folder, $conf['login'], $conf['pass']) or die(imap_last_error());
		
		$dmy=date("d-M-Y H:i:s");
		//$filename = array_pop($attaches);
		$filename = $attach_path;
		$filestring = file_get_contents($attach_path);
		$attachment = chunk_split(base64_encode($filestring));
	   
		$boundary = "------=".md5(uniqid(rand()));
	   
		$msg = ("From: Somebody\r\n"
			. "To: test@example.co.uk\r\n"
			. "Date: $dmy\r\n"
			. "Subject: This is the subject\r\n"
			. "MIME-Version: 1.0\r\n"
			. "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n"
			. "\r\n\r\n"
			. "--$boundary\r\n"
			. "Content-Type: text/html;\r\n\tcharset=\"ISO-8859-1\"\r\n"
			. "Content-Transfer-Encoding: 8bit \r\n"
			. "\r\n\r\n"
			. "Hello this is a test\r\n"
			. "\r\n\r\n"
			. "--$boundary\r\n"
			. "Content-Transfer-Encoding: base64\r\n"
			. "Content-Disposition: attachment; filename=\"$filename\"\r\n"
			. "\r\n" . $attachment . "\r\n"
			. "\r\n\r\n\r\n"
			. "--$boundary--\r\n\r\n"); 
		
		imap_append($mbox, $conf['connect'].$folder, $msg, "\\Draft");
		var_dump(imap_last_error());
		imap_close($mbox);
		
		return true;
	} 
}
?>
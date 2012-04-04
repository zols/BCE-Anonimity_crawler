<?php
class Mailer {
	private $encoding;
	private $contentEncoding; // 'B' => 'base64', 'Q' => 'quoted-printable'
	private $to = array();
	private $cc = array();
	private $bcc = array();
	private $from;
	private $replyto = array();
	private $subject;
	private $priority = 3;// 1 = High, 3 = Normal, 5 = low
	private $bodyText;
	private $bodyHtml;
	//private $attachment = array();

	/*
	To do:
	- If no text content -> setformat
	- AttachImages
	*/
	public function __construct($encoding = "UTF-8", $contentEncoding = "B") {
		$this->encoding = $encoding;
		$this->contentEncoding = $contentEncoding;
	}

	public function addTo($email, $name=null) {
		$this->to[$email] = $name;
	}

	public function addCc($email, $name=null) {
		$this->cc[$email] = $name;
	}

	public function addBcc($email, $name=null) {
		$this->bcc[$email] = $name;
	}

	public function addSubject($subject) {
		$this->subject = $subject;
	}

	public function addFrom($email, $name) {
		$this->from = $this->mimeEncode($name).' <'.$email.'>';
	}

	public function addReplyTo($email, $name) {
		$this->replyto = $this->mimeEncode($name).' <'.$email.'>';
	}

	public function setPrority($priority) {
		$this->priority = $priority;
	}

	public function setTextBody($text) {
		$this->bodyText = $text;
	}

	public function setHtmlBody($text) {
		$this->bodyHtml = $text;
	}

	// array("storage_name" = > "gjh5g34gj", "file_name" => "file.zip", "mime" => "application/zip")
	/*public function addAttachment($file) {
		if (file_exists("files/".$file["storage_name"])) {
				$this->attachment[] = $file;
		} else {
			die('File to be attached does not exist!');
		}
	}*/

	private function parseEmailAddresses($emailArray) {
		$isFirst = true;
		$output = '';
		
		foreach ($emailArray as $key => $value) {
			if ($isFirst) {
				$isFirst = false;
			} else {
				$output.= ', ';
			}
			
			if (empty($value)) {
				$output.= $key;
			} else {
				$output.= $this->mimeEncode($value).' <'.$key.'>';
			}
		}
		
		return $output;
	}

	private function quotedPrintableEncode($string) {
		$string = str_replace(array('%20', '%0D%0A', '%'), array(' ', "\r\n", '='), rawurlencode($string));
		$string = preg_replace('/[^\r\n]{73}[^=\r\n]{2}/', "$0=\r\n", $string);

		return $string;
	}

	private function mimeEncode($string) {
		$output = "=?".$this->encoding."?".$this->contentEncoding."?";
		if ($this->contentEncoding == "B") {
			$output.= base64_encode($string);
		} else if ($this->contentEncoding == "Q") {
			$output.= $this->quotedPrintableEncode($string);
		}
		$output.= "?=";
		return $output;
	}

	public function send() {
		$to = "";
		$subject = "";
		$headers = "";
		$message = "";

		if (isset($this->to)) {
			$to = $this->parseEmailAddresses($this->to);
		} else {
			die("`To` field is compulsory");
		}

		if (isset($this->from)) {
			$headers.= 'From: '.$this->from."\n";
			$headers.= "Return-Path: ".$this->from."\n";
		} else {
			die('`From` field is compulsory');
		}

		if (!empty($this->replyto)) {
			$headers.= "Reply-To: ".$this->replyto."\n";
		}

		if (!empty($this->cc)) {
			$headers.= "cc: ".$this->parseEmailAddresses($this->cc)."\n";
		}

		if (!empty($this->bcc)) {
			$headers.= "Bcc: ".$this->parseEmailAddresses($this->bcc)."\n";
		}

		if (isset($this->subject)) {
			$subject = $this->mimeEncode($this->subject);
		} else {
			die('`Subject` field is compulsory');
		}

		$headers.= 'X-Priority: '.$this->Priority;
		$headers.= 'X-Mailer: '.SITE_NAME."\n";
		$headers.= 'MIME-Version: 1.0'."\n";
		$random_hash = md5(date('r', time()));

		/*if (!empty($this->attachment)) {
			$headers.= "Content-Type: multipart/mixed; boundary=\"mixed-".$random_hash."\"";
			$message = "--mixed-".$random_hash."\n";
			$message.= "Content-Type: multipart/alternative; boundary=\"alt-".$random_hash."\"\n\n";
		} else {*/
			$headers.= "Content-Type: multipart/alternative; boundary=\"alt-".$random_hash."\"";
		//}

		$message.= "--alt-".$random_hash."\n";
		if (isset($this->bodyText)) {
			$message.= "Content-Type: text/plain; charset=\"".$this->encoding."\"\n";
			$message.= "Content-Transfer-Encoding: ";
			if ($this->contentEncoding == "B") {
				$message.= "base64\n\n".base64_encode($this->bodyText)."\n\n";
			} else if ($this->contentEncoding == "Q") {
				$message.= "quoted-printable\n\n".$this->quotedPrintableEncode($this->bodyText)."\n\n";
			}
			$message.= "--alt-".$random_hash;
			if (empty($this->bodyHtml)) {
				$message.= "--";
			}
			$message.= "\n";
		}

		if (isset($this->bodyHtml)) {
			$message.= "Content-Type: text/html; charset=\"".$this->encoding."\"\n";
			$message.= "Content-Transfer-Encoding: ";
			if ($this->contentEncoding == "B") {
				$message.= "base64\n\n".base64_encode($this->bodyHtml)."\n\n";
			} else if ($this->contentEncoding == "Q") {
				$message.= "quoted-printable\n\n".$this->quotedPrintableEncode($this->bodyHtml)."\n\n";
			}
			$message.= "--alt-".$random_hash."--\n\n";
		}

		/*if (!empty($this->attachment)) {
			foreach ($this->attachment as $key => $value) {
				$message.= "--mixed-".$random_hash."\n";
				$message.= "Content-Type: ".$this->attachment[$key]["mime"]."; name=\"".$this->attachment[$key]["file_name"]."\"\n";
				$message.= "Content-Transfer-Encoding: base64\n";
				$message.= "Content-Disposition: attachment\n\n";
				$message.= chunk_split(base64_encode(file_get_contents("files/".$this->attachment[$key]["storage_name"])))."\n";
				$message.= "--mixed-".$random_hash;
			}
		}*/
		$message.= "--";

		$result = mail($to, $subject, $message, $headers);
		
		unset($encoding);
		unset($contentEncoding);
		unset($to);
		unset($cc);
		unset($bcc);
		unset($from);
		unset($replyto);
		unset($subject);
		unset($bodyText);
		unset($bodyHtml);
		//unset($attachment);

		return $result;
	}
}
?>
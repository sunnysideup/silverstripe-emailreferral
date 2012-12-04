<?php

class EmailAFriendForm extends Form {

	protected static $friend_email_address_label = "Friend's Email Address";
		static function set_friend_email_address_label($v) {self::$friend_email_address_label = $v;}
		static function get_friend_email_address_label() {return self::$friend_email_address_label;}

	protected static $message_label = "Message";
		static function set_message_label($v) {self::$message_label = $v;}
		static function get_message_label() {return self::$message_label;}

	protected static $your_email_address_label = "Your email address";
		static function set_your_email_address_label($v) {self::$your_email_address_label = $v;}
		static function get_your_email_address_label() {return self::$your_email_address_label;}

	protected static $send_label = 'Send';
		static function set_send_label($v) {self::$send_label = $v;}
		static function get_send_label() {return self::$send_label;}

	function __construct($controller, $name, $id) {
		Requirements::themedCSS("EmailReferral");
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript("emailreferral/javascript/EmailReferralForm.js");

		$fields[] = new EmailField('To', self::$friend_email_address_label);
		$fields[] = new TextareaField('Message', self::$message_label, 5, 20, EmailAFriendExtension::get_default_message() ? EmailAFriendExtension::get_default_message() : '');
		$fields[] = new LiteralField('AdditionalMessage', '<div id="additionalMessageStuff"><p>'.Director::absoluteURL($controller->Link()).'</p><p>Sent by: <span id="emailReplacer">[your email address]</span></p></div>');
		$fields[] = new EmailField('YourMailAddress', self::$your_email_address_label);
		$fields[] = new HiddenField('PageID', 'PageID', $id);

		$fields = new FieldSet($fields);

		$actions = new FieldSet(new FormAction('sendEmailAFriend', self::$send_label));

		$requiredFields = new RequiredFields(array('To', 'Message'));

		parent::__construct($controller, $name, $fields, $actions, $requiredFields);
	}

	function sendEmailAFriend($RAW_data, $form) {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$data = Convert::raw2sql($RAW_data);
		if($page = DataObject::get_by_id('Page', $data['PageID'])) $pageLink = $page->AbsoluteLink();

		$tos = explode(',', $data['To']);
		$toList = array();
		foreach($tos as $to) $toList = array_merge($toList, explode(';', $to));
		if($data['YourMailAddress']) $toList[] = $data['YourMailAddress'];
		$ip = EmailAFriendExtension::get_ip_user();
		$count = 0;
		if(EmailAFriendExtension::get_max_message_phour_pip()) {
			$anHourAgo = date('Y-m-d H:i:s', mktime(date('G') - 1, date('i'), date('s'), date('n'), date('j'), date('Y')));
			if($friendMails = DataObject::get('FriendEmail', "{$bt}IPAddress{$bt} = '$ip' AND {$bt}Created{$bt} > '$anHourAgo'")) {
				$count = $friendMails->Count();
			}
		}

		if(EmailAFriendExtension::get_sender_name()) {
			$mailFrom = EmailAFriendExtension::get_sender_name();
			if(EmailAFriendExtension::get_sender_email_address()) $mailFrom .= ' <' . EmailAFriendExtension::get_sender_email_address() . '>';
		}
		else if(EmailAFriendExtension::get_sender_email_address()) $mailFrom = EmailAFriendExtension::get_sender_email_address();
		else $mailFrom = 'Unknown Sender';

		foreach($toList as $index => $to) {
			if(! EmailAFriendExtension::get_max_message_phour_pip() || $count < EmailAFriendExtension::get_max_message_phour_pip()) {
				$friendEmail = new FriendEmail();
				$friendEmail->To = $to;
				$friendEmail->Message = $data['Message'];
				$friendEmail->From = $data['YourMailAddress'] ? $data['YourMailAddress'] : 'Unknown Sender';
				$friendEmail->IPAddress = $ip;
				$friendEmail->PageID = $data['PageID'];
				$friendEmail->write();
				$subject = EmailAFriendExtension::get_mail_subject() ? EmailAFriendExtension::get_mail_subject() : '';
				$subject .= ' | from '.$data['YourMailAddress'];
				$count++;
				$email = new Email(
					$mailFrom,
					$to,
					$subject,
					Convert::raw2xml($data['Message']) . '<br/><br/>Page Link : ' . $pageLink. '<br /><br />Sent by: '.$data['YourMailAddress']
				);
				$email->send();
			}
			else {
				$stopIndex = $index;
				break;
			}
		}

		if(count($toList) > 0) {
			$content = '';
			$endIndex = isset($stopIndex) ? $stopIndex : count($toList);
			if(! isset($stopIndex) || $stopIndex > 0) {
				$content .= '<p class="message good">This page has been successfully emailed to the following addresses :</p><ul>';
				for($i = 0; $i < $endIndex; $i++) $content .= '<li>' . $toList[$i] . '</li>';
				$content .= '</ul>';
			}
			if($endIndex < count($toList)) {
				$content .= '<p class="message required">This page could not be emailed to the following addresses :</p><ul>';
				for($i = $endIndex; $i < count($toList); $i++) $content .= '<li>' . $toList[$i] . '</li>';
				$content .= '</ul>';
			}
		}
		else $content = '<p class="message required">This page has not been emailed to anyone.</p>';

		$templateData = array("EmailAFriendThankYouContent" => $content);
		return $this->customise($templateData)->renderWith('EmailAFriendHolder');
	}
}


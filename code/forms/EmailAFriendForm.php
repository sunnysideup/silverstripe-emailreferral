<?php

class EmailAFriendForm extends Form {

	/**
	 * @var String
	 */
	private static $friend_email_address_label = "Friend#039;s Email Address";

	/**
	 * @var String
	 */
	private static $message_label = "Message";

	/**
	 * @var String
	 */
	private static $your_email_address_label = "Your email address";

	/**
	 * @var String
	 */
	private static $send_label = 'Send';

	/**
	 * @param Controller $controller
	 * @param String $name
	 */
	function __construct($controller, $name) {
		Requirements::themedCSS("EmailReferral", "emailreferral");
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript("emailreferral/javascript/EmailReferralForm.js");

		$fields[] = new HiddenField('PageID', 'PageID', $controller->dataRecord->ID);

		$fields[] = new EmailField('To', $this->Config()->get("your_email_address_label"));
		$fields[] = new TextareaField(
			'Message',
			$this->Config()->get("message_label"),
			$this->Config()->get("EmailAFriendExtension", "default_message")
		);
		$fields[] = new LiteralField('AdditionalMessage', '<div id="additionalMessageStuff"><p>'.Director::absoluteURL($controller->Link()).'</p><p>Sent by: <span id="emailReplacer">[your email address]</span></p></div>');
		$fields[] = new EmailField('YourMailAddress', $this->Config()->get("friend_email_address_label"));

		$fields = new FieldList($fields);

		$actions = new FieldList(new FormAction('sendemailafriend', $this->Config()->get("send_label")));

		$requiredFields = new RequiredFields(array('YourMailAddress', 'To', 'Message'));

		parent::__construct($controller, $name, $fields, $actions, $requiredFields);
	}

	/**
	 *
	 * @param Array
	 * @param EmailAFriendForm
	 */
	function sendemailafriend($RAW_data, $form) {
		$data = Convert::raw2sql($RAW_data);
		if($page = Page::get()->byID(intval($data['PageID']))) {
			$pageLink = $page->AbsoluteLink();
		}
		$tos = explode(',', $data['To']);
		$toList = array();
		foreach($tos as $to) $toList = array_merge($toList, explode(';', $to));
		if($data['YourMailAddress']) $toList[] = $data['YourMailAddress'];
		$ip = EmailAFriendExtension::get_ip_user();
		$count = 0;
		if($this->Config()->get("EmailAFriendExtension", "max_message_phour_pip")) {
			$anHourAgo = date('Y-m-d H:i:s', mktime(date('G') - 1, date('i'), date('s'), date('n'), date('j'), date('Y')));
			$count = FriendEmail::get()->filter(
				array(
					"IPAddress" => $ip,
					"Created:GreaterThan" => $anHourAgo
				)
			)->count();
		}
		if($this->Config()->get("EmailAFriendExtension", "sender_name")) {
			$mailFrom = $this->Config()->get("EmailAFriendExtension", "sender_name");
			if($this->Config()->get("EmailAFriendRole", "sender_email_address")) {
				$mailFrom .= ' <' . $this->Config()->get("EmailAFriendExtension", "sender_email_address") . '>';
			}
		}
		elseif($this->Config()->get("EmailAFriendExtension", "sender_email_address")) {
			$mailFrom = $this->Config()->get("EmailAFriendExtension", "sender_email_address");
		}
		else {
			$mailFrom = 'Unknown Sender';
		}

		foreach($toList as $index => $to) {
			$messagesPerHour = $this->Config()->get("EmailAFriendExtension", "max_message_phour_pip");
			if($messagesPerHour && $count > $messagesPerHour) {
				$stopIndex = $index;
				break;
			}
			else {
				$friendEmail = new FriendEmail();
				$friendEmail->To = $to;
				$friendEmail->Message = $data['Message'];
				$friendEmail->From = $data['YourMailAddress'] ? $data['YourMailAddress'] : 'Unknown Sender';
				$friendEmail->IPAddress = $ip;
				$friendEmail->PageID = $data['PageID'];
				$friendEmail->write();
				$subject = $this->Config()->get("EmailAFriendExtension", "mail_subject");
				$subject .= ' | sent by '.$data['YourMailAddress'];
				$email = new Email(
					$mailFrom,
					$to,
					$subject,
					Convert::raw2xml($data['Message']) . '<br/><br/>Page Link : ' . $pageLink. '<br /><br />Sent by: '.$data['YourMailAddress']
				);
				$outcome = $email->send();
				if($outcome) {
					$count++;
				}
				else {
					unset($toList[$index]);
				}
			}
		}

		if(count($toList) > 0) {
			$content = '';
			$endIndex = isset($stopIndex) ? $stopIndex : count($toList);
			if(! isset($stopIndex) || $stopIndex > 0) {
				$content .= '<p class="message good">This page has been successfully emailed to the following addresses :</p><ul>';
				for($i = 0; $i < $endIndex; $i++) {
					$content .= '<li>' . $toList[$i] . '</li>';
				}
				$content .= '</ul>';
			}
			if($endIndex < count($toList)) {
				$content .= '<p class="message required">This page could not be emailed to the following addresses :</p><ul>';
				for($i = $endIndex; $i < count($toList); $i++) {
					$content .= '<li>' . $toList[$i] . '</li>';
				}
				$content .= '</ul>';
			}
		}
		else {
				$content = '<p class="message required bad">This page has not been emailed to anyone.</p>';
		}

		$content .= '<br/><p>Click <a href="' . $this->controller->Link() . '">here</a> to go back to the previous page.</p>';

		$templateData = array("EmailAFriendThankYouContent" => $content);
		return $this->customise($templateData)->renderWith('EmailAFriendHolder');

	}
}


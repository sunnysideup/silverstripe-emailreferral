<?php

class EmailAFriendExtension extends SiteTreeExtension {

	/**
	 * @var String
	 */
	private static $sender_email_address = "";

	/**
	 * @var String
	 */
	private static $sender_name = "";

	/**
	 * @var String
	 */
	private static $default_message = "";

	/**
	 * @var Int
	 */
	private static $max_message_phour_pip = 1;

	/**
	 * @var String
	 */
	private static $mail_subject = "";

	/**
	 *
	 * @return String
	 */
	public static function get_ip_user() {
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else {
			return isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : $_SERVER['REMOTE_ADDR'];
		}
	}

	/**
	 * @return String
	 */
	function EmailAFriendLink() {
		if($this->owner->param("Action") != "emailafriend") {
			return $this->owner->Link('emailafriend');
		}
	}
	protected $emailAFriendThankYouContent = "";

	function setEmailAFriendThankYouContent($v) {
		$this->emailAFriendThankYouContent = $v;
	}

	function EmailAFriendThankYouContent() {
		return $this->emailAFriendThankYouContent;
	}
}


class EmailAFriendExtension_Controller extends Extension {

	private static $allowed_actions = array('EmailAFriendForm', 'emailafriend');

	protected $emailAFriendShowForm = false;

	/**
	 * @return EmailAFriendForm
	 */
	function EmailAFriendForm() {
		return new EmailAFriendForm($this->owner, 'EmailAFriendForm');
	}

	function emailafriend() {
		$this->showForm = true;
		return $this->owner->renderWith('EmailAFriendHolder', 'Page_emailafriend');
	}

	function EmailAFriendShowForm() {
		return $this->emailAFriendShowForm;
	}
}

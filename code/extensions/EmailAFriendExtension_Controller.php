<?php



class EmailAFriendExtension_Controller extends Extension {

	private static $allowed_actions = array('EmailAFriendForm', 'emailafriend');

	protected $emailAFriendShowForm = false;

	/**
	 * @return EmailAFriendForm
	 */
	function EmailAFriendForm() {
		return new EmailAFriendForm($this->owner, 'EmailAFriendForm');
	}

	function emailafriend($request) {
		$this->showForm = true;
		if($request->isAjax()) {
			return $this->owner->renderWith('EmailAFriendHolder', 'Page_emailafriend');
		}
		else {
			return $this->owner->renderWith('EmailAFriendHolder_NOAJAX');
		}
	}

	function EmailAFriendShowForm() {
		return $this->emailAFriendShowForm;
	}

	/**
	 * @return String
	 */
	function EmailAFriendLink() {
		if($this->owner->request->param("Action") != "emailafriend") {
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

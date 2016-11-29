<?php



class EmailAFriendExtension_Controller extends Extension
{
    private static $allowed_actions = array('EmailAFriendForm', 'emailafriend');

    protected $emailAFriendShowForm = false;

    /**
     * @return EmailAFriendForm
     */
    public function EmailAFriendForm()
    {
        return new EmailAFriendForm($this->owner, 'EmailAFriendForm');
    }

    public function emailafriend($request)
    {
        $this->showForm = true;
        if ($request->isAjax()) {
            return $this->owner->renderWith('EmailAFriendHolder', 'Page_emailafriend');
        } else {
            return $this->owner->renderWith('EmailAFriendHolder_NOAJAX');
        }
    }

    public function EmailAFriendShowForm()
    {
        return $this->emailAFriendShowForm;
    }

    /**
     * @return String
     */
    public function EmailAFriendLink()
    {
        if ($this->owner->request->param("Action") != "emailafriend") {
            return $this->owner->Link('emailafriend');
        }
    }
    protected $emailAFriendThankYouContent = "";

    public function setEmailAFriendThankYouContent($v)
    {
        $this->emailAFriendThankYouContent = $v;
    }

    public function EmailAFriendThankYouContent()
    {
        return $this->emailAFriendThankYouContent;
    }
}

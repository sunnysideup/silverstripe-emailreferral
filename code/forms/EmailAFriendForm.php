<?php

class EmailAFriendForm extends Form
{

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
     * yes or no
     * @var String
     */
    private static $mail_to_site_owner_only = 'no';

    /**
     * @param Controller $controller
     * @param String $name
     */
    public function __construct($controller, $name)
    {
        Requirements::themedCSS("EmailReferral", "emailreferral");
        Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
        Requirements::javascript("emailreferral/javascript/EmailReferralForm.js");

        $fields[] = new HiddenField('PageID', 'PageID', $controller->dataRecord->ID);

        $fields[] = new EmailField('YourMailAddress', $this->Config()->get("your_email_address_label"));
        $fields[] = new TextareaField(
            'Message',
            $this->Config()->get("message_label"),
            Config::inst()->get("EmailAFriendExtension", "default_message")
        );
        if ($this->Config()->get("mail_to_site_owner_only") != 'yes') {
            $fields[] = new LiteralField('AdditionalMessage', '<div id="additionalMessageStuff"><p>'.Director::absoluteURL($controller->Link()).'</p><p>Sent by: <span id="emailReplacer">[your email address]</span></p></div>');
            $fields[] = new EmailField('To', $this->Config()->get("friend_email_address_label"));
        }

        $fields = new FieldList($fields);

        $actions = new FieldList(new FormAction('sendemailafriend', $this->Config()->get("send_label")));
        if ($this->Config()->get("mail_to_site_owner_only") != 'yes') {
            $requiredFields = new RequiredFields(array('YourMailAddress', 'To', 'Message'));
        } else {
            $requiredFields = new RequiredFields(array('YourMailAddress', 'Message'));
        }

        parent::__construct($controller, $name, $fields, $actions, $requiredFields);
    }

    /**
     *
     * @param Array
     * @param EmailAFriendForm
     */
    public function sendemailafriend($RAW_data, $form)
    {
        $stopSending = false;
        $adminEmail = Config::inst()->get("Email", "admin_email");
        $data = Convert::raw2sql($RAW_data);

        // get page
        if ($page = Page::get()->byID(intval($data['PageID']))) {
            $pageLink = $page->AbsoluteLink();
        }

        // set to list
        $toList = array();
        if ($this->Config()->get("mail_to_site_owner_only") !== 'yes') {
            $tos = explode(',', $data['To']);
            foreach ($tos as $to) {
                $toList = array_merge($toList, $tos);
            }
        } else {
            $toList[] = $adminEmail;
        }

        //add mailer address
        if ($data['YourMailAddress']) {
            $toList[] = $data['YourMailAddress'];
        }

        //get previously sent ones
        $ip = EmailAFriendExtension::get_ip_user();
        $mailsSent = 0;
        $maxSent = intval(Config::inst()->get("EmailAFriendExtension", "max_message_phour_pip"));
        if ($maxSent) {
            $anHourAgo = date('Y-m-d H:i:s',strtotime('-1 hour'));
            $mailsSent = FriendEmail::get()->filter(
                array(
                    "IPAddress" => $ip,
                    "Created:GreaterThan" => $anHourAgo
                )
            )->count();
        }

        // set mailFrom
        if ($this->Config()->get("mail_to_site_owner_only") !== 'yes') {
            $mailFrom = $data['YourMailAddress'];
        } else {
            $senderName = Config::inst()->get("EmailAFriendExtension", "sender_name");
            $senderEmailAddress = Config::inst()->get("EmailAFriendRole", "sender_email_address");
            if ($senderName && $senderEmailAddress) {
                $mailFrom = $senderName.' <' . $senderEmailAddress . '>';
            } elseif ($senderEmailAddress) {
                $mailFrom = $senderEmailAddress;
            } else {
                $mailFrom = $adminEmail;
            }
        }

        // send emails
        $goodSent = [];
        $badSent = [];
        foreach ($toList as $index => $to) {
            $mailsSent = intval($mailsSent);
            if ($maxSent && $mailsSent > $maxSent) {
                $stopSending = true;
                break;
            } else {
                $mailsSent++;
                $friendEmail = new FriendEmail();
                $friendEmail->To = $to;
                $friendEmail->Message = $data['Message'];
                $friendEmail->From = $mailFrom;
                $friendEmail->IPAddress = $ip;
                $friendEmail->PageID = $data['PageID'];
                $friendEmail->write();
                $subject = Config::inst()->get("EmailAFriendExtension", "mail_subject");
                $subject .= ' | sent by '.$data['YourMailAddress'];
                $email = new Email(
                    $mailFrom,
                    $to,
                    $subject,
                    Convert::raw2xml($data['Message']) . '<br/><br/>Page Link : ' . $pageLink. '<br /><br />Sent by: '.$data['YourMailAddress']
                );
                $outcome = $email->send();
                if($outcome) {
                    $goodSent[] = $to;
                } else {
                    $badSent[] = $to;
                }
                $friendEmail->Sent = $outcome;
                $friendEmail->write();
            }
        }

        if (count($toList)) {
            $content = '';
            $endIndex = isset($stopIndex) ? $stopIndex : count($toList);
            if (count($goodSent)) {
                $content .= '<p class="message good">This page has been successfully e-mailed to the following addresses:</p><ul>';
                foreach($goodSent as $email) {
                    $content .= '<li>' . $email . '</li>';
                }
                $content .= '</ul>';
            }
            if (count($badSent)) {
                $content .= '<p class="message required">This page could not be e-mailed to the following addresses:</p><ul>';
                foreach($badSent as $email) {
                    $content .= '<li>' . $email . '</li>';
                }
                $content .= '</ul>';
            }
        } else {
            $content = '<p class="message required bad">This page has not been e-mailed to anyone.</p>';
        }

        $content .= '<br/><p><a href="' . $this->controller->Link() . '">Send more?</a></p>';

        $templateData = array("EmailAFriendForm" => null, "EmailAFriendThankYouContent" => $content);

        return $this->customise($templateData)->renderWith('EmailAFriendHolder');
    }
}

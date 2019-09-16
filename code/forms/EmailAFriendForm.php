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
        $adminEmail = Config::inst()->get("Email", "admin_email");
        $data = Convert::raw2sql($RAW_data);
        if ($page = Page::get()->byID(intval($data['PageID']))) {
            $pageLink = $page->AbsoluteLink();
        }
        $toList = array();
        if ($this->Config()->get("mail_to_site_owner_only") != 'yes') {
            $tos = explode(',', $data['To']);
            foreach ($tos as $to) {
                $toList = array_merge($toList, explode(';', $to));
            }
        } else {
            $toList[] = $adminEmail;
        }
        if ($data['YourMailAddress']) {
            $toList[] = $data['YourMailAddress'];
        }
        $ip = EmailAFriendExtension::get_ip_user();
        $count = 0;
        if (Config::inst()->get("EmailAFriendExtension", "max_message_phour_pip")) {
            $anHourAgo = date('Y-m-d H:i:s', mktime(date('G') - 1, date('i'), date('s'), date('n'), date('j'), date('Y')));
            $count = FriendEmail::get()->filter(
                array(
                    "IPAddress" => $ip,
                    "Created:GreaterThan" => $anHourAgo
                )
            )->count();
        }
        if ($this->Config()->get("mail_to_site_owner_only") != 'yes') {
            $mailFrom = $data['YourMailAddress'];
        } else {
            if (Config::inst()->get("EmailAFriendExtension", "sender_name")) {
                $mailFrom = Config::inst()->get("EmailAFriendExtension", "sender_name");
                if (Config::inst()->get("EmailAFriendRole", "sender_email_address")) {
                    $mailFrom .= ' <' .Config::inst()->get("EmailAFriendExtension", "sender_email_address") . '>';
                }
            } elseif (Config::inst()->get("EmailAFriendExtension", "sender_email_address")) {
                $mailFrom = Config::inst()->get("EmailAFriendExtension", "sender_email_address");
            } else {
                $mailFrom = $adminEmail;
            }
        }
        foreach ($toList as $index => $to) {
            $messagesPerHour = Config::inst()->get("EmailAFriendExtension", "max_message_phour_pip");
            if ($messagesPerHour && $count > $messagesPerHour) {
                $stopIndex = $index;
                break;
            } else {
                $friendEmail = new FriendEmail();
                $friendEmail->To = $to;
                $friendEmail->Message = $data['Message'];
                $friendEmail->From = $data['YourMailAddress'];
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
                if ($outcome) {
                    $count++;
                } else {
                    unset($toList[$index]);
                }
            }
        }

        if (count($toList) > 0) {
            $content = '';
            $endIndex = isset($stopIndex) ? $stopIndex : count($toList);
            if (! isset($stopIndex) || $stopIndex > 0) {
                $content .= '<p class="message good">This page has been successfully e-mailed to the following addresses :</p><ul>';
                for ($i = 0; $i < $endIndex; $i++) {
                    $content .= '<li>' . $toList[$i] . '</li>';
                }
                $content .= '</ul>';
            }
            if ($endIndex < count($toList)) {
                $content .= '<p class="message required">This page could not be e-mailed to the following addresses :</p><ul>';
                for ($i = $endIndex; $i < count($toList); $i++) {
                    $content .= '<li>' . $toList[$i] . '</li>';
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

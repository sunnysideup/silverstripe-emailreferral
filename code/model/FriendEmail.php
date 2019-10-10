<?php

class FriendEmail extends DataObject
{
    private static $db = array(
        'To' => 'Text',
        'Message' => 'Text',
        'From' => 'Text',
        'IPAddress' => 'Text',
        'Sent' => 'Boolean',
    );

    private static $has_one = array(
        'Page' => 'Page'
    );

    private static $casting = array(
        "ShortMessage" => "Varchar"
    );

    public function ShortMessage()
    {
        return $this->getShortMessage();
    }
    public function getShortMessage()
    {
        return substr($this->Message, 0, 30);
    }

    public function canCreate($member = null)
    {
        return false;
    }

    public function canEdit($member = null)
    {
        return false;
    }

    public function canDelete($member = null)
    {
        return false;
    }

    private static $searchable_fields = array('To', 'Message', 'From', 'IPAddress', 'Page.Title');

    private static $summary_fields = array(
        'Created.Nice' => 'When',
        'To' => 'To',
        'ShortMessage' => 'Message',
        'From' => 'From',
        'Page.Title' => 'Page',
        'Sent.Nice' => 'Sent',
    );

    private static $singular_name = 'Message to Friend';

    private static $plural_name = 'Messages to Friends';

    private static $default_sort = 'Created DESC';

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $page = $this->Page();
        if($page && $page->exists()) {
            $fields->addFieldsToTab(
                'Root.Main',
                [
                    ReadonlyField::create(
                        'PageLink',
                        'From',
                        DBField::create_field('HTMLText', '<a href="'.$page->CMSEditLink().'">'.$page->Title.'</a>')
                    ),
                    ReadonlyField::create(
                        'CreatedInfo',
                        'When',
                        date('d-m-Y H:i', strtotime($this->Created))
                    ),

                ]
            );
        }
        return $fields;
    }
}

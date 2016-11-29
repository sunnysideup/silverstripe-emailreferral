<?php


class FriendEmailAdmin extends ModelAdmin
{
    private static $managed_models = array("FriendEmail");

    private static $url_segment = 'emails';

    private static $menu_title = 'Friend Emails';

    public $showImportForm = false;
}

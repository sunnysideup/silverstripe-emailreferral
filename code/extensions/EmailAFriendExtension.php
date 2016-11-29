<?php

class EmailAFriendExtension extends SiteTreeExtension
{

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
    public static function get_ip_user()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : $_SERVER['REMOTE_ADDR'];
        }
    }
}

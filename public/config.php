<?php
return array(
    /*
     * If the app IS in subdirectory, like: http://localhost/some-directory/app/ you need to specify the extra path:
     * e.g.: app_extra_path = 'some-directory/app'
     * ! DO NOT include trailing '/' (before or after)
     *
     * If the app IS NOT in subdirectory, like: http://localhost/
     * e.g.: app_extra_path = ''
     *
     * */
    'app_extra_path' => '',

    /* db credentials */
    'db' => array(
        'host' => '', # todo
        'username' => '', # todo
        'password' => '', # todo
        'dbname' => 'site_analysis',
        'port' => 3306,
        'charset' => 'UTF8', // ! needed
    ),

    /* smtp data: */
    'email_config' => array(
        'smtp_server' => '', #todo
        'smtp_port' => '465', //'587',
        'smtp_secure' => 'ssl', //'tls',
        'smtp_username' => '', # todo
        'smtp_password' => '', # todo
        'reply_to_mail' => 'no-reply@boomlink.de',
        'new_user_subject' => 'Your new user on boomlink',
        'new_user_body' => '../public/PHPMailer/new_user.html',
        'new_mail_subject' => 'Your email has been changed.', // disabled
        'new_mail_body' => '../public/PHPMailer/new_mail.html', // disabled
    ),

    /* links that do not require authentication */
    'pages_no_auth' => array(
        'appserver',
        'cron',
        'login',
        'trackvisitors',
        'documentation',
        'questions',
    ),

    /* pages that are making logging: action/s that is/are taking place */
    'pages_do_log' => array(
        'campaign',
        'manual_campaign',
        'runningcampaigns',
        'crawllist',
        'import'
    ),

    /* actions that are not being logged: like accessing a controller or exporting data */
    'log_skip_actions' => array(
        'index',
        'export',
    ),
);
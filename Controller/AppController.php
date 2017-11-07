<?php

/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('CakeEmail', 'Network/Email');
App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
define('EMAIL_FROM','YOUR_FROM_EMAIL');
define('EMAIL_FROM_NAME','YOUR_NAME');
class AppController extends Controller {

    public function send_email_static($to = '', $to_name = '', $message_subject = '', $message_body = '', $cc = array(), $cron = NULL, $attachments = array(), $fromName = '') {
        $Email = new CakeEmail('gmail');
        $Email->emailFormat('html');
        $Email->from(array(EMAIL_FROM => EMAIL_FROM_NAME));

        if (!empty($fromName)) {
            $Email->from(array(EMAIL_FROM => $fromName));
        }
        $Email->to($to);

        if (isset($attachments) && !empty($attachments)) {
            $Email->attachments($attachments);
        }
        if (isset($cc) && !empty($cc)) {
            $Email->cc($cc);
        }

        $Email->subject($message_subject);
        
        if ($message_body != '') {
            $sendmail = $Email->send($message_body);
            $this->email_extra_items = array();
            return $sendmail;
        }
        return false;
    }
}

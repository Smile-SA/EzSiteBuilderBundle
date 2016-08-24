<?php

namespace EdgarEz\SiteBuilderBundle\Mail;

use Swift_Mailer;
use Swift_Message;

/**
 * Class Sender
 *
 * @package EdgarEz\SiteBuilderBundle\Mail
 */
class Sender
{
    /** @var Swift_Mailer */
    protected $mailer;

    /**
     * @param Swift_Mailer $mailer
     */
    public function __construct(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Sends e-mail with content based on form settings.
     *
     * @param string $message
     * @param string $title
     * @param string $senderEmail
     * @param string $recipientEmail
     *
     * @return bool
     */
    public function send($message, $subject, $senderEmail, $recipientEmail)
    {
        $message = Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($senderEmail)
            ->setTo($recipientEmail)
            ->setBody($message, 'text/plain')
        ;

        $this->mailer->send($message);
    }
}

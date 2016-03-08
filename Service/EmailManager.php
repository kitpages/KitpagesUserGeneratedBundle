<?php
namespace Kitpages\UserGeneratedBundle\Service;

use Kitpages\UserGeneratedBundle\Event\UserGeneratedEvent;
use Kitpages\UserGeneratedBundle\KitpagesUserGeneratedEvents;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Templating\EngineInterface;


class EmailManager
{
    /** @var null|LoggerInterface */
    protected $logger = null;

    /** @var null|\Symfony\Component\Templating\EngineInterface */
    protected $templating = null;

    /** @var null|\Symfony\Component\EventDispatcher\EventDispatcherInterface */
    protected $dispatcher = null;

    /** @var null|\Swift_Mailer */
    protected $mailer = null;

    /** @var null|string */
    protected $fromEmail = null;

    /** @var array of emails */
    protected $adminEmailList = array();

    public function __construct(
        LoggerInterface $logger,
        \Swift_Mailer $mailer,
        EngineInterface $templating,
        EventDispatcherInterface $dispatcher,
        $fromEmail,
        $adminEmailList
    )
    {
        $this->logger = $logger;
        $this->templating = $templating;
        $this->dispatcher = $dispatcher;
        $this->mailer =  $mailer;
        $this->fromEmail = $fromEmail;
        $this->adminEmailList = $adminEmailList;
    }


    /**
     * Event listener that send an email
     * @param ShopEvent $event
     */
    public function afterCommentPostEvent(UserGeneratedEvent $event)
    {
        if ($event->isPropagationStopped()) {
            return;
        }
        $commentPost = $event->get('commentPost');

        $subject = $this->templating->render(
            "KitpagesUserGeneratedBundle:Email:afterCommentPostSubject.html.twig",
            array(
                "commentPost" => $commentPost
            )
        );
        $body = $this->templating->render(
            "KitpagesUserGeneratedBundle:Email:afterCommentPostBody.html.twig",
            array(
                "commentPost" => $commentPost
            )
        );

        $message = \Swift_Message::newInstance()
            ->setFrom($this->fromEmail)
            ->setTo($this->adminEmailList)
            ->setSubject($subject)
            ->setBody($body)
            ->setContentType('text/html');
        $this->mailer->send($message);

    }
}

<?php

namespace Kitpages\UserGeneratedBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Collections\ArrayCollection;

use Kitpages\UserGeneratedBundle\Entity\CommentPost;
use Kitpages\UserGeneratedBundle\Event\UserGeneratedEvent;
use Kitpages\UserGeneratedBundle\KitpagesUserGeneratedEvents;


class CommentController extends Controller
{
    public function displayPostListAction(
        $itemReference
    )
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $em->getRepository('KitpagesUserGeneratedBundle:CommentPost');
        if($this->get('security.context')->isGranted('ROLE_USER_GENERATED_ADMIN') ) {
            $postList = $repo->findByItemReference($itemReference);
        } else {
            $postList = $repo->findByItemReference($itemReference, CommentPost::STATUS_VALIDATED);
        }

        return $this->render(
            "KitpagesUserGeneratedBundle:Comment:displayPostList.html.twig",
            array(
                'postList' => $postList
            )
        );
    }

    public function displayNumberPostAction(
        $itemReference
    )
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $em->getRepository('KitpagesUserGeneratedBundle:CommentPost');

        // number of post validated
        $nbPost = $repo->countPostByItemReference($itemReference, CommentPost::STATUS_VALIDATED);

        return $this->render(
            "KitpagesUserGeneratedBundle:Comment:displayNumberPost.html.twig",
            array(
                'numberPost' => $nbPost
            )
        );
    }

    public function ajaxNextStatusAction()
    {
        $commentPostId = $this->getRequest()->query->get("postId", null);
        if (!$this->get('security.context')->isGranted('ROLE_USER_GENERATED_ADMIN') ) {
            return new Response("fail");
        }
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $em->getRepository('KitpagesUserGeneratedBundle:CommentPost');
        $post = $repo->find($commentPostId);
        if ($post->getStatus() == CommentPost::STATUS_VALIDATED) {
            $post->setStatus(CommentPost::STATUS_REFUSED);
            $em->flush();
            return new Response(CommentPost::STATUS_REFUSED);
        }
        if ($post->getStatus() == CommentPost::STATUS_REFUSED) {
            $post->setStatus(CommentPost::STATUS_WAITING_VALIDATION);
            $em->flush();
            return new Response(CommentPost::STATUS_WAITING_VALIDATION);
        }
        if ($post->getStatus() == CommentPost::STATUS_WAITING_VALIDATION) {
            $post->setStatus(CommentPost::STATUS_VALIDATED);
            $em->flush();
            return new Response(CommentPost::STATUS_VALIDATED);
        }
        $post->setStatus(CommentPost::STATUS_WAITING_VALIDATION);
        $em->flush();
        return new Response(CommentPost::STATUS_WAITING_VALIDATION);
    }

    public function newPostAction(
        $itemReference,
        $itemUrl = null,
        $itemId = null,
        $itemClass = null,

        $userName = null,
        $userId = null,
        $userEmail = null,

        $languageCode = null,
        $extra = array()
    )
    {
        $form = $this->getPostForm(
            $itemReference,
            $itemUrl,
            $itemId,
            $itemClass,

            $userName,
            $userId,
            $userEmail,

            $languageCode,
            $extra
        );


        $data = $form->getData();
        $targetUrl = $data["targetUrl"];
        return $this->render(
            'KitpagesUserGeneratedBundle:Comment:newPost.html.twig',
            array(
                'form' => $form->createView(),
                'targetUrl' => $targetUrl
            )
        );
    }

    public function doNewPostAction()
    {
        $request = $this->getRequest();
        $form = $this->getPostForm(
            "none"
        );
        $trans = $this->get('translator');

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $encrypted = $data["tokenEncrypted"];
                $hash = $this->get('kitpages_util.hash');
                $check = $hash->checkHash(
                    $encrypted,
                    $data["itemReference"],
                    $data["itemUrl"],
                    $data["itemId"],
                    $data["itemClass"],
                    $data["extraJson"],
                    $data["targetUrl"],
                    $data["userId"],
                    session_id(),
                    "commentForm"
                );

                if ( $check ) {

                    $useCaptcha = $this->container->getParameter("kitpages_user_generated.comment.use_captcha");
                    if ($useCaptcha) {
                        $checkCaptcha = $hash->checkHash(
                            $encrypted,
                            $data["captcha"],
                            $data["itemReference"],
                            $data["itemUrl"],
                            $data["itemId"],
                            $data["itemClass"],
                            $data["extraJson"],
                            $data["targetUrl"],
                            $data["userId"],
                            session_id(),
                            "commentForm"
                        );
                        if (!$checkCaptcha) {
                            $this->getRequest()->getSession()->setFlash("error", $trans->trans("wrong captcha"));
                            return $this->redirect($data["targetUrl"]);
                        }
                    }

                    $comment = new CommentPost();
                    $comment->setContent($data['content']);
                    $comment->setTitle($data['title']);
                    $comment->setLanguageCode($data['languageCode']);
                    $comment->setExtra(json_decode($data['extraJson']));

                    $comment->setItemReference($data["itemReference"]);
                    $comment->setItemUrl($data['targetUrl']);
                    $comment->setItemId($data['itemId']);
                    $comment->setItemClass($data['itemClass']);

                    $comment->setUserId($data['userId']);
                    $comment->setUserIp($_SERVER["REMOTE_ADDR"]);
                    $comment->setUserName($data['userName']);
                    $comment->setUserEmail($data['userEmail']);
                    $comment->setUserUrl($data['userUrl']);

                    if ($data['userEmail']) {
                        $defaultStatus = $this->container->getParameter('kitpages_user_generated.comment.default_status');
                    } else {
                        $defaultStatus = CommentPost::STATUS_DONT_SAVE;
                    }
                    $comment->setStatus($defaultStatus);

                    $eventDispatcher = $this->get("event_dispatcher");
                    $event = new UserGeneratedEvent();
                    $event->set("commentPost", $comment);
                    $eventDispatcher->dispatch(KitpagesUserGeneratedEvents::ON_COMMENT_POST, $event);

                    if (! $event->isDefaultPrevented() ) {
                        if ($comment->getStatus() != CommentPost::STATUS_DONT_SAVE) {
                            $em = $this->getDoctrine()->getEntityManager();
                            $em->persist($comment);
                            $em->flush();
                            $this->getRequest()->getSession()->setFlash("notice", $trans->trans("comment saved"));
                            $eventDispatcher->dispatch(KitpagesUserGeneratedEvents::AFTER_COMMENT_POST, $event);
                        } else {
                            $this->getRequest()->getSession()->setFlash("notice", $trans->trans("comment rejected"));
                        }
                    }

                    return $this->redirect($data["targetUrl"]);
                }
                $this->getRequest()->getSession()->setFlash("error", $trans->trans("technical error, comment not saved"));
                return $this->redirect($data["targetUrl"]);
            }

        }
    }

    protected function getPostForm(
        $itemReference,
        $itemUrl = null,
        $itemId = null,
        $itemClass = null,

        $userName = null,
        $userId = null,
        $userEmail = null,

        $languageCode = null,
        $extra = array()
    )
    {
        $translator = $this->get('translator');

        $formBuilder = $this->createFormBuilder();


        $targetUrl = $this->getRequest()->getUri();

        $extraJson = json_encode($extra);


        $hash = $this->get('kitpages_util.hash');
        $formBuilder->add(
            "tokenEncrypted",
            "hidden",
            array(
                "data" => $hash->getHash(
                    $itemReference,
                    $itemUrl,
                    $itemId,
                    $itemClass,

                    $extraJson,

                    $targetUrl,

                    $userId,
                    session_id(),
                    "commentForm"
                )
            )
        );

        $formBuilder->add(
            "itemReference",
            "hidden",
            array(
                "data" => $itemReference
            )
        );
        $formBuilder->add(
            "itemUrl",
            "hidden",
            array(
                "data" => $itemUrl
            )
        );
        $formBuilder->add(
            "itemId",
            "hidden",
            array(
                "data" => $itemId
            )
        );
        $formBuilder->add(
            "itemClass",
            "hidden",
            array(
                "data" => $itemClass
            )
        );

        if ($userEmail) {
            $formBuilder->add(
                "userEmail",
                "hidden",
                array(
                    "data" => $userEmail
                )
            );
        } else {
            $formBuilder->add(
                "userEmail",
                "email",
                array(
                    "label" => $translator->trans("Email")
                )
            );
        }
        if ($userName) {
            $formBuilder->add(
                "userName",
                "hidden",
                array(
                    "data" => $userName
                )
            );
        } else {
            $formBuilder->add(
                "userName",
                "text",
                array(
                    "label" => $translator->trans("Nick name")
                )
            );
        }
        $formBuilder->add(
            "userId",
            "hidden",
            array(
                "data" => $userId
            )
        );
        $formBuilder->add(
            'languageCode',
            'hidden',
            array(
                "data" => $languageCode
            )
        );
        $formBuilder->add(
            'title',
            'text',
            array(
                "label" => $translator->trans("Title"),
                'required' => false
            )
        );
        $formBuilder->add(
            'content',
            'textarea',
            array(
                "label" => $translator->trans("Content")
            )
        );
        $useCaptcha = $this->container->getParameter("kitpages_user_generated.comment.use_captcha");
        if ($useCaptcha) {
            $a = rand(1, 20);
            $b = rand(1, 20);
            $formBuilder->add('captcha', 'text', array(
                "label" => $a . " + " . $b
            ));
            $formBuilder->add(
                "captchaEncrypted",
                "hidden",
                array(
                    "data" => $hash->getHash(
                        $a+$b,
                        $itemReference,
                        $itemUrl,
                        $itemId,
                        $itemClass,

                        $extraJson,

                        $targetUrl,

                        $userId,
                        session_id(),
                        "commentForm"
                    )
                )
            );

        }
        $formBuilder->add(
            'userUrl',
            'url',
            array(
                "label" => $translator->trans("Your URL"),
                'required' => false
            )
        );
        $formBuilder->add(
            'targetUrl',
            "hidden",
            array(
                "data" => $targetUrl
            )
        );
        $formBuilder->add(
            'extraJson',
            "hidden",
            array(
                "data" => $extraJson
            )
        );
        $form = $formBuilder->getForm();
        return $form;
    }
}

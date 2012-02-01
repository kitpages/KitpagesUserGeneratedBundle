<?php

namespace Kitpages\UserGeneratedBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Kitpages\UserGeneratedBundle\Entity\CommentPost;


class CommentController extends Controller
{
    public function displayPostListAction(
        $itemReference
    )
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $em->getRepository('KitpagesUserGeneratedBundle:CommentPost');
        $postList = $repo->findByItemReference($itemReference);
        return $this->render(
            "KitpagesUserGeneratedBundle:Comment:displayPostList.html.twig",
            array(
                'postList' => $postList
            )
        );
    }

    public function newPostAction(
        $itemReference,
        $itemId = null,
        $itemClass = null,
        $userName = null,
        $userId = null,
        $email = null
    )
    {
        $form = $this->getPostForm(
            $itemReference,
            $itemId,
            $itemClass,
            $userName,
            $userId,
            $email
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
                    $data["itemId"],
                    $data["itemClass"],
                    $data["targetUrl"],
                    $data["userId"],
                    session_id(),
                    "commentForm"
                );
                if ( $check ) {
                    $comment = new CommentPost();
                    $comment->setContent($data['content']);
                    $comment->setTitle($data['title']);
                    $comment->setEmail($data['email']);
                    $comment->setItemReference($data["itemReference"]);
                    $comment->setUserId($data['userId']);
                    $comment->setUserName($data['userName']);
                    $comment->setUrl($data['targetUrl']);
                    $em = $this->getDoctrine()->getEntityManager();
                    $em->persist($comment);
                    $em->flush();
                    $this->getRequest()->getSession()->setFlash("notice", $trans->trans("comment saved"));
                    return $this->redirect($data["targetUrl"]);
                }
                $this->getRequest()->getSession()->setFlash("error", $trans->trans("technical error, comment not saved"));
                return $this->redirect($data["targetUrl"]);
            }

        }
    }

    protected function getPostForm(
        $itemReference,
        $itemId = null,
        $itemClass = null,
        $userName = null,
        $userId = null,
        $email = null
    )
    {
        $formBuilder = $this->createFormBuilder();


        $targetUrl = $_SERVER["REQUEST_URI"];

        $hash = $this->get('kitpages_util.hash');
        $formBuilder->add(
            "tokenEncrypted",
            "hidden",
            array(
                "data" => $hash->getHash(
                    $itemReference,
                    $itemId,
                    $itemClass,
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

        if ($email) {
            $formBuilder->add(
                "email",
                "hidden",
                array(
                    "data" => $email
                )
            );
        } else {
            $formBuilder->add(
                "email",
                "email"
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
                "text"
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
            'title',
            'text'
        );
        $formBuilder->add(
            'content',
            'textarea'
        );
        $formBuilder->add(
            'targetUrl',
            "hidden",
            array(
                "data" => $targetUrl
            )
        );
        $form = $formBuilder->getForm();
        return $form;
    }
}

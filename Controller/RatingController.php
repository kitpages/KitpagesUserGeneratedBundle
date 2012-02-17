<?php

namespace Kitpages\UserGeneratedBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Collections\ArrayCollection;

use Kitpages\UserGeneratedBundle\Entity\RatingScore;
use Kitpages\UserGeneratedBundle\Event\UserGeneratedEvent;
use Kitpages\UserGeneratedBundle\KitpagesUserGeneratedEvents;


class RatingController extends Controller
{

    const DISPLAY_RESULT_TYPE_PERCENTAGE="percentage";
    const DISPLAY_RESULT_TYPE_AVERAGE="average";
    const DISPLAY_RESULT_TYPE_QUANTITY="quantity";

    public function displayRatingResultAction(
        $itemReference,
        $scoreList = array(),
        $scoreType = 'default',
        $displayResultType = self::DISPLAY_RESULT_TYPE_AVERAGE
    )
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $em->getRepository('KitpagesUserGeneratedBundle:RatingScore');

        $resultList = array();
        if ($displayResultType == self::DISPLAY_RESULT_TYPE_AVERAGE) {
            $resultList = $repo->averageByItemReference($itemReference, $scoreType);
        } elseif ($displayResultType == self::DISPLAY_RESULT_TYPE_PERCENTAGE) {
            $resultList = $repo->percentageScoreByItemReference($itemReference, $scoreType);
        } elseif ($displayResultType == self::DISPLAY_RESULT_TYPE_QUANTITY) {
            $resultList = $repo->quantityScoreByItemReference($itemReference, $scoreType);
        }

        return $this->render(
            "KitpagesUserGeneratedBundle:Rating:".$scoreType."/displayRatingResult/".$displayResultType.".html.twig",
            array(
                'resultList' => $resultList,
                'scoreList' => $scoreList
            )
        );
    }

    public function newScoreAction(
        $itemReference,
        $itemUrl = null,
        $itemId = null,
        $itemClass = null,

        $userName = null,
        $userId = null,
        $userEmail = '',

        $scoreList = array(),
        $scoreType = 'default',
        $modifyScore = true,
        $extra = array()
    )
    {

        if (! $this->get('security.context')->isGranted('ROLE_USER_GENERATED_RATING_USER') ) {
            return new Response('The user should be authenticated on this page');
        }

        if ($userName == null) {
            $userName = $this->get('security.context')->getToken()->getUsername();
        }

        $em = $this->getDoctrine()->getEntityManager();
        $repo = $em->getRepository('KitpagesUserGeneratedBundle:RatingScore');

        $ratingScore = $repo->findByItemReferenceAndUserName($itemReference, $scoreType, $userName);


        $score = null;
        if (count($ratingScore) > 0 && $ratingScore[0] instanceof RatingScore) {
            $score = $ratingScore[0]->getScore();
            if (!$modifyScore) {
                return $this->render(
                    'KitpagesUserGeneratedBundle:Rating:'.$scoreType.'/oldScore.html.twig',
                    array(
                        'score' => $score,
                        'scoreList' => $scoreList
                    )
                );
            }
        }

        $form = $this->getScoreForm(
            $itemReference,
            $itemUrl,
            $itemId,
            $itemClass,

            $userName,
            $userId,
            $userEmail,

            $scoreList,
            $scoreType,
            $score,
            $extra
        );

        $data = $form->getData();
        $targetUrl = $data["targetUrl"];
        return $this->render(
            'KitpagesUserGeneratedBundle:Rating:'.$scoreType.'/newScore.html.twig',
            array(
                'form' => $form->createView(),
                'targetUrl' => $targetUrl
            )
        );
    }

    public function doNewScoreAction()
    {

        if (! $this->get('security.context')->isGranted('ROLE_USER_GENERATED_RATING_USER') ) {
            return new Response('The user should be authenticated on this page');
        }

        $request = $this->getRequest();
        $form = $this->getScoreForm(
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
                    $data["scoreType"],
                    $data["userId"],
                    session_id(),
                    "scoreForm"
                );

                if ( $check ) {
                    $em = $this->getDoctrine()->getEntityManager();
                    $repo = $em->getRepository('KitpagesUserGeneratedBundle:RatingScore');

                    $score = $repo->findByItemReferenceAndUserName(
                        $data["itemReference"],
                        $data["scoreType"],
                        $data['userName']
                    );
                    if (count($score) > 0 ){
                        $score = $score[0];
                    }
                    if (!($score instanceof RatingScore)) {
                        $score = new RatingScore();
                    }
                    $score->setScore($data['score']);
                    $score->setScoreType($data['scoreType']);
                    $score->setExtra(json_decode($data['extraJson']));

                    $score->setItemReference($data["itemReference"]);
                    $score->setItemUrl($data['targetUrl']);
                    $score->setItemId($data['itemId']);
                    $score->setItemClass($data['itemClass']);

                    $score->setUserId($data['userId']);
                    $score->setUserIp($_SERVER["REMOTE_ADDR"]);
                    $score->setUserName($data['userName']);
                    $score->setUserEmail($data['userEmail']);
                    $score->setUserUrl($data['userUrl']);

                    $eventDispatcher = $this->get("event_dispatcher");
                    $event = new UserGeneratedEvent();
                    $event->set("ratingScore", $score);
                    $eventDispatcher->dispatch(KitpagesUserGeneratedEvents::ON_RATING_SCORE, $event);

                    if (! $event->isDefaultPrevented() ) {
                        $em->persist($score);
                        $em->flush();
                        $this->getRequest()->getSession()->setFlash("notice", $trans->trans("score saved"));
                        $eventDispatcher->dispatch(KitpagesUserGeneratedEvents::AFTER_RATING_SCORE, $event);
                    }

                    return $this->redirect($data["targetUrl"]);
                }
                $this->getRequest()->getSession()->setFlash("error", $trans->trans("technical error, score not saved"));
                return $this->redirect($data["targetUrl"]);
            }
        }
    }

    protected function getScoreForm(
        $itemReference,
        $itemUrl = null,
        $itemId = null,
        $itemClass = null,

        $userName = null,
        $userId = null,
        $userEmail = null,

        $scoreList = array(),
        $scoreType = 'default',
        $score = null,
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

                    $scoreType,

                    $userId,
                    session_id(),
                    "scoreForm"
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

        if ($userEmail !== null) {
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
            'scoreType',
            'hidden',
            array(
                'required' => true,
                "data" => $scoreType
            )
        );
        $formBuilder->add(
            'score',
            'choice',
            array(
                "label" => $translator->trans("Score"),
                "choices" => $scoreList,
                'required' => true,
                "data" => $score
            )
        );
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

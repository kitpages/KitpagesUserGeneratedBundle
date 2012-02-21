<?php

namespace Kitpages\UserGeneratedBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Collections\ArrayCollection;

use Kitpages\UserGeneratedBundle\Controller\RatingController;


class LikeController extends RatingController
{

    public function newAction(
        $itemReference,
        $itemUrl = null,
        $itemId = null,
        $itemClass = null,

        $userName = null,
        $userId = null,
        $userEmail = '',
        $extra = array()
    ) {
        $scoreList = array(0=>'I not like', 1=>'I like');
        $scoreType = 'like';
        $modifyScore = true;
        return parent::newScoreAction(
            $itemReference,
            $itemUrl,
            $itemId,
            $itemClass,

            $userName,
            $userId,
            $userEmail,

            $scoreList,
            $scoreType,
            $modifyScore,
            $extra
        );
    }

    public function displayResultAction(
        $itemReference
    )
    {
//        $response = $this->forward('KitpagesUserGeneratedBundle:Rating:displayRatingResult',
//            array(
//                $itemReference,
//                $scoreList = array(0=>'I not like', 1=>'I like'),
//                $scoreType = 'like',
//                $displayResultType = RatingController::DISPLAY_RESULT_TYPE_QUANTITY
//            )
//        );
//        return $response;
        $scoreList = array(1=>'I like');
        $scoreType = 'like';
        $displayResultType = self::DISPLAY_RESULT_TYPE_QUANTITY;
        return parent::displayRatingResultAction(
            $itemReference,
            $scoreList,
            $scoreType,
            $displayResultType
        );
    }

}

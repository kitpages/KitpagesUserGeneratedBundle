<?php
namespace Kitpages\UserGeneratedBundle\Service;

use Kitpages\UserGeneratedBundle\Entity\RatingScore;
use Kitpages\UserGeneratedBundle\Entity\RatingCache;

use Symfony\Component\EventDispatcher\Event;
use Doctrine\Bundle\DoctrineBundle\Registry;

class RatingManager
{

    ////
    // dependency injection
    ////
    protected $doctrine = null;

    public function __construct(
        Registry $doctrine
    ){
        $this->doctrine = $doctrine;
    }


    ////
    //  action
    ////
    public function createAllRatingCache(){

        $em = $this->doctrine->getManager();
        $query = $em->getConnection()->executeUpdate("
            DELETE FROM usergenerated_ratingcache
        ");


        $repo = $em->getRepository('KitpagesUserGeneratedBundle:RatingScore');
        $quantityRatingCacheList = $repo->quantityByItemReferenceScoreTypeScore();

        $ratingCacheTotalList = array();
        foreach($quantityRatingCacheList as $quantityRatingCache) {
            $itemReference=$quantityRatingCache['itemReference'];
            $scoreType=$quantityRatingCache['scoreType'];
            $score=$quantityRatingCache['score'];
            $quantity=$quantityRatingCache['quantity'];

            $ratingCache = new RatingCache();
            $ratingCache->setItemReference($itemReference);
            $ratingCache->setScoreType($scoreType);
            $ratingCache->setScore($score);
            $ratingCache->setQuantity($quantity);
            $em->persist($ratingCache);
            $em->flush();
            if (
                isset($ratingCacheTotalList[$itemReference])
                && isset($ratingCacheTotalList[$itemReference][$scoreType])
            ) {
                $ratingCacheTotalList[$itemReference][$scoreType]['quantityTotal']
                    = $ratingCacheTotalList[$itemReference][$scoreType]['quantityTotal'] + $quantity;
                $ratingCacheTotalList[$itemReference][$scoreType]['scoreTotal']
                    = $ratingCacheTotalList[$itemReference][$scoreType]['scoreTotal'] + $quantity*$score;
            } else {
                $ratingCacheTotalList[$itemReference][$scoreType]['quantityTotal'] = $quantity;
                $ratingCacheTotalList[$itemReference][$scoreType]['scoreTotal'] = $quantity*$score;
            }
        }

        foreach($ratingCacheTotalList as $itemReference => $scoreTypeList) {
            foreach($scoreTypeList as $scoreType => $ratingCacheTotal) {
                $query = $em->getConnection()->executeUpdate("
                    UPDATE usergenerated_ratingcache
                    SET quantity_total = ".$ratingCacheTotal['quantityTotal'].",
                    score_total = ".$ratingCacheTotal['scoreTotal']."
                    WHERE item_reference = '".$itemReference."'
                    AND score_type = '".$scoreType."'
                ");
            }
        }





    }


    ////
    // event listener
    ////
    public function onUpdateRatingScore(Event $event){
        $ratingScore = $event->get('ratingScore');

        $em = $this->doctrine->getManager();

        $query = $em->getConnection()->executeUpdate("
            UPDATE usergenerated_ratingcache
            SET quantity_total = quantity_total - 1,
            score_total = score_total - ".$ratingScore->getScore()."
            WHERE item_reference = '".$ratingScore->getItemReference()."'
            AND score_type = '".$ratingScore->getScoreType()."'
        ");

        $query = $em->getConnection()->executeUpdate("
            UPDATE usergenerated_ratingcache SET quantity = quantity - 1
            WHERE item_reference = '".$ratingScore->getItemReference()."'
            AND score_type = '".$ratingScore->getScoreType()."'
            AND score = '".$ratingScore->getScore()."'
        ");
    }

    public function afterRatingScore(Event $event){
        $ratingScore = $event->get('ratingScore');

        $em = $this->doctrine->getManager();
        $repo = $em->getRepository('KitpagesUserGeneratedBundle:RatingCache');
        $ratingCache = $repo->findByItemReferenceAndScore(
            $ratingScore->getItemReference(),
            $ratingScore->getScoreType(),
            $ratingScore->getScore()
        );

        if (count($ratingCache) == 0){
            $quantityTotal = $repo->quantityTotalForItemReferenceAndScoreType(
                $ratingScore->getItemReference(),
                $ratingScore->getScoreType()
            );

            $ratingCache = new RatingCache();
            $ratingCache->initByRatingScore($ratingScore);
            $ratingCache->setQuantityTotal($quantityTotal);
            $ratingCache->setScoreTotal(0);
            $em->persist($ratingCache);
            $em->flush();
        }

        $query = $em->getConnection()->executeUpdate("
            UPDATE usergenerated_ratingcache
            SET quantity_total = quantity_total + 1,
            score_total = score_total + ".$ratingScore->getScore()."
            WHERE item_reference = '".$ratingScore->getItemReference()."'
            AND score_type = '".$ratingScore->getScoreType()."'
        ");

        $query = $em->getConnection()->executeUpdate("
            UPDATE usergenerated_ratingcache SET quantity = quantity + 1
            WHERE item_reference = '".$ratingScore->getItemReference()."'
            AND score_type = '".$ratingScore->getScoreType()."'
            AND score = '".$ratingScore->getScore()."'
        ");
    }

}
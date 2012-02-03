<?php

namespace Kitpages\UserGeneratedBundle\Repository;

use Doctrine\ORM\EntityRepository;

use Kitpages\UserGeneratedBundle\Entity\CommentPost;

/**
 * CommentPostRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CommentPostRepository extends EntityRepository
{
    public function findByItemReference($itemReference, $status = null, $order = 'desc', $limit = null, $offset = null)
    {
        $dql = "
            SELECT p
            FROM KitpagesUserGeneratedBundle:CommentPost p
            WHERE p.itemReference = :itemReference
        ";
        if ($status) {
            $dql .= " AND p.status = :status ";
        }
        $dql .= " ORDER BY p.position ".$order;
        $query = $this->_em
            ->createQuery($dql)
            ->setParameter("itemReference", $itemReference);
        if ($status) {
            $query->setParameter("status", $status);
        }
        if ($limit !== null) {
            $query->setMaxResults($limit);
        }
        if ($offset != null) {
            $query->setFirstResult($offset);
        }
        return $query->getResult();
    }

}
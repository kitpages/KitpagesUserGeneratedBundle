<?php

namespace Kitpages\UserGeneratedBundle\Entity;

use Kitpages\UserGeneratedBundle\Entity\RatingScore;

class RatingCache
{

    /**
     * @var string $itemReference
     */
    private $itemReference;

    /**
     * @var string $scoreType
     */
    private $scoreType;

    /**
     * @var float $score
     */
    private $score;

    /**
     * @var integer $quantity
     */
    private $quantity;

    /**
     * @var integer $quantityTotal
     */
    private $quantityTotal;

    /**
     * @var datetime $createdAt
     */
    private $createdAt;

    /**
     * @var datetime $updatedAt
     */
    private $updatedAt;

    /**
     * @var integer $id
     */
    private $id;


    /**
     * Set itemReference
     *
     * @param string $itemReference
     */
    public function setItemReference($itemReference)
    {
        $this->itemReference = $itemReference;
    }

    /**
     * Get itemReference
     *
     * @return string
     */
    public function getItemReference()
    {
        return $this->itemReference;
    }

    /**
     * Set scoreType
     *
     * @param string $scoreType
     */
    public function setScoreType($scoreType)
    {
        $this->scoreType = $scoreType;
    }

    /**
     * Get scoreType
     *
     * @return string
     */
    public function getScoreType()
    {
        return $this->scoreType;
    }

    /**
     * Set score
     *
     * @param float $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * Get score
     *
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set quantityTotal
     *
     * @param integer $quantityTotal
     */
    public function setQuantityTotal($quantityTotal)
    {
        $this->quantityTotal = $quantityTotal;
    }

    /**
     * Get quantityTotal
     *
     * @return integer
     */
    public function getQuantityTotal()
    {
        return $this->quantityTotal;
    }

    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get updatedAt
     *
     * @return datetime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function initByRatingScore(RatingScore $ratingScore) {
        $this->setItemReference($ratingScore->getItemReference());
        $this->setScoreType($ratingScore->getScoreType());
        $this->setScore($ratingScore->getScore());
        $this->setQuantity(0);
    }

    /**
     * @var float $scoreTotal
     */
    private $scoreTotal;


    /**
     * Set scoreTotal
     *
     * @param float $scoreTotal
     */
    public function setScoreTotal($scoreTotal)
    {
        $this->scoreTotal = $scoreTotal;
    }

    /**
     * Get scoreTotal
     *
     * @return float 
     */
    public function getScoreTotal()
    {
        return $this->scoreTotal;
    }
}
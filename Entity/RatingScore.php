<?php

namespace Kitpages\UserGeneratedBundle\Entity;

class RatingScore
{

    /**
     * @var string $itemReference
     */
    private $itemReference;

    /**
     * @var integer $itemId
     */
    private $itemId;

    /**
     * @var string $itemClass
     */
    private $itemClass;

    /**
     * @var string $itemUrl
     */
    private $itemUrl;

    /**
     * @var integer $userId
     */
    private $userId;

    /**
     * @var string $userName
     */
    private $userName;

    /**
     * @var string $userEmail
     */
    private $userEmail;

    /**
     * @var string $userIp
     */
    private $userIp;

    /**
     * @var string $userUrl
     */
    private $userUrl;

    /**
     * @var string $score
     */
    private $score;

    /**
     * @var array $extra
     */
    private $extra;

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
     * Set itemId
     *
     * @param integer $itemId
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    }

    /**
     * Get itemId
     *
     * @return integer 
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * Set itemClass
     *
     * @param string $itemClass
     */
    public function setItemClass($itemClass)
    {
        $this->itemClass = $itemClass;
    }

    /**
     * Get itemClass
     *
     * @return string 
     */
    public function getItemClass()
    {
        return $this->itemClass;
    }

    /**
     * Set itemUrl
     *
     * @param string $itemUrl
     */
    public function setItemUrl($itemUrl)
    {
        $this->itemUrl = $itemUrl;
    }

    /**
     * Get itemUrl
     *
     * @return string 
     */
    public function getItemUrl()
    {
        return $this->itemUrl;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set userName
     *
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * Get userName
     *
     * @return string 
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Set userEmail
     *
     * @param string $userEmail
     */
    public function setUserEmail($userEmail)
    {
        $this->userEmail = $userEmail;
    }

    /**
     * Get userEmail
     *
     * @return string 
     */
    public function getUserEmail()
    {
        return $this->userEmail;
    }

    /**
     * Set userIp
     *
     * @param string $userIp
     */
    public function setUserIp($userIp)
    {
        $this->userIp = $userIp;
    }

    /**
     * Get userIp
     *
     * @return string 
     */
    public function getUserIp()
    {
        return $this->userIp;
    }

    /**
     * Set userUrl
     *
     * @param string $userUrl
     */
    public function setUserUrl($userUrl)
    {
        $this->userUrl = $userUrl;
    }

    /**
     * Get userUrl
     *
     * @return string 
     */
    public function getUserUrl()
    {
        return $this->userUrl;
    }

    /**
     * Set score
     *
     * @param string $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * Get score
     *
     * @return string 
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set extra
     *
     * @param array $extra
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

    /**
     * Get extra
     *
     * @return array 
     */
    public function getExtra()
    {
        return $this->extra;
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
    /**
     * @var string $scoreType
     */
    private $scoreType;


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
}
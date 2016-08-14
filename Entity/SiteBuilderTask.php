<?php

namespace EdgarEz\SiteBuilderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="edgarez_sitebuilder_task")
 */
class SiteBuilderTask
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\Column(type="integer", name="user_id", nullable=false) */
    private $userID;

    /** @ORM\Column(type="array", name="action", nullable=false) */
    private $action;

    /** @ORM\Column(type="datetime", name="posted_at", nullable=false) */
    private $postedAt;

    /** @ORM\Column(type="integer", name="state", nullable=false, options={"default":0}) */
    private $state;

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
     * Set userID
     *
     * @param integer $userID
     *
     * @return SiteBuilderTask
     */
    public function setUserID($userID)
    {
        $this->userID = $userID;

        return $this;
    }

    /**
     * Get userID
     *
     * @return integer
     */
    public function getUserID()
    {
        return $this->userID;
    }

    /**
     * Set action
     *
     * @param array $action
     *
     * @return SiteBuilderTask
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return array
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set postedAt
     *
     * @param \DateTime $postedAt
     *
     * @return SiteBuilderTask
     */
    public function setPostedAt($postedAt)
    {
        $this->postedAt = $postedAt;

        return $this;
    }

    /**
     * Get postedAt
     *
     * @return \DateTime
     */
    public function getPostedAt()
    {
        return $this->postedAt;
    }

    /**
     * Set state
     *
     * @param integer $state
     *
     * @return SiteBuilderTask
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }
}

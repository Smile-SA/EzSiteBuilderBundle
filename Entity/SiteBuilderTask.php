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

    /** @ORM\Column(type="datetime", name="executed_at", nullable=true) */
    private $executedAt;

    /** @ORM\Column(type="text", name="logs", nullable=true) */
    private $logs;

    /** @ORM\Column(type="integer", name="status", nullable=false, options={"default":0}) */
    private $status;

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
     * Set executedAt
     *
     * @param \DateTime $executedAt
     *
     * @return SiteBuilderTask
     */
    public function setExecutedAt($executedAt)
    {
        $this->executedAt = $executedAt;

        return $this;
    }

    /**
     * Get executedAt
     *
     * @return \DateTime
     */
    public function getExecutedAt()
    {
        return $this->executedAt;
    }

    /**
     * Set logs
     *
     * @param string $logs
     *
     * @return SiteBuilderTask
     */
    public function setLogs($logs)
    {
        $logsArray = array();
        if ($this->logs) {
            $logsArray[] = $this->logs;
        }

        $logsArray[] = $logs;
        $this->logs = explode('\n', $logsArray);

        return $this;
    }

    /**
     * Get logs
     *
     * @return string
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return SiteBuilderTask
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }
}

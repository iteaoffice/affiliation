<?php
/**
 * ITEA copyright message placeholder.
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zend\Form\Annotation;

/**
 * Entity for the Affiliation.
 *
 * @ORM\Table(name="project_loi_reminder")
 * @ORM\Entity
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation_loi_reminder")
 *
 * @category    Affiliation
 */
class LoiReminder extends EntityAbstract
{
    /**
     * @ORM\Column(name="reminder_id", length=10, type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="email", type="text", nullable=false)
     *
     * @var string
     */
    private $email;
    /**
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     *
     * @var \DateTime
     */
    private $dateCreated;
    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Affiliation", inversedBy="loiReminder")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id", nullable=false)
     * })
     *
     * @var \Affiliation\Entity\Affiliation
     */
    private $affiliation;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", inversedBy="loiReminderReceiver")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="receiver_id", referencedColumnName="contact_id", nullable=true)
     * })
     *
     * @var \Contact\Entity\Contact
     */
    private $receiver;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", inversedBy="loiReminderSender")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="contact_id", nullable=true)
     * })
     *
     * @var \Contact\Entity\Contact
     */
    private $sender;

    /**
     * @param $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        return $this->$property;
    }

    /**
     * @param $property
     * @param $value
     *
     * @return void;
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return (string)$this->getId();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return LoiReminder
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return LoiReminder
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return LoiReminder
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return Affiliation
     */
    public function getAffiliation()
    {
        return $this->affiliation;
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return LoiReminder
     */
    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    /**
     * @return \Contact\Entity\Contact
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @param \Contact\Entity\Contact $receiver
     *
     * @return LoiReminder
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * @return \Contact\Entity\Contact
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param \Contact\Entity\Contact $sender
     *
     * @return LoiReminder
     */
    public function setSender($sender)
    {
        $this->sender = $sender;

        return $this;
    }
}
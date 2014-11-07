<?php
/**
 * ITEA copyright message placeholder
 *
 * @category    Project
 * @package     Entity
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Affiliation\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zend\Form\Annotation;

/**
 * Entity for the Affiliation
 *
 * @ORM\Table(name="affiliation_loi_reminder")
 * @ORM\Entity
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation_loi_reminder")
 *
 * @category    Affiliation
 * @package     Entity
 */
class LoiReminder extends EntityAbstract
{
    /**
     * @ORM\Column(name="reminder_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(name="email", type="text", nullable=false)
     * @var string
     */
    private $email;
    /**
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     * @var \DateTime
     */
    private $dateCreated;
    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Loi", inversedBy="reminder")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="loi_id", referencedColumnName="loi_id", nullable=false)
     * })
     * @var \Affiliation\Entity\Loi
     */
    private $loi;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", inversedBy="loiReminderReceiver")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="receiver_id", referencedColumnName="contact_id", nullable=true)
     * })
     * @var \Contact\Entity\Contact
     */
    private $receiver;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", inversedBy="loiReminderSender")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="contact_id", nullable=true)
     * })
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
     * @return void
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }
}

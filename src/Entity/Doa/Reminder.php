<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Entity\Doa;

use Affiliation\Entity\AbstractEntity;
use Affiliation\Entity\Affiliation;
use Contact\Entity\Contact;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Laminas\Form\Annotation;

/**
 * @ORM\Table(name="project_doa_reminder")
 * @ORM\Entity
 * @Annotation\Hydrator("Laminas\Hydrator\ObjectPropertyHydrator")
 * @Annotation\Name("affiliation_doa_reminder")
 */
class Reminder extends AbstractEntity
{
    /**
     * @ORM\Column(name="reminder_id",type="integer",options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private ?int $id = null;
    /**
     * @ORM\Column(name="email", type="text", nullable=false)
     */
    private string $email;
    /**
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     */
    private DateTime $dateCreated;
    /**
     * @ORM\ManyToOne(targetEntity="Affiliation\Entity\Affiliation", inversedBy="doaReminder")
     * @ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id", nullable=false)
     */
    private Affiliation $affiliation;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", inversedBy="doaReminderReceiver")
     * @ORM\JoinColumn(name="receiver_id", referencedColumnName="contact_id", nullable=true)
     */
    private Contact $receiver;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", inversedBy="doaReminderSender")
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="contact_id", nullable=true)
     */
    private Contact $sender;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Reminder
    {
        $this->id = $id;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): Reminder
    {
        $this->email = $email;
        return $this;
    }

    public function getDateCreated(): DateTime
    {
        return $this->dateCreated;
    }

    public function setDateCreated(DateTime $dateCreated): Reminder
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    public function getAffiliation(): Affiliation
    {
        return $this->affiliation;
    }

    public function setAffiliation(Affiliation $affiliation): Reminder
    {
        $this->affiliation = $affiliation;
        return $this;
    }

    public function getReceiver(): Contact
    {
        return $this->receiver;
    }

    public function setReceiver(Contact $receiver): Reminder
    {
        $this->receiver = $receiver;
        return $this;
    }

    public function getSender(): Contact
    {
        return $this->sender;
    }

    public function setSender(Contact $sender): Reminder
    {
        $this->sender = $sender;
        return $this;
    }
}

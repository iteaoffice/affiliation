<?php
/**
 * ITEA copyright message placeholder.
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Entity;

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 * Entity for the Affiliation.
 *
 * @ORM\Table(name="description")
 * @ORM\Entity
 * @Annotation\Hydrator("Zend\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation_description")
 *
 * @category    Affiliation
 */
class Description extends EntityAbstract
{
    /**
     * @ORM\Column(name="description_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Annotation\Exclude()
     *
     * @var integer
     */
    private $id;
    /**
     * @ORM\ManyToMany(targetEntity="Affiliation\Entity\Affiliation", inversedBy="description", cascade={"persist"})
     * @ORM\OrderBy=({"Description"="ASC"})
     * @ORM\JoinTable(name="affiliation_description",
     *    joinColumns={@ORM\JoinColumn(name="description_id", referencedColumnName="description_id")},
     *    inverseJoinColumns={@ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id")}
     * )
     * @Annotation\Exclude()
     *
     * @var \Affiliation\Entity\Affiliation[]|Collections\ArrayCollection()
     */
    private $affiliation;
    /**
     * @ORM\Column(name="description", type="text", nullable=false)
     * @Annotation\Type("\Zend\Form\Element\Textarea")
     * @Annotation\Attributes({"rows":"12"})
     * @Annotation\Options({"label":"txt-affiliation-description","help-block":"txt-affiliation-description-explanation"})
     *
     * @var string
     */
    private $description;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", inversedBy="affiliationDescription", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=true)
     * })
     * @Annotation\Exclude()
     *
     * @var \Contact\Entity\Contact
     */
    private $contact;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->affiliation = new Collections\ArrayCollection();
    }

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

    /**
     * ToString.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getDescription();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Description
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * New function needed to make the hydrator happy
     *
     * @param Collections\Collection $affiliationCollection
     */
    public function addAffiliation(Collections\Collection $affiliationCollection)
    {
        foreach ($affiliationCollection as $affiliation) {
            $this->affiliation->add($affiliation);
        }
    }

    /**
     * New function needed to make the hydrator happy
     *
     * @param Collections\Collection $affiliationCollection
     */
    public function removeAffiliation(Collections\Collection $affiliationCollection)
    {
        foreach ($affiliationCollection as $single) {
            $this->affiliation->removeElement($single);
        }
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
     * @return Description
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Affiliation[]|Collections\ArrayCollection
     */
    public function getAffiliation()
    {
        return $this->affiliation;
    }

    /**
     * @param Affiliation[]|Collections\ArrayCollection $affiliation
     *
     * @return Description
     */
    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;

        return $this;
    }

    /**
     * @return \Contact\Entity\Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param \Contact\Entity\Contact $contact
     *
     * @return Description
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }
}

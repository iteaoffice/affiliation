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

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;

/**
 * Entity for the Affiliation.
 *
 * @ORM\Table(name="description")
 * @ORM\Entity
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation_description")
 *
 * @category    Affiliation
 */
class Description extends EntityAbstract
{
    /**
     * @ORM\Column(name="description_id", length=10, type="integer", nullable=false)
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
     * @Annotation\Attributes({
     * "rows":"12"
     * })
     * @Annotation\Options({
     * "label":"txt-affiliation-description","help-block":
     * "txt-affiliation-description-explanation"
     * })
     * @Annotation\Required(true)
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
     * @param InputFilterInterface $inputFilter
     *
     * @throws \Exception
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception(sprintf("This class %s is unused", __CLASS__));
    }

    /**
     * @return \Zend\InputFilter\InputFilter|\Zend\InputFilter\InputFilterInterface
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();
            $inputFilter->add($factory->createInput([
                    'name'     => 'description',
                    'required' => true,
                ]));
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    /**
     * @param \Affiliation\Entity\Affiliation[]|Collections\ArrayCollection() $affiliation
     */
    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;
    }

    /**
     * @return \Affiliation\Entity\Affiliation[]|Collections\ArrayCollection()
     */
    public function getAffiliation()
    {
        return $this->affiliation;
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
     * @param \Contact\Entity\Contact $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    /**
     * @return \Contact\Entity\Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
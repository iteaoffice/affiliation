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

use Zend\Form\Annotation;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity for the Affiliation
 *
 * @ORM\Table(name="description")
 * @ORM\Entity
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("affiliation_description")
 *
 * @category    Affiliation
 * @package     Entity
 */
class Description //extends EntityAbstract
{
    /**
     * @ORM\Column(name="affiliation_description_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var integer
     */
    private $id;
    /**
     * @ORM\ManyToMany(targetEntity="Affiliation\Entity\Affiliation", inversedBy="description", cascade={"persist"})
     * @ORM\OrderBy=({"Description"="ASC"})
     * @ORM\JoinTable(name="affiliation_description",
     *    joinColumns={@ORM\JoinColumn(name="affiliation_description_id", referencedColumnName="affiliation_description_id")},
     *    inverseJoinColumns={@ORM\JoinColumn(name="affiliation_id", referencedColumnName="affiliation_id")}
     * )
     * @Annotation\Type("DoctrineORMModule\Form\Element\EntityMultiSelect")
     * @Annotation\Options({
     *      "target_class":"Project\Entity\Affiliation",
     *      "find_method":{
     *          "name":"findBy",
     *          "params": {
     *              "criteria":{},
     *              "orderBy":{
     *                  "affiliation":"ASC"}
     *              }
     *          }
     *      }
     * )
     * @Annotation\Attributes({"label":"txt-affiliation"})
     * @var \Project\Entity\Affiliation[]
     */
    private $affiliation;
    /**
     * @ORM\Column(name="description", type="text", nullable=false)
     * @var string
     */
    private $description;
    /**
     * @ORM\ManyToOne(targetEntity="Contact\Entity\Contact", inversedBy="affiliationDescription", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="contact_id", nullable=true)
     * })
     * @var \Contact\Entity\Contact
     */
    private $contact;
}

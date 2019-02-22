<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Content
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Form\Questionnaire;

use Affiliation\Entity\Questionnaire\Answer;
use Affiliation\Entity\Questionnaire\Question;
use Affiliation\Entity\Questionnaire\Questionnaire;
use Affiliation\Service\AffiliationQuestionService;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\InputFilter\CollectionInputFilter;
use Zend\InputFilter\InputFilter;

/**
 * Class Report
 *
 * @package Project\Form\Evaluation
 */
class QuestionnaireForm extends Form
{
    public function __construct(
        Questionnaire              $questionnaire,
        AffiliationQuestionService $affiliationQuestionService,
        EntityManager              $entityManager
    ) {
        parent::__construct($questionnaire->get('underscore_entity_name'));
        $this->setAttributes([
            'method' => 'post',
            'role'   => 'form',
            'class'  => 'form-horizontal',
            'action' => ''
        ]);
        $this->setUseAsBaseFieldset(true);
        $doctrineHydrator = new DoctrineHydrator($entityManager);
        $this->setHydrator($doctrineHydrator);
        $this->bind($questionnaire);

        // Setup input filters
        $answerFilter = new InputFilter();
        $answerFilter->add([
            'name'     => 'value',
            'required' => false,
            'filters'  => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
                ['name' => 'ToNull'],
            ],
        ]);
        $answersFilter = new CollectionInputFilter();
        $answersFilter->setInputFilter($answerFilter);
        $questionnaireFilter = new InputFilter();
        $questionnaireFilter->add($answersFilter, 'questionnaire-answers');
        $this->setInputFilter($questionnaireFilter);

        $answerCollection = new Element\Collection('questionnaire-answers');
        $answerCollection->setCreateNewObjects(false);
        $answerCollection->setAllowAdd(false);
        $answerCollection->setAllowRemove(false);

        /** @var Answer $answer */
        foreach ($affiliationQuestionService->getSortedAnswers($questionnaire) as $answer) {
            $question = $answer->getQuestionnaireQuestion()->getQuestion();

            $answerFieldset = new Fieldset($question->getId());
            $answerFieldset->setHydrator($doctrineHydrator);
            $answerFieldset->setObject($answer);

            $optionTemplate = [
                'label'      => $question->getQuestion(),
                'help-block' => \nl2br((string) $question->getHelpBlock()),
            ];

            switch ($question->getInputType()) {
                case Question::INPUT_TYPE_BOOL:
                    $answerFieldset->add(
                        [
                            'type'       => Element\Radio::class,
                            'name'       => 'value',
                            'attributes' => [
                                'value' => $answer->getValue()
                            ],
                            'options'    => \array_merge(
                                $optionTemplate,
                                [
                                    'value_options' => [
                                        'Yes' => _('txt-yes'),
                                        'No'  => _('txt-no'),
                                    ],
                                ]
                            ),
                        ]
                    );
                    break;

                case Question::INPUT_TYPE_TEXT:
                    $placeholder         = $question->getPlaceholder();
                    $attributes          = (empty($placeholder) ? [] : ['placeholder' => $question->getPlaceholder()]);
                    $attributes['value'] = $answer->getValue();
                    $answerFieldset->add([
                        'type'       => Element\Textarea::class,
                        'name'       => 'value',
                        'attributes' => $attributes,
                        'options'    => $optionTemplate,
                    ]);
                    break;

                case Question::INPUT_TYPE_SELECT:
                    $answerFieldset->add([
                        'type'       => Element\Select::class,
                        'name'       => 'value',
                        'attributes' => [
                            'value' => $answer->getValue()
                        ],
                        'options'    => \array_merge(
                            $optionTemplate,
                            ['value_options' => \json_decode($question->getValues(), true)]
                        ),
                    ]);
                    break;

                case Question::INPUT_TYPE_STRING:
                default:
                    $answerFieldset->add([
                        'type'       => Element\Text::class,
                        'name'       => 'value',
                        'attributes' => [
                            'value' => $answer->getValue()
                        ],
                        'options'    => $optionTemplate
                    ]);
            }
            // Add answer to the answer collection
            $answerCollection->add($answerFieldset);
        }

        // Add the answer collection to the form
        $this->add($answerCollection);

        $this->add(
            [
                'type'       => Element\Submit::class,
                'name'       => 'cancel',
                'attributes' => [
                    'class' => 'btn btn-warning',
                    'value' => _('txt-cancel'),
                ],
            ]
        );

        $this->add(
            [
                'type'       => Element\Submit::class,
                'name'       => 'submit',
                'attributes' => [
                    'class' => 'btn btn-primary',
                    'value' => _('txt-submit'),
                ],
            ]
        );
    }
}

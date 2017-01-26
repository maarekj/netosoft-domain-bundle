<?php

namespace Netosoft\DomainBundle\Form\Type;

use Netosoft\DomainBundle\Entity\CommandLogRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectCommandLogCommandClassType extends AbstractType
{
    /** @var CommandLogRepositoryInterface */
    private $repo;

    public function __construct(CommandLogRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $choices = $this->repo->getChoicesForCommandClass();

        $resolver->setDefaults([
            'choices' => $choices,
            'choice_label' => function ($value, $key, $index) {
                return $value;
            },
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}

<?php

namespace Netosoft\DomainBundle\Form\Type;

use Netosoft\DomainBundle\Entity\CommandLogRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectCommandLogTypeType extends AbstractType
{
    /** @var CommandLogRepositoryInterface */
    private $repo;

    public function __construct(CommandLogRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'choices' => $this->repo->getChoicesForType(),
                'choice_label' => function ($value, $key, $index) {
                    return 'choice.type_'.$key;
                },
            ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}

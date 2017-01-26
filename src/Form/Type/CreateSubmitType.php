<?php

namespace Netosoft\DomainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\SubmitButtonTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateSubmitType extends AbstractType implements SubmitButtonTypeInterface
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'icon' => 'fa fa-plus-circle',
            'attr' => ['class' => 'btn btn-success', 'type' => 'submit'],
            'label' => 'btn_create',
            'translation_domain' => 'SonataAdminBundle',
        ]);
    }

    public function getParent()
    {
        return SubmitType::class;
    }
}

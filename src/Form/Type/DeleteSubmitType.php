<?php

namespace Netosoft\DomainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\SubmitButtonTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteSubmitType extends AbstractType implements SubmitButtonTypeInterface
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'icon' => 'fa fa-save',
            'attr' => ['class' => 'btn btn-danger'],
            'label' => 'btn_delete',
            'translation_domain' => 'SonataAdminBundle',
        ]);
    }

    public function getParent()
    {
        return SubmitType::class;
    }
}

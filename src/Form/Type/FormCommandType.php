<?php

namespace Netosoft\DomainBundle\Form\Type;

use Netosoft\DomainBundle\Form\Object\FormCommandObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('command', $options['command_form'], \array_merge(
            ['required' => true],
            $options['command_form_options']
        ));
        $builder->add('actions', FormType::class, ['mapped' => false]);

        $options['configure_actions_form']($builder->get('actions'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', FormCommandObject::class);
        $resolver->setDefault('command_form_options', []);
        $resolver->setRequired('command_form_options');
        $resolver->setAllowedTypes('command_form_options', ['array']);

        $resolver->setRequired('command_form');
        $resolver->setAllowedTypes('command_form', ['string']);

        $resolver->setRequired('configure_actions_form');
        $resolver->setDefault('configure_actions_form', function (FormBuilderInterface $builder) {
        });
        $resolver->setAllowedTypes('configure_actions_form', ['callable']);
    }
}

<?php

// src/Forms/UserForm.php
namespace App\Form\Type;

use  App\Entity\Fixer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class FixerForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nickname', TextType::class,['label' => '.nickname']);
         $builder->add('fullname', TextType::class,['label' => '.fullname']);
        $builder->add('plainPassword', RepeatedType::class, array(
                'type' => TextType::class,
                'first_options'  => array('label' => '.password'),
                'second_options' => array('label' => 'repeat.password'),));
        $builder->add('email', TextType::class,['label' => '.email']);
        $builder->add('rolestr', ChoiceType::class,[
           'label' => '.rolestr',
            'choices'  => [
             'ROLE_TEMP' => 'ROLE_TEMP',
             'ROLE_USER' => 'ROLE_USER',
             'ROLE_ADMIN' => 'ROLE_ADMIN',
             'ROLE_APWC' => 'ROLE_APWC',
             'ROLE_AEMC' => 'ROLE_AEMC',
             'ROLE_AADA' => 'ROLE_AADA',
             'ROLE_DELL' => 'ROLE_DELL',
       ], ]);
        $builder->add('membership', ChoiceType::class,[
          'label' => '.membership',
           'choices'  => [
             '.temporary' => 'TEMP',
             'USER' => 'USER',
             'ADMIN' => 'ADMIN',
             'DELL' => 'DELL',
       ], ]);

        $builder->get('email')->setRequired(true);
        $builder->get('email')->setDisabled(false);
        $builder->get('plainPassword')->setRequired(false);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Fixer::class,
        ));
    }
}

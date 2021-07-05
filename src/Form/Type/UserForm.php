<?php

// src/Forms/UserForm.php
namespace App\Form\Type;

use  App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nickname', TextType::class,['label' => 'Nickname']);
          $builder->add('fullname', TextType::class,['label' => 'Fullname']);
        $builder->add('plainPassword', RepeatedType::class, array(
                'type' => TextType::class,
                'first_options'  => array('label' => '.password'),
                'second_options' => array('label' => 'repeat.password'),));
        $builder->add('email', TextType::class,['label' => '.email']);
        $builder->add('roles', ChoiceType::class,[
           'label' => '.roles',
            'choices'  => [
             'ROLE_TEMP' => 'ROLE_TEMP',
             'ROLE_USER' => 'ROLE_USER',
             'ROLE_ADMIN' => 'ROLE_ADMIN',
       ], ]);
        $builder->add('membership', ChoiceType::class,[
          'label' => '.membership',
           'choices'  => [
             '.temporary' => 'TEMP',
             'USER' => 'USER',
             'ADMIN' => 'ADMIN',
       ], ]);

        $builder->get('email')->setRequired(false);
        $builder->get('email')->setDisabled(true);
        $builder->get('nickname')->setDisabled(false);
        $builder->get('plainPassword')->setRequired(false);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
        ));
    }
}

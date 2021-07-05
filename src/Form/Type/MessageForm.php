<?php

// src/Forms/MessageForm.php
namespace App\Form\Type;

use  App\Entity\message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class MessageForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

         $builder->add('fromname', TextType::class,['label' => '.fromname']);
         $builder->add('fromemail', TextType::class,['label' => '.fromemail']);
         $builder->add('toname', TextType::class,['label' => '.toname']);
         $builder->add('toemail', TextType::class,['label' => '.toemail']);
         $builder->add('subject', TextType::class,['label' => '.subject']);
         $builder->add('body', TextareaType::class,['label' => '.body']);
         $builder->get('toname')->setDisabled(true);
         $builder->get('toemail')->setDisabled(true);
         $builder->get('fromname')->setDisabled(true);
         $builder->get('fromemail')->setDisabled(true);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Message::class,
        ));
    }
}

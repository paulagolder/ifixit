<?php

// src/Form/Type/RepairForm.php

namespace App\Form\Type;

use  App\Entity\Repair;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;


class RepairForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder ->add('RepairId', IntegerType::class,['label' => 'ReapirId']);
        $builder ->add('Name', TextType::class ,['label' => 'Name',]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Repair::class,
            'csrf_protection' => false,
        ));
    }
}

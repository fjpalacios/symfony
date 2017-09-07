<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\DatetimeType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('username', TextType::class)
                ->add('email', EmailType::class)
                ->add('plainPassword', RepeatedType::class, array(
                        'type' => PasswordType::class,
                        'first_options' => array('label' => 'Password'),
                        'second_options' => array('label' => 'Repeat Password'),
                        'required' => false
                ))
                ->add('name', TextType::class)
                ->add('url', UrlType::class, array('required' => false))
                ->add('bio', TextareaType::class, array('required' => false))
                ->add('roles', ChoiceType::class, array(
                        'choices' => array(
                                'User' => "ROLE_USER",
                                'Admin' => "ROLE_ADMIN",
                                'Superadmin' => "ROLE_SUPER_ADMIN"),
                        'multiple' => true
                ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'AppBundle\Entity\User'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_user';
    }


}

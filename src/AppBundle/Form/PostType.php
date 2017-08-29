<?php

namespace AppBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('status', ChoiceType::class, array(
                        'choices' => array(
                                'Draft' => 'draft',
                                'Publish' => 'publish'
                        ),
                        'multiple' => false
                ))
                ->add('author', EntityType::class, array(
                        'class' => 'AppBundle:User',
                        'choice_label' => 'name',
                        'choice_value' => 'id',
                        'multiple' => false
                ))
                ->add('titleEs', TextType::class)
                ->add('titleEn', TextType::class, array(
                        'required' => false
                ))
                ->add('slug', TextType::class)
                ->add('contentEs', TextareaType::class)
                ->add('contentEn', TextareaType::class, array(
                        'required' => false
                ))
                ->add('excerptEs', TextareaType::class)
                ->add('excerptEn', TextareaType::class, array(
                        'required' => false
                ))
                ->add('image', FileType::class, array(
                        'required' => false
                ))
                ->add('commentStatus', ChoiceType::class, array(
                        'choices' => array(
                                'Open' => 'open',
                                'Close' => 'close'
                        ),
                        'multiple' => false
                ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'AppBundle\Entity\Post'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_post';
    }


}

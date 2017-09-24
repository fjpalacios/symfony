<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parentId', HiddenType::class, array(
                'required' => false
            ))
            ->add('author', TextType::class)
            ->add('email', TextType::class)
            ->add('url', TextType::class, array(
                'required' => false
            ))
            ->add('comment', TextareaType::class)
            ->add('status', ChoiceType::class, array(
                'choices' => array(
                    'COMMENT_STATUS_PENDING' => 'pending',
                    'COMMENT_STATUS_APPROVE' => 'approved'
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
            'data_class' => 'AppBundle\Entity\Comment'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_comment';
    }


}

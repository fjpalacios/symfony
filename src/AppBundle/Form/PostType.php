<?php

namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;
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
    private $categoryName;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         if ($options['lang'] == 'es') {
             $this->categoryName = 'nameEs';
         } else {
             $this->categoryName = 'nameEn';
         }
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
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('u')
                                ->orderBy('u.name', 'ASC');
                        },
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
                        'required' => false,
                        'data_class' => null
                ))
                ->add('commentStatus', ChoiceType::class, array(
                        'choices' => array(
                                'Open' => 'open',
                                'Close' => 'close'
                        ),
                        'multiple' => false
                ))
                ->add('category', EntityType::class, array(
                        'class' => 'AppBundle:Category',
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                ->orderBy('c.'.$this->categoryName, 'ASC');
                        },
                        'choice_label' => $this->categoryName,
                        'choice_value' => 'id',
                        'placeholder' => 'CHOOSE_A_CATEGORY',
                        'multiple' => false
                ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'AppBundle\Entity\Post',
                'lang' => null
        ));
        $resolver->setRequired('lang');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_post';
    }


}

<?php

namespace App\Form;

use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'empty_data' => ''
            ])
            ->add('description')
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $category = $event->getData();
            $form = $event->getForm();

            $options = [
                'class' => Category::class,
                'choice_label' => function (Category $category) {
                    $level = "";
                    $parent = $category->getParent();
                    while ($parent) {
                        $level .= " >";
                        $parent = $parent->getParent();
                    }

                    return $level ." ". $category->getTitle();
                },
                'required' => false,
            ];

            if ($category && null !== $category->getId()) {
                // Recuperation des ids des sous-categories de la categorie en cours d edition
                $visited = [];
                $queue = [$category];

                while($queue) {
                    $current = array_shift($queue);
                    $visited[$current->getId()] = 1;

                    foreach($current->getSubCategories() as $cat) {
                        if (!isset($visited[$cat->getId()])) {
                            $queue[] = $cat;
                        }
                    }
                }

                // Recuperation des sous-categories qui ne sont pas dans la recuperation precedente
                // afin de ne pas creer de boucle
                $options['query_builder'] = function (EntityRepository $er) use ($category, $visited) {
                    $qb = $er->createQueryBuilder('c');
                    return $qb->andWhere(
                        $qb->expr()->notIn('c.id', array_keys($visited))
                    );
                };
            }

            $form->add('parent', EntityType::class, $options);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}

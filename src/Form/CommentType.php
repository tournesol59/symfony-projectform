<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Conference;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('text')
            ->add('email')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            /* fred: ceci est la bonne solution mais on impose un champ texte pour Schematic
		    ->add('schematics', EntityType::class, [
				'class' => Schematic::class,
				'query_builder' => function(SchematicRepository $er) {
					return $er->createQueryBuilder('s')
						->orderBy('s.id', 'ASC');
				},
				'choice_label' => 'updatedAt'
    // FRED: faire encore la migration 
			])  // just an array with two entries like in recipe
            */
            // on impose donc un champ texte pour entrer le lien vers un document
            ->add('schematic', TextType::class)  // fred: ne sert pas a priori car embedded form
            ->add('conference', EntityType::class, [
                'class' => Conference::class,
				'query_builder' => function(ConferenceRepository $cr) {
					return $cr->createQueryBuilder('c')
						->orderBy('c.id', 'ASC');
				},
            ])
            ->addListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data=$event->getData();
                
                if (!isset($data['title']) || empty($data['title'])){
                    $pattern = '/([^a-zA-Z0-9]+) ([^a-zA-Z0-9]+) ([^a-zA-Z0-9]+)/';
                    $matchword = preg_match($pattern, $data['text'], $matchword);     
                    if ($matchword) {
                        $data['title'] = $matchword[0];
                    } else {
                        $data['title'] = substr($data['text'], 0, 10);
                    }
                }
                $event->setData($data);
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}

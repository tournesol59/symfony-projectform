<?php

namespace App\Form;

use App\Entity\Conference;
use App\Entity\Comment;
use App\Repository\ConferenceRepository;
use App\Repository\CommentRepository;
use App\Repository\CategoryRepository;
use App\Form\CommentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\File;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ConferenceRetroType extends AbstractType
{
	private CategoryRepository $categoryRepository;

	public function __construct(CategoryRepository $categoryRepository)
	{
		$this->categoryRepository = $categoryRepository;
	}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address')
            ->add('year')
            ->add('description')
    	    ->add('category', ChoiceType::class, [
        'choices' => [
	   // for the moment in hard to follow the doc, TBC
	     //new Category('attendee-workshop'),
	     //new Category('webinar'),
		 $this->categoryRepository->findOneById(1),
		 $this->categoryRepository->findOneById(2),
	],
	// Symfony will look for a property or a public method like "getName"
	'choice_value' => 'name',
	// a callback to return the label for a given choice
	'choice_label' => function (?Category $category): string {
            return $category ? strtoupper($category->getName()) : '';
	},
	// return the html attributes if wanted 
	    ])
	    ->add('comment', CollectionType::class, [
	// each entry in the array will be a "comment" field
	'entry_type' => CommentType,
	// 'entry_options' => [
	//    'name' => ['class' => 'text-box'],
	//   ],
	    ])
		    ->add('imagePath', FileType::class, [
		    'label' => 'Image (jpg file)',
            // unmapped means that this field is not associated to any entity property
		    'mapped' => false,
            // make it optional so you dont have to reupload file
	    // every time you edit the Product details
		    'required' => false,
        // unmapped fields cannot define their validation using attributes
	    // in the associated entity, so you can use the PHP constraint classses
		    'constraints' => [
			 new \Assert\File([
				 'maxSize' => '1024K',
				 'mimeTypes' => [
					 'application/pdf',
					 'application/x-pdf',
					 'image/jpeg',
					 'image/jpg',
				 ],
            'mimeTypesMessage' => 'Please upload a valid Jpeg image',
			 ]),
		    ]
	    ])
		// add a form event listener that imposes the 'year' to a single four digit if
		//  it is a workshop, and an automatic timestamp at 2:00 PM if is is a ddMMyyy date
		->addEventListener(FormEvents::PRE_SUBMIT,
					function (PreSubmitEvent $event): void {
						$data = $event->getData();
						$form = $event->getFrom();
						$category = $data['category'];
						$conferencedate = $data['year'];
						
					    $pattern = '^([\d]{2})-([\d]{2})-([\d]{4})';
						preg_match($pattern, $conferencedate, $matches);
						if (($category == "attendee-workshop") && isset($matches[3])) {
							$data['year'] = $matches[3];
							$event->setData($data);
						}
						if (($category=="webinar") && isset($matches[0])) {
							$data['year'] = $matches[0]." 2:00 PM";
							$event->setData($data);
						}
					});
			     //new Category('attendee-workshop'),
	     //new Category('webinar'),
			->add('save' , SubmitType::class, [
		    'label' => "Envoyer"
	    ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conference::class,
        ]);
    }
}

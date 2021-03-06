<?php

namespace SmartCore\Module\SimpleNews\Form\Type;

use SmartCore\Module\SimpleNews\Entity\News;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \SmartCore\Module\SimpleNews\Entity\News $news */
        $news = $options['data'];
        $newsInstance = $news->getInstance();

        $builder
            ->add('is_enabled', null, ['required' => false])
            ->add('title',      null, ['attr' => ['autofocus' => 'autofocus']])
            ->add('slug')
        ;

        if ($newsInstance->isUseImage()) {
            $builder->add('image', ImageFormType::class, [
                'label' => 'Image',
                'required' => false,
                'data' => $news->getImageId(),
            ]);
        }

        $builder->add('annotation', null, ['attr' => ['class' => 'wysiwyg', 'data-theme' => 'advanced']]);

        if ($newsInstance->isUseAnnotationWidget()) {
            $builder->add('annotation_widget', null, ['attr' => ['class' => 'wysiwyg', 'data-theme' => 'advanced']]);
        }

        $builder
            ->add('text',       null, ['attr' => ['class' => 'wysiwyg', 'data-theme' => 'advanced']])
            ->add('publish_date')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => News::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'smart_module_news_item';
    }
}

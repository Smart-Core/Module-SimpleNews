<?php

namespace SmartCore\Module\SimpleNews\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageFormType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }

    public function getParent()
    {
        return 'file';
    }

    public function getName()
    {
        return 'smart_module_simple_news_image';
    }
}

<?php

namespace SmartCore\Module\SimpleNews\Form\Type;

use SmartCore\Bundle\CMSBundle\Module\AbstractNodePropertiesFormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class NodePropertiesFormType extends AbstractNodePropertiesFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('items_per_page', IntegerType::class, ['attr' => ['autofocus' => 'autofocus']])
        ;
    }

    public function getBlockPrefix()
    {
        return 'smart_module_news_node_properties';
    }
}

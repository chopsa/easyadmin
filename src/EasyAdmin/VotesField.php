<?php

namespace App\EasyAdmin;

use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;

class VotesField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null)
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            // this template is used in the index and details pages
            ->setTemplatePath('admin/field/votes.html.twig')
            // this is used in the edit and new pages
            ->setFormType(IntegerType::class)
            ->addCssClass('field-integer')
            ->setDefaultColumns('col-md-4 col-xxl-3');
    }
}
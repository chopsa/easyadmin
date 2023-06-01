<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Question;
use App\EasyAdmin\VotesField;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

#[IsGranted('ROLE_MODERATOR')]
class QuestionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Question::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->onlyOnIndex();
        yield Field::new('slug')
            ->hideOnIndex()
            ->setFormTypeOption(
                'disabled',
                $pageName !== Crud::PAGE_NEW
            );
        yield Field::new('name')
            ->setSortable(false);
        yield AssociationField::new('topic');
        yield TextareaField::new('question')
            ->hideOnIndex()
            ->setFormTypeOptions([
                'row_attr' => [
                    'data-controller' => 'snarkdown',
                ],
                'attr' => [
                    'data-snarkdown-target' => 'input',
                    'data-action' => 'snarkdown#render'
                ],
            ])
            ->setHelp('Preview:');
        yield VotesField::new('votes', 'Total Votes')
            ->setPermission('ROLE_SUPER_ADMIN');
        yield AssociationField::new('askedBy')
            ->autocomplete()
            ->formatValue(static function($value, Question $question) {
                if(!$user = $question->getAskedBy()) {
                    return null;
                }

                return sprintf('%s&nbsp;(%s)', $user->getEmail(), $user->getQuestions()->count());
            })
            ->setQueryBuilder(function(QueryBuilder $queryBuilder) {
                $queryBuilder->andWhere('entity.enabled = :enabled')
                    ->setParameter('enabled', true);
            });
        
        yield AssociationField::new('answers')
            ->autocomplete()
            ->setFormTypeOption('by_reference', false);
        
        yield Field::new('createdAt')
            ->hideOnForm();
        yield AssociationField::new('updatedBy')
            ->onlyOnDetail();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort([
                'askedBy.enabled' => 'DESC',
                'createdAt' => 'DESC',
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
        // ->update(Crud::PAGE_INDEX, Action::DELETE, function(Action $action) {
        //     $action->displayIf(static function(Question $question) {
        //         return !$question->getIsApproved();

        //     });

        //     return $action;
        // })
            ->disable(Action::BATCH_DELETE)
            ->setPermission(Action::INDEX, 'ROLE_MODERATOR')
            ->setPermission(Action::DETAIL, 'ROLE_MODERATOR')
            ->setPermission(Action::EDIT, 'ROLE_MODERATOR')
            ->setPermission(Action::NEW, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::BATCH_DELETE, 'ROLE_SUPER_ADMIN');
    }
    
    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('topic')
            ->add('createdAt')
            ->add('votes')
            ->add('name'); 
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new \LogicException('Currently logged in user is not an instance of User?');
        }

        $entityInstance->setUpdatedBy($user);

        parent::updateEntity($entityManager, $entityInstance);
    }

    // /**
    //  * @param Question $entityInstance
    //  */
    // public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    // {
    //     if ($entityInstance->getIsApproved()) {
    //         throw new \Exception('Deleting approved questions is forbidden');
    //     }

    //     parent::deleteEntity($entityManager, $entityInstance);
    // }
}

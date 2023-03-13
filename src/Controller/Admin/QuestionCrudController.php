<?php

namespace App\Controller\Admin;

use App\EasyAdmin\VotesField;
use App\Entity\Question;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted('ROLE_MODERATOR')]
class QuestionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Question::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield Field::new('slug')
            ->hideOnIndex()
            ->setFormTypeOption(
                'disabled',
                $pageName !== Crud::PAGE_NEW
            );

        yield IdField::new('id')
            ->onlyOnIndex();
        yield AssociationField::new('topic');
        yield Field::new('name')
            ->setSortable(false);
        yield TextareaField::new('question')
            ->hideOnIndex()
            ->setHelp('Preview:')
            ->setFormTypeOptions([
                'row_attr' => [
                    'data-controller' => 'snarkdown',
                ],
                'attr' => [
                    'data-snarkdown-target' => 'input',
                    'data-action' => 'snarkdown#render',
                ],
            ]);
        yield VotesField::new('votes', 'Total Votes')
            ->setTextAlign('right')
            ->setPermission('ROLE_SUPER_ADMIN');
        yield AssociationField::new('askedBy')
            ->autocomplete()
            ->formatValue(static function($value, ?Question $question): ?string {
                if (!$user = $question?->getAskedBy()) {
                   return null;
                }

                return sprintf('%s (%s)', $user->getEmail(), $user->getQuestions()->count());
            })
            ->setQueryBuilder(function (QueryBuilder $qb) {
                $qb->andWhere('entity.enabled = :enabled')->setParameter(':enabled', true);
            });
        yield AssociationField::new('answers')
            ->autocomplete()
            ->setFormTypeOption('by_reference', false);
        yield Field::new('createdAt')
            ->hideOnForm();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort([
                'askedBy.enabled' => 'DESC',
                'createdAt' => 'DESC'
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
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
}

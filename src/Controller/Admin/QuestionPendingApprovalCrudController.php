<?php

namespace App\Controller\Admin;

use App\Entity\Question;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

class QuestionPendingApprovalCrudController extends QuestionCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setHelp(Crud::PAGE_INDEX, 'Questions are not published to users until approved by a moderator')
            ->setPageTitle(Crud::PAGE_INDEX, 'Questions pending approval')
            ->setPageTitle(Crud::PAGE_DETAIL, static function (Question $question) {
                return sprintf('#%s %s', $question->getId(), $question->getName());
            });
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->andWhere('entity.isApproved = :approved')
            ->setParameter(':approved', false);
    }
}

<?php

namespace Netosoft\DomainBundle\Admin;

use Doctrine\DBAL\Query\QueryBuilder;
use Netosoft\DomainBundle\Form\Type\SelectCommandLogCommandClassType;
use Netosoft\DomainBundle\Form\Type\SelectCommandLogTypeType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

class CommandLogAdmin extends AbstractAdmin
{
    const TRANSLATION_DOMAIN = 'admin_app_command_log';

    /** {@inheritdoc} */
    public function configure()
    {
        $this->setTemplate('inner_list_row', 'admin/command_log/list_row.html.twig');
        $this->setTranslationDomain(self::TRANSLATION_DOMAIN);
    }

    protected function configureDefaultFilterValues(array &$filterValues)
    {
        $filterValues['_sort_order'] = 'DESC';
        $filterValues['_sort_by'] = 'date';
    }

    /** {@inheritdoc} */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('show')
            ->remove('delete')
            ->remove('edit')
            ->remove('create');
    }

    /** {@inheritdoc} */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('sessionId')
            ->add('requestId')
            ->add('pathInfo')
            ->add('currentUsername')
            ->add('date')
            ->add('commandClass', 'doctrine_orm_callback', [
                'field_type' => SelectCommandLogCommandClassType::class,
                'callback' => function ($query, $alias, $field, $value) {
                    if (!isset($value['value']) || empty($value['value'])) {
                        return false;
                    }

                    $placeholder = $alias.'_'.\uniqid().'_field';

                    /** @var ProxyQuery $query */
                    /** @var QueryBuilder $qb */
                    $qb = $query->getQueryBuilder();
                    $qb
                        ->andWhere("${alias}.${field} = :${placeholder}")
                        ->setParameter($placeholder, $value['value']);

                    return true;
                },
            ])
            ->add('message')
            ->add('type', null, [], SelectCommandLogTypeType::class, [
                'choice_translation_domain' => $this->getTranslationDomain(),
            ]);
    }

    /** {@inheritdoc} */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('type')
            ->add('date');
    }
}

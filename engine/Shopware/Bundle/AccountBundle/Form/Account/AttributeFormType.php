<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\AccountBundle\Form\Account;

use Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface;
use Shopware\Components\Model\ModelManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributeFormType extends AbstractType
{
    /**
     * @var ModelManager
     */
    private $entityManager;

    /**
     * @var CrudServiceInterface
     */
    private $attributeService;

    /**
     * AttributeFormType constructor.
     *
     * @param ModelManager         $entityManager
     * @param CrudServiceInterface $attributeService
     */
    public function __construct(ModelManager $entityManager, CrudServiceInterface $attributeService)
    {
        $this->entityManager = $entityManager;
        $this->attributeService = $attributeService;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'attribute';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['allow_extra_fields' => true]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $metaData = $this->entityManager->getClassMetadata($options['data_class']);

        $attributes = $this->attributeService->getList($metaData->getTableName());

        foreach ($attributes as $attribute) {
            if ($attribute->isIdentifier()) {
                continue;
            }

            $field = $metaData->getFieldForColumn($attribute->getColumnName());

            $builder->add($field);
        }
    }
}

<?php
namespace Dtrof\Gifts\Api\Data;


interface ConfigurationInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const CONFIG_ID         = 'id';
    const STORE_ID          = 'store_id';
    const ENTITY_ID         = 'entity_id';
    const ATTRIBUTE_ID      = 'attribute_id';
    const CATEGORY_NAME     = 'category_name';
    const ATTRIBUTE_NAME    = 'attribute_name';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Store ID
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Get Attribute ID
     *
     * @return int|null
     */
    public function getAttributeId();

    /**
     * Get Entity ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Get title
     *
     * @return string|null
     */
    public function getCategoryTitle();

    /**
     * Get title
     *
     * @return string|null
     */
    public function getAttributeTitle();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Dtrof\Gifts\Api\Data\ConfigurationInterface
     */
    public function setId($id);

    /**
     * Set Store ID
     *
     * @param int $id
     * @return \Dtrof\Gifts\Api\Data\ConfigurationInterface
     */
    public function setStoreId($id);

    /**
     * Set Attribute ID
     *
     * @param int $id
     * @return \Dtrof\Gifts\Api\Data\ConfigurationInterface
     */
    public function setAttributeId($id);

    /**
     * Set Entity ID
     *
     * @param int $id
     * @return \Dtrof\Gifts\Api\Data\ConfigurationInterface
     */
    public function setEntityId($id);

    /**
     * Set Category Title
     *
     * @param string $title
     * @return \Dtrof\Gifts\Api\Data\ConfigurationInterface
     */
    public function setCategoryTitle($title);

    /**
     * Set Attribute Title
     *
     * @param string $title
     * @return \Dtrof\Gifts\Api\Data\ConfigurationInterface
     */
    public function setAttributeTitle($title);
}
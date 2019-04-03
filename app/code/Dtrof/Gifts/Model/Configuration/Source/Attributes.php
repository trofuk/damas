<?php
namespace Dtrof\Gifts\Model\Configuration\Source;

class Attributes implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Dtrof\Gifts\Model\Configuration
     */
    protected $configuration;
    protected $resource;

    /**
     * Constructor
     *
     * @param \Dtrof\Gifts\Model\Configuration $post
     */
    public function __construct(
        \Dtrof\Gifts\Model\Configuration $configuration,
        \Magento\Framework\App\ResourceConnection $resource
    )
    {
        $this->configuration = $configuration;
        $this->resource = $resource;
        $this->connection = $this->resource->getConnection();
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $eav_attribute = $this->connection->fetchAll("SELECT * FROM `eav_attribute` WHERE `frontend_input` = 'multiselect' ");
        foreach($eav_attribute as $key=>$attribute) {
            if($attribute['frontend_label'] != '') {
                $options[] = ['label' => $attribute['frontend_label'], 'value' => $attribute['attribute_id']];
            }
        }
        return $options;
    }

    public function getRootAttribute($id)
    {
        $attribute = $this->connection->fetchRow("SELECT * FROM `oyi_gifts_configuration`
        JOIN `eav_attribute` ON `eav_attribute`.`attribute_id` = `oyi_gifts_configuration`.`attribute_id`
        WHERE `oyi_gifts_configuration`.`id` = '".$id."'");
        return $attribute;
    }

    public function getRootAttributeByEntity($id)
    {
        $attribute = $this->connection->fetchRow("SELECT * FROM `oyi_gifts_configuration`
        WHERE `oyi_gifts_configuration`.`entity_id` = '".$id."'");
        return $attribute;
    }

    public function getSelectedAttributeOptions($configuration_id, $attribute_id, $parent_id)
    {
        $attribute = $this->connection->fetchAll("SELECT
        `oyi_gifts_link`.`configuration_id`,
        `oyi_gifts_link`.`attribute_id`,
        `oyi_gifts_link`.`option_id`,
        `oyi_gifts_link`.`value_id`,
        `oyi_gifts_link`.`id` AS `link_id`,
        `oyi_gifts_tree`.`parent_link`,
        `eav_attribute_option_value`.`store_id`,
        `eav_attribute_option_value`.`value`,
        `oyi_gifts_images`.`image`
        FROM `oyi_gifts_link`
        JOIN `oyi_gifts_tree` ON `oyi_gifts_tree`.`link_id` = `oyi_gifts_link`.`id` AND `oyi_gifts_tree`.`parent_link` = ".(int)$parent_id."
        JOIN `eav_attribute_option_value` ON `eav_attribute_option_value`.`value_id` = `oyi_gifts_link`.`value_id`
        LEFT JOIN `oyi_gifts_images` ON `oyi_gifts_images`.`option_id` = `oyi_gifts_link`.`option_id`
        WHERE `oyi_gifts_link`.`configuration_id` = ".(int)$configuration_id."
        AND `oyi_gifts_link`.`attribute_id` = ".(int)$attribute_id."
        ");

        return $attribute;
    }

    public function getAttributeItemsCount($arr,$category_id)
    {
        $sql = [];
        if(!empty($arr)){
            foreach($arr as $k=>$v){
                if(!empty($v['attribute_id']) && !empty($v['option_id'])){
                    $sql[] = 'JOIN `catalog_product_index_eav` AS `eav'.$v['attribute_id'].'` ON `eav'.$v['attribute_id'].'`.`entity_id` = `catalog_category_product`.`product_id`
                    AND `eav'.$v['attribute_id'].'`.`attribute_id` = '.$v['attribute_id'].' AND `eav'.$v['attribute_id'].'`.`value` = '.$v['option_id'];
                }
            }
        }
        $a = $this->connection->fetchAll("
                SELECT DISTINCT `catalog_category_product`.`product_id`
                FROM  `catalog_category_product`
                ".implode(' ',$sql)."
                WHERE  `catalog_category_product`.`category_id` IN (".implode(',',$category_id).")
                ");

        return (int)count($a);
    }

    public function getAttribute($id)
    {
        $attribute = $this->connection->fetchRow("SELECT * FROM `oyi_gifts_attributes`
        JOIN `eav_attribute` ON `eav_attribute`.`attribute_id` = `oyi_gifts_attributes`.`attribute_id`
        WHERE `oyi_gifts_attributes`.`id` = '".$id."'");
        return $attribute;
    }

    public function saveAttributeImages($data)
    {
        $response = '';
        if(!empty($data)){
            foreach($data as $key=>$val){
                $id = $this->connection->fetchRow("SELECT id FROM `oyi_gifts_images`
                WHERE `attribute_id` = '".$val['attribute_id']."' AND `option_id` = '".$val['option_id']."'");
                if($id['id'] > 0){
                    $response = $this->connection->update('oyi_gifts_images',array('image'=>$val['image']),'id='.$id['id']);
                }else{
                    $response = $this->connection->insert('oyi_gifts_images',$val);
                }
            }
        }
        return $response;
    }

    public function getAttributeImages($attribute_id)
    {
        $array = array();
        $images = $this->connection->fetchAll("SELECT * FROM `oyi_gifts_images`
        WHERE `attribute_id` = '".$attribute_id."'");
        if(!empty($images)){
            foreach($images as $key=>$val){
                $array[$val['option_id']] = $val;
            }
        }
        return $array;
    }

    public function getAttributeOptions($attribute_id, $store_id)
    {
        return $this->connection->fetchAll("SELECT * FROM `eav_attribute_option`
        JOIN `eav_attribute_option_value` ON `eav_attribute_option_value`.`option_id` = `eav_attribute_option`.`option_id`
        AND `eav_attribute_option_value`.`store_id` = '".$store_id."'
        WHERE `eav_attribute_option`.`attribute_id` = '".$attribute_id."'");
    }

    public function getAttributeByCode($code)
    {
        $attribute = $this->connection->fetchRow("SELECT * FROM `eav_attribute` WHERE `attribute_code` LIKE '".$code."' LIMIT 1");
        return $attribute;
    }

    public function getAttributeById($id)
    {
        $attribute = $this->connection->fetchRow("SELECT * FROM `eav_attribute` WHERE `attribute_id` LIKE '".$id."' LIMIT 1");
        return $attribute;
    }

    public function checkSelected($configuration_id, $attribute_id, $option_id, $value_id, $parent_id)
    {
        $attribute = $this->connection->fetchRow("SELECT `oyi_gifts_link`.`id`
            FROM `oyi_gifts_link`
            JOIN `oyi_gifts_tree` ON `oyi_gifts_tree`.`link_id` = `oyi_gifts_link`.`id`
            AND `oyi_gifts_tree`.`parent_link` = ".$parent_id."
            WHERE `oyi_gifts_link`.`configuration_id` = ".(int)$configuration_id."
            AND `oyi_gifts_link`.`attribute_id` = ".(int)$attribute_id."
            AND `oyi_gifts_link`.`option_id` = ".(int)$option_id."
            AND `oyi_gifts_link`.`value_id` = ".(int)$value_id."
            LIMIT 1");
        return ($attribute['id'] > 0) ? ' primary ' : '';
    }

    public function saveAttributeLink($array)
    {
        $id = $this->checkAttributeLink($array);
        if($id == 0) {
            try {
                $this->connection->insert("oyi_gifts_link", $array);
            } catch (\PDOException $e) {
                echo $e->getMessage();
            }
            $id = $this->connection->lastInsertId();
        }
        return $id;
    }

    public function checkAttributeLink($array)
    {
        $attribute = $this->connection->fetchRow("SELECT `id`
        FROM `oyi_gifts_link`
        WHERE `configuration_id` = ".(int)$array['configuration_id']."
        AND `attribute_id` = ".(int)$array['attribute_id']."
        AND `option_id` = ".(int)$array['option_id']."
        AND `value_id` = ".(int)$array['value_id']."
        LIMIT 1");
        return ($attribute['id'] > 0) ? $attribute['id'] : 0;
    }

    public function removeAttributeLink($array)
    {
        $this->removeChildren($array['link_id']);
        return $this->connection->delete("oyi_gifts_tree", [
            $this->connection->quoteInto('link_id = ?', (int)$array['link_id']),
            $this->connection->quoteInto('parent_link = ?', (int)$array['parent_link'])
        ]);
    }

    public function removeChildren($id)
    {
        $array = $this->getChildrenLinks($id);
        foreach($array as $item) {
            $this->removeAttributeLink([
                'link_id' => $item['link_id'],
                'parent_link' => $id
            ]);
        }
    }

    public function getChildrenLinks($id)
    {
        return $this->connection->fetchAll("SELECT `link_id`
        FROM `oyi_gifts_tree`
        WHERE `parent_link` = ".(int)$id);
    }

    public function saveLinkTree($link_id, $parent_id)
    {
        $id = $this->checkLinkTree($link_id, $parent_id);
        if($id == 0) {
            try {
                $this->connection->insert("oyi_gifts_tree", array(
                    'link_id' => (int)$link_id,
                    'parent_link' => (int)$parent_id,
                ));
            } catch (\PDOException $e) {
                echo $e->getMessage();
            }
            $id = $this->connection->lastInsertId();
        }
        return $id;
    }

    public function checkLinkTree($link_id, $parent_id)
    {
        $attribute = $this->connection->fetchRow("SELECT `id`
        FROM `oyi_gifts_tree`
        WHERE `link_id` = ".(int)$link_id."
        AND `parent_link` = ".(int)$parent_id."
        LIMIT 1");
        return ($attribute['id'] > 0) ? $attribute['id'] : 0;
    }

    public function childAttribute($attribute_id)
    {
        $attribute = $this->connection->fetchRow("SELECT `attribute_id`
        FROM `oyi_gifts_attributes`
        WHERE `parent_id` = ".(int)$attribute_id."
        LIMIT 1");
        if($attribute['attribute_id'] > 0) {
            return $attribute['attribute_id'];
        } else {
            return $attribute_id;
        }
    }

    public function getParentId($attribute_id)
    {
        $attribute = $this->connection->fetchRow("SELECT `parent_id`
        FROM `oyi_gifts_attributes`
        WHERE `attribute_id` = ".(int)$attribute_id."
        LIMIT 1");
        if($attribute['parent_id'] > 0) {
            return $attribute['parent_id'];
        } else {
            return 0;
        }
    }

    public function getAttributeCode($attribute_id)
    {
        $attribute = $this->getAttributeById($attribute_id);
        return $attribute['attribute_code'];
    }

    public function getOptionValue($link_id)
    {
        $attribute = $this->connection->fetchRow("SELECT `value_id`
        FROM `oyi_gifts_link`
        WHERE `id` = ".(int)$link_id."
        LIMIT 1");
        $attribute = $this->connection->fetchRow("SELECT `value`
        FROM `eav_attribute_option_value`
        WHERE `value_id` = ".(int)$attribute['value_id']."
        LIMIT 1");
        return $attribute['value'];
    }

    public function getEntityName($store_id, $entity_id)
    {
        $attribute = $this->connection->fetchRow("
                    SELECT `value`
                    FROM `catalog_category_entity_varchar`
                    WHERE `attribute_id` = 42
                    AND `store_id` = ".$store_id."
                    AND `entity_id` = ".$entity_id." LIMIT 1");
        return $attribute['value'];
    }

    public function getLinkId($configuration_id, $attribute_id, $option_id)
    {
        $attribute = $this->connection->fetchRow("SELECT `id`
        FROM `oyi_gifts_link`
        WHERE `configuration_id` = ".(int)$configuration_id."
        AND `attribute_id` =".(int)$attribute_id."
        AND `option_id` =".(int)$option_id."
        LIMIT 1");
        return ($attribute['id'] > 0) ? $attribute['id'] : 0;
    }

    public function getAttributeChildrenCode($attribute_id = 0, $array = [])
    {
        $attribute = $this->connection->fetchRow("SELECT `oyi_gifts_attributes`.`attribute_id`, `eav_attribute`.`attribute_code`
        FROM `oyi_gifts_attributes`
        JOIN `eav_attribute` ON `eav_attribute`.`attribute_id` = `oyi_gifts_attributes`.`attribute_id`
        WHERE `oyi_gifts_attributes`.`parent_id` = ".(int)$attribute_id."
        LIMIT 1");
        $array[] = $attribute['attribute_code'];
        if($attribute['attribute_id'] > 0) {
            return $this->getAttributeChildrenCode($attribute['attribute_id'], $array);
        } else {
            return $array;
        }
    }

    public function getViewAllParams($steps,$parent_link = 0, $array = [])
    {
        if($steps != ''){
            $steps_arr = explode(',',$steps);
            foreach($steps_arr as $key => $val ){
                if($val > 0){
                    $attribute = $this->connection->fetchRow("SELECT `oyi_gifts_tree`.`parent_link`,
                `oyi_gifts_link`.`option_id`,
                `eav_attribute`.`attribute_code`,
                `eav_attribute`.`attribute_id`
                FROM `oyi_gifts_tree`
                JOIN `oyi_gifts_link` ON `oyi_gifts_link`.`id` = `oyi_gifts_tree`.`link_id`
                JOIN `eav_attribute` ON `eav_attribute`.`attribute_id` = `oyi_gifts_link`.`attribute_id`
                WHERE `oyi_gifts_tree`.`link_id` = ".(int)$val."
                LIMIT 1");

                    $array[] = [
                        'attribute_code' => $attribute['attribute_code'],
                        'option_id' => $attribute['option_id'],
                        'attribute_id' => $attribute['attribute_id'],
                    ];
                }

            }

        }




//        if($attribute['parent_link'] > 0) {
//            return $this->getViewAllParams($attribute['parent_link'], $array);
//        } else {
            return $array;
//        }
    }

    public function getReturnParams($pid)
    {
        $attribute = $this->connection->fetchRow("SELECT `oyi_gifts_link`.`attribute_id`,`oyi_gifts_tree`.`parent_link`
        FROM `oyi_gifts_link`
        JOIN  `oyi_gifts_tree` ON `oyi_gifts_tree`.`link_id` = `oyi_gifts_link`.`id`
        WHERE `oyi_gifts_link`.`id` = ".$pid."
        LIMIT 1");

        return $attribute;
    }

    public function getGiftsAttributes()
    {
        $attributes =  $this->connection->fetchAll('SELECT oyi_gifts_attributes.attribute_id, eav_attribute.attribute_code
                FROM oyi_gifts_attributes
                JOIN eav_attribute ON eav_attribute.attribute_id = oyi_gifts_attributes.attribute_id
                ');
        $array = [];

        if(!empty($attributes)){
            foreach($attributes as $attribute){
                $array[$attribute['attribute_code']] = $attribute['attribute_id'];

            }
        }
        return $array;
    }

    public function checkIsRootAttribute($id)
    {
        return $this->connection->fetchAll('SELECT id FROM oyi_gifts_configuration WHERE attribute_id = '.$id);
    }

    public function getAllLinksByOptionId($id)
    {
        return $this->connection->fetchAll("
          SELECT DISTINCT oyi_gifts_tree.parent_link, parent.option_id as value, parent.attribute_id, eav_attribute_option_value.value as label
          FROM oyi_gifts_link
          JOIN oyi_gifts_tree ON oyi_gifts_tree.link_id = oyi_gifts_link.id
          JOIN oyi_gifts_link as parent ON parent.id = oyi_gifts_tree.parent_link
          JOIN eav_attribute_option_value ON eav_attribute_option_value.value_id = parent.value_id
          WHERE oyi_gifts_link.option_id =".$id
        );
    }
    
    public function checkIssetAttributeInAdvisor($option_id)
    {
        if($option_id > 0){
            return $this->connection->fetchAll("
                SELECT oyi_gifts_link.id
                FROM oyi_gifts_link 
                JOIN oyi_gifts_tree ON oyi_gifts_tree.link_id = oyi_gifts_link.id
                WHERE oyi_gifts_link.option_id = ".$option_id."
        ");
        }
    }
}
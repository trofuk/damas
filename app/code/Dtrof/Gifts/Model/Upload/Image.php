<?php
namespace Dtrof\Gifts\Model\Upload;

use Magento\Framework\App\Filesystem\DirectoryList;

class Image extends \Magento\Framework\Model\AbstractModel
{
    protected $_directory;
    protected $_media;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_storeManager = $storeManager;
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->_media = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_resource = $resource;
        $this->_resourceCollection = $resourceCollection;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function _construct(){}

    public function _getBaseDir()
    {
        return $this->_directory->getAbsolutePath();
    }

    public function savePhoto($file)
    {
        if(isset($file)){

            require_once $this->_getBaseDir() . 'lib/acimage/AcImage.php';
            $data = array();

            if(!empty($file['tmp_name'])){
                foreach($file['tmp_name'] as $key=>$val){
                    foreach($val as $k=>$v){
                        if($file['error'][$key][$k] == 0){
                            $image_tmp = $v;

                            $media_dir = $this->_media->getAbsolutePath();
                            $upload_dir = $media_dir.'gifts/';
                            if(!is_dir($upload_dir)) mkdir($upload_dir,0775);
                            $ext = '.' . pathinfo($file['name'][$key][$k], PATHINFO_EXTENSION);
                            $image_name = 'option_'.$k.''.$ext;

                            $img = \AcImage::createImage($image_tmp);
                            \AcImage::setRewrite(true);
                            \AcImage::setQuality(100);
                            $img->cropCenter('1pr', '1pr');
                            $img->resize(60, 60);
                            $img->saveAsJPG($upload_dir . $image_name);
                            $data[] = array(
                                'attribute_id' => $key,
                                'option_id' =>$k,
                                'image' => $image_name
                            );
                        }
                    }
                }
            }

            return $data;
        }
    }

}

<?php
namespace Dtrof\Gifts\Model\Configuration\Source;

class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Dtrof\Gifts\Model\Configuration
     */
    protected $post;

    /**
     * Constructor
     *
     * @param \Dtrof\Gifts\Model\Configuration $post
     */
    public function __construct(\Dtrof\Gifts\Model\Configuration $post)
    {
        $this->post = $post;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
//        $availableOptions = $this->post->getAvailableStatuses();
//        foreach ($availableOptions as $key => $value) {
//            $options[] = [
//                'label' => $value,
//                'value' => $key,
//            ];
//        }
        return $options;
    }
}
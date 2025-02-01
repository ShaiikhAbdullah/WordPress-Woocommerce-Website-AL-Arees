<?php
namespace TenWebQueue\DTO;

class QueueDataDTO
{
    /**
     * @var string $name
     */
    public $name;

    /**
     * @var bool $multiple_items
     */
    public $multipleItems = false;

    /**
     * @param $data
     */
    public function __construct($data = array())
    {
        if (!empty($data['name'])) {
            $this->name = $data['name'];
        }
        if (!empty($data['multiple_items'])) {
            $this->multipleItems = $data['multiple_items'];
        }
    }
}

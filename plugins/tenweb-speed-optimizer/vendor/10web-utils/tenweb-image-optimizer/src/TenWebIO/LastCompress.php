<?php
namespace TenWebIO;

class LastCompress
{
    private $image_size = 0;
    private $image_orig_size = 0;
    private $count = 0;

    /**
     * @param $force
     */
    public function __construct($force = false)
    {
        if (!$force) {
            $data = get_site_option(TENWEBIO_PREFIX . '_last_compress_data');
            if (!empty($data)) {
                if (!empty($data['image_size'])) {
                    $this->image_size = $data['image_size'];
                }
                if (!empty($data['image_orig_size'])) {
                    $this->image_orig_size = $data['image_orig_size'];
                }
                if (!empty($data['count'])) {
                    $this->count = $data['count'];
                }
            }
        }
    }

    /**
     * @param $image_size
     * @param $image_orig_size
     * @param $count
     *
     * @return void
     */
    public function update($image_size, $image_orig_size, $count = 1)
    {
        update_site_option(TENWEBIO_PREFIX . '_last_compress_data', array(
            "image_size"      => $this->image_size + $image_size,
            "image_orig_size" => $this->image_orig_size + $image_orig_size,
            "count"           => $this->count + $count,
        ));
    }

    /**
     * @return int
     */
    public function getImageSize()
    {
        return $this->image_size;
    }

    /**
     * @return int
     */
    public function getImageOrigSize()
    {
        return $this->image_orig_size;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

}
<?php
namespace TenWebQueue\DTO;

class QueueConfigDTO
{
    /**
     * @var string
     */
    public $connectionType = 'file';

    /**
     * @var string
     */
    public $fileConnectionPath = '';

    /**
     * @var QueueDataDTO
     */
    public $queueData;

    /**
     * @var string
     */
    public $restartFilePath = '';

    /**
     * @var string
     */
    public $restartRoute = '';

    /**
     * @var array
     */
    public $restartBody = array();

    /**
     * @var int
     */
    public $itemsCountForRestart = 20;

    /**
     * @var bool
     */
    public $force = false;


    /**
     * @param $data
     */
    public function __construct($data = array())
    {
        if (!empty($data['connection_type'])) {
            $this->connectionType = $data['connection_type'];
        }

        if (!empty($data['file_connection_path'])) {
            $this->fileConnectionPath = $data['file_connection_path'];
        }
        if (!empty($data['queue_data'])) {
            $this->queueData = $data['queue_data'];
        }
        if (!empty($data['restart_file_path'])) {
            $this->restartFilePath = $data['restart_file_path'];
        }
        if (!empty($data['restart_route'])) {
            $this->restartRoute = $data['restart_route'];
        }
        if (!empty($data['restart_body'])) {
            $this->restartBody = $data['restart_body'];
        }
        if (!empty($data['items_count_for_restart'])) {
            $this->itemsCountForRestart = $data['items_count_for_restart'];
        }
        if (!empty($data['force'])) {
            $this->force = $data['force'];
        }
    }
}

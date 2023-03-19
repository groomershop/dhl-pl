<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Logger;

use Exception;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Class Handler
 * @package DHL\Dhl24pl\Model\Logger
 */
class Handler extends Base
{
    /**
     * Handler constructor.
     * @param DriverInterface $filesystem
     * @param null $filePath
     * @throws Exception
     */
    public function __construct(DriverInterface $filesystem, $filePath = null)
    {
        parent::__construct($filesystem, $filePath);
        $this->getFormatter()->ignoreEmptyContextAndExtra(true);
    }

    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/dhl_api.log';
}

<?php

namespace Calcurates\ModuleMagento\Model\Shipment;

use Calcurates\ModuleMagento\Api\Client\CalcuratesClientInterface;

class CustomPackagesProvider
{
    /**
     * @var CalcuratesClientInterface
     */
    private $calcuratesClient;

    /**
     * @var array|null
     */
    private $packages;

    public function __construct(CalcuratesClientInterface $calcuratesClient)
    {
        $this->calcuratesClient = $calcuratesClient;
    }

    /**
     * @param int|null $storeId
     * @return array
     */
    public function getCustomPackages($storeId = null)
    {
        if ($this->packages === null) {
            $this->packages = $this->calcuratesClient->getCustomPackages($storeId);
        }

        return $this->packages;
    }
}

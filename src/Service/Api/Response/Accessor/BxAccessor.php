<?php
namespace Boxalino\RealTimeUserExperience\Service\Api\Response\Accessor;

/**
 * Class BxAccessor
 * A generic bx-<accessor> object that holds the total hit count for a given accessor
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api\Response\Accessor
 */
class BxAccessor extends Accessor
    implements AccessorInterface
{
    /**
     * @var int
     */
    protected $totalHitCounts = 0;

    /**
     * @return int
     */
    public function getTotalHitCounts(): int
    {
        return $this->totalHitCounts;
    }

    /**
     * @param int $totalHitCounts
     * @return BxAccessor
     */
    public function setTotalHitCounts(int $totalHitCounts): BxAccessor
    {
        $this->totalHitCounts = $totalHitCounts;
        return $this;
    }

}

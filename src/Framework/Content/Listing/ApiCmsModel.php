<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Listing;

use Boxalino\RealTimeUserExperienceApi\Framework\Content\Listing\ApiCmsModelInterface;
use Shopware\Core\Framework\Struct\Struct;

/**
 * Class ApiCmsModel
 * Model used for the Boxalino Narrative CMS block
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Content\Listing
 */
class ApiCmsModel extends Struct
    implements ApiCmsModelInterface
{
    /**
     * @var \ArrayIterator
     */
    protected $blocks;

    /**
     * @var string
     */
    protected $requestId;

    /**
     * @var string
     */
    protected $groupBy;

    /**
     * @var string
     */
    protected $variantUuid;

    /**
     * @var int
     */
    protected $totalHitCount = 0;

    /**
     * @var null | string
     */
    protected $navigationId = null;

    /**
     * @var \ArrayIterator
     */
    protected $left;

    /**
     * @var \ArrayIterator
     */
    protected $right;

    /**
     * @var \ArrayIterator
     */
    protected $bottom;

    /**
     * @var \ArrayIterator
     */
    protected $top;

    /**
     * @return \ArrayIterator
     */
    public function getBlocks(): \ArrayIterator
    {
        return $this->blocks;
    }

    /**
     * @param \ArrayIterator $blocks
     * @return ApiCmsModel
     */
    public function setBlocks(\ArrayIterator $blocks): ApiCmsModel
    {
        $this->blocks = $blocks;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * @param string $requestId
     * @return ApiCmsModel
     */
    public function setRequestId(string $requestId): ApiCmsModel
    {
        $this->requestId = $requestId;
        return $this;
    }

    /**
     * @return string
     */
    public function getGroupBy(): string
    {
        return $this->groupBy;
    }

    /**
     * @param string $groupBy
     * @return ApiCmsModel
     */
    public function setGroupBy(string $groupBy): ApiCmsModel
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    /**
     * @return string
     */
    public function getVariantUuid(): string
    {
        return $this->variantUuid;
    }

    /**
     * @param string $variantUuid
     * @return ApiCmsModel
     */
    public function setVariantUuid(string $variantUuid): ApiCmsModel
    {
        $this->variantUuid = $variantUuid;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalHitCount(): int
    {
        return $this->totalHitCount;
    }

    /**
     * @param int $totalHitCount
     * @return ApiCmsModel
     */
    public function setTotalHitCount(int $totalHitCount): ApiCmsModel
    {
        $this->totalHitCount = $totalHitCount;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNavigationId(): ?string
    {
        return $this->navigationId;
    }

    /**
     * @param string|null $navigationId
     * @return ApiCmsModel
     */
    public function setNavigationId(?string $navigationId): ApiCmsModel
    {
        $this->navigationId = $navigationId;
        return $this;
    }

    /**
     * @return \ArrayIterator
     */
    public function getLeft(): \ArrayIterator
    {
        return $this->left;
    }

    /**
     * @param \ArrayIterator $left
     * @return ApiCmsModel
     */
    public function setLeft(\ArrayIterator $left): ApiCmsModel
    {
        $this->left = $left;
        return $this;
    }

    /**
     * @return \ArrayIterator
     */
    public function getRight(): \ArrayIterator
    {
        return $this->right;
    }

    /**
     * @param \ArrayIterator $right
     * @return ApiCmsModel
     */
    public function setRight(\ArrayIterator $right): ApiCmsModel
    {
        $this->right = $right;
        return $this;
    }

    /**
     * @return \ArrayIterator
     */
    public function getBottom(): \ArrayIterator
    {
        return $this->bottom;
    }

    /**
     * @param \ArrayIterator $bottom
     * @return ApiCmsModel
     */
    public function setBottom(\ArrayIterator $bottom): ApiCmsModel
    {
        $this->bottom = $bottom;
        return $this;
    }

    /**
     * @return \ArrayIterator
     */
    public function getTop(): \ArrayIterator
    {
        return $this->top;
    }

    /**
     * @param \ArrayIterator $top
     * @return ApiCmsModel
     */
    public function setTop(\ArrayIterator $top): ApiCmsModel
    {
        $this->top = $top;
        return $this;
    }


}

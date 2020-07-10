<?php

declare(strict_types=1);

namespace Yireo\ExampleDealersGraphQl\Plugin;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\UrlRewriteGraphQl\Model\Resolver\EntityUrl;
use Yireo\ExampleDealers\Api\Data\DealerInterface;
use Yireo\ExampleDealers\Api\DealerRepositoryInterface;

class EntityUrlResolverPlugin
{
    /**
     * @var array
     */
    private $args = [];
    /**
     * @var DealerRepositoryInterface
     */
    private $dealerRepository;

    /**
     * EntityUrlResolverPlugin constructor.
     * @param DealerRepositoryInterface $dealerRepository
     */
    public function __construct(DealerRepositoryInterface $dealerRepository)
    {
        $this->dealerRepository = $dealerRepository;
    }

    /**
     * @param EntityUrl $subject
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function beforeResolve(
        EntityUrl $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->args = $args;
        return [$field, $context, $info, $value, $args];
    }

    /**
     * @param EntityUrl $subject
     * @param $result
     * @return array
     */
    public function afterResolve(EntityUrl $subject, $result)
    {
        if (is_array($result) && !empty($result) && !empty($result['id'])) {
            return $result;
        }

        $url = preg_replace('/\.html$/', '', (string)$this->args['url']);
        $items = $this->dealerRepository->search($url, 'url_key');
        if (count($items) < 1) {
            return null;
        }

        /** @var DealerInterface $item */
        $item = array_shift($items);
        return [
            'id' => $item->getId(),
            'canonical_url' => $url,
            'relative_url' => $url,
            'redirectCode' => null,
            'type' => 'DEALER'
        ];
    }
}

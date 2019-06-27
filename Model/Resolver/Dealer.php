<?php
declare(strict_types=1);

namespace Yireo\ExampleDealersGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

use Yireo\ExampleDealers\Api\DealerRepositoryInterface;

class Dealer implements ResolverInterface
{
    /**
     * @var DealerRepositoryInterface
     */
    private $dealerRepository;

    /**
     * Dealer constructor.
     * @param DealerRepositoryInterface $dealerRepository
     */
    public function __construct(
        DealerRepositoryInterface $dealerRepository
    ) {
        $this->dealerRepository = $dealerRepository;
    }

    /**
     * Fetches the data from persistence models and format it according to the GraphQL schema.
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws \Exception
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $items = [];
        $dealers = $this->dealerRepository->getAll();

        foreach ($dealers as $dealer) {
            $items[] = [
                'id' => $dealer->getId(),
                'name' => $dealer->getName(),
                'address' => $dealer->getAddress(),
            ];
        }

        return [
            'items' => $items,
            'total_count' => count($items)
        ];
    }
}

<?php
declare(strict_types=1);

namespace Yireo\ExampleDealersGraphQl\Test\Integration;

use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\AbstractController as ControllerTestCase;
use Yireo\ExampleDealers\Api\Data\DealerInterface;
use Yireo\ExampleDealers\Api\DealerRepositoryInterface;
use Laminas\Http\Headers;

class GraphqlTest extends ControllerTestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var DealerRepositoryInterface
     */
    private $repository;

    /**
     * Setup
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->repository = $this->objectManager->get(DealerRepositoryInterface::class);
        parent::setUp();
    }

    /**
     * Test if the module is registered
     */
    public function testIfGraphQlOutputIsGiven()
    {
        $query = <<<QUERY
query fetchDealers {
  dealers {
    items {
      name
      address
      description
    }
  }
}
QUERY;

        $dealer = $this->createDealer('Kermit the Frog', 'Sesame Street', 'Green');
        $this->assertNotEmpty($dealer->getName());

        $this->dispatchGraphQlQuery($query);
        $responseBody = $this->getResponse()->getBody();
        $this->assertNotEmpty($responseBody);

        /** @var Serialize $serializer */
        $serializer = $this->objectManager->get(Serialize::class);
        $data = $serializer->unserialize($responseBody);
        $this->assertNotEmpty($data['data']['dealers']['items']);

        $items = $data['data']['dealers']['items'];
        $searchMatch = false;
        foreach ($items as $item) {
            if ($item['name'] === $dealer->getName()) {
                $searchMatch = true;
            }
        }

        $this->assertTrue($searchMatch, 'Assuming that Kermit the Frog is in the list of dealers');
    }

    /**
     * @param string $query
     * @param array $variables
     * @param string $token
     */
    private function dispatchGraphQlQuery(string $query, array $variables = [], string $token = '')
    {
        $data = ['query' => $query, 'variables' => $variables];
        $serializer = $this->objectManager->get(Serialize::class);
        $content = $serializer->serialize($data);

        $headers = new Headers();
        $headers->addHeaderLine('Content-Type', 'application/json');
        if (null !== $token) {
            $headers->addHeaderLine('Authorization', 'Bearer ' . $token);
        }

        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setHeaders($headers);
        $this->getRequest()->setContent($content);

        $this->dispatch('/graphql');
    }

    /**
     * @param string $name
     * @param string $address
     * @param string $description
     * @return DealerInterface
     */
    private function createDealer(string $name, string $address, string $description): DealerInterface
    {
        $dealer = $this->repository->getEmpty();
        $dealer->setName($name);
        $dealer->setAddress($address);
        $dealer->setDescription($description);
        $this->repository->save($dealer);
        return $dealer;
    }
}

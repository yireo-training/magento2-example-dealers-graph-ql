<?php
declare(strict_types=1);

namespace Yireo\ExampleDealersGraphQl\Test\Integration;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\AbstractController as ControllerTestCase;
use Yireo\ExampleDealers\Api\Data\DealerInterface;
use Yireo\ExampleDealers\Api\DealerRepositoryInterface;
use Zend\Http\Headers;
use Zend\Http\Request;

/**
 * Class GraphqlTest
 * @package Yireo\Yireo_ExampleDealersGraphQl\Test\Integration
 */
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
    }
  }
}
QUERY;

        $dealer = $this->createDealer('Kermit the Frog', 'Sesame Street');
        $this->assertNotEmpty($dealer->getName());

        $this->dispatchGraphQlQuery($query);
        $responseBody = $this->getResponse()->getBody();
        $this->assertNotEmpty($responseBody);

        $data = json_decode($responseBody, true);
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
        $content = json_encode(['query' => $query, 'variables' => $variables]);

        $headers = new Headers();
        $headers->addHeaderLine('Content-Type', 'application/json');
        if (null !== $token) {
            $headers->addHeaderLine('Authorization', 'Bearer ' . $token);
        }

        $this->getRequest()->setMethod(Request::METHOD_POST);
        $this->getRequest()->setHeaders($headers);
        $this->getRequest()->setContent($content);

        $this->dispatch('/graphql');
    }

    /**
     * @param string $name
     * @param string $address
     * @return DealerInterface
     */
    private function createDealer(string $name, string $address): DealerInterface
    {
        $dealer = $this->repository->getEmpty();
        $dealer->setName($name);
        $dealer->setAddress($address);
        $this->repository->save($dealer);
        return $dealer;
    }
}

<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\BenchmarkBundle\Service;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\BenchmarkBundle\BenchmarkCollectorInterface;
use Shopware\Bundle\BenchmarkBundle\Exception\TransmissionNotNecessaryException;
use Shopware\Bundle\BenchmarkBundle\StatisticsClientInterface;
use Shopware\Bundle\BenchmarkBundle\Struct\StatisticsRequest;
use Shopware\Bundle\BenchmarkBundle\Struct\StatisticsResponse;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Models\Benchmark\BenchmarkConfig;
use Shopware\Models\Benchmark\Repository as BenchmarkRepository;

class StatisticsService
{
    /**
     * @var BenchmarkCollectorInterface
     */
    private $benchmarkCollector;

    /**
     * @var StatisticsClientInterface
     */
    private $statisticsClient;

    /**
     * @var BenchmarkRepository
     */
    private $benchmarkRepository;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param BenchmarkCollectorInterface $benchmarkCollector
     * @param StatisticsClientInterface   $statisticsClient
     * @param BenchmarkRepository         $benchmarkRepository
     * @param ContextServiceInterface     $contextService
     * @param Connection                  $connection
     */
    public function __construct(
        BenchmarkCollectorInterface $benchmarkCollector,
        StatisticsClientInterface $statisticsClient,
        BenchmarkRepository $benchmarkRepository,
        ContextServiceInterface $contextService,
        Connection $connection
    ) {
        $this->benchmarkCollector = $benchmarkCollector;
        $this->statisticsClient = $statisticsClient;
        $this->benchmarkRepository = $benchmarkRepository;
        $this->contextService = $contextService;
        $this->connection = $connection;
    }

    /**
     * @param BenchmarkConfig $config
     * @param int             $batchSize
     *
     * @throws TransmissionNotNecessaryException
     *
     * @return StatisticsResponse
     */
    public function transmit(BenchmarkConfig $config, $batchSize = null)
    {
        $benchmarkData = $this->benchmarkCollector->get($this->contextService->createShopContext($config->getShopId()), $batchSize);

        $ordersCount = count($benchmarkData['orders']['list']);
        $customersCount = count($benchmarkData['customers']['list']);
        $productsCount = count($benchmarkData['products']['list']);
        $analyticsCount = count($benchmarkData['analytics']['list']);

        if ($ordersCount === 0 && $customersCount === 0 && $productsCount === 0 && $analyticsCount === 0) {
            $config->setLastSent(new \DateTime('now', new \DateTimeZone('UTC')));
            $this->benchmarkRepository->save($config);

            throw new TransmissionNotNecessaryException();
        }

        $benchmarkDataJson = json_encode($benchmarkData, JSON_HEX_TAG);

        $request = new StatisticsRequest($benchmarkDataJson);

        /** @var StatisticsResponse $statisticsResponse */
        $statisticsResponse = $this->statisticsClient->sendStatistics($request);

        $config->setToken($statisticsResponse->getToken());

        $this->updateLastIds($config, $benchmarkData);

        $this->benchmarkRepository->save($config);

        return $statisticsResponse;
    }

    /**
     * @param BenchmarkConfig $config
     * @param array           $benchmarkData
     */
    private function updateLastIds(BenchmarkConfig $config, array $benchmarkData)
    {
        if (!empty($benchmarkData['orders']['list'])) {
            $order = end($benchmarkData['orders']['list']);
            $config->setLastOrderId($order['orderId']);
        }

        if (!empty($benchmarkData['customers']['list'])) {
            $customer = end($benchmarkData['customers']['list']);
            $config->setLastCustomerId($customer['customerId']);
        }

        if (!empty($benchmarkData['products']['list'])) {
            $product = end($benchmarkData['products']['list']);
            $config->setLastProductId($product['productId']);
        }

        if (!empty($benchmarkData['analytics']['list'])) {
            $lastId = $this->connection->fetchColumn('SELECT id FROM s_statistics_visitors WHERE shopID = ? ORDER BY id DESC LIMIT 1', [
                $config->getShopId(),
            ]);

            $config->setLastAnalyticsId($lastId);
        }
    }
}

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

namespace Shopware\Bundle\PluginInstallerBundle;

use Shopware\Bundle\PluginInstallerBundle\Exception\AccountException;
use Shopware\Bundle\PluginInstallerBundle\Exception\AuthenticationException;
use Shopware\Bundle\PluginInstallerBundle\Exception\LicenceException;
use Shopware\Bundle\PluginInstallerBundle\Exception\OrderException;
use Shopware\Bundle\PluginInstallerBundle\Exception\SbpServerException;
use Shopware\Bundle\PluginInstallerBundle\Exception\StoreException;
use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Shopware\Components\HttpClient\HttpClientInterface;
use Shopware\Components\HttpClient\RequestException;


/**
 * @package Shopware\Bundle\PluginInstallerBundle
 */
class StoreClient
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $apiEndPoint;

    /**
     * @var Struct\StructHydrator
     */
    private $structHydrator;

    /**
     * @param HttpClientInterface $httpClient
     * @param string $apiEndPoint
     * @param Struct\StructHydrator $structHydrator
     */
    public function __construct(
        HttpClientInterface $httpClient,
        $apiEndPoint,
        Struct\StructHydrator $structHydrator
    ) {
        $this->httpClient = $httpClient;
        $this->apiEndPoint = $apiEndPoint;
        $this->structHydrator = $structHydrator;
    }

    /**
     * @param $shopwareId
     * @param $password
     * @return AccessTokenStruct
     * @throws \Exception
     */
    public function getAccessToken($shopwareId, $password)
    {
        $response = $this->doPostRequest(
            '/accesstokens',
            array(
                'shopwareId' => $shopwareId,
                'password'   => $password
            )
        );

        return $this->structHydrator->hydrateAccessToken($response, $shopwareId);
    }

    /**
     * @param string $resource
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function doGetRequest($resource, $params = array())
    {
        $response = $this->getRequest(
            $resource,
            $params
        );

        return json_decode($response->getBody(), true);
    }

    /**
     * @param AccessTokenStruct $accessToken
     * @param string $resource
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function doAuthGetRequest(
        AccessTokenStruct $accessToken,
        $resource,
        $params
    ) {
        $response = $this->getRequest(
            $resource,
            $params,
            $accessToken
        );

        return json_decode($response->getBody(), true);
    }

    /**
     * @param $resource
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function doGetRequestRaw($resource, $params = array())
    {
        $response = $this->getRequest(
            $resource,
            $params
        );

        return $response->getBody();
    }

    /**
     * @param AccessTokenStruct $accessToken
     * @param string $resource
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function doAuthGetRequestRaw(
        AccessTokenStruct $accessToken,
        $resource,
        $params
    ) {
        $response = $this->getRequest(
            $resource,
            $params,
            $accessToken
        );
        return $response->getBody();
    }

    /**
     * @param string $resource
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function doPostRequest($resource, $params)
    {
        $response = $this->postRequest(
            $resource,
            $params
        );
        return json_decode($response->getBody(), true);
    }

    /**
     * @param AccessTokenStruct $accessToken
     * @param string $resource
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function doAuthPostRequest(
        AccessTokenStruct $accessToken,
        $resource,
        $params
    ) {
        $response = $this->postRequest(
            $resource,
            $params,
            $accessToken
        );

        return json_decode($response->getBody(), true);
    }

    /**
     * @param AccessTokenStruct $accessToken
     * @param $resource
     * @param $params
     * @return \Shopware\Components\HttpClient\Response
     * @throws \Exception
     */
    public function doAuthPostRequestRaw(
        AccessTokenStruct $accessToken,
        $resource,
        $params
    ) {
        $response = $this->postRequest(
            $resource,
            $params,
            $accessToken
        );

        return $response;
    }

    /**
     * @param $resource
     * @param $params
     * @param AccessTokenStruct $token
     * @return \Shopware\Components\HttpClient\Response
     * @throws StoreException
     * @throws \Exception
     */
    private function getRequest($resource, $params, AccessTokenStruct $token = null)
    {
        $url = $this->apiEndPoint . $resource;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $header = [];
        if ($token) {
            $header['X-Shopware-Token'] = $token->getToken();
        }

        try {
            $response = $this->httpClient->get($url, $header);
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }

        return $response;
    }

    /**
     * @param $resource
     * @param array $params
     * @param AccessTokenStruct $token
     * @return \Shopware\Components\HttpClient\Response
     * @throws StoreException
     * @throws \Exception
     */
    private function postRequest($resource, $params = array(), AccessTokenStruct $token = null)
    {
        $url = $this->apiEndPoint . $resource;

        $header = [];
        if ($token) {
            $header['X-Shopware-Token'] = $token->getToken();
        }
        try {
            $response = $this->httpClient->post(
                $url,
                $header,
                json_encode($params)
            );
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }

        return $response;
    }

    /**
     * Handles an Exception thrown by the HttpClient
     * Parses it to detect and extract details provided
     * by SBP about what happened
     *
     * @param \Exception $requestException
     * @throws \Exception
     * @throws SbpServerException
     * @throws AuthenticationException
     * @throws AccountException
     * @throws OrderException
     * @throws LicenceException
     * @throws StoreException
     */
    private function handleRequestException(\Exception $requestException)
    {
        if (!$requestException->getBody()) {
            throw $requestException;
        }

        $data = json_decode($requestException->getBody(), true);

        if (!isset($data['code'])) {
            throw $requestException;
        }

        $httpCode = $data['error'];
        $sbpCode  = $data['code'];

        switch ($sbpCode) {

            case 'BinariesException-0':       //Link not found
            case 'BinariesException-1':       //Deserialization failure
            case 'BinariesException-2':       //Upload file is invalid
            case 'BinariesException-3':       //Binary is invalid
            case 'BinariesException-4':       //Binary changeset is invalid
            case 'BinariesException-5':       //Cannot delete binary that succeeded code review
            case 'BinariesException-6':       //Could not load from path
            case 'BinariesException-7':       //Binary is getting checked although not waiting for code review
            case 'BinariesException-8':       //Failed storing encrypted binary
            case 'BinariesException-9':       //Ioncube encryption failed
            case 'BinariesException-11':      //No fitting binary found
            case 'PluginLicensesException-6': //Deserialization failed.
            case 'OrdersException-2':         //Deserialization failed
            case 'UsersException-5':          //Deserialization failed
                throw new SbpServerException($sbpCode, 'server_error', $httpCode, $requestException);

            case 'BinariesException-10': //Shopware version not given
            case 'BinariesException-12': //Shopware version is invalid
                throw new SbpServerException($sbpCode, 'shopware_version', $httpCode, $requestException);

            case 'BinariesException-14':      //Unauthorized
            case 'UsersException-4':          //Unauthorized
            case 'OrdersException-0':         //Order authentification failed
            case 'PluginLicensesException-8': //Unauthorized
            case 'UserTokensException-0':     //Authorization failed!
            case 'UserTokensException-1':     //Token invalid.
            case 'UserTokensException-2':     //Given token is invalid.
                throw new AuthenticationException($sbpCode, 'authentication', $httpCode, $requestException);

            case 'UsersException-1':      //Invalid parameters for registration.
                throw new AccountException($sbpCode, 'registration', $httpCode, $requestException);

            case 'UsersException-2':      //ShopwareID is already taken
                throw new AccountException($sbpCode, 'shopware_id_already_taken', $httpCode, $requestException);

            case 'UsersException-3':      //User is invalid
                throw new AccountException($sbpCode, 'invalid_user', $httpCode, $requestException);

            case 'UsersException-6':      //Invalid password reset parameters
                throw new AccountException($sbpCode, 'invalid_password_reset', $httpCode, $requestException);

            case 'UserTokensException-3': //Account is banned.
                throw new AccountException($sbpCode, 'account_banned', $httpCode, $requestException);

            case 'BinariesException-13':      //Plugin not found
            case 'OrdersException-1':         //Ordered plugin not found
            case 'PluginLicensesException-1': //Referenced plugin not found.
                throw new OrderException($sbpCode, 'plugin_not_found', $httpCode, $requestException);

            case 'OrdersException-4':         //Insufficient balance
                throw new OrderException($sbpCode, 'insufficient_balance', $httpCode, $requestException);

            case 'OrdersException-3':  //Empty order
            case 'OrdersException-5':  //Order invalid
            case 'OrdersException-6':  //Order position invalid
                throw new OrderException($sbpCode, 'order_invalid', $httpCode, $requestException);

            case 'OrdersException-7':  //Shop version incompatible with license version
                throw new OrderException($sbpCode, 'incompatible_version', $httpCode, $requestException);

            case 'PluginLicensesException-0': //License not found.
                throw new LicenceException($sbpCode, 'licence_not_found', $httpCode, $requestException);

            case 'PluginLicensesException-2': //License is invalid.
            case 'PluginLicensesException-9': //Invalid parameters.
                throw new LicenceException($sbpCode, 'licence_invalid', $httpCode, $requestException);

            case 'PluginLicensesException-3': //Referenced shop not found.
                throw new LicenceException($sbpCode, 'shop_not_found', $httpCode, $requestException);

            case 'PluginLicensesException-4': //License is already ordered for this shop.
            case 'PluginLicensesException-7': //License already ordered with a better price model.
                throw new LicenceException($sbpCode, 'already_ordered', $httpCode, $requestException);

        }

        throw new StoreException(
            $data[$sbpCode],
            $data['reason'],
            $httpCode,
            $requestException
        );
    }
}

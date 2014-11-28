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

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Controllers_Frontend_AccountTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * test testPartnerStatistic controller action
     *
     * @group disable
     * @return array|int|string $id
     */
    public function testPartnerStatistic()
    {
        //Login to the frontend
        $this->Request()
                ->setMethod('POST')
                ->setPost('email', 'test@example.com')
                ->setPost('password', 'shopware');
        $this->dispatch('/account/login');
        $this->assertTrue($this->Response()->isRedirect());
        $this->reset();

        //setting date range
        $params['fromDate'] = '01.01.2000';
        $params['toDate'] = '01.01.2222';
        $this->Request()->setParams($params);
        Shopware()->Session()->partnerId = 1;

        $this->dispatch('/account/partnerStatistic');
        $this->assertEquals('01.01.2000', $this->View()->partnerStatisticFromDate);
        $this->assertEquals('01.01.2222', $this->View()->partnerStatisticToDate);
        $chartData = $this->View()->sPartnerOrderChartData[0];

        $this->assertInstanceOf('\DateTime', $chartData['date']);
        $this->assertTrue(!empty($chartData['timeScale']));
        $this->assertTrue(!empty($chartData['netTurnOver']));
        $this->assertTrue($chartData['provision'] !== '0' ? !empty($chartData['provision']) : empty($chartData['provision']));
    }

    /**
     * SW-8258 - check if email addresses with new domains like .berlin are valid
     */
    public function testValidEmailAddresses()
    {
        $emailAddresses = array(
            // old domains
            'test@example.de',
            'test@example.com',
            'test@example.org',

            // new released domains
            'test@example.berlin',
            'test@example.email',
            'test@example.systems',

            // new non released domains
            'test@example.active',
            'test@example.love',
            'test@example.video'
        );

        $invalidEmailAddresses = array(
            'test',
            'test@example',
            'test@.de',
            '@example',
            '@example.de',
            '@.',
            ' @ .de',
        );

        $validator = new Zend_Validate_EmailAddress();
        $validator->getHostnameValidator()->setValidateTld(false);

        foreach($emailAddresses as $email) {
            $this->assertTrue($validator->isValid($email));
        }

        foreach($invalidEmailAddresses as $email) {
            $this->assertFalse($validator->isValid($email));
        }
    }

    /**
     * Test if the download goes through php
     * @ticket SW-5226
     */
    public function testDownloadESDViaPhp()
    {
        $loremIpsum = "Lorem ipsum dolor sit amet, consetetur sadipscing elitr,
        sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.
        At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus
        est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
        eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam
        et justo duo dolores et ea rebum. Stet clita kasd gubergren,
        no sea takimata sanctus est Lorem ipsum dolor sit amet.";

        $filePath = Shopware()->OldPath() . 'files/'.Shopware()->Config()->get('sESDKEY');
        $deleteFolderOnTearDown = !file_exists($filePath) ? $filePath : false;
        mkdir($filePath, 0777);
        file_put_contents($filePath . '/shopware_packshot_community_edition_72dpi_rgb.png', $loremIpsum);

        $this->Request()
            ->setMethod('POST')
            ->setPost('email', 'test@example.com')
            ->setPost('password', 'shopware');

        $this->dispatch('/account/login');
        $this->reset();

        $params["esdID"] = 204;
        $this->Request()->setParams($params);
        $this->dispatch('/account/download');

        $header = $this->Response()->getHeaders();
        $this->assertEquals("Content-Disposition", $header[1]["name"]);
        $this->assertEquals('attachment; filename="shopware_packshot_community_edition_72dpi_rgb.png"', $header[1]["value"]);
        $this->assertEquals('Content-Length', $header[2]["name"]);
        $this->assertGreaterThan(630, intval($header[2]["value"]));
        $this->assertEquals(strlen($this->Response()->getBody()), intval($header[2]["value"]));

        if ($deleteFolderOnTearDown) {
            $files = glob($deleteFolderOnTearDown . '/*'); // get all file names
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($deleteFolderOnTearDown);
        }
    }

    /**
     * Checks that the login don't work with the MD5 encrypted password.
     * This is only valid if the parameter $ignoreAccountMode is set with the MD5 encrypted password.
     *
     * @ticket SW-5409
     */
    public function testNormalLogin()
    {
        $this->assertEmpty(Shopware()->Session()->sUserId);
        $this->Request()->setMethod('POST')
            ->setPost('email', 'test@example.com')
            ->setPost('password', 'shopware');

        $this->dispatch('/account/login');
        $this->assertNotEmpty(Shopware()->Session()->sUserId);
        $this->assertEquals(1, Shopware()->Session()->sUserId);

        $this->logoutUser();
    }

    /**
     * Checks that the login don't work with the MD5 encrypted password.
     * This is only valid if the parameter $ignoreAccountMode is set with the MD5 encrypted password.
     *
     * @ticket SW-5409
     */
    public function testHashPostLogin()
    {
        //test with md5 password and without the ignoreAccountMode parameter
        $this->assertEmpty(Shopware()->Session()->sUserId);
        $this->setUserDataToPost();
        $this->dispatch('/account/login');
        $this->assertEmpty(Shopware()->Session()->sUserId);

        $this->logoutUser();
    }

    /**
     * Checks that the login don't work with the MD5 encrypted password.
     * This is only valid if the parameter $ignoreAccountMode is set with the MD5 encrypted password.
     *
     * @ticket SW-5409
     */
    public function testWithoutIgnoreLogin()
    {
        //test the internal call of the method with the $ignoreAccountMode parameter

        $this->setUserDataToPost();
        $this->dispatch('/');
        $result = Shopware()->Modules()->Admin()->sLogin(true);
        $this->assertNotEmpty(Shopware()->Session()->sUserId);
        $this->assertEquals(1, Shopware()->Session()->sUserId);
        $this->assertEmpty($result["sErrorFlag"]);
        $this->assertEmpty($result["sErrorMessages"]);

        $this->logoutUser();
        //test the internal call of the method without the $ignoreAccountMode parameter

        $this->setUserDataToPost();

        $this->dispatch('/');
        $result = Shopware()->Modules()->Admin()->sLogin();
        $this->assertEmpty(Shopware()->Session()->sUserId);
        $this->assertNotEmpty($result["sErrorFlag"]);
        $this->assertNotEmpty($result["sErrorMessages"]);
    }

    /**
     * Checks that the login don't work with the MD5 encrypted password.
     * This is only valid if the parameter $ignoreAccountMode is set with the MD5 encrypted password.
     *
     * @ticket SW-5409
     */
    public function testWithIgnoreLogin()
    {
        //test the internal call of the method without the $ignoreAccountMode parameter
        $this->setUserDataToPost();

        $this->dispatch('/');
        $result = Shopware()->Modules()->Admin()->sLogin();
        $this->assertEmpty(Shopware()->Session()->sUserId);
        $this->assertNotEmpty($result["sErrorFlag"]);
        $this->assertNotEmpty($result["sErrorMessages"]);

        $this->logoutUser();
    }

    /**
     * helper to logout the user
     */
    private function logoutUser()
    {
        //reset the request
        $this->reset();
        $this->Request()->setMethod('POST');
        $this->dispatch('/account/logout');
        //reset the request
        $this->reset();
    }

    /**
     * set user data to post
     * @return void
     */
    private function setUserDataToPost() {
        $sql = 'SELECT email, password FROM s_user WHERE id = 1';
        $userData = Shopware()->Db()->fetchRow($sql);
        $this->Request()->setMethod('POST')
            ->setPost('email', $userData['email'])
            ->setPost('passwordMD5', $userData['password']);
    }
}

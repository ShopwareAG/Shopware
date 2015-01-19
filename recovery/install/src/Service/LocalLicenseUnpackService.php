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

namespace Shopware\Recovery\Install\Service;

use Shopware\Recovery\Install\Struct\LicenseInformation;
use Shopware\Recovery\Install\Struct\LicenseUnpackRequest;

/**
 * @category  Shopware
 * @package   Shopware\Recovery\Install\Service
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class LocalLicenseUnpackService implements LicenseUnpackService
{
    /**
     * @param  LicenseUnpackRequest $request
     * @return LicenseInformation
     */
    public function evaluateLicense(LicenseUnpackRequest $request)
    {
        $license = $request->licenseKey;
        $host    = $request->host;
        $product = $request->edition;

        $license = str_replace('-------- LICENSE BEGIN ---------', '', $license);
        $license = str_replace('--------- LICENSE END ----------', '', $license);
        $license = preg_replace('#--.+?--#', '', (string) $license);
        $license = preg_replace('#[^A-Za-z0-9+/=]#', '', $license);

        $info = base64_decode($license);
        if ($info === false) {
            return false;
        }

        $info = @gzinflate($info);
        if ($info === false) {
            // License can not be unpacked.
            $this->throwException("License key seems to be incorrect");
        }

        if (strlen($info) > (512 + 60) || strlen($info) < 100) {
            // License too long / short.
            $this->throwException("License key seems to be incorrect");
        }

        $hash          = substr($info, 0, 20);
        $coreLicense   = substr($info, 20, 20);
        $moduleLicense = substr($info, 40, 20);
        $info          = substr($info, 60);

        if ($hash !== sha1($coreLicense . $info . $moduleLicense, true)) {
            return false;
        }

        $info = unserialize($info);
        if ($info === false) {
            $this->throwException("License key seems to be incorrect");
        }

        $info['license'] = $license;

        if (!empty($product) && $product != $info['product']) {
            $this->throwException("License key is formally correct but does not match to the selected shopware edition");
        }

        if ($info['host'] != $host) {
            $this->throwException("License key is not valid for domain " . $request->host);
        }

        $licenseInformation = new LicenseInformation([
            'label'   => $info['label'],
            'module'  => $info['module'],
            'product' => $info['product'],
            'host'    => $info['host'],
            'type'    => $info['type'],
            'license' => $info['license'],
        ]);

        return $licenseInformation;
    }

    /**
     * @param $string
     * @throws \RuntimeException
     */
    public function throwException($string)
    {
        throw new \RuntimeException($string);
    }
}

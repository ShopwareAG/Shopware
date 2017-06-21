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

namespace Shopware\Tests\Mink\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

class Form extends Page implements HelperSelectorInterface
{
    /**
     * @var string
     */
    protected $path = 'shopware.php?sViewport=ticket&sFid={formId}';

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'captchaPlaceholder' => 'div.captcha--placeholder',
            'captchaImage' => 'div.captcha--placeholder img',
            'captchaHidden' => 'div.captcha--placeholder input',
            'inquiryForm' => 'form#support',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [
            'submitButton' => ['de' => 'Senden', 'en' => 'Send'],
        ];
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     *
     * @throws \Exception
     */
    public function verifyPage()
    {
        $errors = [];

        if (!$this->hasField('sCaptcha')) {
            $errors[] = '- captcha input field not found!';
        }

        if (!Helper::hasNamedButton($this, 'submitButton')) {
            $errors[] = '- submit button not found!';
        }

        if (!$errors) {
            return;
        }

        $message = ['You are not on a form page:'];
        $message = array_merge($message, $errors);
        $message[] = 'Current URL: ' . $this->getSession()->getCurrentUrl();
        Helper::throwException($message);
    }

    /**
     * Checks, whether a captcha exists and has loaded correctly
     *
     * @throws \Exception
     */
    public function checkCaptcha()
    {
        $placeholderSelector = Helper::getRequiredSelector($this, 'captchaPlaceholder');

        if (!$this->getSession()->wait(5000, "$('$placeholderSelector').children().length > 0")) {
            $message = 'The captcha was not loaded or does not exist!';
            Helper::throwException($message);
        }

        $element = Helper::findElements($this, ['captchaPlaceholder', 'captchaImage', 'captchaHidden']);

        $captchaPlaceholder = $element['captchaPlaceholder']->getAttribute('data-src');
        $captchaImage = $element['captchaImage']->getAttribute('src');
        $captchaHidden = $element['captchaHidden']->getValue();

        if ((strpos($captchaPlaceholder, '/widgets/Captcha/refreshCaptcha') === false)
            || (strpos($captchaImage, 'data:image/png;base64') === false)
            || (empty($captchaHidden))
        ) {
            $message = 'The captcha was not loaded correctly!';
            Helper::throwException($message);
        }
    }

    /**
     * Fills the fields of the inquiry form with $data and submits it
     *
     * @param array $data
     */
    public function submitInquiryForm(array $data)
    {
        Helper::fillForm($this, 'inquiryForm', $data);
        Helper::pressNamedButton($this, 'submitButton');
    }
}

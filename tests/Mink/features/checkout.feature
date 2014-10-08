@checkout
Feature: Checkout articles

    Background: I can login as a user with correct credentials
        Given I am on the page "Account"
        And   I log in successful as "Max Mustermann" with email "test@example.com" and password "shopware"

        When  I am on the detail page for article 167
        Then  I should see "Sonnenbrille Speed Eyes"

        When  I put the article "3" times into the basket
        Then  the cart should contain 1 articles with a value of "38,47 €"
        And   the total sum should be "42,37 €" when shipping costs are "3,90 €" and VAT is:
            | percent | value  |
            | 19 %    | 6,76 € |

    Scenario: I can put articles to the basket, check all prices and pay via C.O.D. service
        Given I add the article "SW10170" to my basket
        Then  the cart should contain 2 articles with a value of "78,42 €"
        And   the total sum should be "82,32 €" when shipping costs are "3,90 €" and VAT is:
            | percent | value   |
            | 19 %    | 13,14 € |

        When  I remove the article on position 1
        Then  the cart should contain 1 articles with a value of "37,95 €"
        And   the total sum should be "41,85 €" when shipping costs are "3,90 €" and VAT is:
            | percent | value  |
            | 19 %    | 6,68 € |

        When  I follow the link "checkout" of the page "CheckoutCart"
        And   I change the payment method to 3
        Then  I should see "Nachnahme"
        And   the total sum should be "41,85 €" when shipping costs are "3,90 €" and VAT is:
            | percent | value  |
            | 19 %    | 6,68 € |
        And   I should see "AGB und Widerrufsbelehrung"

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware 4 Demo!"

    @shipping @payment @noResponsive
    Scenario: I can change the shipping-country to a non-EU-country and back and pay via bill
        Given I follow the link "changeButton" of the element "CheckoutShipping"
        And   I submit the form "shippingForm" on page "Account" with:
            | field   | register[shipping] |
            | country | Schweiz            |

        Then  the total sum should be "53,02 €" when shipping costs are "21,00 €"
        And   I should not see "MwSt."

        Given I follow the link "changeButton" of the element "CheckoutShipping"
        And   I submit the form "shippingForm" on page "Account" with:
            | field   | register[shipping] |
            | country | Deutschland        |

        Then  the total sum should be "42,37 €" when shipping costs are "3,90 €" and VAT is:
            | percent | value  |
            | 19 %    | 6,76 € |

        Given I change the payment method to 4
        Then  I should see "Rechnung"
        And   I should see "Zuschlag für Zahlungsart"
        Then  the total sum should be "47,37 €" when shipping costs are "3,90 €" and VAT is:
            | percent | value  |
            | 19 %    | 7,56 € |
        And   I should see "AGB und Widerrufsbelehrung"

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware 4 Demo!"

    @shipping @payment @noEmotion
    Scenario: I can change the shipping-country to a non-EU-country and back and pay via bill
        Given I follow the link "checkout" of the page "CheckoutCart"
        And   I follow the link "changeButton" of the element "CheckoutShipping"
        And   I submit the form "shippingForm" on page "Account" with:
            | field   | register[shipping] |
            | country | Schweiz            |

        Then  the total sum should be "53,02 €" when shipping costs are "21,00 €"
        And   I should not see "MwSt."

        Given I follow the link "changeButton" of the element "CheckoutShipping"
        And   I submit the form "shippingForm" on page "Account" with:
            | field   | register[shipping] |
            | country | Deutschland        |

        Then  the total sum should be "42,37 €" when shipping costs are "3,90 €" and VAT is:
            | percent | value  |
            | 19 %    | 6,76 € |

        Given I change the payment method to 4
        Then  I should see "Rechnung"
        And   I should see "Zuschlag für Zahlungsart"
        Then  the total sum should be "47,37 €" when shipping costs are "3,90 €" and VAT is:
            | percent | value  |
            | 19 %    | 7,56 € |
        And   I should see "AGB und Widerrufsbelehrung"

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware 4 Demo!"

    @delivery @payment @noResponsive
    Scenario: I can change the delivery to Express and pay via debit
        Given I change the payment method to 2:
            | field             | value          |
            | sDebitAccount     | 123456789      |
            | sDebitBankcode    | 1234567        |
            | sDebitBankName    | Shopware Bank  |
            | sDebitBankHolder  | Max Mustermann |
        And   I change the shipping method to 14

        Then  I should see "Lastschrift"
        And   I should see "Abschlag für Zahlungsart"
        Then  the total sum should be "44,52 €" when shipping costs are "9,90 €" and VAT is:
            | percent | value  |
            | 19 %    | 7,10 € |
        And   I should see "AGB und Widerrufsbelehrung"

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware 4 Demo!"

    @delivery @payment @noEmotion
    Scenario: I can change the delivery to Express and pay via debit
        Given I follow the link "checkout" of the page "CheckoutCart"

        When  I change the payment method to 2:
            | field             | value          |
            | sDebitAccount     | 123456789      |
            | sDebitBankcode    | 1234567        |
            | sDebitBankName    | Shopware Bank  |
            | sDebitBankHolder  | Max Mustermann |
        And   I change the shipping method to 14

        Then  I should see "Lastschrift"
        And   I should see "Abschlag für Zahlungsart"
        Then  the total sum should be "44,52 €" when shipping costs are "9,90 €" and VAT is:
            | percent | value  |
            | 19 %    | 7,10 € |
        And   I should see "AGB und Widerrufsbelehrung"

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware 4 Demo!"

    @currency @payment @noResponsive
    Scenario: I can change the currency and pay via prepayment
        Given I press "USD"
        Then  the total sum should be "57,72 $" when shipping costs are "5,31 $" and VAT is:
            | percent | value  |
            | 19 %    | 9,21 $ |

        When  I press "EUR"
        Then  the total sum should be "42,37 €" when shipping costs are "3,90 €" and VAT is:
            | percent | value  |
            | 19 %    | 6,76 € |
        And   I should see "AGB und Widerrufsbelehrung"

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware 4 Demo!"

    @javascript @plugin @noResponsive
    Scenario: I can change the language and pay via PayPal
    //Given the "PayPal Payment" plugin is enabled
        When  I select "English" from "__shop"
        Then  I should see "Your shopping cart does not contain any products"

        When  I select "Deutsch" from "__shop"
        Then  I should see "Sonnenbrille Speed Eyes"

        When  I follow the link "checkout" of the page "CheckoutCart"
        Then  I should see "AGB und Widerrufsbelehrung"
        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware 4 Demo!"
        And   I log me out
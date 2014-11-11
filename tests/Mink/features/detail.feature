@detail
Feature: detail page

    @captchaInactive
    Scenario: I can see evaluations
        Given I am on the detail page for article 198

        Then  I should see "Kundenbewertungen für \"Artikel mit Bewertung\""
        And   I should see an average customer evaluation of 10 from following evaluations:
            | author        | stars | headline      | comment                                                                                                                                     | answer                                                                                                                                                      |
            | Bert Bewerter | 10    | Super Artikel | Dieser Artikel zeichnet sich durch extreme Stabilität aus und fasst super viele Klamotten. Das Preisleistungsverhältnis ist exorbitant gut. | Vielen Dank für die positive Bewertung. Wir legen bei der Auswahl unserer Artikel besonders Wert auf die Qualität, sowie das Preis - / Leistungsverhältnis. |
            | Pep Eroni     | 10    | Hervorragend  | bin sehr zufrieden...                                                                                                                       | Danke                                                                                                                                                       |

        When  I write an evaluation:
            | field        | value           |
            | sVoteName    | Max Mustermann  |
            | sVoteMail    | info@example.de |
            | sVoteStars   | 1 sehr schlecht |
            | sVoteSummary | Neue Bewertung  |
            | sVoteComment | Hallo Welt      |
            | sCaptcha     | 123456          |
        And  I click the link in my latest email
        And  the shop owner activates my latest evaluation

        Then  I should see an average customer evaluation of 7 from following evaluations:
            | stars |
            | 1     |
            | 10    |
            | 10    |


    @plugin @notification
    Scenario: I can let me notify, when an article is available
        Given I am on the detail page for article 243
        Then  I should see "Benachrichtigen Sie mich, wenn der Artikel lieferbar ist"

        When  I submit the form "notificationForm" on page "Detail" with:
            | field              | value           |
            | sNotificationEmail | test@example.de |
        Then  I should see "Bestätigen Sie den Link der eMail die Sie gerade erhalten haben. Sie erhalten dann eine eMail sobald der Artikel wieder verfügbar ist"

        When  I click the link in my latest email
        Then  I should see "Vielen Dank! Wir haben Ihre Anfrage gespeichert! Sie werden benachrichtigt sobald der Artikel wieder verfügbar ist."

    @language @javascript
    Scenario: I can change the language
        Given I am on the detail page for article 229
        Then  I should see "Magnete London"

        When  I select "English" from "__shop"
        Then  I should see "Magnets London"

        When  I select "Deutsch" from "__shop"
        Then  I should see "Magnete London"

    @captchaInactive
    Scenario: I can write an evaluation
        Given I am on the detail page for article 100
        Then  I should see "Bewertungen (0)"
        And   I should see "Bewertung schreiben"
        When  I write an evaluation:
            | field        | value           |
            | sVoteName    | Max Mustermann  |
            | sVoteMail    | info@example.de |
            | sVoteStars   | 3               |
            | sVoteSummary | Neue Bewertung  |
            | sVoteComment | Hallo Welt      |
            | sCaptcha     | 123456          |
        Then  I should not see "Bitte füllen Sie alle rot markierten Felder aus"
        But   I should see "Vielen Dank für die Abgabe Ihrer Bewertung! Sie erhalten in wenigen Minuten eine Bestätigungsmail. Bestätigen Sie den Link in dieser E-Mail um die Bewertung freizugeben."
        But   I should not see "Hallo Welt"

        When  I click the link in my latest email
        Then  I should see "Vielen Dank für die Abgabe Ihrer Bewertung! Ihre Bewertung wird nach Überprüfung freigeschaltet."
        But   I should not see "Hallo Welt"

        When  the shop owner activates my latest evaluation
        Then  I should see an average customer evaluation of 3 from following evaluations:
            | author         | stars | headline       | comment    |
            | Max Mustermann | 3     | Neue Bewertung | Hallo Welt |

    @graduatedPrices
    Scenario Outline: An article can have graduated prices
        Given I am on the detail page for article 209
        Then  I should see "<grade> <itemPrice>"

        When  I put the article "<quantity>" times into the basket
        Then  the element "CartPosition" should have the content:
            | position  | content       |
            | name      | Staffelpreise |
            | number    | SW10208       |
            | quantity  | <quantity>    |
            | itemPrice | <itemPrice>   |
            | sum       | <sum>         |

    Examples:
        | grade  | itemPrice | quantity | sum   |
        | bis 10 | 1,00      | 10       | 10,00 |
        | ab 11  | 0,90      | 20       | 18,00 |
        | ab 21  | 0,80      | 30       | 24,00 |
        | ab 31  | 0,75      | 40       | 30,00 |
        | ab 41  | 0,70      | 50       | 35,00 |

    @minimumQuantity @maximumQuantity @graduation
    Scenario: An article can have a minimum/maximum quantity with graduation
        Given I am on the detail page for article 207
        Then  I can select every 3. option of "sQuantity" from "3 Stück" to "30 Stück"

        When  I press "In den Warenkorb"
        Then  I can select every 3. option of "sQuantity" from "3" to "30"
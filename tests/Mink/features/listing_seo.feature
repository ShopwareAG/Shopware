@listing @seo @basicSettings
Feature: Listing Seo BasicSettings

    @browsing
    Scenario: I see canonical and index in emotion world page for category with no pagination SEO
      Given basic settings element "seoIndexPaginationLinks" has value 0
      And I am on the listing page for category 5

      Then I should see canonical link "genusswelten"
      And I should not see pagination metas
      And I should robots metas "index" and "follow"

    @browsing
    Scenario: I see canonical and noindex in listing page 1 for category with no pagination SEO
      Given basic settings element "seoIndexPaginationLinks" has value 0
      And I am on the listing page for category 5 on page 1

      Then I should see canonical link "genusswelten"
      And I should not see pagination metas
      And I should robots metas "noindex" and "follow"


    @browsing
    Scenario: I see canonical and index in emotion world page for category with pagination SEO
      Given basic settings element "seoIndexPaginationLinks" has value 1
      And I am on the listing page for category 5

      Then I should see canonical link "genusswelten"
      And I should not see pagination metas
      And I should robots metas "index" and "follow"

    @browsing
    Scenario: I see canonical, index and next in listing page 1 for category with pagination SEO
      Given basic settings element "seoIndexPaginationLinks" has value 1
      And I am on the listing page for category 5 on page 1

      Then I should see canonical link "genusswelten" and page 1
      And I should see next page meta with link "genusswelten" and page 2
      And I should not see prev page meta
      And I should robots metas "index" and "follow"

    @browsing
    Scenario: I see canonical, index, prev and next in listing page 2 for category with pagination SEO
      Given basic settings element "seoIndexPaginationLinks" has value 1
      And I am on the listing page for category 5 on page 2

      Then I should see canonical link "genusswelten" and page 2
      And I should see prev page meta with link "genusswelten" and page 1
      And I should see next page meta with link "genusswelten" and page 3
      And I should robots metas "index" and "follow"

    @browsing
    Scenario: I see canonical, index and prev in last category page with pagination SEO
      Given basic settings element "seoIndexPaginationLinks" has value 1
      And I am on the listing page for category 5 on page 4

      Then I should see canonical link "genusswelten" and page 4
      And I should see prev page meta with link "genusswelten" and page 3
      And I should not see next page meta
      And I should robots metas "index" and "follow"

    @browsing
    Scenario: I see canonical, index and prev in last category page with pagination SEO
      Given basic settings element "seoIndexPaginationLinks" has value 1
      And I am on the listing page for category 11

      Then I should see canonical link "genusswelten/tees-und-zubehoer" and page 1
      And I should see next page meta with link "genusswelten/tees-und-zubehoer" and page 2
      And I should not see prev page meta
      And I should robots metas "index" and "follow"

Feature: Send dump data
  In order to use phpscaffold
  I need to send good SQL data through the form

  Scenario: send valid data
    Given I am on the home page
    And I fill the form
    When I click on button Make pages
    Then I should see the login page

  Scenario: send invalid data
    Given I am on the home page
    When I click on button Make pages
    Then I should see SQL error message

Given /I am on the home page/ do
  visit 'http://localhost/Projects/phpscaffold/'
end

Given /I fill the form/ do
  fill_in 'sql', :with => 'CREATE TABLE `users_test` (
  `id` int(10) NOT NULL auto_increment,
  `email` varchar(100) NOT NULL,
  `pass` varchar(32) NOT NULL,
  `curriculum` text NOT NULL,
  `is_admin` int(1) NOT NULL,
  `last_login` datetime NOT NULL,
  `created` date NOT NULL,
  PRIMARY KEY (`id`)
);'
end

When /I click on button (.*)/ do |button|
  click_button button
end

Then /I should see the login page/ do
  response_body.should contain("Created projects:")
end

Then /I should see SQL error message/ do
  response_body.should contain("Couldn't find CREATE TABLE syntax!")
end

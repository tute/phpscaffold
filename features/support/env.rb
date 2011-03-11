# As described on https://github.com/aslakhellesoy/cucumber/wiki/PHP
require 'rspec/expectations'
require 'webrat'

require 'test/unit/assertions'
World(Test::Unit::Assertions)

Webrat.configure do |config|
  config.mode = :mechanize
end

World do
  session = Webrat::Session.new
  session.extend(Webrat::Methods)
  session.extend(Webrat::Matchers)
  session
end

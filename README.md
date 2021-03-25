## MySurveys Backend

MySurveys backend is a complementary repository to MySurveys frontend

# Installation

Clone the repository
run 'composer install'
configure the .env file and database
set the default password in the .env file

run 'php artisan migrate --seed'

# Testing

~PL +DOCS

./vendor/bin/phpunit --group=001

The project's core functions are covered by tests:
//!--LoginAdmin 002

--Register,RegisterWithQualificationForm 001
--invite researchers 003

--configure project - group 3
--create mock users - group 4
--create project participant selection - group 5
--send project participant invitations - group 6
--halt project after adequate survey completions - group 7
--run payments - group 8

# Full documentation

https://docs.google.com/document/d/1IhZD4RmyarY-Kn3iSRXIRboweJbqwUJOCRHqaf_P_bM/edit?usp=sharing

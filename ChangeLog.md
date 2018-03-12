# Changelog

v1.4
----
- Added field "identifier" to be able to display public profile [BC-Break] (12/03/2018)
- Added publicProfile (12/03/2018)

v1.3.5
------
- Added 'label.dashboard' missing translations (09/03/2018)
- Renamed register to signup (09/03/2018)
- Added 'label.signup' missing translation for Sign up submit value (09/03/2018)
- Suppressed cookies link in register acceptance (09/03/2018)
- Added clean Challenge in session when connecting to avoid errors if someone goes to register, cnacel and the sign in (09/03/2018)

v1.3.4
------
- Added "_locale requirement" part for multilingual prefix in `routing.yml` in `README.md` (04/03/2018)
- Added rel="nofollow" for the Signin/Signout link (09/03/2018)

v1.3.3
------
- Added template to display sign in/sign out link (03/03/2018)
- Modified text from link to button in emails sent (03/03/2018)

v1.3.2.1
--------
- Removed the "|raw" for `toolbar_button` call as safe html is now sent (01/03/2018)

v1.3.2
------
- Moved "Forget password" link below submit button on signin form (28/02/2018)

v1.3.1
------
- Added c957L/IncludeLibrary to `composer.json` (27/02/2018)

v1.3
----
- Added c957L/IncludeLibrary to include libraries in `layout.html.twig` (27/02/2018)

v1.2.2.1
--------
- Corrected @Index mention in User entity (27/02/2018)

v1.2.2
------
- Added strotlower to `email` field (26/02/2018)
- Added `un_email` index on table `user` (26/02/2018)

v1.2.1.1
--------
- Suppressed echo forgotten... (22/02/2018)

v1.2.1
------
- Replaced links in `register` and `resetPassword` emails by a button (22/02/2018)

v1.2
----
- Corrected sizes in User Entity to be coherent with what defined in user.sql (22/02/2018)
- Suppressed email layout to use those defined in c975L\EmailBundle, to have only one place for user to override them (22/02/2018)
- Abandoned Glyphicon and replaced by fontawesome (22/02/2018)
- Added help pages (22/02/2018)
- Added `roleNeeded` config value to enable access to other user's data (22/02/2018)

v1.1.1
------
- Removed translations not used (21/02/2018)
- Removed Twig fragments not used (21/02/2018)
- Redirected already signed in user who tries to register (21/02/2018)

v1.1
----
- Push first set of files (21/02/2018)

v1.0
----
- Initial commit (19/02/2018)
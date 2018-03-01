# Changelog

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
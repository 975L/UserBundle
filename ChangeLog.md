# Changelog

v1.10.4
-------
Use of `EntityManagerInterface` (22/05/2018)

v1.10.3
-------
- Modified gender field to varchar(24) (15/05/2018)
- Removed required in composer.json (22/05/2018)

v1.10.2
-------
- Modified toolbars calls due to modification of c975LToolbarBundle (13/05/2018)
- Modified help (13/05/2018)

v1.10.1
-------
- Added information in `README.md` (03/05/2018)

v1.10
-----
- Added phone and fax fields for Address and Business (03/05/2018)
- Added use of Misd\PhoneNumberBundle to check phone numbers (03/05/2018)
- Added sql code for creation of archive table (03/05/2018)
- Added key un_identifier (03/05/2018)

v1.9.1
------
- Added info in `README.md` about `publicProfile` config value (02/05/2018)
- Added possibilty to provide user to Twig user_avatar() function (02/05/2018)

v1.9
----
- Replaced submit button by `SubmitType` in some Forms Types (16/04/2018)
- Corrected `UserChangePasswordType.php` (16/04/2018)
- Corrected `validators.en.xlf` (16/04/2018)
- Corrected Route `user_reset_password_confirm` (16/04/2018)
- Corrected Repository calls to call the defined Entity in config.yml (16/04/2018)
- Moved to `AdvancedUserInterface` instead of `UserInterface` to display message for disabled accounts (16/04/2018)

v1.8.1
------
- Added `UserSiret` Twig extension to display formatted Siret number (05/04/2018)
- Added `UserVat` Twig extension to display formatted VAT number (05/04/2018)

v1.8
----
- Added cancel link below submit button in dlete form, even if already present in toolbar, it's more stress-less for user (03/04/2018)
- Added possibility to define number of signin attempts and disbale sign in button for a delay (04/04/2018)
- Removed `createNotFoundException()` for Route `user_reset_password` to un-allow checking which emails are registered (04/04/2018)

v1.7.5
------
- Corrected missing gravatar size in `OAuthUserProvider` (03/04/2018)
- Added mention in `README.md` to add `c975L\UserBundle\Security\OAuthUserProvider` in `services.yml` (03/04/2018)
- Replaced the warning in delete account by a madatory checkbox (03/04/2018)

v1.7.4
------
- Suppressed autowire of Security as it can't find the class `OAuthAwareUserProviderInterface` when HWIOAuth is not used (03/04/2018)

v1.7.3
------
- Made creation of table `user_archives` by default (02/04/2018)

v1.7.2
------
- Added fill in for field `identifier` in `MigrationFosUser.sql` and made field as `NOT NULL` (02/04/2018)
- Simplified `MigrationFosUser.sql` (02/04/2018)
- Changed sql code to create user_archives, to simplfiy it (02/04/2018)

v1.7.1
------
- Changed throw `createNotFoundException` to redirect to `user_signin` for some Controller methods, which is more ergonomic (02/04/2018)

v1.7
----
- Moved mandatory field mention in signin form (29/03/2018)
- Added `_target_path_` form field in signin form (29/03/2018)
- Added info about requesting signin form and custom redirect after (29/03/2018)
- Changed `registration` config value to `signup` to be coherent with the naming in the bundle [BC-Break] (01/04/2018)
- Made signup config value false as default (01/04/2018)
- Updated `README.md` (01/04/2018)
- Added Service methods to retrieve user (01/04/2018)
- Modified `UserController` methods to throw `createNotFoundException()` or `createAccessDeniedException()` depending if the user has been found or not (01/04/2018)
- Corrected `UserAbstract` for address fields (01/04/2018)
- Corrected `Service` to use the Entity defined in config.yml (01/04/2018)
- Added event `USER_SIGNEDUP` to allow interact with its id (01/04/2018)
- Added authentication via social networks using HWIOAuthBundle ! :-) (01/04/2018)
- Renamed config value `gravatar` to `avatar` [BC-Break] and made it false as default (02/04/2018)
- Renamed Twig extension `UserGravatar` to `UserAvatar` (02/04/2018)
- Modified behaviour of Twig Extension `UserAVatar` to return the avatar (social network or Gravatar) if enabled (02/04/2018)
- Made default size of gravatar stored in database to 512 in place of 128 (02/04/2018)

v1.6.1
------
- Set `Resources/views/fragments/avatar.html.twig` to allow override display of avatar (26/03/2018)

v1.6
----
- Corrected locale field to varchar(2) in sql file (22/03/2018)
- Removed switch for `setLocale()` in `UserAbstract()` (22/03/2018)
- Updated `README.md` (22/03/2018)
- Added signin option in `Resources/views/fragments/navBarMenu.html.twig` when user has not signed in (23/03/2018)
- Simpified signup redirect Routes (23/03/2018)

v1.5.1
------
- Added `findUserByIdentifier()` method to `UserService` (21/03/2018)
- Remove `NotFound` from Controller `user_signup_confirm()` and replaced by redirect to signin (+ flash), in case user click more than once on the provided link (22/03/2018)

v1.5
----
- Added `Entity/UserAbstract.php` to allow extending `User` entity (15/03/2018)
- Changed `Entity/User` to only extend `Entity/UserAbstract.php` (15/03/2018)
- Replace if `$user instanceof User` by `is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')` as they won't pass if the Entity is extended (15/03/2018)
- Updated `README.md` about extending entities (15/03/2018)
- Added Siret and Tva validators (16/03/2018)
- Added possibility to extend `UserProfileType` and `UserRegisterType` (16/03/2018)
- Added multiples entities classes + abstract to cover differents types of usage (17/03/2018)
- Removed `action` property form User entity and passed it in the form options array (17/03/2018)
- Removed lastname from signup as it's not required and can be set on the modify form (17/03/2018)
- Added Twig extension `UserDivData` to populate user's informations to be used by javascript (18/03/2018)
- Added `UserService` (18/03/2018)
- Renamed folder `Listeners` to `Listener` (18/03/2018)
- Added Events (18/03/2018)
- Added `findUserById` service (18/03/2018)
- Re-ordered `README.md` (18/03/2018)
- Replaced `findByXxx` repository methods by `findOneByXxx` Doctrine's ones (19/03/2018)
- Corrected `layout.html.twig` (19/03/2018)
- Added switch to user preferred language in `user_dashboard` Route if multilingual support is enabled (21/03/2018)
- Made the acceptance Signup as a checkbox [BC-Break] (21/03/2018)

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
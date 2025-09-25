# 3.3.7

* ECP-1081 Remove typed class constants. 
* ECP-1082 Remove misplaced Style.php.
* PD-444 Fix PPW Javascript parsing of large numbers.

# 3.3.6

* ECP-1061 Initial RWS implementation.
* ECP-1063 Use nullsafe operator to prevent QA warning.
* ECP-1066 Removed deprecated widget code.
* ECP-1062 related translations missing for WOO-1471.
* ECP-1072 Loader widget, to display a generic loader overlay and animation on top of any GUI component.
* ECP-1070 Add content caching to all widgets but SupportInfo, CallbackList and PaymentInformation.
* ECP-1069 Add cacheDynamicData config option.
* ECP-1071 Rename cacheDynamicData to cacheWidgets.
* ECP-1075 Add caching of JWT token.

# 3.3.5

* ECP-1029 Improve error message display for PPW
* ECP-1060 Make payment history logging after callback processing conditional

# 3.3.4

* ECP-1058 Change default network timeout from 0 to 30 seconds.
* ECP-1054 Corrected composer.json PHP requirement.

# 3.3.3

* ECP-1040 Added widget to reload payment information widget on demand. Written generically in order to replace parts of the part payment widget JS.
* ECP-1044 PPW button click event listener. 

# 3.3.2

* ECP-1037 Add IS\_READY\_FOR\_AUTHORIZATION Payment History event.
* ECP-1026 Modify handling of after shop payment history logging to add sums even when there are no order lines provided. 
* ECP-1013 Resolver changed to IPv4 only due to issues with MAPI (INC0087967).

# 3.3.1

* Update Payment::isCancelled condition.
* Change 'fånga' to 'debitera' in the translation file.

# 3.3.0

* Fixes for PrestaShop integration.
* Minor fixes for collections.
* Added new translation phrases.
* Added support for Credit Application callbacks (to support manual inspection).
* Added missing tests.
* Fixed data type issue in part payment config widget.
* Re-structured widget rendering method.
* Added JS / CSS rendering base controller classes.

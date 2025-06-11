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

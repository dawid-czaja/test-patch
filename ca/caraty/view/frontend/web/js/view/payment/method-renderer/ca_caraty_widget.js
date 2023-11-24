/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
    ],
    function (Component) {
        'use strict';
        if (window.checkoutConfig.ca_raty.isCartValid === false) {
            return Component.extend({});
        }

        return Component.extend({
            defaults: {
                template: 'CA_CARaty/payment/form'
            },
            email: window.checkoutConfig.ca_raty.email ? window.checkoutConfig.ca_raty.email : document.querySelector('#customer-email').value,
            orderNumber: window.checkoutConfig.ca_raty.orderNumber,
            formUrl: window.checkoutConfig.ca_raty.formUrl,
        });
    }
);

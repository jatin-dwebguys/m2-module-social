define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function($, Component, customerData) {
    var s = {
        defaults: {
            methods: [],
            template: 'LoganStellway_Social/login'
        }
    };

    s.url = function(method, key) {
        if (typeof method[key] !== 'undefined') {
            return method[key] + '?redirect_url=' + encodeURIComponent(window.checkout.checkoutUrl || window.checkoutConfig.checkoutUrl || '')
        }
        return false;
    };

    s.login = function(method, event) {
        if (s.url(method, 'login')) {
            customerData.invalidate(['customer']);
            window.location.href = s.url(method, 'login');
        }
    };

    s.register = function(method, event) {
        if (s.url(method, 'register')) {
            customerData.invalidate(['customer']);
            window.location.href = s.url(method, 'register');
        }
    };

    return Component.extend(s);
});

var PureClarity = {
    config: null,
    init: function() {
        this.config = window.pureclarityConfig;
        if (!this.config)
            setTimeout(this.init.bind(this), 1000);
        else
            this.process();
    },
    process: function() {

        if (!this.config.enabled) return;

        (function (w, d, s, u, f) {
            w['PureClarityObject'] = f;w[f] = w[f] || function () { 
                (w[f].q = w[f].q || []).push(arguments)
            }
            var p = d.createElement(s), h = d.getElementsByTagName(s)[0];
            p.src = u;p.async=1;h.parentNode.insertBefore(p, h);
        })(window, document, 'script', this.config.tracking.apiUrl, '_pc');
        _pc('page_view',this.config.page_view);
        
        if (this.config.product){
            _pc('product_view', { id: this.config.product.id });
        }
        
        if (this.config.tracking.customer) {
            var userCookieId = this.getCookie("pc_user_id");
            if (userCookieId != this.config.tracking.customer.id){
                this.setCookie("pc_user_id", this.config.tracking.customer.id);
                _pc('customer_details', this.config.tracking.customer.data);
            }
        }
        else if (this.config.tracking.islogout) {
            _pc('customer_logout');
        }

        var orderElement = document.getElementById('pc_order_info');
        if(this.config.tracking.order) {
            _pc('order', this.config.tracking.order);
        } else if(orderElement) {
            _pc('order', JSON.parse(orderElement.value));
        }

        if(this.config.tracking.cart) {
            var cartCookieId = this.getCookie("pc_cart_id");
            if (cartCookieId != this.config.tracking.cart.id){
                this.setCookie("pc_cart_id", this.config.tracking.cart.id);
                _pc("set_basket", this.config.tracking.cart.items);
            }
        }
    },
    getCookie: function(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1);
            if (c.indexOf(name) != -1) return c.substring(name.length, c.length);
        }
        return "";
    },
    setCookie: function(cname, cvalue, exdays = 0, exmins = 0) {
        var expires = "";
        if (exdays > 0 || exmins > 0) {
            var d = new Date();
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000) + (exmins * 60 * 1000));
            expires = "expires=" + d.toUTCString();
        }
        document.cookie = cname + "=" + cvalue + "; " + expires + "; path=/";
    }
}
PureClarity.init();
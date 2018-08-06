var PureClarity = {
    blah: "sdf",
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

        if (this.config.autocomplete.enabled){
            $searchFields = jQuery(this.config.autocomplete.searchSelector);
            if ($searchFields){
                $searchFields.attr("id", "pc_search");
            }
        }

        if (this.config.search.do){
            
            var pcContainer = document.createElement('div');
            var wrapper = document.createElement('div');
            jQuery(wrapper).addClass('pureclarity-wrapper');
            jQuery(pcContainer).addClass('pureclarity-container').attr("data-pureclarity", "navigation_search");
            jQuery(this.config.search.domSelector).wrap(wrapper).hide();
            jQuery(".pureclarity-wrapper").append(pcContainer);
            jQuery(wrapper).addClass('site-main')
            if (this.config.search.bmz1) {
                jQuery(".pureclarity-wrapper").prepend(this.config.search.bmz1);
            }
            if (this.config.search.bmz2) {
                jQuery(".pureclarity-wrapper").append(this.config.search.bmz2);
            }
        }

        if (this.config.prodlist.do){
            var pcContainer = document.createElement('div');
            var wrapper = document.createElement('div');
            jQuery(wrapper).addClass('pureclarity-wrapper');
            jQuery(pcContainer).addClass('pureclarity-container').attr("data-pureclarity", "navigation_category:" + this.config.categoryId);
            jQuery(this.config.prodlist.domSelector).wrap(wrapper).hide();
            jQuery(".pureclarity-wrapper").append(pcContainer);
            jQuery(wrapper).addClass('site-main')
            if (this.config.prodlist.bmz1) {
                jQuery(".pureclarity-wrapper").prepend(this.config.prodlist.bmz1);
            }
            if (this.config.prodlist.bmz2) {
                jQuery(".pureclarity-wrapper").append(this.config.prodlist.bmz2);
            }
        }

        (function (w, d, s, u, f) {
            w['PureClarityObject'] = f;w[f] = w[f] || function () { 
                (w[f].q = w[f].q || []).push(arguments)
            }
            var p = d.createElement(s), h = d.getElementsByTagName(s)[0];
            p.src = u;p.async=1;h.parentNode.insertBefore(p, h);
        })(window, document, 'script', this.config.tracking.apiUrl, '_pc');
        _pc('page_view');
        
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

        if(this.config.tracking.order) {
            _pc('order:addTrans', this.config.tracking.order.transaction);
            for (var i=0; i<this.config.tracking.order.items.length; i++) {
                _pc('order:addItem', this.config.tracking.order.items[i]);
            }
            _pc('order:track');
        }

        if(this.config.tracking.cart) {
            var cartCookieId = this.getCookie("pc_cart_id");
            if (cartCookieId != this.config.tracking.cart.id){
                this.setCookie("pc_cart_id", this.config.tracking.cart.id);
                if (this.config.tracking.cart.items.length == 0){
                    _pc("set_basket", {cart_empty: true});
                }
                else {
                    _pc("set_basket", this.config.tracking.cart.items);
                }
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
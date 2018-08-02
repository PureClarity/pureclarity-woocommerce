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
            _pc('customer_details', this.config.tracking.customer);
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
    }
}
PureClarity.init();
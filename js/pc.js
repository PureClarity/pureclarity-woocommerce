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

        if (this.config.searchEnabled){
            $searchFields = jQuery(this.config.searchSelector);
            if ($searchFields){
                $searchFields.attr("id", "pc_search");
            }
            if (this.config.isSearch){
                console.log("Search Page");
                var pcContainer = document.createElement('div');
                var wrapper = document.createElement('div');
                jQuery(wrapper).addClass('pureclarity-wrapper');
                jQuery(pcContainer).addClass('pureclarity-container').attr("data-pureclarity", "navigation_search");
                jQuery(pureclarityConfig.searchResultsDOMSelector).wrap(wrapper).hide();
                jQuery(".pureclarity-wrapper").append(pcContainer);
                jQuery(wrapper).addClass('site-main')
            }
        }

        if (this.config.prodListEnabled){
            if (this.config.isCategory){
                console.log("Is Category!");
            }
        }

        (function (w, d, s, u, f) {
            w['PureClarityObject'] = f;w[f] = w[f] || function () { 
                (w[f].q = w[f].q || []).push(arguments)
            }
            var p = d.createElement(s), h = d.getElementsByTagName(s)[0];
            p.src = u;p.async=1;h.parentNode.insertBefore(p, h);
        })(window, document, 'script', this.config.apiUrl, '_pc');
        _pc('page_view');
        
        if (this.config.product){
            console.log("PureClarity: product_view:", this.config.product.sku, this.config.product.id);
            _pc('product_view', { id: this.config.product.id });
        }
    }
}
PureClarity.init();
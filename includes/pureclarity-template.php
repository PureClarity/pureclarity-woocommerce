<?php

class PureClarity_Template {

	private $bmz;
	private $pureClarityPlugin;
	private $pureClarityPluginSettings;

	public function __construct( &$plugin ) {
		$this->pureClarityPlugin = $plugin;
		$this->bmz               = $this->pureClarityPlugin->get_bmz();
		if ( ! is_ajax() ) {
			add_filter(
				'wp_head',
				array(
					$this,
					'render_pureclarity_json',
				)
			);
		}
	}

	public function render_pureclarity_json() {
		$style = '';
		if ( $this->is_pureclarity_active()
			&& (
				( is_search() && $this->get_pureclarity_plugin_settings()->is_search_enabled() )
				|| ( is_product_category() && $this->get_pureclarity_plugin_settings()->is_prod_enabled() )
				|| ( is_shop() && $this->get_pureclarity_plugin_settings()->is_shop_enabled_admin() )
			)
		) {
			$style = "<style type='text/css'>" . $this->get_pureclarity_plugin_settings()->get_search_result_element() . ' {display:none}</style>';
		}

		$script = '<script type="text/javascript">window.pureclarityConfig = ' . wp_json_encode( $this->getConfig() ) . ';</script>';

		echo $style . $script;
	}

	private function getConfig() {
		$pureclarity_settings = $this->get_pureclarity_plugin_settings();
		$pureclarity_session  = $this->get_pureclarity_plugin()->get_state();
		$pl01                 = $this->get_bmz( 'PL-01' );
		$pl02                 = $this->get_bmz( 'PL-02' );
		return array(
			'enabled'      => $this->is_pureclarity_active(),
			'product'      => $pureclarity_session->get_product(),
			'categoryId'   => ( is_shop() ? '*' : $pureclarity_session->get_category_id() ),
			'autocomplete' => array(
				'enabled'        => $this->get_pureclarity_plugin_settings()->is_search_enabled(),
				'searchSelector' => $pureclarity_settings->get_search_selector(),
			),
			'search'       => array(
				'do'   => $this->get_pureclarity_plugin_settings()->is_search_enabled() && is_search(),
				'bmz1' => $this->get_bmz( 'SR-01' ),
				'bmz2' => $this->get_bmz( 'SR-02' ),
			),
			'merch'        => array(
				'enabled' => $this->get_pureclarity_plugin_settings()->is_merch_enabled(),
			),
			'prodlist'     => array(
				'do'   => $this->get_pureclarity_plugin_settings()->is_prod_enabled() && is_product_category(),
				'bmz1' => $pl01,
				'bmz2' => $pl02,
			),
			'shop'         => array(
				'do'           => $this->get_pureclarity_plugin_settings()->is_shop_enabled_admin() && is_shop() && ! is_search(),
				'domSelector'  => $this->get_pureclarity_plugin_settings()->get_shop_selector(),
				'bmz1'         => $pl01,
				'bmz2'         => $pl02,
				'shopSelector' => $pureclarity_settings->get_shop_selector(),
			),
			'tracking'     => array(
				'accessKey' => $pureclarity_settings->get_access_key(),
				'apiUrl'    => $pureclarity_settings->get_api_url(),
				'customer'  => $pureclarity_session->get_customer(),
				'islogout'  => $pureclarity_session->is_logout(),
				'order'     => $pureclarity_session->get_order(),
				'cart'      => $pureclarity_session->get_cart(),
			),
		);
	}

	private function is_pureclarity_active() {
		return (
				$this->get_pureclarity_plugin_settings()->is_search_enabled()
				|| $this->get_pureclarity_plugin_settings()->is_merch_enabled()
				|| $this->get_pureclarity_plugin_settings()->is_prod_enabled()
			)
			&& ( $this->get_pureclarity_plugin_settings()->get_access_key() != '' )
			&& $this->get_pureclarity_plugin_settings()->is_pureclarity_enabled();
	}

	private function get_pureclarity_plugin() {
		return $this->pureClarityPlugin;
	}

	private function get_pureclarity_plugin_settings() {
		if ( ! isset( $this->pureClarityPluginSettings ) ) {
			$this->pureClarityPluginSettings = $this->get_pureclarity_plugin()->get_settings();
		}
		return $this->pureClarityPluginSettings;
	}

	private function get_bmz( $bmz_id ) {
		$acronym = substr( $bmz_id, 0, 2 );
		$index   = (int) substr( $bmz_id, 4, 5 );
		if ( $index > 2
			|| ( $acronym == 'SR' && ! $this->get_pureclarity_plugin_settings()->is_bmz_on_search_page() )
			|| ( $acronym == 'PL' && ! $this->get_pureclarity_plugin_settings()->is_bmz_on_category_page() )
		) {
			return null;
		}

		$bmzSettings = array(
			'id' => $bmz_id,
		);
		switch ( $index ) {
			case 1:
				$bmzSettings['bottom'] = '10';
				break;
			case 2:
				$bmzSettings['top'] = '10';
				break;
			default:
				return null;
		}

		return $this->bmz->pureclarity_render_bmz( $bmzSettings );
	}

}

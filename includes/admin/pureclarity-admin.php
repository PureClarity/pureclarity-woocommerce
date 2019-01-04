<?php

class PureClarity_Admin
{
    private $settings_slug = 'pureclarity-settings';
    private $settings_section = 'pureclarity_section_settings';
    private $datafeed_slug = 'pureclarity-datafeed';
    private $datafeed_section = 'pureclarity_section_datafeed';
    private $advanced_section = 'pureclarity_section_advanced';
    private $advanced_slug = 'pureclarity-advanced';
    private $settings_option_group = 'pureclarity_settings';
    private $datafeed_option_group = 'pureclarity_datafeed';
    private $advanced_option_group = 'pureclarity_advanced';
    private $plugin;
    private $settings;
    private $feed;

    public function __construct( &$plugin ) {

        $this->plugin = $plugin;
        $this->settings = $this->plugin->get_settings();
        $this->feed = $this->plugin->get_feed();

        add_action( 'admin_notices', array( $this, 'display_dependency_notices' ) );
        add_action( 'admin_menu', array( $this, 'add_menus' ) );
        add_action( 'admin_init', array( $this, 'add_settings' ) );
        wp_register_script( 'pureclarity-adminjs', plugin_dir_url( __FILE__ ) . 'js/pc-admin.js', array( 'jquery' ), PURECLARITY_VERSION );
        wp_enqueue_script(  'pureclarity-adminjs' );
        add_action( 'wp_ajax_pureclarity_run_datafeed', array( $this, 'run_data_feed' ) );
    }


    public function run_data_feed() {
        try {
            session_write_close();
            if ( ! isset( $_POST['page'] ) ) {
				throw new RuntimeException( 'Page has not been set.' );
			}
            $currentPage = (int) $_POST['page'];
            
            if ( ! isset( $_POST['type'] ) ) {
				throw new RuntimeException( 'Type has not been set.' );
			}
            $type = $_POST['type'];
            
            if ($type !== "product" && $type !== "category" && $type !== "brand" && $type !== "user" && $type !== "order") {
				throw new RuntimeException( 'Unknown type.' );
            }
            
            $totalPagesCount = $this->feed->get_total_pages($type);

            if ( $currentPage ===1 ) {
                $this->feed->start_feed( $type );
            }

            if ( $currentPage <= $totalPagesCount || $totalPagesCount === 0 ) {
                $data = $this->feed->build_items( $type, $currentPage );
                $this->feed->send_data( $type, $data );
            }

            $finished = $currentPage >= $totalPagesCount;

            if ($finished && $totalPagesCount > 0) {
                $this->feed->end_feed( $type );
                $this->update_successfeed( $type );
            }

            $response = array(
                'totalPagesCount' => $totalPagesCount,
                'finished'        => $finished,
            );

            wp_send_json( $response );

        } catch ( \Exception $exception ) {
            error_log("PureClarity: An error occurred generating the {$type} feed: " . $exception->getMessage() );
            wp_send_json( array( "error" => "An error occurred generating the {$type} feed. See error logs for more information.") );
        }
    }

    public function update_successfeed( $type ) {
        switch($type) {
            case "product":
                $this->settings->update_prodfeed_run();
            break;
            case "category":
                $this->settings->update_catfeed_run();
            break;
            case "brand":
                $this->settings->update_brandfeed_run();
            break;
            case "user":
                $this->settings->update_userfeed_run();
            break;
            case "order":
                $this->settings->update_orderfeed_run();
            break;
        }
    }

    public function add_menus() {
        add_menu_page(
            'PureClarity',
            'PureClarity',
            'manage_options',
            $this->settings_slug,
            array( $this, 'settings_render' ),
            'data:image/svg+xml;base64,' . $this->pureclarity_svg()
        );

        $settings = __('Settings', 'pureclarity');
        add_submenu_page(
            $this->settings_slug,
            "PureClarity: {$settings}",
            $settings,
            "manage_options",
            $this->settings_slug,
            array( $this, 'settings_render' )
        );

        add_submenu_page(
            $this->settings_slug,
            'PureClarity: ' . __('Data Feed Management', 'pureclarity'),
            __('Data Feeds', 'pureclarity'),
            "manage_options",
            $this->datafeed_slug,
            array( $this, 'datafeed_render' )
        );
    
        add_submenu_page(
            $this->settings_slug,
            'PureClarity: ' . __('Advanced Settings', 'pureclarity'),
            __('Advanced', 'pureclarity'),
            "manage_options",
            $this->advanced_slug,
            array( $this, 'advanced_render' )
        );
    }

    public function pureclarity_svg(){
        return 'iVBORw0KGgoAAAANSUhEUgAAAH4AAAB8CAYAAACv6wSDAAASs0lEQVR4nO1dCZAdVRU9fzLJTLbJZJnEJCbBmKRAdqVAXEAUQZEq3FC0kHKXRS3FlcUN2QqlSkQE0SpLI7iAFIVgFMSlIFiCRRYxGAxJyL7OJCFhQjKZsU5z7/jS73f//7tf93szmVP1a2a6//zf3efd9d13X2XBggUYQGgDcDyAEwEcCWAGgMkAKgC2AVgL4GkAjwN4EkBnlls7cOAAtm7dir6+PuvcYEHzALgPkvpWAOfKz1nWO6pjA4A/AfiF/By8LGZAU+DX9y4AjwL4I4CPN0A6MQ3ABQAeBPAIgHdb7ziEESrxxwB4AMA9AF5nnW0crwfwWwALxFQc8giR+EsBLARwlnUmP94mGuSrA0DbFYqQbn6SSOWNAMZYZ91hFIDrANwPYF7ZNxkKQiH+aAB/LdkOv12k/x3WmUMAIRB/qnjdR1pnikcHgHsBfMLDd3uFb+Jpc38nsbgvMKS9HcCX/D6KcuGT+DPFpo+1zvjBDQAuC+RaCocv4k8BcLc4WiHhWokqBj18ZO6OEtKL9Nwzoampia8be3t7d1Uqlbv6+vqojUYA6AGwB8AuAPtDu+4sKJv4qZKU6bDOeEKlUokIZ35+x44d6OzsRFdX1+3t7e1Xjxo1anRvb+8wAL0A9gLYCmA1gMUAHpM5gc2h3EsjKJN4Ss6dAOZaZzxh2LBh6OnpwcaNG7FhwwY8//zz0d+VSqXS3d09ZcaMGRg+fLhO1owGMBHA4eKUEtslHXyXZAW7Qrm3WiiT+O8BeJN11AMo4QRn4FatWoWdO3f2S35z80uP5MUXX4wGBMnnuYSZOg6Ed8qLM4O/BHAHgKXWOwNDWc7dxwBcZB31AJK7f/9+LFu2DEuXLsWuXbsiydfBoODfe/bswZYtWyLi6wCniL8M4AkA8wEcF8L9JqEM4o8FcJN11ANI8AsvvIBFixZh/fr1/VKeBJ7r6uqKbD//t07QpJ0P4O8AbgYwPYR7jyP5rt2AdvFn8tMrSNzu3buxePHiSMpVpdcCB8fmzZvR3d1dr+QrWgF8WjTARVJXEAyKJv56kXivoORS0pcsWRL9bEB6I/T29mLTpk2RnW+QfEgk80OpCzjKOusJRRJ/lox4ryBR9NSfeuqpTKRDPoP/u23btizEK06X6WbvzwQFEj8ewC3WUQ8gUcuXL2/UTlug1mCMT4cvzS+ogTax+wz/pqS/tVgURfxtAA6zjpYM2vF169ZFYVm9Nj0NVPW090z25JB84r0yJXyKdaYkFEH8RwG8zzpa9o01NUUJmWeffTaPhB4Ekr13715s3749L/HEHKklvNg6UwJcEz9VHLogsGLFiihmd0BSP1Tl08t3MKBaxSR+v+z0uWvirw8hD0+1Ti+czlgeu54EqnwmdhKyeVnwGSkImeD8YhPgkvjTAHzIOuoB+/btw+rVqwv7YmoQOnlM7rgyI1IC9hCA2daZAuDqqodLkaT3JAWlnRMuTNY4JMUCyaetd2xKXg3gYflZKFw9mU+FUK+uztfatWsLJV2/i6Q7cvRMHCZO36nWGYdw8XRYL/c166gH0J4zdMuQXs0EDi7mBwr4vklSi1jE2oIILoi/3HOxZAQ+eNp2qvmipd0E07l0IgvAWKlUek8RH573CR0uat47KO30tOl0lSHtCg4y+hMF+RQjAfymiDRv3iv9lsSi3sFsGqW9TNJN0NYXtKy6SdK8TsnPQ/yJknr0Dko7QytOt5ap5hU6icNKniLyBgKSf6F1NCPyPKWvh7T2jgkb2ltfKCi8i+NWAB+2jmZAVuLeEMqaMz5ketV86AVKW13XwTo9ap6Czc1PXGjarMRfbh3xBJJNr5rxu2+QcIZ3BUv9MKnpO9060wCyEH+SLH8KAlTv9OZ92PY4NKlD8gu+nlbx9jNn+LJc3RdCse069erLqasGkk8nr2CphxS73NNge5h+NPq0GLefYx31BJJN2y6LIPpfshTqoGNlhXmaSOJgLOE7Z0mSp+GFp43OAV8s5cNeoUQybibxBMlnLE/Vr/G0kk8/wKyd5/kiW5mp1Le3t1vnCsAJAH4qXcHqvqlGiGf++IPW0ZJgkk2JogdN0llaRdKVcHPVi/6uGmDEiBHRi8uiVBqLGAA6WcQs4tixY8sIM5nW/TaAK60zCWiE+HNlyVCpIGFKNu05Xwzf1Knj7xrGKZmmilXyqQ2YZOH7OXU7atQotLS0pC2PygV+JqWexJeEKwAskUJOZ8TzSX7EOloglHASrdWt6jCpHW+0/EkHBD+HNpiSP2bMmP6FkS4HgC7BomailimpSyY7e/wLwH+sMzHUS/wJ8iocSg6JYXzOyQ/IcVOySR4fahYHSv+HWoQJF5JPDQDHqp9ahgN30qRJZRHfLiuXTpVl3YmoV1zOK7q6RqWY6phlU3zpjJc6aeZ7+T7a9rzfSfB7SBBiZiIv+Fn83JJTyZxDucY6GkM9xLcUHcKRWJLIhYxctsyHpYQngcS7lCJ+nmvyNY1LR6/kWcNLpe9vIpKf7P/BEfRK66gDqJQz08X6dy1oSCMckq0jUa6lkz4Dpd9l3M9rLbtGQMAJnXHWUUH6E34JZ1tHHEDbjzAcW7NmTWSz65lkUSmifXb9MNWEuCRKK3I9tECnsF5lHRXUIr4p72RANajHu3LlyigWj9vwNKhkctAUASWKg8tFGlgHalZHNCcuEY1todadzXLZcVJVO1U6bTltX5apVEpl0aC9d7BGLgI/p6wC0Bj4cL9bjWfrQAwniXOXG5ooYekzS6Q0o9YI+Bn6EIuelKFtNkPJvOA1e8Ibq83f13p6J1tHMoAk0SZTypmMaUS1x8HPoT9QBlRF54Wqe48VQt+IC3At4nM38FF7TtL5M0+VjObAy3yA6pjlkXpNOOksoge8CsD7za9NI3503jCOpDNfzWQMJdWFeqbKLNNDJlkuQkeaqLI0VQIuFZsfIY2JqXlWvupcOUM1SqgL0kl42ckQjSLySiuvvYTijDSwF9Fb9HwaGy/LOvdOkjlzxkwcHDlH2sumiPi9FjhwXThnniUesqFThDTiG+7Rohkveu0sd87jxFX7bJJeVPyeBvUt8kp93rkFBzhTBDqV+MR0XzWY4RpbhRYRbpF4X54x7y2v1JvVQZ7Qpgm5NHbqXhplks5pzqLq232XUKvGyToVHMjOldyLJ5X4umCSnrelWBq0CsejcxSR7iKu9wwm5UakEV9TrykJRZMOUZO+nSO19Vni+ry5AIdgGv6wNOJTe6/rTXB2rWjSIdLmMQFy0HVklXo+owDIZ9XVnDTi11tHYqD3zhRs0aRrjt7nokgFJTcr8S6aLDrCrDTi18oeLBbosbPDY1HtxOLQGD4E4jX92qiTx/ey6DIQTEojnnutrIgf1GlVJmjKXJ1K4kPZz50DsFGp53NjOXcg9zAyjfg+6bfaDy2TYoOhsm2Vj8RNEnS2DXVmJUm2LuQIhPgDacQT9+kv2uvFZRq2EYREPEQD1RtlkOzRo0cHs7CTjnutK6HEP6MjnB68q6qURhEa8ZCETj0g4VxRE4qpArCmSVeQJLyoz27VokhXU6tZEIJjF4cSnyYIKu2tra2hEM+LeKZJ69eTXs3Nzbd3dXVt1Fp3L1fa1xck8fWoew6KCRMmpA6OkrGGxDfXCjEqlcoZHR0d45mDZ9YqIDvlHTrHTqetGjhYx48fH0l8QAOX+/TvraXqp/X29t7W0tLSOnv27CgB4eMGApIWC0l2ns+P4VtHR0dIth2y22fNSRo20J9CG89RO2fOHG/k++xolQRNLMUdXq0gnjp1avS8AiKeK2n/ghrEn2v2UeXN0TOdO3duNJLL9rJDNTGq7s2/OQhIOlfgBuabXK+7YSc9zXYpxD8IJJtLiufNm4e2trZSs2khSrxCp4tV0qdPn45x48aFRjpD81/rH0nEXwZgpnVUyKfEU/J5gygp1ApogsOCevcjR47EzJkzI80YWN6hW3rh9l9Utac5V/ZISQSJ5gjnTsuUfGbzNNwryhEz+9aEBC2nYqMjqndeY4Ch5xelTUo/qhF/pbTLTgVvliOdxNPxYym1uQerS5vM7yLxVPehPFQlnPfPiIexekgTSQZukS1OD0Kc+COk+0XdUI92ypQpUczKAcDZO1344EILKPFU955WnfZDCedgp8abNm1aNAFDOx8g6dzP/rPW0SrEfy5LLb1KPyWS6o6xK3vYcBDQBJhNi7KSptOaHrpL9JPNa6BKJ9mTJ0+OCNcVMmY7tkAwX/btr6oiTeKnNSrtcegA4AOg9PNFyecg4HQu16FpbbmagkbWxdN54pKsomGmiDmYGclMnDgxIpveujZ10Hsx26wFQvyNYtcTYRJ/ntRd54b2lSM4OUHC+NAorbqNBwcB1aO5yMD0C+IDQtUriXD1cM0spYKfz2sm2ZRuvsyWaBwQ8YURGrsH4HxyXffnpbV5KpT4pqK6VprOmA4CmgJdDqVNBzkoaL+1rEnVq/lQtZiB/1PLeUwaHEoO/QWSzM/jNTHZwoFFkvk3v0sluBrZ1aDv9zQAnpCWs/+0zlSBEn+kiyXRtWAOAhKnDxyGetXlxPypGkHTojDalCeFTRpR6E+TYP6ubU11EOl5s88tPzdLHO5J6vdKsu3aekriFUr8GeYS2jJQrZMkHxodOGoG85j5vlmzZkVLtNKIiRNQzWzoZyrR1QZRo/Dg4P1e9vx70jpTA0r8aelvKwfVBkMclEwOjLT6AB+xvmmSSiCe6vw66VefCSSeuvbooq/UFfhQaYeL7HyVBwWrekr2TRKfp1eA1ABFZgaA6elvCwcknjaZGbMAEyZFSDxH959ltvS1AH6el3SIxM8s277nBVU5ncJ9+/b17d69uxJSDt+hg7dedp9gU+JF1tmcaM7SACEEkPy2traKxx5yVZHTztMr/5uo8ge4gaX1DkcYsMRDHjKzg5CmSCGXaKWAtVuPA7hXdpB+Jvmt7tAsm/4PSKgnzZkxpnJdNiTMggaSNzuFbIZjDwJYZr2jYJD4zoFKPIwMHVOrzAFwXsBXd6mUnD3/+K9UwTwkP9dZH1AiSPwmnxfgCrT5zASSfJ0L0HCvjEFgppf7+vp6+vr6nqtUKgy/HgGwEMC/2SzT+kdPIPHPyYgckAbShFYGaXEI7T5f1ABmUidvvzoTFdkyhalfpoCZCu7p6bmis7Pz5gMHDnSH6neQ+FUAtgxkJ8+ESh6zekz0cADohJDm/uOTQGlQ4syVRmb+39zTzlD1W1taWrq1ICUpw+gTzdL84EnthjRYYJKq0qgrWnTaWH+PtyGrxHaoNEmPx+nxRSiCsZR8FqWwY4jWEIQk/Zqrv3+wEW8iLtnx/W6SCIlrgyoEJ2GYmh0WcNDvYDWSr5XG1aB3f69M4h8SiEupzs7FXwnSXA+iN+tns9ya5VqcXAqlWFSJ3yDbUg/BDQ7y3inpNDVU/Zpw8j3PYHod3wkp3Bjg2BO/fJV0qn5WJPsuFTeJ57aUP7DeMYQs2FHtf9Sp5AQTVb/P5dPxOOMqyTANIR+2pv03yabqp+Qz45jBh8iNOPG7ZPPg6ou+h1AP9kmruFSopHPfWVYg6ybKvoiHpBerrr4YQl3YUg/xMFS/ev0M+8pS/dWIJ36UtkvhEFKxsppzlwaSrwmfsrpjJREP2bLqWuvoEGqh4YpXGPMMVPucZkbBIV8a8cQVAL5iHR1CGh5JOZcKdfIY69PxK9Lu1zN7cIPsWZbaxnwIETZIYWRmqN1nqEfVTxNQhN2vd9qIWb03AfiHdWYIJu5LiuEbhXYeodPHWUbX5DcyX7hUFl6wgY737ZQCxZ0uL0v369N43yX5jU4Ud0t/nNPina2HEC1aXOj6MehkEVO9tP2ubH7WCoFHRfVfKIUcQ3hprqOQIFxn+ejtcwC4kPw8pSEHJN5/jSzc22i949DBwjzr2OoFBwAbM7hQ+y5qgujtXw3geGmctNp6x+DGfmkhU/hCPg33KPn0+vOofZfFYExTXiMD4AJpnRne4jb3uLLeZgQuoJW8VPl5avmKqALcIY133gzgZGm1VXNHqwGK+ZLnKBU6u8fp3axSX3T5J+P+SwAcA+B8iXOr7mw1AHG3uTuzD+TpB1RW3S9X69wB4BxZi/9JWRToJNnhAbcB+IDP6eu8RZs+Cr65Q8KPAZwN4ChpujR/gISF22XQXjTQk1jVWpqWifWyJJiv0QCOlfzAqeIkdni+PgU99l8B+Ga1vfgGIkJqCc057MfkxengSTIQTpZOEEdIE4cyr7lT/JJbZXXroEG4vcCBbQAelheksfIrxDwcJ77CHGnjMtb67+xYZyxh/sNgjUhCJj6ObllHvsxYA8C+u5MBvFy3x5bfp4qZaJPmTq3GvVJts4ycGoYRBlcL0794Wla0Lh9EkUd1APgfXXxx1BgSZj0AAAAASUVORK5CYII=';
    }

    public function settings_render() {
        include_once( 'views/settings-page.php' );
    }

    public function datafeed_render() {
        include_once( 'views/datafeed-page.php' );
    }

    public function advanced_render() {
        include_once( 'views/advanced-page.php' );
    }

    public function add_settings() {

        // General Settings
        add_settings_section(
			$this->settings_section,
			__("General Settings", 'pureclarity'),
			array( $this, 'print_settings_section_text' ),
			$this->settings_slug
        );
        
        add_settings_field(
			'pureclarity_accesskey',
			__('Access Key', 'pureclarity'),
			array( $this, 'accesskey_callback' ),
			$this->settings_slug,
			$this->settings_section
        );

        add_settings_field(
			'pureclarity_secretkey',
			__('Secret Key', 'pureclarity'),
			array( $this, 'secretkey_callback' ),
			$this->settings_slug,
			$this->settings_section
        );

        add_settings_field(
			'pureclarity_region',
			__('Region', 'pureclarity'),
			array( $this, 'pureclarity_region_callback' ),
			$this->settings_slug,
			$this->settings_section
        );

        add_settings_field(
			'pureclarity_mode',
			__('Enable Mode', 'pureclarity'),
			array( $this, 'pureclarity_mode_callback' ),
			$this->settings_slug,
			$this->settings_section
        );

   //      add_settings_field(
			// 'pureclarity_search_enabled',
			// 'Enable Search',
			// array( $this, 'search_enabled_callback' ),
			// $this->settings_slug,
			// $this->settings_section
   //      );

   //      add_settings_field(
			// 'pureclarity_prodlist_enabled',
			// 'Enable Product Listing',
			// array( $this, 'prodlist_enabled_callback' ),
			// $this->settings_slug,
			// $this->settings_section
   //      );

   //      add_settings_field(
			// 'pureclarity_merch_enabled',
			// 'Enable Merchandizing',
			// array( $this, 'merch_enabled_callback' ),
			// $this->settings_slug,
			// $this->settings_section
   //      );

        add_settings_field(
            'pureclarity_shop_enabled',
            __('Enable Shop', 'pureclarity'),
            array( $this, 'shop_enabled_callback' ),
            $this->settings_slug,
            $this->settings_section
        );

        add_settings_field(
			'pureclarity_deltas_enabled',
			__('Enable Delta Sync', 'pureclarity'),
			array( $this, 'enabled_deltas_callback' ),
			$this->settings_slug,
			$this->settings_section
        );

        register_setting( $this->settings_option_group, 'pureclarity_accesskey', 'sanitize_callback' );
        register_setting( $this->settings_option_group, 'pureclarity_secretkey', 'sanitize_callback' );
        register_setting( $this->settings_option_group, 'pureclarity_region', 'sanitize_callback' );
        register_setting( $this->settings_option_group, 'pureclarity_mode', 'sanitize_callback' );
        // register_setting( $this->settings_option_group, 'pureclarity_search_enabled', array( $this, 'sanitize_checkbox' ) );
        // register_setting( $this->settings_option_group, 'pureclarity_merch_enabled', array( $this, 'sanitize_checkbox' ) );
        // register_setting( $this->settings_option_group, 'pureclarity_prodlist_enabled', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->settings_option_group, 'pureclarity_deltas_enabled', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->settings_option_group, 'pureclarity_shop_enabled', array($this, 'sanitize_checkbox') );

        // Advanced Settings
        add_settings_section(
			$this->advanced_section,
			null,
			array( $this, 'print_advanced_section_text' ),
			$this->advanced_slug
        );

        add_settings_field(
			'pureclarity_bmz_debug',
			__('Enable BMZ Debugging', 'pureclarity'),
			array( $this, 'pureclarity_bmz_debug_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

   //      add_settings_field(
			// 'pureclarity_search_selector',
			// 'Autocomplete Input DOM Selector',
			// array( $this, 'searchselector_callback' ),
			// $this->advanced_slug,
			// $this->advanced_section
   //      );

   //      add_settings_field(
			// 'pureclarity_search_result_selector',
			// 'Search Results DOM Selector',
			// array( $this, 'searchresults_selector_callback' ),
			// $this->advanced_slug,
			// $this->advanced_section
   //      );

        // add_settings_field(
        //     'pureclarity_prodlist_result_selector',
        //     'Product List DOM Selector',
        //     array ($this, 'prodlist_selector_callback'),
        //     $this->advanced_slug,
        //     $this->advanced_section
        // );

        add_settings_field(
            'pureclarity_shop_selector',
            __('Shop DOM Selector', 'pureclarity'),
            array ($this, 'shop_selector_callback'),
            $this->advanced_slug,
            $this->advanced_section
        );


        add_settings_field(
			'pureclarity_add_bmz_homepage',
			__('Show Home Page BMZs', 'pureclarity'),
			array( $this, 'pureclarity_add_bmz_homepage_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        add_settings_field(
			'pureclarity_add_bmz_categorypage',
			__('Show Product Listing BMZs', 'pureclarity'),
			array( $this, 'pureclarity_add_bmz_categorypage_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        add_settings_field(
			'pureclarity_add_bmz_searchpage',
			__('Show Search Results BMZs', 'pureclarity'),
			array( $this, 'pureclarity_add_bmz_searchpage_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        add_settings_field(
			'pureclarity_add_bmz_productpage',
			__('Show Product Page BMZs', 'pureclarity'),
			array( $this, 'pureclarity_add_bmz_productpage_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        add_settings_field(
			'pureclarity_add_bmz_basketpage',
			__('Show Cart Page BMZs', 'pureclarity'),
			array( $this, 'pureclarity_add_bmz_basketpage_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        add_settings_field(
			'pureclarity_add_bmz_checkoutpage',
            __('Show Order Confirmation BMZs', 'pureclarity'),
			array( $this, 'pureclarity_add_bmz_checkoutpage_callback' ),
			$this->advanced_slug,
			$this->advanced_section
        );

        register_setting( $this->advanced_option_group, 'pureclarity_bmz_debug', array( $this, 'sanitize_checkbox' ) );
        // register_setting( $this->advanced_option_group, 'pureclarity_search_selector', 'sanitize_callback' );
        register_setting( $this->advanced_option_group, 'pureclarity_shop_selector', 'sanitize_callback' );
        register_setting( $this->advanced_option_group, 'pureclarity_prodlist_selector', 'sanitize_callback' );
        // register_setting( $this->advanced_option_group, 'pureclarity_search_result_selector', 'sanitize_callback' );
        register_setting( $this->advanced_option_group, 'pureclarity_add_bmz_homepage', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->advanced_option_group, 'pureclarity_add_bmz_categorypage', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->advanced_option_group, 'pureclarity_add_bmz_searchpage', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->advanced_option_group, 'pureclarity_add_bmz_productpage', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->advanced_option_group, 'pureclarity_add_bmz_basketpage', array( $this, 'sanitize_checkbox' ) );
        register_setting( $this->advanced_option_group, 'pureclarity_add_bmz_checkoutpage', array( $this, 'sanitize_checkbox' ) );

        // Data feed section
        add_settings_section(
			$this->datafeed_section,
			null,
			null,
			$this->datafeed_slug
        );

        add_settings_field(
			'pureclarity_product_feed',
			__('Run Product Feed', 'pureclarity'),
			array( $this, 'product_feed_callback' ),
			$this->datafeed_slug,
			$this->datafeed_section
        );

    }

    public function accesskey_callback() {
        ?>
		<input type="text" name="pureclarity_accesskey" class="regular-text" value="<?php echo esc_attr( $this->settings->get_accesskey() ); ?>" />
		<p class="description" id="home-description"><?php _e('Enter your Access Key', 'pureclarity'); ?></p>
        <?php
    }
    
    public function secretkey_callback() {

        ?>
		<input type="text" name="pureclarity_secretkey" class="regular-text" value="<?php echo esc_attr( $this->settings->get_secretkey() ); ?>" />
		<p class="description" id="home-description"><?php _e('Enter your Secret Key', 'pureclarity'); ?></p>
        <?php
    }

    public function pureclarity_region_callback() {

        $regions = $this->settings->get_regions();
        $region = $this->settings->get_region();

        ?>
        <select id="pureclarity_region" name="pureclarity_region">
            <?php foreach($regions as $key=>$url): ?>
                <option value="<?php echo $key ?>" <?php echo ( $region == $key ? "selected='selected'" : "" ); ?>><?php _e('Region', 'pureclarity'); ?> <?php echo $key ?></option>
            <?php endforeach; ?>
        </select>
        <p class="description" id="home-description"><?php _e('Select the Region Id supplied with your PureClarity credentials', 'pureclarity'); ?></p>
        <?php
    }


    public function pureclarity_mode_callback() {

        $mode = $this->settings->get_pureclarity_mode();
        $selected = "selected='selected'";

        ?>

        <select id="pureclarity_mode" name="pureclarity_mode">
            <option value="on" <?php echo ( $mode == "on" ? $selected : "" ); ?> ><?php _e('On', 'pureclarity'); ?></option>
            <option value="admin" <?php echo ( $mode == "admin" ? $selected : "" ); ?>><?php _e('Admin only', 'pureclarity'); ?></option>
            <option value="off" <?php echo ( $mode=="off" ? $selected : "" ); ?>><?php _e('Off', 'pureclarity'); ?></option>
        </select>
        <p class="description" id="home-description"><?php _e("Set PureClarity Enable Mode. When the mode is set to 'Admin only' PureClarity only shows for administrators on the front end.", 'pureclarity'); ?></p>

        <?php
    }
    
    public function search_enabled_callback() {

        $enabled = $this->settings->get_search_enabled_admin();
        $checked = ( $enabled ? 'checked' : '' );

        ?>
		<input type="checkbox" id="checkbox_search" name="pureclarity_search_enabled" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Check to activate PureClarity Search.', 'pureclarity'); ?></p>
        <?php

    }
    
    public function merch_enabled_callback() {

        $enabled = $this->settings->get_merch_enabled_admin();
        $checked = ( $enabled ? 'checked' : '' );

        ?>

		<input type="checkbox" id="checkbox_merch" name="pureclarity_merch_enabled" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Check to activate PureClarity Merchandizing', 'pureclarity'); ?></p>

        <?php

    }
    
    public function prodlist_enabled_callback() {

        $enabled = $this->settings->get_prod_enabled_admin();
        $checked = ( $enabled ? 'checked' : '' );

        ?>

		<input type="checkbox" id="checkbox_prodlist"  name="pureclarity_prodlist_enabled" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Check to activate PureClarity Product Listing', 'pureclarity'); ?></p>

        <?php

    }

    public function shop_enabled_callback() {

        $enabled = $this->settings->get_shop_enabled_admin();
        $checked = ( $enabled ? 'checked' : '' );

        ?>

		<input type="checkbox" id="checkbox_shop"  name="pureclarity_shop_enabled" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Check to activate PureClarity on the Shop page', 'pureclarity'); ?></p>

        <?php

    }

    public function enabled_deltas_callback() {

        $enabled = $this->settings->get_deltas_enabled_admin();
        $checked = ( $enabled ? 'checked' : '' );

        ?>

		<input type="checkbox" id="checkbox_deltas"  name="pureclarity_deltas_enabled" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Check to activate automatic data synchronisation', 'pureclarity'); ?></p>

        <?php

    }

    public function pureclarity_bmz_debug_callback() {

        $enabled = $this->settings->get_bmz_debug_enabled();
        $checked = ( $enabled ? 'checked' : '' );

        ?>
		<input type="checkbox" id="checkbox_bmz_debug"  name="pureclarity_bmz_debug" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Check to activate debugging for PureClarity BMZs. They will show even if empty.', 'pureclarity'); ?></p>
        <?php

    }

    public function pureclarity_add_bmz_homepage_callback() {

        $enabled = $this->settings->add_bmz_homepage();
        $checked = ( $enabled ? 'checked' : '' );

        ?>
		<input type="checkbox" id="checkbox_bmz_homepage"  name="pureclarity_add_bmz_homepage" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Auto insert BMZs on Home Page', 'pureclarity'); ?></p>
        <?php

    }

    public function pureclarity_add_bmz_categorypage_callback() {

        $enabled = $this->settings->add_bmz_categorypage();
        $checked = ( $enabled ? 'checked' : '' );

        ?>
		<input type="checkbox" id="checkbox_bmz_categorypage"  name="pureclarity_add_bmz_categorypage" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Auto insert BMZs on Product Listing Page', 'pureclarity'); ?></p>
        <?php

    }

    public function pureclarity_add_bmz_searchpage_callback() {

        $enabled = $this->settings->add_bmz_searchpage();
        $checked = ( $enabled ? 'checked' : '' );

        ?>
		<input type="checkbox" id="checkbox_bmz_searchpage"  name="pureclarity_add_bmz_searchpage" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Auto insert BMZs on Search Results Listing Page', 'pureclarity'); ?></p>
        <?php

    }

    public function pureclarity_add_bmz_productpage_callback() {

        $enabled = $this->settings->add_bmz_productpage();
        $checked = ( $enabled ? 'checked' : '' );

        ?>
		<input type="checkbox" id="checkbox_bmz_productpage"  name="pureclarity_add_bmz_productpage" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Auto insert BMZs on Product Page', 'pureclarity'); ?></p>
        <?php

    }

    public function pureclarity_add_bmz_basketpage_callback() {

        $enabled = $this->settings->add_bmz_basketpage();
        $checked = ( $enabled ? 'checked' : '' );

        ?>
		<input type="checkbox" id="checkbox_bmz_basketpage"  name="pureclarity_add_bmz_basketpage" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Auto insert BMZs on Cart Page', 'pureclarity'); ?></p>
        <?php

    }

    public function pureclarity_add_bmz_checkoutpage_callback() {

        $enabled = $this->settings->add_bmz_checkoutpage();
        $checked = ( $enabled ? 'checked' : '' );

        ?>
		<input type="checkbox" id="checkbox_bmz_checkoutpage"  name="pureclarity_add_bmz_checkoutpage" class="regular-text" <?php echo $checked; ?> />
		<p class="description" id="home-description"><?php _e('Auto insert BMZs on Order Confirmation Page', 'pureclarity'); ?></p>
        <?php

    }

    public function searchselector_callback() {
        ?>
		<input type="text" name="pureclarity_search_selector" class="regular-text" value="<?php echo esc_attr( $this->settings->get_search_selector() ); ?>" />
		<p class="description" id="home-description"><?php _e('Enter DOM selector for the autocomplete input box (default is .search-field).', 'pureclarity'); ?></p>
        <?php
    }


    public function searchresults_selector_callback() {
        ?>
		<input type="text" name="pureclarity_search_result_selector" class="regular-text" value="<?php echo esc_attr( $this->settings->get_search_result_element() ); ?>" />
		<p class="description" id="home-description"><?php _e('Enter DOM selector for the main body where search results will be displayed (default is #main).', 'pureclarity'); ?></p>
        <?php
    }


    public function shop_selector_callback() {
        ?>
		<input type="text" name="pureclarity_shop_selector" class="regular-text" value="<?php echo esc_attr( $this->settings->get_shop_selector() ); ?>" />
		<p class="description" id="home-description"><?php _e('Enter DOM selector for the main body where shop results will be displayed (default is #main).', 'pureclarity'); ?></p>
        <?php
    }

    public function prodlist_selector_callback() {
        ?>
		<input type="text" name="pureclarity_prodlist_selector" class="regular-text" value="<?php echo esc_attr( $this->settings->get_prodlist_result_element() ); ?>" />
		<p class="description" id="home-description"><?php _e('Enter DOM selector for the main body where product list results will be displayed (default is #main)', 'pureclarity'); ?></p>
        <?php
    }
    
    public function sanitize_checkbox( $value ) {
		return ( $value == 'on' ? 'yes' : 'no' );
    }

    public function print_settings_section_text() {
        echo "<p>" . __('To get started with PureClarity, you will need a PureClarity account and to then enter your access credentials below.', 'pureclarity') . "</p>";
        $url = "https://www.pureclarity.com/free-trial/?source=woocommerce&medium=listing&campaign=freetrial";
        $link = sprintf( 
            wp_kses(    // sanitize result 
                __( "If you don't yet have an account, <a href='%s' target='_blank'>register for your free trial today</a>.", 'pureclarity' ), 
                array(      // permitted html
                    'a' => array( 
                        'href' => array(),
                        'target' => array() 
                        ) 
                    ) 
            ), 
            esc_url( $url ) 
        );
        echo $link;
    }

    public function print_advanced_section_text() {
        $url = "https://support.pureclarity.com/hc/en-us/sections/360001594074-WooCommerce";
        $link = sprintf( 
            wp_kses(    // sanitize result 
                __( "Configure advanced settings for PureClarity.  For more information, please see the <a href='%s' target='_blank'>PureClarity support documentation</a>.", 'pureclarity' ), 
                array(      // permitted html
                    'a' => array( 
                        'href' => array(),
                        'target' => array()
                        ) 
                    ) 
            ), 
            esc_url( $url ) 
        );
        echo $link;
    }

    public function display_dependency_notices() {
		if ( ! extension_loaded( 'curl' ) ) {
			echo '<div class="error notice">
                    <p>';

            printf(
                __("PureClarity requires the %s extension to be installed and enabled. Please contact your hosting provider.", 'pureclarity'),
                '"cURL" PHP'
            );

            echo '</p>
				</div>';
        }
        
        $whitelist_admin_pages = array( 'toplevel_page_pureclarity-settings', 'pureclarity_page_pureclarity-advanced' );
        $admin_page = get_current_screen();

        if( in_array( $admin_page->base, $whitelist_admin_pages ) 
            && isset( $_GET[ 'settings-updated' ] ) 
            &&  $_GET[ 'settings-updated' ] ):
        ?>
            <div class="notice notice-success is-dismissible"> 
                <p><strong><?php _e('Settings saved.', 'pureclarity'); ?></strong></p>
            </div>
        <?php
        endif;
	}

}
<?php
/*
 * Plugin Name: Payment QR Code Generator
 * Plugin URI: https://www.smitka.net/wp-plugins/payment-qr-code-generator
 * Update URI: https://www.smitka.net/wp-plugin/payment-qr-code-generator
 * Description: Generátor QR kódů pro bankovní platby
 * Version: 1.0
 * Author: Ivan Smitka
 * Author URI: http://www.smitka.net
 * License: The MIT License
 *
 * Copyright 2025 Web4People Ivan Smitka <ivan at stimulus dot cz>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 *
 */

include 'lib/PaymentQRCodeGeneratorApi.php';

/**
 * @author Ivan Smitka <ivan at stimulus dot cz>
 */
class PaymentQRCodeGenerator {

	const CACHE_KEY = "PaymentQRCodeGenerator";

	const UPDATE_URI = "https://www.smitka.net/wp-plugin/payment-qr-code-generator";

	private static function log( $msg ) {
		$stdout = fopen( 'php://stdout', 'w' );
		fwrite( $stdout, "PaymentQRCodeGenerator: {$msg}\n" );
		fclose( $stdout );
	}

	public static function init() {
		// Scripts
		if ( is_admin() ) {
			add_filter( 'update_plugins_www.smitka.net', function ( $update, $plugin_data, $plugin_file, $locales ) {
				if ( $plugin_file === plugin_basename( __FILE__ ) ) {
					self::log( "get_update: " . json_encode( $plugin_data ) );
					$remote = self::getUpdate( $plugin_data['UpdateURI'] );
					self::log( "get_update - remote: " . json_encode( $remote ) );
					self::log( "remote version: {$remote->version}, current version: {$plugin_data['Version']}" );
					self::log( version_compare( $remote->version, $plugin_data['Version'], '>' ) ? "NEW version" : "no new version" );

					return $remote;
				}

				return false;
			}, 10, 4 );
			add_filter( 'plugins_api', static function ( $res, $action, $args ) {
				self::log( "plugins_api: {$action}" );

				if ( 'plugin_information' !== $action ) {
					return $res;
				}
				if ( plugin_basename( __DIR__ ) !== $args->slug ) {
					return $res;
				}

				$res                = self::getUpdate( self::UPDATE_URI );
				$res->download_link = $res->package;

				self::log( "plugins_api: " . json_encode( $res ) );

				return $res;

			}, 9999, 3 );
			add_action( 'admin_enqueue_scripts', [ 'PaymentQRCodeGenerator', 'enqueue_admin_scripts' ] );
			add_action( 'admin_menu', [ 'PaymentQRCodeGenerator', 'admin_menus' ] );
		} else {
			add_action( 'wp_enqueue_scripts', [ 'PaymentQRCodeGenerator', 'enqueue_scripts' ] );
			add_shortcode( 'payment-qr-code-generator', [ 'PaymentQRCodeGenerator', 'form' ] );
		}
	}

	/**
	 * @param $update_URI
	 *
	 * @return mixed
	 */
	private static function getUpdate( $update_URI ): mixed {
		try {
			$request = wp_remote_get( $update_URI, [
				'timeout' => 10,
				'headers' => [
					'Accept' => 'application/json'
				]
			] );
			if (
				is_wp_error( $request )
				|| wp_remote_retrieve_response_code( $request ) !== 200
				|| empty( $request_body = wp_remote_retrieve_body( $request ) )
			) {
				return false;
			}

			return json_decode( $request_body, false );
		} catch ( Throwable $e ) {
			self::log( $e->getMessage() );

			return false;
		}
	}

	public static function enqueue_admin_scripts() {
		wp_enqueue_script( 'payment-qr-code-generator', plugins_url( 'static/js/admin.js', __FILE__ ), [ 'jquery-form' ] );
		wp_enqueue_style( 'payment-qr-code-generator', plugins_url( 'static/css/default.css', __FILE__ ), [] );
	}

	public static function settings_page( $links ) {
		$url = get_admin_url() . "options-general.php?page=payment-qr-code-generator";
		// Create the link.
		$links[] = "<a href='$url'>" . __( 'Settings' ) . '</a>';

		return $links;
	}

	public static function admin_menus() {
		add_options_page( 'Payment QR-Code Generator', 'Payment QR-Code Generator', 'manage_options', 'payment-qr-code-generator', [ 'PaymentQRCodeGenerator', 'admin' ] );
		add_filter( 'plugin_action_links_payment-qr-code-generator/payment-qr-code-generator.php', [ 'PaymentQRCodeGenerator', 'settings_page' ] );
	}

	public static function enqueue_scripts() {
		wp_enqueue_script( 'payment-qr-code-generator', plugins_url( 'static/js/default.js', __FILE__ ), [ 'jquery-form' ] );
		wp_enqueue_style( 'payment-qr-code-generator', plugins_url( 'static/css/default.css', __FILE__ ), [] );
	}

	public static function admin( $args = array() ) {
		$accs = PaymentQRCodeGeneratorApi::getAcc();
		?>
        <section class="payment-qr-code-generator admin">
            <h1>Payment QR-Code Generator</h1>
            <table>
                <caption>Seznam bankovních účtů</caption>
                <thead>
                <tr>
                    <th>Název</th>
                    <th>IBAN</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
				<?php
				foreach ( $accs as $index => $acc ) {
					?>
                    <tr data-index="<?= $index ?>">
                        <td><input name="name" type="text" value="<?= $acc["name"] ?>"></td>
                        <td><input name="IBAN" type="text" value="<?= $acc["IBAN"] ?>"
                                   pattern="^CZ\d{2}[ ]\d{4}[ ]\d{4}[ ]\d{4}[ ]\d{4}[ ]\d{2}|CZ\d{20}$"></td>
                        <td><a href="#remove">smazat</a></td>
                    </tr>
					<?php
				}
				?>
                <tr data-index="<?= count( $accs ) ?>" data-new="true">
                    <td><input name="name" type="text"></td>
                    <td><input name="IBAN" type="text"
                               pattern="^CZ\d{2}[ ]\d{4}[ ]\d{4}[ ]\d{4}[ ]\d{4}[ ]\d{2}|CZ\d{20}$">
                    </td>
                    <td><a href="#remove">smazat</a></td>
                </tr>
                </tbody>
            </table>
        </section>
		<?php
	}


	public static function form( $args = array() ) {
		ob_start();
		$acc = array_key_exists( "ACC", $_GET ) ? $_GET["ACC"] : null;
		$am  = array_key_exists( "AM", $_GET ) ? $_GET["AM"] : null;
		$msg = array_key_exists( "MSG", $_GET ) ? $_GET["MSG"] : null;
		$vs  = array_key_exists( "X-VS", $_GET ) ? $_GET["X-VS"] : null;
		?>
        <section class="payment-qr-code-generator">
            <form id="qr">
                <div>
                    <label>Účet:
                        <a href=""><i class="fa fa-bookmark-o"></i> QR Platba</a>
                        <select name="ACC"><?= PaymentQRCodeGenerator::getAccOptions( $acc ) ?></select>
                    </label>
                </div>
                <div>
                    <label>Částka:<input name="AM" type="number" value="<?= $am ?>"/></label>
                </div>
                <div>
                    <label>Zpráva:<input name="MSG" type="text" value="<?= $msg ?>" maxlength="60"/></label>
                </div>
                <div>
                    <label>VS:<input name="X-VS" type="number" value="<?= $vs ?>" maxlength="10"/></label>
                </div>
            </form>
            <img src=''>
        </section>
		<?php
		return ob_get_clean();
	}

	/**
	 * @param $selected string IBAN
	 *
	 * @return string html options
	 */
	private static function getAccOptions( $selected ) {
		$data = [
			[
				"name" => "Vyber...",
				"IBAN" => null
			],
		];
		$data = array_merge( $data, PaymentQRCodeGeneratorApi::getAcc() );

		return implode( "", array_map( static function ( $ACC ) use ( $selected ) {
			$iban = $ACC["IBAN"];
			$name = $ACC["name"];

			return "<option value='{$iban}'" . ( $iban === $selected ? " selected" : "" ) . ">{$name}</option>";
		}, $data ) );
	}
}

add_action( 'plugins_loaded', [
	'PaymentQRCodeGenerator',
	'init'
], 100 );

<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Patient ID documents — secure capture, storage and gated viewing.
 *
 * Built to the engineering standards in CLAUDE.md (sister-site incident:
 * 208 patient IDs publicly downloadable for 18 hours). Non-negotiables
 * implemented here:
 *
 *  - FAIL CLOSED: secure_doc_dir() returns WP_Error until the outside-
 *    webroot directory is confirmed by the host and wired via the
 *    TC_SECURE_DOC_DIR constant (wp-config.php) or tc_secure_doc_dir
 *    filter. Until then every upload is refused with patient-facing
 *    copy — there is NO fallback location, ever.
 *  - Never media_handle_upload() / the Media Library. Files are moved
 *    with move_uploaded_file() into the secure directory only.
 *  - Opaque random filenames (48 alnum chars + real extension); only the
 *    filename is stored (order meta, underscore-prefixed), resolved
 *    against candidate directories at read time — relocation-safe.
 *  - Real MIME validation via wp_check_filetype_and_ext(), size cap,
 *    per-IP+order rate limit, chmod 0640.
 *  - Gated view endpoint: nonce + manage_woocommerce capability +
 *    realpath containment + nocache + nosniff. Views are audit-logged.
 *
 * Wiring the real directory once Kinsta confirms is a one-line change:
 *   define( 'TC_SECURE_DOC_DIR', '/www/<site>_<id>/additional/secure-uploads/' );
 * Then verify empirically (CLAUDE.md §3): is_writable() true AND
 * curl -I https://<site>/additional/secure-uploads/test.jpg → 404.
 */
class TC_Secure_Docs {

	const META_FILENAME    = '_tc_id_document';
	const META_UPLOADED_AT = '_tc_id_uploaded_at';
	const META_ORIG_MIME   = '_tc_id_mime';

	const MAX_BYTES     = 10485760; // 10 MB
	const RATE_LIMIT    = 5;        // attempts…
	const RATE_WINDOW   = 900;      // …per 15 minutes per IP+order

	const ALLOWED_TYPES = [
		'jpg|jpeg' => 'image/jpeg',
		'png'      => 'image/png',
		'webp'     => 'image/webp',
		'pdf'      => 'application/pdf',
	];

	public static function init() {
		add_action( 'admin_post_tc_upload_id', [ __CLASS__, 'handle_upload' ] );
		add_action( 'admin_post_nopriv_tc_upload_id', [ __CLASS__, 'handle_upload' ] );
		add_action( 'admin_post_tc_view_id', [ __CLASS__, 'handle_view' ] );
		add_action( 'admin_notices', [ __CLASS__, 'unconfigured_notice' ] );
	}

	/**
	 * The secure storage directory, or WP_Error until configured.
	 * FAIL CLOSED — no fallback path exists anywhere in this class.
	 */
	public static function secure_doc_dir() {
		$dir = defined( 'TC_SECURE_DOC_DIR' ) ? TC_SECURE_DOC_DIR : '';
		$dir = apply_filters( 'tc_secure_doc_dir', $dir );

		if ( ! $dir || ! is_dir( $dir ) || ! wp_is_writable( $dir ) ) {
			return new WP_Error(
				'no_secure_storage',
				'Secure storage is not configured. Uploads are disabled.'
			);
		}
		return trailingslashit( $dir );
	}

	public static function is_configured() {
		return ! is_wp_error( self::secure_doc_dir() );
	}

	/**
	 * Candidate directories for the READ path (relocation-safe: files
	 * stored under a previous location stay readable after a move).
	 */
	private static function candidate_dirs() {
		$dirs = [];
		$main = self::secure_doc_dir();
		if ( ! is_wp_error( $main ) ) {
			$dirs[] = $main;
		}
		return apply_filters( 'tc_secure_doc_candidate_dirs', $dirs );
	}

	public static function has_document( WC_Order $order ) {
		return (bool) $order->get_meta( self::META_FILENAME );
	}

	/* ---------------------------------------------------------------
	 * Patient upload (admin-post, multipart form from the thank-you page)
	 * ------------------------------------------------------------- */

	public static function handle_upload() {
		$order_id  = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		$order_key = isset( $_POST['order_key'] ) ? wc_clean( wp_unslash( $_POST['order_key'] ) ) : '';
		$order     = $order_id ? wc_get_order( $order_id ) : false;

		// Same trust level as WooCommerce's own order-pay / order-received
		// pages: possession of the order key.
		if ( ! $order || ! hash_equals( $order->get_order_key(), $order_key ) ) {
			wp_die( 'Invalid order.', '', [ 'response' => 403 ] );
		}
		if ( ! wp_verify_nonce( $_POST['tc_id_nonce'] ?? '', 'tc_upload_id_' . $order_id ) ) {
			wp_die( 'Security check failed. Please go back and try again.', '', [ 'response' => 403 ] );
		}
		if ( ! class_exists( 'TC_Review_Status' ) || ! TC_Review_Status::is_treatment_order( $order ) ) {
			wp_die( 'Invalid order.', '', [ 'response' => 403 ] );
		}
		if ( $order->has_status( [ 'cancelled', 'refunded', 'failed' ] ) ) {
			self::redirect_back( $order, 'closed' );
		}

		// Rate limit per IP + order.
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$key = 'tc_idrl_' . md5( $ip . '|' . $order_id );
		$hits = (int) get_transient( $key );
		if ( $hits >= self::RATE_LIMIT ) {
			self::redirect_back( $order, 'rate' );
		}
		set_transient( $key, $hits + 1, self::RATE_WINDOW );

		// FAIL CLOSED before touching the file.
		$dir = self::secure_doc_dir();
		if ( is_wp_error( $dir ) ) {
			TC_Log::error( 'id_upload_refused_no_storage', [ 'order_id' => $order_id ] );
			self::redirect_back( $order, 'unavailable' );
		}

		$file = $_FILES['tc_id_file'] ?? null;
		if ( ! $file || ! isset( $file['tmp_name'] ) || UPLOAD_ERR_OK !== (int) $file['error'] || ! is_uploaded_file( $file['tmp_name'] ) ) {
			self::redirect_back( $order, 'nofile' );
		}
		if ( (int) $file['size'] > self::MAX_BYTES ) {
			self::redirect_back( $order, 'toobig' );
		}

		// Real content validation — never the extension alone.
		$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], self::ALLOWED_TYPES );
		if ( empty( $check['type'] ) || empty( $check['ext'] ) ) {
			self::redirect_back( $order, 'type' );
		}

		// Opaque random filename: no order number, patient ID or date.
		$filename = wp_generate_password( 48, false, false ) . '.' . $check['ext'];
		$dest     = $dir . $filename;

		if ( ! move_uploaded_file( $file['tmp_name'], $dest ) ) {
			TC_Log::error( 'id_upload_move_failed', [ 'order_id' => $order_id ] );
			self::redirect_back( $order, 'unavailable' );
		}
		@chmod( $dest, 0640 );

		// Replacing an earlier document? Remove the old file after the new
		// one is safely stored.
		$previous = (string) $order->get_meta( self::META_FILENAME );

		// Filename only — never the path (relocation-safe).
		$order->update_meta_data( self::META_FILENAME, $filename );
		$order->update_meta_data( self::META_UPLOADED_AT, time() );
		$order->update_meta_data( self::META_ORIG_MIME, $check['type'] );
		$order->save();
		$order->add_order_note( $previous ? 'Patient replaced their ID document.' : 'Patient uploaded their ID document.' );

		if ( $previous && $previous !== $filename ) {
			$old = self::resolve( $previous );
			if ( $old ) {
				@unlink( $old );
			}
		}

		TC_Log::info( 'id_uploaded', [ 'order_id' => $order_id, 'mime' => $check['type'] ] );

		self::redirect_back( $order, 'uploaded' );
	}

	private static function redirect_back( WC_Order $order, $flag ) {
		wp_safe_redirect( add_query_arg( 'tc_id', $flag, $order->get_checkout_order_received_url() ) );
		exit;
	}

	/* ---------------------------------------------------------------
	 * Staff viewing (gated endpoint — never a static URL)
	 * ------------------------------------------------------------- */

	public static function handle_view() {
		$order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;

		check_admin_referer( 'tc_view_id_' . $order_id );                 // nonce
		if ( ! current_user_can( 'manage_woocommerce' ) ) {               // pharmacy-staff capability
			wp_die( 'Not allowed.', '', [ 'response' => 403 ] );
		}

		$order = $order_id ? wc_get_order( $order_id ) : false;
		$filename = $order ? (string) $order->get_meta( self::META_FILENAME ) : '';
		if ( ! $order || ! $filename ) {
			wp_die( 'Not found.', '', [ 'response' => 404 ] );
		}

		$path = self::resolve( $filename );
		if ( ! $path ) {
			wp_die( 'Not found.', '', [ 'response' => 404 ] );
		}

		// Audit log: who viewed which document, when (CLAUDE.md §2).
		$user = wp_get_current_user();
		TC_Log::info( 'id_doc_viewed', [
			'order_id' => $order_id,
			'by'       => $user ? $user->user_login : 'unknown',
			'user_id'  => $user ? $user->ID : 0,
		] );

		$mime = (string) $order->get_meta( self::META_ORIG_MIME );
		nocache_headers();
		header( 'Content-Type: ' . ( $mime ?: 'application/octet-stream' ) );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Content-Disposition: inline; filename="patient-id.' . pathinfo( $filename, PATHINFO_EXTENSION ) . '"' );
		header( 'Content-Length: ' . filesize( $path ) );
		readfile( $path );
		exit;
	}

	/**
	 * Resolve a stored filename against candidate directories with
	 * realpath containment (blocks ../ traversal). Returns full path
	 * or false.
	 */
	private static function resolve( $filename ) {
		// Filenames we generate are strictly alnum + one extension dot.
		if ( ! preg_match( '/^[A-Za-z0-9]+\.[a-z0-9]{2,5}$/', $filename ) ) {
			return false;
		}
		foreach ( self::candidate_dirs() as $dir ) {
			$real_dir  = realpath( $dir );
			$candidate = realpath( $dir . $filename );
			if ( $real_dir && $candidate && strpos( $candidate, $real_dir ) === 0 && is_file( $candidate ) ) {
				return $candidate;
			}
		}
		return false;
	}

	public static function view_url( WC_Order $order ) {
		return wp_nonce_url(
			admin_url( 'admin-post.php?action=tc_view_id&order_id=' . $order->get_id() ),
			'tc_view_id_' . $order->get_id()
		);
	}

	/* ---------------------------------------------------------------
	 * Admin visibility
	 * ------------------------------------------------------------- */

	/** Persistent notice while storage is unconfigured (fail-closed state). */
	public static function unconfigured_notice() {
		if ( self::is_configured() || ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || strpos( (string) $screen->id, 'woocommerce' ) === false && strpos( (string) $screen->id, 'shop_order' ) === false && strpos( (string) $screen->id, 'wc-orders' ) === false ) {
			return;
		}
		// Per-check diagnostics: name the FIRST failing check so nobody has
		// to debug this blind. Evaluated live in the web PHP context — the
		// one that actually matters (CLI can pass while FPM fails).
		$dir = defined( 'TC_SECURE_DOC_DIR' ) ? TC_SECURE_DOC_DIR : '';
		$dir = apply_filters( 'tc_secure_doc_dir', $dir );

		if ( ! $dir ) {
			$detail = 'Failing check: <strong>the <code>TC_SECURE_DOC_DIR</code> constant is not defined</strong> in this web request. If it has been added to wp-config.php, the web server is still running the old cached copy — restart PHP (MyKinsta &rarr; Tools &rarr; Restart PHP).';
		} elseif ( ! is_dir( $dir ) ) {
			$detail = 'Failing check: <strong>web PHP cannot see the directory</strong> <code>' . esc_html( $dir ) . '</code> (it exists per SSH, so this is usually <code>open_basedir</code> not yet loaded by PHP-FPM) — restart PHP (MyKinsta &rarr; Tools &rarr; Restart PHP), or confirm the path with the host.';
		} else {
			$detail = 'Failing check: <strong>web PHP cannot write to</strong> <code>' . esc_html( $dir ) . '</code> — ask the host to make it writable by the site&rsquo;s PHP user.';
		}

		echo '<div class="notice notice-warning"><p><strong>Together Clinic:</strong> secure ID storage is not configured — patient ID uploads are disabled (fail-closed, per engineering standards). ' . wp_kses_post( $detail ) . '</p></div>';
	}

	/** Panel for the order screen (called from TC_Order_Admin). */
	public static function render_admin_panel( WC_Order $order ) {
		if ( ! TC_Review_Status::is_treatment_order( $order ) ) {
			return;
		}
		if ( self::has_document( $order ) ) {
			$at = (int) $order->get_meta( self::META_UPLOADED_AT );
			printf(
				'<div style="background:#ecfdf5;border-left:4px solid #10b981;padding:10px 14px;margin:12px 0;clear:both;"><strong>Patient ID on file.</strong> Uploaded %s. <a href="%s" target="_blank" rel="noopener">View document</a> <em>(access is audit-logged)</em></div>',
				$at ? esc_html( date_i18n( get_option( 'date_format' ) . ' H:i', $at ) ) : '&mdash;',
				esc_url( self::view_url( $order ) )
			);
		} else {
			echo '<div style="background:#fef2f2;border-left:4px solid #ef4444;padding:10px 14px;margin:12px 0;clear:both;"><strong>No patient ID on file.</strong> The patient has not uploaded an identity document yet. Confirm identity before approving.</div>';
		}
	}
}

<?php
/**
 * AT Health — Customer Note Email
 * Overrides: woocommerce/templates/emails/customer-note.php
 *
 * Sent when admin adds a note to an order (e.g. dose changes, clinical updates).
 * Tone: Professional, caring — clinical communication channel.
 *
 * @var WC_Order $order
 * @var string   $email_heading
 * @var string   $customer_note
 * @var bool     $sent_to_admin
 * @var bool     $plain_text
 * @var WC_Email $email
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$billing_first = $order->get_billing_first_name();
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p style="font-size: 16px; color: #111827; margin-bottom: 8px;">
    Hi <?php echo esc_html( $billing_first ); ?>,
</p>

<p style="color: #6b7280; margin-bottom: 24px;">
    Our team has an update regarding your order:
</p>

<!-- Note card -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 28px;">
    <tr>
        <td style="background-color: #f7f4f9; border-left: 4px solid #8e88d0; border-radius: 0 12px 12px 0; padding: 20px 24px;">
            <p style="margin: 0; font-size: 15px; color: #374151; line-height: 1.7;">
                <?php echo wp_kses_post( wpautop( wptexturize( $customer_note ) ) ); ?>
            </p>
        </td>
    </tr>
</table>

<!-- Order reference -->
<p style="font-size: 14px; color: #9ca3af; margin-bottom: 24px;">
    Order reference: <strong style="color: #6b7280;">#<?php echo esc_html( $order->get_order_number() ); ?></strong>
</p>

<!-- CTA -->
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td align="center">
            <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="ah-email-btn" style="display: inline-block; background-color: #8e88d0; color: #ffffff; font-size: 15px; font-weight: 600; text-decoration: none; padding: 14px 32px; border-radius: 10px;">
                View Your Order
            </a>
        </td>
    </tr>
</table>

<p style="color: #6b7280; font-size: 14px; text-align: center; margin-top: 16px;">
    If you have any questions about this update, just reply to this email or give us a call.
</p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>

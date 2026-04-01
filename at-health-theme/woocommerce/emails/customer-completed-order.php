<?php
/**
 * AT Health — Order Complete / Dispatched Email
 * Overrides: woocommerce/templates/emails/customer-completed-order.php
 *
 * Sent when order is marked "Completed" (medication dispatched).
 * Tone: Encouraging — "Your medication is on its way."
 *
 * @var WC_Order $order
 * @var string   $email_heading
 * @var bool     $sent_to_admin
 * @var bool     $plain_text
 * @var WC_Email $email
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$billing_first = $order->get_billing_first_name();
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<!-- Dispatch message -->
<p style="font-size: 16px; color: #111827; margin-bottom: 8px;">
    Great news, <?php echo esc_html( $billing_first ); ?>!
</p>

<p style="font-size: 16px; color: #4b5563; margin-bottom: 24px;">
    <strong style="color: #111827;">Your medication has been approved and dispatched.</strong> It's on its way to you now with tracked, discreet delivery.
</p>

<!-- Delivery info card -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f7f4f9; border-radius: 16px; margin-bottom: 28px;">
    <tr>
        <td style="padding: 24px 28px;">
            <p style="font-family: 'DM Serif Display', Georgia, serif; font-size: 18px; color: #111827; margin: 0 0 16px; font-weight: 400;">Delivery details</p>
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="padding: 6px 0;">
                        <p style="margin: 0; font-size: 14px; color: #6b7280;">
                            &#128230; <strong style="color: #374151;">Expected delivery:</strong> Within 48 hours
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 6px 0;">
                        <p style="margin: 0; font-size: 14px; color: #6b7280;">
                            &#128233; <strong style="color: #374151;">Packaging:</strong> Plain, unmarked — your privacy is protected
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 6px 0;">
                        <p style="margin: 0; font-size: 14px; color: #6b7280;">
                            &#128205; <strong style="color: #374151;">Delivering to:</strong>
                            <?php echo esc_html( $order->get_shipping_address_1() ? $order->get_formatted_shipping_address() : $order->get_formatted_billing_address() ); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Tips card -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #ffffff; border: 2px solid #8e88d0; border-radius: 16px; margin-bottom: 28px;">
    <tr>
        <td style="padding: 24px 28px;">
            <p style="font-family: 'DM Serif Display', Georgia, serif; font-size: 18px; color: #111827; margin: 0 0 16px; font-weight: 400;">Getting started with your medication</p>
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="padding: 4px 0;">
                        <p style="margin: 0; font-size: 14px; color: #4b5563;">&#10004;&#65039; Read the patient information leaflet included in your package</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;">
                        <p style="margin: 0; font-size: 14px; color: #4b5563;">&#10004;&#65039; Store your medication in the fridge (2–8°C) until use</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;">
                        <p style="margin: 0; font-size: 14px; color: #4b5563;">&#10004;&#65039; Inject on the same day each week for best results</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;">
                        <p style="margin: 0; font-size: 14px; color: #4b5563;">&#10004;&#65039; Mild nausea in the first week is normal and usually settles quickly</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px 0;">
                        <p style="margin: 0; font-size: 14px; color: #4b5563;">&#10004;&#65039; Contact us anytime if you have questions — we're here to help</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Order details -->
<p style="font-family: 'DM Serif Display', Georgia, serif; font-size: 18px; color: #111827; margin: 0 0 16px; font-weight: 400;">Your order summary</p>

<?php do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email ); ?>

<!-- CTA -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 28px;">
    <tr>
        <td align="center">
            <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="ah-email-btn" style="display: inline-block; background-color: #8e88d0; color: #ffffff; font-size: 15px; font-weight: 600; text-decoration: none; padding: 14px 32px; border-radius: 10px;">
                Track Your Order
            </a>
        </td>
    </tr>
</table>

<p style="color: #6b7280; font-size: 14px; text-align: center; margin-top: 16px;">
    We'll check in with you soon to see how you're getting on. You've got this! &#128170;
</p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>

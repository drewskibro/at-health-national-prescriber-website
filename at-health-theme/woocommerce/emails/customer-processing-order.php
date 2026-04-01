<?php
/**
 * AT Health — Order Confirmation Email (Processing)
 * Overrides: woocommerce/templates/emails/customer-processing-order.php
 *
 * Sent when a customer's order is marked as "Processing".
 * Tone: Warm, supportive — "Your journey starts now."
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

<!-- Welcome message -->
<p style="font-size: 16px; color: #111827; margin-bottom: 8px;">
    Hi <?php echo esc_html( $billing_first ); ?>,
</p>

<p style="font-size: 16px; color: #4b5563; margin-bottom: 24px;">
    <strong style="color: #111827;">You're on your journey now, and we're here to support you every step of the way.</strong>
</p>

<p style="color: #6b7280; margin-bottom: 24px;">
    We've received your order and our clinical team is reviewing it now. You'll receive a notification once your medication has been approved and dispatched.
</p>

<!-- What happens next -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f7f4f9; border-radius: 16px; margin-bottom: 28px;">
    <tr>
        <td style="padding: 24px 28px;">
            <p style="font-family: 'DM Serif Display', Georgia, serif; font-size: 18px; color: #111827; margin: 0 0 16px; font-weight: 400;">What happens next</p>
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td width="36" valign="top" style="padding-top: 2px;">
                        <div style="width: 24px; height: 24px; background-color: #8e88d0; border-radius: 50%; text-align: center; line-height: 24px; color: #fff; font-size: 12px; font-weight: 700;">1</div>
                    </td>
                    <td style="padding: 0 0 12px 8px;">
                        <p style="margin: 0; color: #374151; font-size: 14px;"><strong>Clinical review</strong> — A UK-registered prescriber will review your order within 24 hours.</p>
                    </td>
                </tr>
                <tr>
                    <td width="36" valign="top" style="padding-top: 2px;">
                        <div style="width: 24px; height: 24px; background-color: #8e88d0; border-radius: 50%; text-align: center; line-height: 24px; color: #fff; font-size: 12px; font-weight: 700;">2</div>
                    </td>
                    <td style="padding: 0 0 12px 8px;">
                        <p style="margin: 0; color: #374151; font-size: 14px;"><strong>Prescription approved</strong> — Once approved, your medication will be dispatched the same day.</p>
                    </td>
                </tr>
                <tr>
                    <td width="36" valign="top" style="padding-top: 2px;">
                        <div style="width: 24px; height: 24px; background-color: #8e88d0; border-radius: 50%; text-align: center; line-height: 24px; color: #fff; font-size: 12px; font-weight: 700;">3</div>
                    </td>
                    <td style="padding: 0 0 0 8px;">
                        <p style="margin: 0; color: #374151; font-size: 14px;"><strong>Delivered to your door</strong> — Tracked, discreet packaging within 48 hours.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Order details -->
<p style="font-family: 'DM Serif Display', Georgia, serif; font-size: 18px; color: #111827; margin: 0 0 16px; font-weight: 400;">Your order details</p>

<?php do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email ); ?>

<!-- Reassurance -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 28px; margin-bottom: 28px; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;">
    <tr>
        <td style="padding: 20px 0; text-align: center;">
            <p style="margin: 0; font-size: 13px; color: #9ca3af;">
                &#9989; GPhC Regulated &nbsp;&middot;&nbsp; &#128274; 100% Confidential &nbsp;&middot;&nbsp; &#128230; Discreet Delivery &nbsp;&middot;&nbsp; &#10060; Cancel Anytime
            </p>
        </td>
    </tr>
</table>

<!-- CTA -->
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td align="center" style="padding-bottom: 16px;">
            <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="ah-email-btn" style="display: inline-block; background-color: #8e88d0; color: #ffffff; font-size: 15px; font-weight: 600; text-decoration: none; padding: 14px 32px; border-radius: 10px;">
                View Your Order
            </a>
        </td>
    </tr>
</table>

<p style="color: #6b7280; font-size: 14px; text-align: center; margin-top: 8px;">
    Questions? Our team is here for you — just reply to this email or call us.
</p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
